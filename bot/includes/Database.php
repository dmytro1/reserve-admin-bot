<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 7/18/18
 * Time: 16:36
 */

//namespace bot\includes;

include_once 'PrettyResponse.php';

use WPKit\Options\Option;

class Database {
	protected $connection;

	static $users_table = 'bot_users_info';
	static $reserve_table = 'reserve_list';

//	public function __construct( $servername, $username, $password, $dbname ) {
//		$this->connection = new mysqli( $servername, $username, $password, $dbname );
//	}
//
//	public function check_connection() {
//		// Check connection
//		if ( $this->connection->connect_error ) {
//			die( "Connection failed: " . mysqli_connect_error() );
//		}
//
//		return true;
//	}

	public static function create_table() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$users_table = self::$users_table;

		$sql = "CREATE TABLE {$users_table} (
			    id int(6) UNSIGNED NOT NULL AUTO_INCREMENT,
				chat_id int(11) DEFAULT NULL,
			    first_name varchar(30) NOT NULL,
				last_name varchar(30) NOT NULL,
				username varchar(50) DEFAULT NULL,
				phone_number BIGINT, 
				last_update timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (id)
			) $charset_collate;";

		$reserve_table = self::$reserve_table;

		$sql2 = "CREATE TABLE {$reserve_table} (
				id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
				user_id INT (6) UNSIGNED NOT NULL,
				reserve_date DATE,
				reserve_time TIME,
				table_id INT (6),
				reserve_persons INT (2),
				reserve_additional VARCHAR(30),
				reserve_status VARCHAR (10),
				last_update TIMESTAMP,
				KEY user_id (user_id),
				CONSTRAINT reserve_list_ibfk_1 FOREIGN KEY (user_id) REFERENCES {$users_table} (id)
			) $charset_collate;";

		self::create_table_query( $users_table, $sql );
		self::create_table_query( $reserve_table, $sql2 );

