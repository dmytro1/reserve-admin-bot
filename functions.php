<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 11/6/17
 * Time: 14:23
 */

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . '/vendor/kirki/kirki.php';
require_once __DIR__ . '/vendor/cmb2/init.php';
$loader = new \WPKit\Module\Loader();
$loader->load_modules();
new \Timber\Timber();

if ( is_admin() ) {

	require_once 'plugins/cmb2-field-ajax-search.php';
}
//var_dump($_POST);
//
//add_action( 'edit_user_profile', 'update_extra_profile_fields' );
//
//function update_extra_profile_fields( $user_id ) {
//	global $wpdb;
//
//	$wpdb->insert( 'bot_users_info', [
//		'id'           => $user_id,
//		'first_name'   => $_POST['first_name'],
//		'last_name'    => $_POST['last_name'],
//		'username'     => $_POST['user_login'],
//		'phone_number' => $_POST['phone_number'],
//	] );
//}

//echo 60 * \WPKit\Options\Option::get( 'time_interval_minutes' );

//add_action( 'customize_register', 'register' );
//
//
//function register( $wp_customize ) {
//
//
//	\Kirki::add_config( 'theme' );
//	\Kirki::add_config( 'option', array(
//		'capability'  => 'manage_options',
//		'option_type' => 'option',
//	) );
//
//	$panel_id = 'template_homepage_settings';
//
//	\Kirki::add_panel(
//		$panel_id, array(
//			'priority' => 50,
//			'title'    => esc_attr__( 'Homepage_ settings', 'ttl' ),
//			//'description' => esc_attr__( 'Contains sections for all kirki controls.', 'kirki' ),
//		)
//	);
//
//	\Kirki::add_section( 'homepage_next_lvl', array(
//		'title' => __( 'Next-level Talent experiences', 'ttl' ),
//		'panel' => $panel_id,
//		//'priority' => $priority,
//	) );
//
//	Kirki::add_field( 'homepage_next_lvl', array(
//		'type'        => 'dropdown-pages',
//		'settings'    => 'my_setting',
//		'label'       => esc_attr__( 'This is the label', 'textdomain' ),
//		'section'     => 'homepage_next_lvl',
//		'default'     => 42,
//		'priority'    => 10,
//	) );
//	var_dump( get_theme_mod( 'my_setting' ) );
//}

//add_filter( 'manage_edit-table_sortable_columns', 'sortable_table_columns', 200 );
//function sortable_table_columns( $columns ) {
//	$columns['persons']  = 'persons';
//	$columns['location'] = 'location';
//	$columns['statuses'] = 'status';
//
//	return $columns;
//}

