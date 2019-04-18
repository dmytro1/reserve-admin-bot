<?php

namespace modules\theme;

use WPKit\AdminPage\OptionPage;
use WPKit\Module\AbstractThemeInitialization;
use WPKit\Options\OptionBox;

require_once get_template_directory() . "/bot/includes/Database.php";

class Initialization extends AbstractThemeInitialization {


	public function add_action_admin_init() {

		if ( ! mysqli_connect_error() ) {
			\Database::create_table();
		}

		// Прячем все сообщения обновлений
		add_action( 'admin_menu', function () {
			remove_action( 'admin_notices', 'update_nag', 3 );
		} );

		// Скрываем версию
		remove_action( 'wp_head', 'wp_generator' );

		// WordPress вставляет в исходный код сообщений в RSS
		// Запретим ему это делать

		add_filter( 'the_generator', function ( $g ) {
			return '';
		} );

		remove_all_filters( 'admin_footer_text' );
		add_filter( 'admin_footer_text', function () {
			echo 'Developed by <a href="#">DmytroV</a>';
		} );


		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'index_rel_link' );
		remove_action( 'wp_head', 'parent_post_rel_link', 10 );
		remove_action( 'wp_head', 'start_post_rel_link', 10 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10 );

		if ( ! current_user_can( 'publish_posts' ) ):
			show_admin_bar( false );
		endif;


		add_action( 'wp_before_admin_bar_render', function () {

			/** @var \WP_Admin_Bar $wp_admin_bar */
			global $wp_admin_bar;

			$wp_admin_bar->remove_menu( 'wp-logo' ); // Remove the WordPress logo
			$wp_admin_bar->remove_menu( 'about' ); // Remove the about WordPress link
			$wp_admin_bar->remove_menu( 'wporg' ); // Remove the WordPress.org link
			$wp_admin_bar->remove_menu( 'documentation' ); // Remove the WordPress documentation link
			$wp_admin_bar->remove_menu( 'support-forums' ); // Remove the support forums link
			$wp_admin_bar->remove_menu( 'feedback' ); // Remove the feedback link
			// $wp_admin_bar->remove_menu('site-name');        // Remove the site name menu
//			$wp_admin_bar->remove_menu( 'view-site' ); // Remove the view site link
			$wp_admin_bar->remove_menu( 'updates' ); // Remove the updates link
//			$wp_admin_bar->remove_menu( 'comments' ); // Remove the comments link
//			$wp_admin_bar->remove_menu( 'new-content' ); // Remove the content link
//			$wp_admin_bar->remove_menu( 'w3tc' );             // If you use w3 total cache remove the performance link
			//$wp_admin_bar->remove_menu('my-account');       // Remove the user details tab
		} );


		add_filter( 'login_headerurl', function ( $url ) {
			return home_url();
		} );

		add_filter( 'login_headertitle', function ( $url ) {
			return home_url();
		} );


