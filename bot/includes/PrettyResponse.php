<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 6/13/18
 * Time: 15:12
 */

class PrettyResponse {

	/**
	 * Store every object/array key=>value in output
	 * @var string
	 */
	private static $output = '';

	/**
	 * Store iteration number
	 * @var int
	 */
	private static $iteration = 0;

	/**
	 * Returns the response from Telegram API in HTML
	 *
	 * @param  $object_array
	 *
	 * @return string
	 */
	public static function print_response_string( $object_array ) {

		foreach ( $object_array as $key => $value ) {

			if ( gettype( $value ) == 'array' || gettype( $value ) == 'object' ) {
				self::$output .= self::add_space( self::$iteration ) . "<strong>" . $key . " => " . self::what_data_type( gettype( $value ) ) . "</strong>\r\n";
				self::$iteration ++;

				self::print_response_string( $value );

				self::$iteration --;
			} else {
				self::$output .= self::add_space( self::$iteration ) . "<strong>" . $key . "</strong> => " . $value . "\r\n";
			}
		}

		return self::$output;
	}

	/**
	 * Add white space before string
	 *
	 * @param $iteration
	 *
	 * @return string
	 */
	private static function add_space( $iteration ) {
		$space = "";
		for ( $i = 0; $i < $iteration; $i ++ ) {
			$space .= "\t\t\t\t\t";
		}

		return $space;
	}

	/**
	 * returns object or array brackets string
	 *
	 * @param $data_type
	 *
	 * @return string
	 */
	private static function what_data_type( $data_type ) {
		return $data_type == 'array' ? '[]' : '{}';
	}

	private static function strpos_array( $haystack, $needle, $offset = 0 ) {

		if ( ! is_array( $needle ) ) {
			$needle = array( $needle );
		}
		foreach ( $needle as $query ) {
			if ( strpos( $haystack, $query, $offset ) !== false ) {
				return true;
			} // stop on first true result
		}

		return false;
	}

	public static function check_weekday( $text ) {
		$string = $text;
		$array  = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );

		return self::strpos_array( $string, $array );
	}

	/**
	 * returns associative array
	 *
	 * @param array $array_of_objects WPDB get_results method response
	 * @param string $key Key what need to execute
	 *
	 * @return array
	 */
	public static function convert_obj_to_array( $array_of_objects, $key ) {

		$new_array = [];

		foreach ( $array_of_objects as $item ) {
			$new_array[] = $item->$key;
		}

		return $new_array;

	}
}

