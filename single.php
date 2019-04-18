<?php
/**
 * Created by PhpStorm.
 * User: dmytro
 * Date: 7/11/18
 * Time: 16:59
 */


$context = Timber::get_context();

$context['menu'] =  new Timber\Menu('top-nav');
//$context['footer_menu'] =  new Timber\Menu('top-nav');
$post = new Timber\Post();



$custom_logo_id = get_theme_mod( 'custom_logo' );
$logo           = wp_get_attachment_image_url( $custom_logo_id, 'full', true );
$context['logo'] = $logo;

$context['post'] = $post;
if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}

