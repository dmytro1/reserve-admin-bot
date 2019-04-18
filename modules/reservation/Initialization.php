<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 1/31/18
 * Time: 17:27
 */

namespace modules\reservation;


use WPKit\Module\AbstractModuleInitialization;
use WPKit\PostType\PostType;

class Initialization extends AbstractModuleInitialization {

	const POST_TYPE = 'reservation';

	private static $post_type;
	private static $date_range;
	private static $date_range_args = [ 'today' => 'Today', 'upcoming' => 'Upcoming' ];

	private static $reserve_table = 'reserve_list';

	public function __construct() {

	}

	/**
	 * @throws \WPKit\Exception\WpException
	 */
	public function register_post_type() {
		self::$post_type  = $_GET['post_type'] ?? '';
		self::$date_range = $_GET['date_range'] ?? '';

		$post_type = new PostType( self::POST_TYPE, 'Reservation', [ 'menu_name' => 'Reservations' ] );
		$post_type->set_menu_icon( 'dashicons-welcome-write-blog' );
		$post_type->set_publicly_queryable( false );
		$post_type->set_rewrite( false );
		$post_type->set_use_archive( false );
		$post_type->set_public( false );
		$post_type->set_exclude_from_search( true );
		$post_type->set_show_in_nav_menus( false );
		$post_type->set_menu_position( 10 );
		$post_type->set_show_in_rest( true );

		$post_type->add_column( 'Reserve date', [ $this, 'get_column_data' ], true, 2 );
		$post_type->add_column( 'Reserve time', [ $this, 'get_column_data' ], true, 3 );
		$post_type->add_column( 'User', [ $this, 'get_column_data' ], true, 4 );
		$post_type->add_column( 'Reserve table', [ $this, 'get_column_data' ], true, 5 );
		$post_type->add_column( 'Reserve persons', [ $this, 'get_column_data' ], true, 6 );
		$post_type->add_column( 'Additional', [ $this, 'get_column_data' ], true, 7 );


	}

	public function add_action_admin_init() {

		remove_post_type_support( self::POST_TYPE, 'title' );
		remove_post_type_support( self::POST_TYPE, 'editor' );
		remove_post_type_support( self::POST_TYPE, 'thumbnail' );

		if ( self::$post_type == self::POST_TYPE ) {
			add_filter( 'months_dropdown_results', '__return_empty_array' );
		}

		add_filter( 'manage_posts_columns', [ $this, 'rename_title_column' ] );
		add_filter( 'pre_get_posts', __CLASS__ . '::pre_get_posts_custom_columns' );
		add_action( 'save_post', __CLASS__ . '::save_post', 10, 3 );
		add_action( 'delete_post', __CLASS__ . '::delete_post', 35, 1 );
		add_filter( 'views_edit-reservation', __CLASS__ . '::add_quicklinks' );
	}

	public function rename_title_column( $columns ) {

		if ( self::$post_type == self::POST_TYPE ) {
			unset( $columns['date'] );
			$columns['title'] = 'Order ID';
		}

		return $columns;
	}

	public function add_action_cmb2_admin_init() {
		$cmb_reserve_info = new_cmb2_box( array(
			'id'           => 'reserve_info',
			'title'        => 'Reserve information',
			'object_types' => array( self::POST_TYPE ),
		) );

		$cmb_reserve_info->add_field( array(
			'name'        => 'Reserve date',
//			'desc'     => 'field description',
			'id'          => 'reserve_date',
			'type'        => 'text_date',
			'date_format' => 'Y-m-d',
		) );

		$cmb_reserve_info->add_field( array(
			'name'        => 'Reserve time',
//			'desc'     => 'field description',
			'id'          => 'reserve_time',
			'type'        => 'text_time',
			'time_format' => 'H:i',
		) );

		$cmb_reserve_info->add_field( array(
			'name'       => 'User name',
//			'desc'    => 'field description (optional)',
//			'default' => 'qqqq',
			'id'         => 'reserve_user',
			'type'       => 'user_ajax_search',
//			'multiple'   => true,
//			'limit'      => 1,
			'query_args' => array(
				'role'           => 'user',
				'search_columns' => array( 'user_login', 'display_name' ) // TODO: search phone_number
			)
		) );

		$cmb_reserve_info->add_field( array(
			'name'    => 'Select table',
			'id'      => 'reserve_table',
			'type'    => 'select',
			'options' => $this->get_tables_select()
		) );

		$cmb_reserve_info->add_field( array(
			'name'            => 'Persons',
			'id'              => 'reserve_persons',
			'type'            => 'text',
			'attributes'      => array(
				'type'    => 'number',
				'pattern' => '\d*',
				'min'     => '1',
				'max'     => '10',
				'step'    => '1',
			),
			'sanitization_cb' => 'absint',
			'escape_cb'       => 'absint',
		) );

		$cmb_reserve_info->add_field( array(
			'name' => 'Additional',
			'desc' => 'Additional info',
			'id'   => 'reserve_additional',
			'type' => 'textarea_small'
		) );
//		var_dump( get_post_meta( '55', 'reserve_date', true ) );
	}

