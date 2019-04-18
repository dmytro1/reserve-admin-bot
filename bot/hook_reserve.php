<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 6/11/18
 * Time: 12:01
 */

require_once '../vendor/autoload.php';
require_once 'connection/Connection.php';
include_once 'includes/PrettyResponse.php';
//require $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once '../../../../wp-load.php';

echo '<br><b>Check WP functions: ' . get_bloginfo() . '</b><br><br>';


try {
	$connection = new \Bot\Connection\Connection();

	if ( ! $connection->check_conn() ) {
		return;
	}

	$result = $connection->telegram->getWebhookUpdate();
	//	$result = $telegram->getUpdates( [] );
	//	https://api.telegram.org/bot690376345:AAFqaS-u3o45VHAM9KocGiLpEdQJY30HUnQ/setWebhook?url=https://www.e-landing.top/wp-content/themes/reserve-admin/bot/hook_reserve.php
	//	https://api.telegram.org/bot690376345:AAFqaS-u3o45VHAM9KocGiLpEdQJY30HUnQ/getWebhookInfo

	// Message type data
	if ( $result->isType( 'message' ) ) {

		$text = $connection->text;

		if ( $text == "/start" || $text == $connection->keyboard_start[0][1] ) {
			$connection->start_message();
		} //
		// "Choose table" button
		elseif ( $text == $connection->keyboard_start[0][0] ) {
			$connection->choose_table();
		} //
		// Choose date
		elseif ( PrettyResponse::check_weekday( $text ) ) {
			$connection->choose_date();
		} //
		// Choose time
		elseif ( strpos( $text, ':00' ) || strpos( $text, ':30' ) ) {
			$connection->choose_time();
		} //
		// Choose number of persons
		elseif ( is_numeric( $text ) && $text > 0 && $text <= 8 ) {
			$connection->choose_persons();
		}  //
		// More than 8 persons
		elseif ( $text == '> 8' ) {
			$connection->more_persons();
		}  //
		// "Submit" button
		elseif ( $connection->phone_number || $text == $connection->submit_btn_text ) {
			$connection->onclick_submit();
		} //
		// "Discard" message
		elseif ( $text == $connection->discard_btn_text ) {
			$connection->onclick_discard();
		} //
		// '/discard_{$date}' message
		elseif ( ( strpos( $text, '/discard_' ) !== false ) ) {
			$connection->discard_reservation();
		} //
		// "Additional wishes" message
		elseif ( $text == $connection->wishes_button_text ) {
			$connection->additional_wishes();
		} //
		// Empty message
		elseif ( $text == "" ) {
			$connection->empty_message();
		} //
		// Not found message
		else {
			$connection->not_found_message();
		}
	} //
	// Callback type data
	elseif ( $result->isType( 'callback_query' ) ) {
		echo 'callback type';
	} else {
		echo 'no response from api';
	}
} catch ( Exception $e ) {
	echo $e;
}