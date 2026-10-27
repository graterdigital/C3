<?php
defined( 'ABSPATH' ) || exit;

/**
 * Design direction. config/site.php 'direction' names a file in assets/css/directions/ that is
 * loaded after main.css and overrides the --gd-* tokens (and any component rules it needs to).
 * 'fonts_url' is the Google Fonts (or self-hosted) stylesheet for that direction.
 */
function gd_direction(): string {
	return sanitize_key( gd_site_config()['direction'] ?? '' );
}
function gd_fonts_url(): string {
	return gd_site_config()['fonts_url'] ?? 'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap';
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'gd-fonts', gd_fonts_url(), [], null );
	wp_enqueue_style( 'gd-main', GD_THEME_URI . '/assets/css/main.css', [ 'gd-fonts' ], GD_THEME_VERSION );
	if ( gd_direction() && file_exists( GD_THEME_DIR . '/assets/css/directions/' . gd_direction() . '.css' ) ) {
		wp_enqueue_style( 'gd-direction', GD_THEME_URI . '/assets/css/directions/' . gd_direction() . '.css', [ 'gd-main' ], GD_THEME_VERSION );
	}
	wp_enqueue_script( 'gd-main', GD_THEME_URI . '/assets/js/main.js', [], GD_THEME_VERSION, [ 'strategy' => 'defer', 'in_footer' => true ] );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

/** Preconnect for fonts. */
add_filter( 'wp_resource_hints', function ( array $urls, string $relation ) {
	if ( $relation === 'preconnect' ) {
		$urls[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' ];
	}
	return $urls;
}, 10, 2 );

/** Editor: same fonts in the block editor. */
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_style( 'gd-fonts-editor', gd_fonts_url(), [], null );
} );
add_action( 'after_setup_theme', function () {
	if ( gd_direction() && file_exists( GD_THEME_DIR . '/assets/css/directions/' . gd_direction() . '.css' ) ) {
		add_editor_style( 'assets/css/directions/' . gd_direction() . '.css' );
	}
}, 20 );