//		$wpdb->close();
	}

	private static function create_table_query( $table, $sql ) {
		global $wpdb;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) != $table ) {

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			$message = "Table '{$table}' created successfully";
			$class   = 'notice notice-error';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );

		} else {
			$message = "<b>Debug:</b> Error creating table: '{$table}'. Table already exists";
			$class   = 'notice notice-error';

			// Show alert in wp-admin
//			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}

	public static function insert_new_user( $first_name, $last_name, $username, $chat_id ) {

		global $wpdb;

		$users_table = self::$users_table;

		$chats_id = $wpdb->get_results( "SELECT chat_id FROM {$users_table}" );

		$chats_id_arr = PrettyResponse::convert_obj_to_array( $chats_id, 'chat_id' );

		if ( in_array( $chat_id, $chats_id_arr ) ) {
			return "<b>Debug:</b> <i>This user already exists</i>";
		} else {

			$reply = '';

			$user_id = self::new_user_registration( $username, $first_name, $last_name, $chat_id );

			if ( ! is_numeric( $user_id ) ) {
				$reply .= $user_id;
			}

			$insert = $wpdb->insert( $users_table, [
				'id'         => $user_id,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'username'   => $username,
				'chat_id'    => $chat_id,
			] );

			if ( $insert ) {
				$reply .= "<i>Debug: New user added</i>";

				return $reply;
			}

			return 'error insert_new_user()';
		}
	}

	public static function new_user_registration( $username, $first_name, $last_name, $chat_id ) {

		$user_data = array(
			'user_login' => $username,
			'user_pass'  => wp_generate_password(),
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => 'user',
		);

		$user_id = wp_insert_user( $user_data );

		update_user_meta( $user_id, 'chat_id', $chat_id );

		if ( ! is_wp_error( $user_id ) ) {
			return $user_id;
		} else {
			return $user_id->get_error_message();
		}
	}

	public static function get_existing_reserves( $chat_id ) {

		$user_reservations = self::get_user_reservations( $chat_id );

		if ( empty( $user_reservations ) ) {
			$message = "<b>Debug:</b> <i>You don't have reservations now</i>";

			return $message;
		}

		$user_reservations_arr = [];
		foreach ( $user_reservations as $reservation ) {
			$user_reservations_arr[] = [ 'date' => $reservation->reserve_date, 'time' => $reservation->reserve_time ];
		}

		$message = self::build_message( $user_reservations_arr );

		return $message;
	}

	private static function build_message( $user_reservations ) {
		$message = "Existing reservations 📋:\r\n";

		for ( $i = 0; $i < count( $user_reservations ); $i ++ ) {

			$date     = $user_reservations[ $i ]['date'];
			$time     = $user_reservations[ $i ]['time'];
			$time_cut = substr( $time, 0, - 3 );

			$message .= $i + 1 . ') ';
			$message .= "<b>" . $date . "</b> ";
			$message .= "<b>" . $time_cut . "</b>";

			$date_format_ = str_replace( '-', '_', $date );

			$message .= ' /' . "discard_{$date_format_} ❎" . "\r\n";
		}

		return $message;
	}

	private static function get_user_id( $chat_id ) {
		global $wpdb;

		$users_table = self::$users_table;

		$user_id = $wpdb->get_var( "SELECT id FROM {$users_table} WHERE chat_id = $chat_id" );

		return $user_id;
	}

	public static function get_reserve_id( $chat_id ) {
		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id = self::get_user_id( $chat_id );

		$reserve_id = $wpdb->get_var( "SELECT id FROM {$reserve_table} WHERE user_id = $user_id ORDER BY last_update DESC LIMIT 1" );

		return $reserve_id;
	}

	public static function set_reserve_date_draft( $date, $chat_id ) {

		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id = self::get_user_id( $chat_id );

//		$user_reservations = $wpdb->get_results( "SELECT reserve_date FROM {$reserve_table} WHERE user_id = $user_id" );
		$user_reservations = self::get_user_reservations( $chat_id );

		$user_reservations_arr = PrettyResponse::convert_obj_to_array( $user_reservations, 'reserve_date' );

		if ( $user_reservations_arr && in_array( $date, $user_reservations_arr ) ) {
//			echo 'draft existing reserve';

			$reserve_id = self::get_reserve_id( $chat_id );

			wp_update_post( [
				'ID'          => $reserve_id,
				'post_status' => 'draft',
			] );

			update_post_meta( $reserve_id, 'reserve_table', '' );

			$wpdb->update( $reserve_table,
				[
					'table_id'       => null,
					'reserve_status' => 'draft',
					'last_update'    => current_time( 'mysql' )
				],
				[
					'user_id'      => $user_id,
					'reserve_date' => $date,
				] );

			return true;

		} else {
//			echo 'insert new reserve';

			$new_post_id = wp_insert_post( [ 'post_type' => 'reservation' ] );

			wp_update_post( [
				'ID'         => $new_post_id,
				'post_title' => $new_post_id,
			] );
			update_post_meta( $new_post_id, 'reserve_user', $user_id );
			update_post_meta( $new_post_id, 'reserve_date', $date );


			$insert = $wpdb->insert( $reserve_table, [
				'id'             => $new_post_id,
				'user_id'        => $user_id,
				'reserve_date'   => $date,
				'reserve_status' => 'draft',
			] );


			if ( $insert ) {
				return "New draft reservation created ($date)";
			}

			return 'error with set_reserve_date_draft() method';
		}
	}

	private static function get_user_reservations( $chat_id ) {
		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id = self::get_user_id( $chat_id );

		$user_reservations = $wpdb->get_results( "SELECT reserve_date, reserve_time FROM {$reserve_table} 
														 WHERE user_id = $user_id 
														 AND reserve_date >= CURRENT_DATE
														 AND reserve_status IN ( 'publish', 'draft' )
														 ORDER BY reserve_date ASC" );


		return $user_reservations;
	}

	public static function update_reserve_time( $time, $chat_id ) {

		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id    = self::get_user_id( $chat_id );
		$reserve_id = self::get_reserve_id( $chat_id );

		update_post_meta( $reserve_id, 'reserve_time', $time );

		$update = $wpdb->update( $reserve_table,
			[
				'reserve_time' => $time,
				'last_update'  => current_time( 'mysql' )
			],
			[
				'id'             => $reserve_id,
				'user_id'        => $user_id,
				'reserve_status' => 'draft'
			] );

		if ( $update ) {
			return "Reservation time added ($time)";
		}

		return 'error with set_reserve_time() method';
	}

	public static function update_reserve_persons( $persons, $chat_id ) {

		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id    = self::get_user_id( $chat_id );
		$reserve_id = self::get_reserve_id( $chat_id );

		update_post_meta( $reserve_id, 'reserve_persons', $persons );

		$update = $wpdb->update( $reserve_table,
			[
				'reserve_persons' => $persons,
				'last_update'     => current_time( 'mysql' )
			],
			[
				'id'             => $reserve_id,
				'user_id'        => $user_id,
				'reserve_status' => 'draft'
			] );

		if ( $update ) {
			return "Reservation for <b>$persons</b> persons";
		}

		return 'error with update_reserve_persons() method';
	}

	public static function update_table_id( $persons, $chat_id ) {

		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id    = self::get_user_id( $chat_id );
		$reserve_id = self::get_reserve_id( $chat_id );

		$table_ids = self::get_tables_posts_ids( $persons );

		$available_tables = self::check_available_tables( $table_ids, $reserve_id );

		if ( $available_tables === false ) {
			return false;
		}

		update_post_meta( $reserve_id, 'reserve_table', $available_tables[0] );

		$update = $wpdb->update( $reserve_table,
			[ 'table_id' => $available_tables[0] ],
			[
				'id'             => $reserve_id,
				'user_id'        => $user_id,
				'reserve_status' => 'draft'
			] );

		if ( $update ) {
			return "<b>Debug: </b><i>Table ID: $available_tables[0] added to DB</i>";
		}

		return "<b>Debug: </b><i>Table ID the same</i>";
	}

	private static function check_available_tables( $table_ids, $reserve_id ) {

		global $wpdb;

		$reserve_table = self::$reserve_table;

		$current_reserve_draft = $wpdb->get_row( "SELECT * FROM {$reserve_table} WHERE id = $reserve_id" );

		$time_interval_min = Option::get( 'time_interval_minutes' ) ?? 90;
		$time_interval     = 60 * $time_interval_min;

		$reserve_time_offset_before = date( 'H:i', strtotime( $current_reserve_draft->reserve_time ) - $time_interval );
		$reserve_time_offset_after  = date( 'H:i', strtotime( $current_reserve_draft->reserve_time ) + $time_interval );

		$reservations = $wpdb->get_results( "SELECT * FROM {$reserve_table} 
										   WHERE reserve_date = '$current_reserve_draft->reserve_date' 
										   AND reserve_time >= '$reserve_time_offset_before' 
										   AND reserve_time < '$reserve_time_offset_after'
										   AND reserve_status = 'publish' " );

		$reserved_tables = PrettyResponse::convert_obj_to_array( $reservations, 'table_id' );

		$available_tables = array_diff( $table_ids, $reserved_tables );

		if ( $available_tables ) {

			return array_values( $available_tables );
		}

		return false;
	}

	private static function get_tables_posts_ids( $persons ) {
		$query = array(
			'post_type'   => 'table',
			'post_status' => 'publish',
			'order'       => 'ASC',
			'orderby'     => 'table_info_persons',
			'meta_query'  => array(
				array(
					'key'     => 'table_info_persons',
					'value'   => $persons,
					'compare' => '>=',
					'type'    => 'NUMERIC'
				)
			)
		);

		$q_result = new Timber\PostQuery( $query );
		$posts    = $q_result->get_posts();

		$table_ids = PrettyResponse::convert_obj_to_array( $posts, 'ID' );

		return $table_ids;
	}

	public static function get_reserve_details( $chat_id ) {

		global $wpdb;

		$reserve_table = self::$reserve_table;

		$reserve_id = self::get_reserve_id( $chat_id );

		$select = $wpdb->get_row( "SELECT * FROM {$reserve_table} WHERE id = $reserve_id" );

//		print_r( $select );

		if ( $select ) {
			$message = "Your reservation details 🗒:";
			$message .= "\r\nID: " . $select->id;
			$message .= "\r\n📆️ Reserve date: " . $select->reserve_date;
			$message .= "\r\n🕑 Reserve time: " . $select->reserve_time;
			$message .= "\r\n👪 Persons: " . $select->reserve_persons;

			return $message;
		}

		return 'error get_reserve_details() method';
	}

	public static function update_submit_reserve_with_phone( $chat_id, $phone_number = '' ) {
		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id    = self::get_user_id( $chat_id );
		$reserve_id = self::get_reserve_id( $chat_id );

		if ( $phone_number ) {
			self::update_phone_number( $user_id, $phone_number );
		}

		wp_update_post( [
			'ID'          => $reserve_id,
			'post_status' => 'publish'
		] );

		$update = $wpdb->update( $reserve_table,
			[
				'reserve_status' => 'publish'
			],
			[
				'id'      => $reserve_id,
				'user_id' => $user_id,
			] );

		if ( $update ) {
			return "✅ Table for you was reserved!";
		}

		return 'error update_submit_reserve() method';
	}

	private static function update_phone_number( $user_id, $phone_number ) {

		global $wpdb;

		$users_table = self::$users_table;

		update_user_meta( $user_id, 'phone_number', $phone_number );

		$update = $wpdb->update( $users_table,
			[
				'phone_number' => $phone_number,
				'last_update'  => current_time( 'mysql' )
			],
			[
				'id' => $user_id,
			] );

		if ( $update ) {
			return true;
		}

		return 'Error update_phone_number() method';
	}

	public static function delete_reserve( $chat_id, $date = null ) {
		global $wpdb;

		$reserve_table = self::$reserve_table;

		$user_id = self::get_user_id( $chat_id );

		if ( isset( $date ) ) {
			$reserve_id = $wpdb->get_var( "SELECT id FROM {$reserve_table} WHERE user_id = $user_id AND reserve_date = '$date'" );
		} else {
			$reserve_id = self::get_reserve_id( $chat_id );
		}

		wp_delete_post( $reserve_id );

		$delete = $wpdb->delete( $reserve_table,
			[
				'id'      => $reserve_id,
				'user_id' => $user_id,
			] );

		if ( $delete ) {
			return "Reservation was deleted";
		}

		return 'Error with delete_reserve() method';
	}

}