<?php

namespace modules\theme;


use Detection\MobileDetect;
use WPKit\Module\AbstractFunctions;

class Functions extends AbstractFunctions {

	public static function get_logo_url() {
		if ( function_exists( 'the_custom_logo' ) ) {
			$custom_logo_id = get_theme_mod( 'custom_logo' );
			$logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			$logo_url       = $logo[0];

			return $logo_url;
		}
	}

	public static function is_mobile() {
		$detect = new MobileDetect();

		return $detect->isMobile() ? $detect->isMobile() : false;
	}

}