	public function get_tables_select() {
		$options = [ '' => '--Select table--' ];
		$tables  = get_posts( [
			'post_type'      => 'table',
			'status'         => 'publish',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => - 1
		] );

		foreach ( $tables as $table ) {
			$options[ $table->ID ] = $table->post_title;
		}

		return $options;
	}

	public function get_column_data( $column ) {
		if ( $column == 'reservedate' ) {
			echo get_post_meta( get_the_ID(), 'reserve_date', true );
		} elseif ( $column == 'reservetime' ) {
			echo get_post_meta( get_the_ID(), 'reserve_time', true );
		} elseif ( $column == 'user' ) {
			$user_id = get_post_meta( get_the_ID(), 'reserve_user', true );
			echo get_userdata( $user_id ) ? get_userdata( $user_id )->first_name . ' ' . get_userdata( $user_id )->last_name : '<i>no user</i>';
		} elseif ( $column == 'reservetable' ) {
			$table_id = get_post_meta( get_the_ID(), 'reserve_table', true );
			if ( $table_id ) {
				echo get_the_title( $table_id );
			}
		} elseif ( $column == 'reservepersons' ) {
			echo get_post_meta( get_the_ID(), 'reserve_persons', true );
		} elseif ( $column == 'additional' ) {
			echo get_post_meta( get_the_ID(), 'reserve_additional', true );
		}
	}

	/**
	 * @param $wp_query \WP_Query
	 */
	public static function pre_get_posts_custom_columns( $wp_query ) {

//		global $wp_query;
//		var_dump($wp_query);

		if ( ( $wp_query->is_main_query() ) && is_admin() && $wp_query->query['post_type'] == self::POST_TYPE ) {


//			$wp_query->set('posts_per_page', 10);

//			$wp_query->set( 'orderby', 'date' );
//			$wp_query->set( 'meta_query', array(
//				array(
//					'key'     => 'reserve_date',
//					'value'   => date( 'Y-m-d' ),
//					'compare' => '>=',
//					'type'    => 'DATE'
//				)
//			) );


			$date_range = self::$date_range;

			if ( $date_range ) {
				$compare = ( $date_range == 'today' ) ? '==' : '>=';

				$wp_query->set( 'meta_query', array(
					array(
						'key'     => 'reserve_date',
						'value'   => date( 'Y-m-d' ),
						'compare' => $compare,
						'type'    => 'DATE'
					)
				) );
			}


			$orderby = $wp_query->get( 'orderby' );
			if ( 'menu_order title' == $orderby ) {
				$wp_query->set( 'orderby', 'date' );
				$wp_query->set( 'order', 'desc' );
			} elseif ( 'title' == $orderby ) {
				$wp_query->set( 'orderby', 'title_number' );
			} elseif ( 'user' == $orderby ) {
				$wp_query->set( 'meta_key', 'reserve_user' );
				$wp_query->set( 'orderby', 'meta_value' );
			} elseif ( 'reservedate' == $orderby ) {
				$wp_query->set( 'meta_key', 'reserve_date' );
				$wp_query->set( 'orderby', 'meta_value' );
			} elseif ( 'reservetime' == $orderby ) {
				$wp_query->set( 'meta_key', 'reserve_time' );
				$wp_query->set( 'orderby', 'meta_value' );
			} elseif ( 'reservetable' == $orderby ) {
				$wp_query->set( 'meta_key', 'reserve_table' );
				$wp_query->set( 'orderby', 'meta_value' );
			} elseif ( 'reservepersons' == $orderby ) {
				$wp_query->set( 'meta_key', 'reserve_persons' );
				$wp_query->set( 'orderby', 'meta_value_num' );
			} elseif ( 'reserveadditional' == $orderby ) {
				$wp_query->set( 'meta_key', 'reserve_additional' );
				$wp_query->set( 'orderby', 'meta_value' );
			}

		}

	}

