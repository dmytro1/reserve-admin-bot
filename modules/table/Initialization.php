<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 1/31/18
 * Time: 17:27
 */

namespace modules\table;

use WPKit\Fields\Select;
use WPKit\Module\AbstractModuleInitialization;
use WPKit\Options\Option;
use WPKit\PostType\MetaBox;
use WPKit\PostType\PostType;

class Initialization extends AbstractModuleInitialization {

	const POST_TYPE = 'table';

	private static $reserve_table = 'reserve_list';

	public function register_post_type() {
		$post_type = new PostType( self::POST_TYPE, 'Table', [ 'menu_name' => 'Tables' ] );
		$post_type->set_menu_icon( 'dashicons-list-view' );
		$post_type->set_publicly_queryable( false );
		$post_type->set_public( false );
		$post_type->set_show_in_rest( true );
		$this->add_parameters_metabox( self::POST_TYPE );
		$this->add_status_metabox( self::POST_TYPE );
	}

	public function add_parameters_metabox( $post_type ) {
		$metabox = new MetaBox( 'table_info', 'Table information' );
		$metabox->set_priority( 'high' );
		$metabox->add_field( 'persons', 'Persons', 'Number' );
		$metabox->add_field( 'location', 'Location', function () {
			$s = new Select();
			$s->set_options( [
				'first_floor'  => 'First floor',
				'second_floor' => 'Second floor',
				'terassa'      => 'Terassa',
				'bar'          => 'Bar'
			] );

			return $s;
		} );
		$metabox->add_post_type( $post_type );
	}

	public function add_status_metabox( $post_type ) {
		$metabox = new MetaBox( 'reserve', 'Reserve status' );
//		$metabox->set_priority( 'high' );
		$metabox->set_context( 'side' );
		$metabox->add_field( 'status', 'Booked', 'Checkbox' );
		$metabox->add_post_type( $post_type );
	}

