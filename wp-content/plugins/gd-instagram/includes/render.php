<?php
defined( 'ABSPATH' ) || exit;

function gd_ig_render( array $args = [] ): string {
	$args  = wp_parse_args( $args, [ 'limit' => 8, 'columns' => 4, 'captions' => false, 'header' => true ] );
	$items = gd_ig_get_media( (int) $args['limit'] );
	if ( ! $items ) {
		return current_user_can( 'manage_options' ) ? '<p class="gd-ig-empty">' . esc_html__( 'Instagram feed not connected — see Settings → Instagram Feed.', 'gd-instagram' ) . '</p>' : '';
	}
	wp_enqueue_style( 'gd-instagram' );

	$handle = gd_ig_get( 'handle', '' );
	$cols   = max( 2, min( 6, (int) $args['columns'] ) );
	$html   = '<div class="gd-ig" style="--gd-ig-cols:' . $cols . '">';

	if ( $args['header'] ) {
		$html .= '<div class="gd-ig__head">';
		$html .= '<a class="gd-ig__handle" href="https://www.instagram.com/' . esc_attr( $handle ) . '/" target="_blank" rel="noopener">' . gd_ig_icon() . '<span>@' . esc_html( $handle ) . '</span></a>';
		$html .= '<a class="gd-ig__follow" href="https://www.instagram.com/' . esc_attr( $handle ) . '/" target="_blank" rel="noopener">' . esc_html__( 'Follow', 'gd-instagram' ) . '</a>';
		$html .= '</div>';
	}

	$html .= '<ul class="gd-ig__grid">';
	foreach ( $items as $m ) {
		$alt   = wp_trim_words( wp_strip_all_tags( $m['caption'] ), 15, '' );
		$html .= '<li class="gd-ig__item">';
		$html .= '<a href="' . esc_url( $m['permalink'] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $alt ?: 'View on Instagram' ) . '">';
		$html .= '<img src="' . esc_url( $m['image'] ) . '"' . ( ! empty( $m['srcset'] ) ? ' srcset="' . esc_attr( $m['srcset'] ) . '" sizes="(max-width:720px) 50vw, 25vw"' : '' ) . ' alt="' . esc_attr( $alt ) . '" loading="lazy" decoding="async">';
		if ( in_array( $m['type'], [ 'VIDEO', 'CAROUSEL_ALBUM' ], true ) ) {
			$html .= '<span class="gd-ig__badge gd-ig__badge--' . strtolower( $m['type'] ) . '" aria-hidden="true">' . ( $m['type'] === 'VIDEO' ? '▶' : '⧉' ) . '</span>';
		}
		if ( $args['captions'] && $m['caption'] ) {
			$html .= '<span class="gd-ig__caption">' . esc_html( wp_trim_words( $m['caption'], 18, '…' ) ) . '</span>';
		}
		$html .= '</a></li>';
	}
	$html .= '</ul></div>';
	return $html;
}

function gd_ig_icon(): string {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>';
}

add_shortcode( 'gd_instagram', fn( $atts ) => gd_ig_render( shortcode_atts( [ 'limit' => 8, 'columns' => 4, 'captions' => 0, 'header' => 1 ], $atts, 'gd_instagram' ) ) );

add_action( 'init', function () {
	register_block_type( GD_IG_DIR . 'block', [
		'render_callback' => fn( $attrs ) => gd_ig_render( [
			'limit'    => $attrs['limit'] ?? 8,
			'columns'  => $attrs['columns'] ?? 4,
			'captions' => ! empty( $attrs['captions'] ),
			'header'   => $attrs['header'] ?? true,
		] ),
	] );
} );