		// Hide static front page element from customizer
		add_action( 'customize_register', function ( \WP_Customize_Manager $wp_customizer ) {
			$wp_customizer->remove_section( 'static_front_page' );
		} );


	}

	public function add_action_admin_menu() {

		// Remove Post page
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'edit.php?post_type=page' );
		remove_menu_page( 'edit-comments.php' );

		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'core' ); // Comments Widget
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );  // Incoming Links Widget
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );         // Plugins Widget
////
////		// Remove_meta_box('dashboard_quick_press', 'dashboard', 'core');  // Quick Press Widget
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'core' );   // Recent Drafts Widget
		remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );         //
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );       //
//
////		// Removing plugin dashboard boxes
		remove_meta_box( 'yoast_db_widget', 'dashboard', 'normal' );         // Yoast's SEO Plugin Widget
	}

	public function add_action_login_enqueue_scripts() {
		$logo = Functions::get_logo_url();
		?>
        <link rel="stylesheet" id="custom_wp_admin_css"
              href="<?php echo get_stylesheet_directory_uri() . '/assets/css/custom-branding.css'; ?>" type="text/css"
              media="all"/>

        <style type="text/css">
            #login h1 a, .login h1 a {
                background: url('<?php echo $logo;?>') no-repeat center;
                width: auto;
                height: 70px;
            }
        </style>
		<?php
	}

	public function add_action_widgets_init() {
		unregister_widget( 'WP_Widget_Pages' );
		unregister_widget( 'WP_Widget_Calendar' );
		unregister_widget( 'WP_Widget_Archives' );
		unregister_widget( 'WP_Widget_Links' );
		unregister_widget( 'WP_Widget_Meta' );
		unregister_widget( 'WP_Widget_Search' );
//		unregister_widget( 'WP_Widget_Text' );
		unregister_widget( 'WP_Widget_Categories' );
		unregister_widget( 'WP_Widget_Recent_Posts' );
		unregister_widget( 'WP_Widget_Recent_Comments' );
		unregister_widget( 'WP_Widget_RSS' );
		unregister_widget( 'WP_Widget_Tag_Cloud' );
		unregister_widget( 'WP_Nav_Menu_Widget' );


		// Woocommerce
//		unregister_widget( 'WC_Widget_Products' );
//		unregister_widget( 'WC_Widget_Recent_Products' );
//		unregister_widget( 'WC_Widget_Featured_Products' );
//		unregister_widget( 'WC_Widget_Product_Categories' );
//		unregister_widget( 'WC_Widget_Product_Tag_Cloud' );
//		unregister_widget( 'WC_Widget_Cart' );
//		unregister_widget( 'WC_Widget_Layered_Nav' );
//		unregister_widget( 'WC_Widget_Layered_Nav_Filters' );
//		unregister_widget( 'WC_Widget_Price_Filter' );
//		unregister_widget( 'WC_Widget_Product_Search' );
//		unregister_widget( 'WC_Widget_Top_Rated_Products' );
//		unregister_widget( 'WC_Widget_Recent_Reviews' );
//		unregister_widget( 'WC_Widget_Recently_Viewed' );
//		unregister_widget( 'WC_Widget_Best_Sellers' );
//		unregister_widget( 'WC_Widget_Onsale' );
//		unregister_widget( 'WC_Widget_Random_Products' );

		//QTranslate-X
//		unregister_widget( 'qTranslateXWidget' );

		// WP User Avatar
		unregister_widget( 'WP_User_Avatar_Profile_Widget' );
	}

	public function add_action_wp_dashboard_setup() {
		global $wp_meta_boxes;
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] ); // Прямо сейчас
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'] ); // Плагины
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links'] ); // Входящие ссылки
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['wpseo-dashboard-overview'] ); // Входящие ссылки
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['jetpack_summary_widget'] ); // Входящие ссылки
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['blc_dashboard_widget'] ); // Входящие ссылки
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['woocommerce_dashboard_status'] ); // Входящие ссылки
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments'] ); // Свежие комментарии
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] ); // Активность
		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] ); // Быстрая публикация
//		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts'] ); // Свежие черновики
//		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary'] ); // Блог WordPress
//		unset( $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'] ); // Другие новости WordPress
//		unset( $wp_meta_boxes['dashboard']['normal']['core']['tribe_dashboard_widget'] ); // Новости от Modern Tribe

	}

	/*
	 * Adding WP Functions & Theme Support
	 */
	public function add_action_after_setup_theme() {

		// Add Support for WP Controlled Title Tag
		add_theme_support( 'title-tag' );

		// Add custom logo
		add_theme_support( 'custom-logo' );
	}

	public function register_settings_page() {
		$settings = new OptionPage( 'theme-settings', __( 'Main Options' ) );
		$settings->set_menu_icon( 'dashicons-screenoptions' );


		$reserve_interval = new OptionBox( 'reserve_interval', 'Reserve time interval' );
		$reserve_interval->add_field( 'time_interval_minutes', 'Minutes (90 minutes default)', 'Number' );
		$reserve_interval->set_page( $settings );


		$phone_request = new OptionBox( 'phone_request', 'Phone number reserve confirm' );
		$phone_request->add_field( 'contact_request', 'Enable', 'Checkbox' );
		$phone_request->set_page( $settings );
	}

	public function add_action_after_switch_theme() {

		add_role( 'user', 'User',
			array(
				'read'         => false,
				'edit_posts'   => false,
				'delete_posts' => false,
				'upload_files' => false,
			)
		);

	}

	/**
	 * Example method for setting image sizes
	 */
	public function register_image_sizes() {

	}

	/**
	 * Example method for init nav menus
	 */
	public function register_nav_menus() {

	}

	public function register_dynamic_sidebars() {
		// TODO: Implement register_dynamic_sidebars() method.
	}

}