	public function add_action_admin_init() {
		remove_post_type_support( self::POST_TYPE, 'editor' );

		// Custom columns actions
		add_filter( 'manage_table_posts_columns', __CLASS__ . '::add_custom_columns' );
		add_action( 'manage_table_posts_custom_column', __CLASS__ . '::add_custom_columns_content' );
		add_filter( 'manage_edit-table_sortable_columns', __CLASS__ . '::add_sortable_custom_columns' );
		add_action( 'pre_get_posts', __CLASS__ . '::pre_get_posts_custom_columns' );


		// Quick edit "Status" field
		add_action( 'quick_edit_custom_box', __CLASS__ . '::add_quickedit_custom_field', 10, 2 );
		add_action( 'save_post', __CLASS__ . '::quickedit_save_post', 10, 2 );
		add_action( 'admin_print_footer_scripts-edit.php', __CLASS__ . '::add_quickedit_js_code' );
		add_filter( 'page_row_actions', __CLASS__ . '::quickedit_set_data', 10, 2 );

		// Register styles
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == self::POST_TYPE ) {
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::setup_admin_styles' );
		}

	}

	public static function add_custom_columns( $columns ) {
		$columns['persons']  = 'Persons';
		$columns['location'] = 'Location';
		$columns['status']   = 'Status';
		unset( $columns['date'] );

		return $columns;
	}

	public static function add_custom_columns_content( $column ) {

		switch ( $column ) {
			case 'persons':
				echo MetaBox::get( get_the_ID(), 'table_info', 'persons' );
				break;
			case 'location':
				$location = MetaBox::get( get_the_ID(), 'table_info', 'location' );
				switch ( $location ) {
					case 'first_floor':
						echo 'First floor';
						break;
					case 'second_floor':
						echo 'Second floor';
						break;
					case 'terassa':
						echo 'Terassa';
						break;
					case 'bar':
						echo 'Bar';
						break;
				}
				break;
			case 'status':
				if ( self::is_current_booked( get_the_ID() ) !== null ) {
					//		if ( MetaBox::get( $table_id, 'reserve', 'status' ) ) {
					echo '<span class="status-box booked">Booked</span> ';
				} else {
					echo '<span class="status-box free">Free</span> ';
				}
				break;
		}
	}

	public static function setup_admin_styles() {
		wp_enqueue_style( 'status-column-style', get_template_directory_uri() . '/bot/assets/css/admin.css', array() );
	}

	public static function is_current_booked( $table_id ) {
		global $wpdb;

		$reserve_table = self::$reserve_table;

		$current_time = current_time( 'H:i' );
		$current_date = current_time( 'Y-m-d' );

		$reserve_time = Option::get( 'time_interval_minutes' );

		$time_interval_min = ! empty( $reserve_time ) ? $reserve_time : 90;
		$time_interval     = 60 * $time_interval_min;

		$reserve_time_offset_before = date( 'H:i', strtotime( $current_time ) - $time_interval );
//		$reserve_time_offset_after  = date( 'H:i', strtotime( $current_time ) + $time_interval );


		$reserve = $wpdb->get_var( "SELECT * FROM {$reserve_table}
		                                             WHERE table_id = {$table_id}
		                                             AND reserve_date = '$current_date'
		                                             AND reserve_status = 'publish'
		                                             AND reserve_time <= '$current_time' 
										             AND reserve_time > '$reserve_time_offset_before'" );

		return $reserve;
	}

	public static function add_sortable_custom_columns( $columns ) {
		$columns['persons']  = 'persons';
		$columns['location'] = 'location';
		$columns['status']   = 'status';

		return $columns;
	}

	/**
	 * @param $query \WP_Query
	 */
	public static function pre_get_posts_custom_columns( $query ) {
		if ( $query->is_main_query() && is_admin() && isset( $_GET['post_type'] ) && $_GET['post_type'] == self::POST_TYPE ) {
			$orderby = $query->get( 'orderby' );
			if ( $orderby == 'menu_order title' || $orderby == 'title' ) {
				$query->set( 'orderby', 'meta_value_num' );
			}
			if ( 'status' == $orderby ) {
				$query->set( 'meta_key', 'reserve_status' );
				$query->set( 'orderby', 'meta_value_num' );
			} elseif ( 'persons' == $orderby ) {
				$query->set( 'meta_key', 'table_info_persons' );
				$query->set( 'orderby', 'meta_value_num' );
			} elseif ( 'location' == $orderby ) {
				$query->set( 'meta_key', 'table_info_location' );
				$query->set( 'orderby', 'meta_value' );
			}


		}
	}


	public static function add_quickedit_custom_field( $column_name, $post_type ) {
		if ( 'status' != $column_name ) {
			return;
		}
		?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php echo 'Booked:' ?></span>
                    <span class="input-text-wrap">
                    <input type="checkbox" name="status" class="booked-status">
                </span>
                </label>
            </div>
        </fieldset>
		<?php
	}

	public static function quickedit_save_post( $post_id, $post ) {
		// if called by autosave, then bail here
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// if this "post" post type?
		if ( $post->post_type != 'table' ) {
			return;
		}

		// does this user have permissions?
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Return if save post in post editor
		$screen = get_current_screen();
		if ( isset( $screen ) ) {
			return;
		}

		if ( isset( $_POST['status'] ) ) {
			update_post_meta( $post_id, 'reserve_status', 1 );
		} else {
			update_post_meta( $post_id, 'reserve_status', '' );
		}
	}

	public static function add_quickedit_js_code() {

		$current_screen = get_current_screen();

		if ( $current_screen->id != 'edit-table' || $current_screen->post_type != 'table' ) {
			return;
		}

		// Ensure jQuery library loads
		wp_enqueue_script( 'jquery' );
		?>
        <script type="text/javascript">
            jQuery(function ($) {
                $('#the-list').on('click', 'a.editinline', function (e) {
                    e.preventDefault();
                    // generated by 'page_row_actions' action
                    var statusData = $(this).data('status');
                    // console.log(this);
                    inlineEditPost.revert();
                    if (statusData) {
                        $('.booked-status').prop("checked", "checked");
                    } else {
                        $('.booked-status').prop("checked", "");
                    }
                });
            });
        </script>
		<?php
	}

	public static function quickedit_set_data( $actions, $post ) {

		$found_value = MetaBox::get( $post->ID, 'reserve', 'status' );

		if ( $found_value ) {
			if ( isset( $actions['inline hide-if-no-js'] ) ) {
				$new_attribute                   = sprintf( 'data-status="%s"', esc_attr( $found_value ) );
				$actions['inline hide-if-no-js'] = str_replace( 'class=', "$new_attribute class=", $actions['inline hide-if-no-js'] );
			}
		}

		return $actions;
	}

}