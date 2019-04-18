<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 10/5/18
 * Time: 20:26
 */

namespace Bot\Connection;

use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Helpers\Emojify;
use Telegram\Bot\Keyboard\Keyboard;
use Database;
use PrettyResponse;
use WPKit\Options\Option;

include_once '../includes/Database.php';
include_once '../includes/PrettyResponse.php';


class Connection {

	public $telegram;
	public $result;

	public $text;
	public $chat_id;
	public $first_name;
	public $last_name;
	public $username;
	public $phone_number;

	protected $keyboard_date = [];
	public $keyboard_start = [ [ "➡️ Choose table", "View reservations 📋" ] ];
	protected $keyboard_time = [
		[ '10:00', '10:30' ],
		[ '11:00', '11:30' ],
		[ '12:00', '12:30' ],
		[ '13:00', '13:30' ],
		[ '14:00', '14:30' ],
		[ '15:00', '15:30' ],
		[ '16:00', '16:30' ],
		[ '17:00', '17:30' ],
		[ '18:00', '18:30' ],
		[ '19:00', '19:30' ],
		[ '20:00', '20:30' ],
		[ '21:00', '21:30' ],
		[ '22:00', '22:30' ],
		[ '23:00' ],
	];
	protected $keyboard_persons = [
		[ '1', '2', '3', '4' ],
		[ '5', '6', '7', '8' ],
		[ '> 8' ],
	];

	public $wishes_button_text = 'Additional wishes';
	public $submit_btn_text = 'Submit 🚀';
	public $discard_btn_text = 'Discard ❎';

	const TOKEN = '690376345:AAFqaS-u3o45VHAM9KocGiLpEdQJY30HUnQ';

	public function __construct() {
		$this->telegram = new Api( self::TOKEN );
		$this->result   = $this->telegram->getWebhookUpdate();

		$message            = $this->result->getMessage();
		$this->chat_id      = $message->chat->id;
		$this->first_name   = $message->from->firstName;
		$this->last_name    = $message->from->lastName;
		$this->username     = $message->from->username;
		$this->text         = $message->text;
		$this->phone_number = $message->contact->phoneNumber;

	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function check_conn() {

		global $wpdb;
		if ( $wpdb->check_connection() ) {
			return true;
		} else {
			$this->telegram->sendMessage( [
				'chat_id'    => $this->chat_id,
				'text'       => 'Connection error. Please try again later.',
				'parse_mode' => 'HTML',
			] );
		}
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function start_message() {
		$params = [
			'keyboard'          => $this->keyboard_start,
			'resize_keyboard'   => true,
			'one_time_keyboard' => true
		];

		$reply_markup = Keyboard::make( $params );

		$reply = "Hi " . "<strong>" . $this->first_name . " " . $this->last_name . " (@" . $this->username . ") 😊</strong>";

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => $reply,
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );

		$reply = Database::insert_new_user( $this->first_name, $this->last_name, $this->username, $this->chat_id );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => $reply,
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );

		$reply = Database::get_existing_reserves( $this->chat_id );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => $reply,
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function choose_table() {

		$this->create_keyboard_date();

		$params = [
			'keyboard'          => $this->keyboard_date,
			'resize_keyboard'   => true,
			'one_time_keyboard' => true
		];

		$reply_markup = Keyboard::make( $params );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => '➡️ Choose date 📆:',
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );
	}

	protected function create_keyboard_date() {
		$keyboard_date = [];

		$timestamp = time();
		for ( $i = 0; $i < 8; $i ++ ) {
			$keyboard_date[][] = strftime( '%d.%m - %A', $timestamp );
			$timestamp         = strtotime( '+1 day', $timestamp );
		}

		$this->keyboard_date = $keyboard_date;
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function choose_date() {

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => 'You chosen date: ' . $this->text,
			'parse_mode' => 'HTML',

		] );

		$date_format_str           = substr_replace( $this->text, '', strpos( $this->text, ' - ' ) );
		$date_format_str_with_year = $date_format_str . '.' . date( 'Y' );
		$date_format               = strtotime( $date_format_str_with_year );
		$date_format_mysql         = date( 'Y-m-d', $date_format );

		// Store reserve draft with date

		$response = Database::set_reserve_date_draft( $date_format_mysql, $this->chat_id );

