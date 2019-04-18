<?php

namespace modules\theme;

class Shortcodes {


	public static function init() {
		add_shortcode( 'button', [ 'modules\theme\Shortcodes', 'ufx_shortcode_button' ] );
	}

	## Link Button -------------------------------------------------- #

	public static function ufx_shortcode_button( $atts ) {

		$value = shortcode_atts( array(
			'text'      => '',
			'url'       => '',
			'class'     => '',
			'font-size' => '',
			'target'    => '',
		), $atts );

		$class     = ( ! empty( $value['class'] ) ) ? $value['class'] : '';
		$font_size = ( ! empty( $value['font-size'] ) ) ? 'style=font-size:' . $value['font-size'] . ';' : '';
		$target    = ( ! empty( $value['target'] ) ) ? 'target="_blank"' : '';
		$url       = $value['url'];
		$output    = '<a class="button-primary ' . $class . '" ' . $font_size . ' href="' . $url . '" ' . $target . '>' . $value['text'] . '</a>';

		return $output;
	}
}