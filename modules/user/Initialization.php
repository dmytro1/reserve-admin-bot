<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 11/20/18
 * Time: 16:34
 */

namespace modules\user;

use WPKit\Module\AbstractInitialization;

class Initialization extends AbstractInitialization {

	const POST_TYPE = 'user';

	static $users_table = 'bot_users_info';

	public function add_action_cmb2_admin_init() {
		$cmb_user_info = new_cmb2_box( array(
			'id'           => 'user_info',
			'title'        => 'User information',
			'object_types' => array( self::POST_TYPE ),
			'context'      => 'normal', //  'normal', 'advanced', or 'side'
			'priority'     => 'high',  //  'high', 'core', 'default' or 'low'
			'show_names'   => true,
		) );

		$cmb_user_info->add_field( array(
			'id'   => 'chat_id',
			'type' => 'hidden',
		) );

		$cmb_user_info->add_field( array(
			'name' => 'Phone number',
			'id'   => 'phone_number',
			'type' => 'text_medium',
//			'attributes' => array(
//				'type'    => 'number',
//				'pattern' => '\d*',
//				'min'     => '1',
//			),
//			'sanitization_cb' => 'absint',
//			'escape_cb'       => 'absint',
		) );
	}

	public function add_action_admin_init() {

		add_action( 'after_switch_theme', array( $this, 'add_user_role' ) );

		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
//		remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );

//		add_action( 'personal_options_update', array( $this, 'save_user' ) );
//		add_action( 'edit_user_profile_update', array( $this, 'save_user' ) );
//		add_action( 'user_register', __CLASS__, '::save_user' );
//		var_dump($_POST);
	}


	public function add_user_role() {

		add_role( 'user', 'User',
			array(
				'read'         => false,
				'edit_posts'   => false,
				'delete_posts' => false,
				'upload_files' => false,
			)
		);

	}

	public function add_action_user_register( $user_id ) {
		global $wpdb;

		$wpdb->insert( self::$users_table, [
			'id'           => $user_id,
			'first_name'   => $_POST['first_name'],
			'last_name'    => $_POST['last_name'],
			'username'     => $_POST['user_login'],
			'phone_number' => $_POST['phone_number'],
		] );
	}

	public function add_action_edit_user_profile_update( $user_id ) {
		global $wpdb;

		$wpdb->update( self::$users_table,
			[
				'first_name'   => $_POST['first_name'],
				'last_name'    => $_POST['last_name'],
				'phone_number' => $_POST['phone_number'],
			],
			[ 'id' => $user_id ] );
	}

	public function add_action_delete_user( $user_id ) {
		global $wpdb;

		$wpdb->delete( self::$users_table,
			[ 'id' => $user_id ] );
	}


}