		if ( $response === true ) {
			$reply = "Reserve on $date_format_mysql exists. Do you want to change your reservation details ?";
		} else {
			$reply = $response;
		}

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => $reply,
			'parse_mode' => 'HTML',
		] );


		$reserve_id = Database::get_reserve_id( $this->chat_id );

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => 'Reserve ID: ' . $reserve_id,
			'parse_mode' => 'HTML',
		] );

		$params = [
			'keyboard'          => $this->keyboard_time,
			'resize_keyboard'   => true,
			'one_time_keyboard' => true
		];

		$reply_markup = Keyboard::make( $params );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => '➡️ Choose time 🕑:',
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function choose_time() {
		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => 'Your chosen time: ' . $this->text,
			'parse_mode' => 'HTML',
		] );


		// Store reserve time

		$reply = Database::update_reserve_time( $this->text, $this->chat_id );

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => $reply,
			'parse_mode' => 'HTML',
		] );

		$params = [
			'keyboard'          => $this->keyboard_persons,
			'resize_keyboard'   => true,
			'one_time_keyboard' => true
		];

		$reply_markup = Keyboard::make( $params );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => '➡️ Choose persons number ️👪️:',
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function choose_persons() {

		$int_val_persons = intval( $this->text );

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => 'Number of persons: ' . $int_val_persons,
			'parse_mode' => 'HTML',
		] );

		// Store number of persons

		$reply = Database::update_reserve_persons( $int_val_persons, $this->chat_id );

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => $reply,
			'parse_mode' => 'HTML',
		] );

		// Table assigning starts
		// Store TABLE ID

		$response = Database::update_table_id( $int_val_persons, $this->chat_id );


		if ( $response === false ) {

			$params = [
				'keyboard'          => $this->keyboard_time,
				'resize_keyboard'   => true,
				'one_time_keyboard' => true
			];

			$reply_markup = Keyboard::make( $params );
			$this->telegram->sendMessage( [
				'chat_id'      => $this->chat_id,
				'text'         => 'All reserved on this time. Please choose another time.',
				'parse_mode'   => 'HTML',
				'reply_markup' => $reply_markup
			] );
		} else {

			$this->telegram->sendMessage( [
				'chat_id'    => $this->chat_id,
				'text'       => $response,
				'parse_mode' => 'HTML',
			] );

			$params = [
				'resize_keyboard'   => true,
				'one_time_keyboard' => true
			];

			$reply_markup = Keyboard::make( $params )->row(
				Keyboard::button( [
					'text'            => $this->submit_btn_text,
					'request_contact' => boolval( $this->get_option_contact_request() ),
				] )
			)->row(
				Keyboard::button( [ 'text' => $this->discard_btn_text ] ),
				Keyboard::button( [ 'text' => $this->wishes_button_text ] )
			);

			$details_reply = Database::get_reserve_details( $this->chat_id );

			$this->telegram->sendMessage( [
				'chat_id'      => $this->chat_id,
				'text'         => $details_reply,
				'parse_mode'   => 'HTML',
				'reply_markup' => $reply_markup
			] );
		}
	}

//	public function create_button_submit() {
//		return $this->submit_btn_text;
//	}
//
//	public function create_button_discard() {
//		return $this->discard_btn_text . Emojify::getInstance()->toEmoji( ':heavy_multiplication_x:' );
//	}

	protected function get_option_contact_request() {
		return Option::get( 'contact_request' );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function more_persons() {
		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => 'Please contact administrator to check tables',
			'parse_mode' => 'HTML',
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function onclick_submit() {

		if ( $this->get_option_contact_request() ) {

			$phone_number = $this->phone_number;
			$this->telegram->sendMessage( [
				'chat_id'    => $this->chat_id,
				'text'       => "<b>Debug: </b><i>Your phone number is $phone_number</i>",
				'parse_mode' => 'HTML',
			] );

			$reply = Database::update_submit_reserve_with_phone( $this->chat_id, $phone_number );
		} else {
			$reply = Database::update_submit_reserve_with_phone( $this->chat_id );
		}

		$params = [
			'keyboard'          => $this->keyboard_start,
			'resize_keyboard'   => true,
			'one_time_keyboard' => true
		];

		$reply_markup = Keyboard::make( $params );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => $reply,
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function onclick_discard() {
		$params = [
			'keyboard'          => $this->keyboard_start,
			'resize_keyboard'   => true,
			'one_time_keyboard' => true
		];

		$reply_markup = Keyboard::make( $params );

		$reply = Database::delete_reserve( $this->chat_id );

		$this->telegram->sendMessage( [
			'chat_id'      => $this->chat_id,
			'text'         => $reply,
			'parse_mode'   => 'HTML',
			'reply_markup' => $reply_markup
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function discard_reservation() {

		$date        = str_replace( '/discard_', '', $this->text );
		$date_format = str_replace( '_', '-', $date );

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => $date_format,
			'parse_mode' => 'HTML',
		] );

		$reply = Database::delete_reserve( $this->chat_id, $date );

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => $reply,
			'parse_mode' => 'HTML',
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function additional_wishes() {

		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => 'Floor, terrassa, smoke area features will be available soon ..',
			'parse_mode' => 'HTML',
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function empty_message() {
		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'parse_mode' => 'HTML',
			'text'       => 'Empty message '
		] );
		$this->telegram->sendMessage( [
			'chat_id'    => $this->chat_id,
			'text'       => PrettyResponse::print_response_string( $this->result ),
			'parse_mode' => 'HTML'
		] );
	}

	/**
	 * @throws TelegramSDKException error
	 */
	public function not_found_message() {
		$reply = "Nothing found on this query: " . "<strong>\"" . $this->text . "\"</strong>";
		$this->telegram->sendMessage( [ 'chat_id' => $this->chat_id, 'parse_mode' => 'HTML', 'text' => $reply ] );
	}


}