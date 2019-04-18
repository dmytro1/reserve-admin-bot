<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 10/18/18
 * Time: 16:54
 */

namespace modules\reservation;


class Example_List_Table extends \WP_List_Table {

	function __construct() {
		parent::__construct( array(
			'singular' => 'log',
			'plural'   => 'logs',
			'ajax'     => false,
		) );

		$this->bulk_action_handler();

		// screen option
		add_screen_option( 'per_page', array(
			'label'   => 'Показывать на странице',
			'default' => 20,
			'option'  => 'logs_per_page',
		) );

		$this->prepare_items();

		add_action( 'wp_print_scripts', [ __CLASS__, '_list_table_css' ] );
	}

	// создает элементы таблицы
	function prepare_items() {
		global $wpdb;

		// пагинация
		$per_page = get_user_meta( get_current_user_id(), get_current_screen()->get_option( 'per_page', 'option' ), true ) ?: 20;

		$this->set_pagination_args( array(
			'total_items' => 3,
			'per_page'    => $per_page,
		) );
		$cur_page = (int) $this->get_pagenum(); // желательно после set_pagination_args()

		// элементы таблицы
		// обычно элементы получаются из БД запросом
		// $this->items = get_posts();

		// чтобы понимать как должны выглядеть добавляемые элементы
		$this->items = array(
			(object) array(
				'reserve_id'   => 60,
				'reserve_date' => '2018-10-12',
				'reserve_time' => '18:30',
				'user'         => 'Dmytro',
				'persons'      => 6,
				'table'        => 'Table 11',
				'additional'   => 'Hookah',
				'status'       => 'Publish',
			),
			(object) array(
				'reserve_id'   => 61,
				'reserve_date' => '2018-10-13',
				'reserve_time' => '20:30',
				'user'         => 'Vasia',
				'persons'      => 2,
				'table'        => 'Table 4',
				'additional'   => 'Terazza',
				'status'       => 'Draft',
			),
		);

	}

	// колонки таблицы
	function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'reserve_id'   => 'ID',
			'reserve_date' => 'Date',
			'reserve_time' => 'Time',
			'user'         => 'User',
			'table'        => 'Table',
			'persons'      => 'Persons',
			'additional'   => 'Additional',
			'status'       => 'Status'
		);
	}

	// сортируемые колонки
	function get_sortable_columns() {
		return array(
			'reserve_date' => array( 'orderby', 'desc' ),
		);
	}

	protected function get_bulk_actions() {
		return array(
			'delete' => 'Delete',
		);
	}

	// Элементы управления таблицей. Расположены между групповыми действиями и панагией.
	function extra_tablenav( $which ) {
		echo '<div class="alignleft actions">HTML код полей формы (select). Внутри тега form...</div>';
	}

	// вывод каждой ячейки таблицы -------------

	static function _list_table_css() {
		?>
        <style>
            table.logs .column-id {
                width: 2em;
            }

            table.logs .column-license_key {
                width: 8em;
            }

            table.logs .column-customer_name {
                width: 15%;
            }
        </style>
		<?php
	}

	// вывод каждой ячейки таблицы...
	function column_default( $item, $colname ) {

		if ( $colname === 'customer_name' ) {
			// ссылки действия над элементом
			$actions         = array();
			$actions['edit'] = sprintf( '<a href="%s">%s</a>', '#', __( 'edit', 'hb-users' ) );

			return esc_html( $item->name ) . $this->row_actions( $actions );
		} else {
			return isset( $item->$colname ) ? $item->$colname : print_r( $item, 1 );
		}

	}

	// заполнение колонки cb
	function column_cb( $item ) {
		echo '<input type="checkbox" name="licids[]" id="cb-select-' . $item->reserve_id . '" value="' . $item->reserve_id . '" />';
	}

	// остальные методы, в частности вывод каждой ячейки таблицы...

	// helpers -------------

	private function bulk_action_handler() {
		if ( empty( $_POST['licids'] ) || empty( $_POST['_wpnonce'] ) ) {
			return;
		}

		if ( ! $action = $this->current_action() ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'bulk-' . $this->_args['plural'] ) ) {
			wp_die( 'nonce error' );
		}

		// делает что-то...
		die( $action ); // delete
		die( print_r( $_POST['licids'] ) );

	}

	/*
	// Пример создания действий - ссылок в основной ячейки таблицы при наведении на ряд.
	// Однако гораздо удобнее указать их напрямую при выводе ячейки - см ячейку customer_name...

	// основная колонка в которой будут показываться действия с элементом
	protected function get_default_primary_column_name() {
		return 'disp_name';
	}

	// действия над элементом для основной колонки (ссылки)
	protected function handle_row_actions( $post, $column_name, $primary ) {
		if ( $primary !== $column_name ) return ''; // только для одной ячейки

		$actions = array();

		$actions['edit'] = sprintf( '<a href="%s">%s</a>', '#', __('edit','hb-users') );

		return $this->row_actions( $actions );
	}
	*/

}