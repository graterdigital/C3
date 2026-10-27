<?php
defined( 'ABSPATH' ) || exit;

function gd_events_icon( string $name ): string {
	if ( function_exists( 'gd_icon' ) ) {
		return gd_icon( $name );
	}
	$paths = [
		'pin'   => '<path d="M12 22s7-7.2 7-12a7 7 0 1 0-14 0c0 4.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
		'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'tag'   => '<path d="M3 12l9-9h9v9l-9 9z"/><circle cx="16" cy="8" r="1.5"/>',
	];
	return '<svg class="gd-i gd-i--' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

/** One event card. */
function gd_events_card_html( int $post_id ): string {
	$m    = gd_events_get_meta( $post_id );
	$url  = get_permalink( $post_id );
	$html = '<li class="gd-event' . ( $m['is_past'] ? ' gd-event--past' : '' ) . '">';
	$html .= '<a class="gd-event__media" href="' . esc_url( $url ) . '" aria-hidden="true" tabindex="-1">' . ( has_post_thumbnail( $post_id ) ? get_the_post_thumbnail( $post_id, 'gd-card', [ 'loading' => 'lazy' ] ) : '' ) . '</a>';
	if ( $m['start_dt'] ) {
		$html .= '<div class="gd-event__date"><span>' . esc_html( wp_date( 'M', $m['start_dt']->getTimestamp() ) ) . '</span><strong>' . esc_html( wp_date( 'j', $m['start_dt']->getTimestamp() ) ) . '</strong></div>';
	}
	$html .= '<div class="gd-event__body">';
	$html .= '<h3><a href="' . esc_url( $url ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></h3>';
	$html .= '<ul class="gd-event__meta">';
	if ( $m['when'] ) {
		$html .= '<li>' . gd_events_icon( 'clock' ) . '<span>' . esc_html( $m['is_past'] ? $m['when'] : $m['when'] ) . '</span></li>';
	}
	if ( $m['venue'] || $m['address'] ) {
		$html .= '<li>' . gd_events_icon( 'pin' ) . '<span>' . esc_html( $m['venue'] ?: $m['address'] ) . '</span></li>';
	}
	if ( $m['cost'] ) {
		$html .= '<li>' . gd_events_icon( 'tag' ) . '<span>' . esc_html( $m['cost'] ) . '</span></li>';
	}
	$html .= '</ul>';
	if ( has_excerpt( $post_id ) ) {
		$html .= '<p>' . esc_html( get_the_excerpt( $post_id ) ) . '</p>';
	}
	$html .= '</div></li>';
	return $html;
}

/**
 * Render a list of events. args: scope (upcoming|past|all), limit, empty (text when none).
 */
function gd_events_render( array $args = [] ): string {
	$args  = wp_parse_args( $args, [ 'scope' => 'upcoming', 'limit' => -1, 'empty' => '' ] );
	$posts = gd_events_query( $args['scope'], (int) $args['limit'] );
	wp_enqueue_style( 'gd-events' );
	if ( ! $posts ) {
		$empty = $args['empty'] ?: ( $args['scope'] === 'upcoming'
			? __( 'Nothing on the calendar right now. Follow us on Instagram — new project days and fundraisers are announced there first.', 'gd-events' )
			: __( 'No past events yet.', 'gd-events' ) );
		return '<div class="gd-events-empty">' . esc_html( $empty ) . '</div>';
	}
	$html = '<ul class="gd-events gd-events--' . esc_attr( $args['scope'] ) . '">';
	foreach ( $posts as $p ) {
		$html .= gd_events_card_html( $p->ID );
	}
	return $html . '</ul>';
}

add_shortcode( 'gd_events', fn( $atts ) => gd_events_render( shortcode_atts( [ 'scope' => 'upcoming', 'limit' => -1, 'empty' => '' ], $atts, 'gd_events' ) ) );

add_action( 'init', function () {
	register_block_type( GD_EVENTS_DIR . 'block', [
		'render_callback' => fn( $attrs ) => gd_events_render( [
			'scope' => $attrs['scope'] ?? 'upcoming',
			'limit' => $attrs['limit'] ?? -1,
			'empty' => $attrs['empty'] ?? '',
		] ),
	] );
} );
