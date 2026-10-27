<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'gd-starter', GD_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ] );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'custom-logo', [ 'height' => 80, 'width' => 240, 'flex-width' => true, 'flex-height' => true ] );
	add_editor_style( 'assets/css/main.css' );

	register_nav_menus( [
		'primary' => __( 'Primary navigation', 'gd-starter' ),
		'footer'  => __( 'Footer navigation', 'gd-starter' ),
	] );

	add_image_size( 'gd-hero', 1920, 1080, true );
	add_image_size( 'gd-card', 900, 600, true );
	add_image_size( 'gd-square', 800, 800, true );
} );

/** Remove clutter. */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
add_filter( 'emoji_svg_url', '__return_false' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_filter( 'the_generator', '__return_empty_string' );
add_filter( 'xmlrpc_enabled', '__return_false' );

/** Body classes. */
add_filter( 'body_class', function ( array $classes ) {
	if ( is_front_page() && ( gd_site_config()['transparent_header'] ?? true ) ) {
		$classes[] = 'has-transparent-header';
	}
	if ( function_exists( 'gd_direction' ) && gd_direction() ) {
		$classes[] = 'gd-dir-' . gd_direction();
	}
	return $classes;
} );

/** Allow SVG uploads for logos (admins only). */
add_filter( 'upload_mimes', function ( array $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}
	return $mimes;
} );

/** Slightly longer excerpts, no "[…]". */
add_filter( 'excerpt_length', fn() => 32 );
add_filter( 'excerpt_more', fn() => '…' );