	public static function save_post( $post_id, $post, $update ) {

		// if called by autosave, then bail here
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// if this "post" post type?
		if ( $post->post_type != self::POST_TYPE ) {
			return;
		}

		// does this user have permissions?
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$my_post = array(
			'ID'         => $post_id,
			'post_title' => $post_id,
		);

		if ( $post->post_title == "Auto Draft" || $post->post_title == "" ) {
			wp_update_post( $my_post );
		}

//		var_dump( $post );


		if ( $post->post_status != 'auto-draft' ) {
			self::save_in_custom_table( $post_id, $post->post_status, $update );
		}


	}

	public static function save_in_custom_table( $post_id, $post_status, $update ) {
		global $wpdb;

		$reserve_table = self::$reserve_table;

		$exists = $wpdb->get_var( "SELECT id FROM {$reserve_table} WHERE id = {$post_id}" );

		if ( $exists ) {

			// Return if save post in post editor
			$screen = get_current_screen();

			if ( $_GET['action'] == 'trash' || $_GET['action'] == 'untrash' || ! isset( $screen ) ) {
				$wpdb->update( $reserve_table,
					[
						'reserve_status' => $post_status,
						'last_update'    => current_time( 'mysql' )
					],
					[
						'id' => $post_id,
					] );
			} else {
				$wpdb->update( $reserve_table,
					[
						'user_id'            => $_POST['reserve_user'],
						'reserve_date'       => $_POST['reserve_date'],
						'reserve_time'       => $_POST['reserve_time'],
						'table_id'           => $_POST['reserve_table'],
						'reserve_persons'    => $_POST['reserve_persons'],
						'reserve_additional' => $_POST['reserve_additional'],
						'reserve_status'     => $post_status,
						'last_update'        => current_time( 'mysql' )
					],
					[
						'id' => $post_id,
					] );
			}

		} else {
			$wpdb->insert( $reserve_table, [
				'id'                 => $post_id,
				'user_id'            => $_POST['reserve_user'],
				'reserve_date'       => $_POST['reserve_date'],
				'reserve_time'       => $_POST['reserve_time'],
				'table_id'           => $_POST['reserve_table'],
				'reserve_persons'    => $_POST['reserve_persons'],
				'reserve_additional' => $_POST['reserve_additional'],
				'reserve_status'     => $post_status,
			] );
		}

	}

	public static function delete_post( $post_id ) {
		global $wpdb;

		$post = get_post( $post_id );
		if ( $post->post_type != self::POST_TYPE ) {
			return;
		}

		$reserve_table = self::$reserve_table;

		$wpdb->delete(
			$reserve_table,
			[ 'id' => $post_id ]
		);

	}

	public static function add_quicklinks( $views ) {

		if ( is_admin() && self::$post_type == self::POST_TYPE ) {

			unset( $views['publish'] );
//			unset( $views['all'] );

			foreach ( self::$date_range_args as $arg => $title ) {
				$get_args = [
//				'post_status' => 'publish',
					'post_type'  => self::POST_TYPE,
					'date_range' => $arg,
				];

				$link = "edit.php?" . build_query( $get_args );

				$query = array(
					'post_type'  => self::POST_TYPE,
					'meta_query' => array(
						array(
							'key'     => 'reserve_date',
							'value'   => date( 'Y-m-d' ),
							'compare' => ( $arg == 'today' ) ? '==' : '>=',
							'type'    => 'DATE'
						),
					)
				);
				$posts = new \WP_Query( $query );

				$class = ( self::$date_range == $arg ) ? ' class="current"' : '';

				$views[ $arg ] = '<a href=' . $link . $class . '>' . $title . '<span class="count"> (' . $posts->found_posts . ')</span></a>';

			}

			return $views;

		}

		return $views;

	}


}

