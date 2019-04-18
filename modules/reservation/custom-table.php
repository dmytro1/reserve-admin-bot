<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 10/27/18
 * Time: 18:20
 */

//	public function add_action_admin_init() {
//		// сохранение опции экрана per_page. Нужно вызывать рано до события 'admin_menu'
//
//		add_filter( 'set-screen-option', function ( $status, $option, $value ) {
//			var_dump('qqqqqqqq');
//			return ( $option == 'lisense_table_per_page' ) ? (int) $value : $status;
//		}, 10, 3 );
//	}

public function add_action_admin_menu() {
	$hook = add_menu_page( 'Заголовок', 'Имя в меню', 'manage_options', 'page-slug', __CLASS__ . '::example_table_page', 'dashicons-products', 100 );
	add_action( "load-$hook", __CLASS__ . '::example_table_page_load' );
}

public static function example_table_page_load() {
//		require_once __DIR__ . '/Example_List_Table.php'; // тут находится класс Example_List_Table...

	// создаем экземпляр и сохраним его дальше выведем
	$GLOBALS['Example_List_Table'] = new \modules\reservation\Example_List_Table();
}

public static function example_table_page() {
	?>
	<div class="wrap">
		<h2>
			<?php echo get_admin_page_title() ?>
			<a href="#" class="add-new-h2 page-title-action add-booking"><?php _e( 'Add New', 'restaurant-reservations' ); ?></a>
		</h2>


		<?php
		// выводим таблицу на экран где нужно
		echo '<form action="" method="POST">';
		$GLOBALS['Example_List_Table']->display();
		echo '</form>';
		?>

	</div>
	<!-- Restaurant Reservations add/edit booking modal -->
	<div id="rtb-booking-modal" class="rtb-admin-modal" style="display: none;">
		<div class="rtb-booking-form rtb-container">
			<form method="POST">
				<input type="hidden" name="action" value="admin_booking_request">
				<input type="hidden" name="ID" value="">

				<?php
				/**
				 * The generated fields are wrapped in a div so we can
				 * replace its contents with an HTML blob passed back from
				 * an Ajax request. This way the field data and error
				 * messages are always populated from the same server-side
				 * code.
				 */
				?>
				<div id="rtb-booking-form-fields">
					<!--						--><?php //echo $this->print_booking_form_fields(); ?>
				</div>

				<button type="submit" class="button button-primary">
					<?php _e( 'Add Booking', 'restaurant-reservations' ); ?>
				</button>
				<a href="#" class="button" id="rtb-cancel-booking-modal">
					<?php _e( 'Cancel', 'restaurant-reservations' ); ?>
				</a>
				<div class="action-status">
					<span class="spinner loading"></span>
					<span class="dashicons dashicons-no-alt error"></span>
					<span class="dashicons dashicons-yes success"></span>
				</div>
			</form>
		</div>
	</div>
	<?php
}