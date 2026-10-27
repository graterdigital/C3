<?php
defined( 'ABSPATH' ) || exit;

/**
 * Block patterns: the whole home page plus reusable sections.
 * Pattern files live in /patterns/*.php and return block markup.
 *
 * Also: a "Canvas" checkbox on pages so the block content renders edge-to-edge
 * (no page hero / reading column) — used for the home page and landing pages.
 */
add_action( 'init', function () {
	register_block_pattern_category( 'gd-starter', [ 'label' => __( 'C3', 'gd-starter' ) ] );

	$patterns = [
		'home'          => __( 'Home page', 'gd-starter' ),
		'hero'          => __( 'Hero', 'gd-starter' ),
		'hero-cover'    => __( 'Hero (photo cover)', 'gd-starter' ),
		'hero-split'    => __( 'Hero (split block + photo)', 'gd-starter' ),
		'trust-strip'   => __( 'Trust strip', 'gd-starter' ),
		'intro'         => __( 'Intro (text + image)', 'gd-starter' ),
		'steps'         => __( 'How it works (3 steps)', 'gd-starter' ),
		'reviews-section'=> __( 'Reviews section', 'gd-starter' ),
		'instagram-section' => __( 'Instagram section', 'gd-starter' ),
		'mission'       => __( 'Mission statement', 'gd-starter' ),
		'services'      => __( 'Services grid', 'gd-starter' ),
		'doors'         => __( 'Request help / Volunteer doors', 'gd-starter' ),
		'events-section'=> __( 'Upcoming events', 'gd-starter' ),
		'partners'      => __( 'Partners & affiliates', 'gd-starter' ),
		'get-involved'  => __( 'Get involved (forms)', 'gd-starter' ),
		'donate'        => __( 'Ways to give', 'gd-starter' ),
		'funding-loop'  => __( 'How it’s funded', 'gd-starter' ),
		'about-story'   => __( 'About: story + photos', 'gd-starter' ),
		'faq'           => __( 'FAQ accordion', 'gd-starter' ),
		'contact'       => __( 'Contact page layout', 'gd-starter' ),
	];
	foreach ( $patterns as $slug => $title ) {
		$file = GD_THEME_DIR . '/pattern-parts/' . $slug . '.php';
		if ( ! file_exists( $file ) ) {
			continue;
		}
		ob_start();
		include $file;
		register_block_pattern( 'gd-starter/' . $slug, [
			'title'      => $title,
			'categories' => [ 'gd-starter' ],
			'content'    => ob_get_clean(),
		] );
	}
} );

/**
 * Pattern copy lives in content/copy.json (repo) so the client copy document can round-trip.
 * The seed copies it into the theme (theme/content/copy.json) so it survives `rm -rf content` on the host.
 */
function gd_copy_data(): array {
	static $d = null;
	if ( $d === null ) {
		$d = [];
		foreach ( [
			GD_THEME_DIR . '/content/copy.json',
			dirname( (string) realpath( GD_THEME_DIR ), 3 ) . '/content/copy.json',
			ABSPATH . 'content/copy.json',
		] as $src ) {
			if ( file_exists( $src ) ) {
				$d = (array) json_decode( (string) file_get_contents( $src ), true );
				break;
			}
		}
	}
	return $d;
}
/** HTML-escaped copy string. */
function gd_copy( string $key ): string {
	return esc_html( gd_copy_data()[ $key ] ?? '' );
}
/** Copy string with line breaks → paragraphs (for multi-paragraph cells). */
function gd_copy_paras( string $key ): string {
	$v = gd_copy_data()[ $key ] ?? '';
	return implode( '', array_map( fn( $p ) => '<p>' . esc_html( trim( $p ) ) . '</p>', array_filter( preg_split( '/\n\s*\n|\n/', $v ) ) ) );
}

/** Canvas toggle. */
add_action( 'add_meta_boxes_page', function () {
	add_meta_box( 'gd-canvas', __( 'Layout', 'gd-starter' ), function ( WP_Post $post ) {
		wp_nonce_field( 'gd_canvas', 'gd_canvas_nonce' );
		printf( '<label><input type="checkbox" name="gd_canvas" value="1" %s> %s</label><p class="description">%s</p>', checked( get_post_meta( $post->ID, '_gd_canvas', true ), '1', false ), esc_html__( 'Canvas (no page title/hero — blocks run edge to edge)', 'gd-starter' ), esc_html__( 'The front page is always canvas.', 'gd-starter' ) );
	}, 'page', 'side' );
} );
add_action( 'save_post_page', function ( int $id ) {
	if ( isset( $_POST['gd_canvas_nonce'] ) && wp_verify_nonce( $_POST['gd_canvas_nonce'], 'gd_canvas' ) && current_user_can( 'edit_post', $id ) ) {
		update_post_meta( $id, '_gd_canvas', empty( $_POST['gd_canvas'] ) ? '' : '1' );
	}
} );
register_post_meta( 'page', '_gd_canvas', [ 'type' => 'string', 'single' => true, 'show_in_rest' => true, 'auth_callback' => fn() => current_user_can( 'edit_pages' ) ] );
