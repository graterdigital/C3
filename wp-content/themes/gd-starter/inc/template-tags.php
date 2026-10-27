<?php
defined( 'ABSPATH' ) || exit;

function gd_icon( string $name ): string {
	$paths = [
		'whatsapp'    => '<path fill="currentColor" stroke="none" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2zm0 1.8a8.2 8.2 0 1 1-4.2 15.3l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 0 1 12 3.8zm-3 4.4c-.2 0-.5 0-.8.3-.3.3-1 1-1 2.4s1 2.8 1.2 3c.1.2 2 3.2 5 4.4 2.5 1 3 .8 3.5.7.5 0 1.7-.7 2-1.4.2-.7.2-1.3.1-1.4l-.5-.3-2-1c-.3-.1-.5-.1-.7.1l-.9 1.1c-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.5.3-.5v-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.5z"/>',
		'phone'       => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.8 2z"/>',
		'mail'        => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'pin'         => '<path d="M12 22s7-7.2 7-12a7 7 0 1 0-14 0c0 4.8 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
		'instagram'   => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>',
		'facebook'    => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8z"/>',
		'tripadvisor' => '<circle cx="12" cy="12" r="10"/><circle cx="8" cy="13" r="2.5"/><circle cx="16" cy="13" r="2.5"/><path d="M4 9c2-2 5-3 8-3s6 1 8 3"/>',
		'arrow'       => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'       => '<path d="M6 6l12 12M18 6L6 18"/>',
		'check'       => '<path d="M5 12l4 4L19 7"/>',
		'star'        => '<path fill="currentColor" stroke="none" d="M12 2.5l2.9 6.2 6.8.8-5 4.7 1.3 6.8L12 17.7 5.9 21l1.3-6.8-5-4.7 6.8-.8z"/>',
	];
	return '<svg class="gd-i gd-i--' . esc_attr( $name ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

/** Social icon links, used in header + footer. */
function gd_social_links( string $class = 'gd-social' ): void {
	$links = array_filter( [
		'facebook'    => [ gd_get( 'facebook' ), 'Facebook' ],
		'instagram'   => [ gd_get( 'instagram' ), 'Instagram' ],
		'tripadvisor' => [ gd_get( 'tripadvisor' ), 'TripAdvisor' ],
		'whatsapp'    => [ ! empty( gd_site_config()['whatsapp'] ) ? gd_whatsapp_url() : '', 'WhatsApp' ],
	], fn( $l ) => $l[0] );
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $links as $icon => [ $url, $label ] ) {
		printf( '<li><a href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a></li>', esc_url( $url ), esc_attr( $label ), gd_icon( $icon ) );
	}
	echo '</ul>';
}

/** Page hero for standard pages (not front page / treks). */
function gd_page_hero( string $title, string $eyebrow = '', string $lede = '', ?string $img = null ): void {
	// $img: null = featured image of the current singular post (none on archives), '' = no image, or a URL.
	if ( $img === null ) {
		$img = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'gd-hero' ) : '';
	}
	?>
	<header class="gd-page-hero<?php echo $img ? ' gd-page-hero--image' : ''; ?>" <?php echo $img ? 'style="--hero:url(' . esc_url( $img ) . ')"' : ''; ?>>
		<div class="gd-wrap">
			<?php if ( $eyebrow ) : ?><p class="gd-eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $lede ) : ?><p class="gd-lede"><?php echo esc_html( $lede ); ?></p><?php endif; ?>
		</div>
	</header>
	<?php
}

/** Sticky WhatsApp button (mobile-first). */
function gd_whatsapp_fab(): void {
	printf( '<a class="gd-fab" href="%s" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">%s<span>WhatsApp</span></a>', esc_url( gd_whatsapp_url() ), gd_icon( 'whatsapp' ) );
}

/**
 * URL of a theme photo. Prefers the Media Library copy the seed imported (keyed by filename in
 * option gd_seed_image_map) so the site never depends on theme-folder file permissions; falls
 * back to assets/img/ in the theme.
 */
function gd_img( string $file, string $size = 'gd-hero' ): string {
	static $map = null;
	$map ??= (array) get_option( 'gd_seed_image_map', [] );
	if ( ! empty( $map[ $file ] ) ) {
		$url = wp_get_attachment_image_url( (int) $map[ $file ], $size );
		if ( $url ) {
			return $url;
		}
	}
	return GD_THEME_URI . '/assets/img/' . $file;
}

/**
 * Pages seeded from patterns carry theme asset URLs in their saved markup. Rewrite those to the
 * Media Library copies on output so file permissions in the theme folder can never blank a hero.
 */
add_filter( 'the_content', function ( string $html ): string {
	if ( ! str_contains( $html, '/assets/img/' ) ) {
		return $html;
	}
	return preg_replace_callback( '~' . preg_quote( GD_THEME_URI . '/assets/img/', '~' ) . '([\w.-]+\.(?:jpe?g|png|webp))~i', fn( $m ) => gd_img( $m[1] ), $html );
}, 5 );

/* ---------------------------------------------------------------------- */
/* Rating cards: TripAdvisor + Google                                       */
/* ---------------------------------------------------------------------- */

/** Brand mark: uploaded official logo if set, otherwise a plain text wordmark. */
function gd_brand_mark( string $brand ): string {
	$names = [ 'tripadvisor' => 'Tripadvisor', 'google' => 'Google' ];
	$id    = (int) get_theme_mod( 'gd_logo_' . $brand );
	if ( $id && ( $img = wp_get_attachment_image( $id, 'medium', false, [ 'class' => 'gd-rating__logo', 'alt' => $names[ $brand ] ] ) ) ) {
		return $img;
	}
	return '<span class="gd-rating__wordmark gd-rating__wordmark--' . esc_attr( $brand ) . '">' . esc_html( $names[ $brand ] ) . '</span>';
}

function gd_rating_symbols( float $rating, string $style = 'star' ): string {
	$html = '<span class="gd-rating__symbols gd-rating__symbols--' . esc_attr( $style ) . '" aria-label="' . esc_attr( sprintf( '%s out of 5', number_format_i18n( $rating, 1 ) ) ) . '">';
	for ( $i = 1; $i <= 5; $i++ ) {
		$fill = max( 0, min( 1, $rating - ( $i - 1 ) ) ) * 100;
		$html .= '<i style="--fill:' . (int) $fill . '%"></i>';
	}
	return $html . '</span>';
}

/** Aggregate data for each platform. */
function gd_rating_data(): array {
	$out = [];
	// TripAdvisor: full totals from the Apify adapter if present, else what's imported.
	$ta = get_option( 'gd_reviews_tripadvisor_aggregate', [] );
	if ( empty( $ta['count'] ) && function_exists( 'gd_reviews_aggregate' ) ) {
		$agg = gd_reviews_aggregate();
		$ta  = [ 'count' => $agg['count'], 'average' => $agg['average'] ];
	}
	if ( ! empty( $ta['count'] ) ) {
		$out['tripadvisor'] = [ 'rating' => (float) $ta['average'], 'count' => (int) $ta['count'], 'url' => gd_get( 'tripadvisor' ), 'style' => 'bubble', 'cta' => 'Read reviews on Tripadvisor' ];
	}
	// Google: live from the Places adapter, else Customizer fallback.
	$g = get_option( 'gd_reviews_google_aggregate', [] );
	if ( empty( $g['count'] ) && gd_get( 'google_rating' ) !== '' ) {
		$g = [ 'average' => (float) gd_get( 'google_rating' ), 'count' => (int) gd_get( 'google_count' ), 'url' => gd_get( 'google_url' ) ];
	}
	if ( ! empty( $g['average'] ) ) {
		$out['google'] = [ 'rating' => (float) $g['average'], 'count' => (int) ( $g['count'] ?? 0 ), 'url' => $g['url'] ?: gd_get( 'google_url' ), 'style' => 'star', 'cta' => 'See reviews on Google' ];
	}
	return $out;
}

function gd_rating_cards_html(): string {
	$data = gd_rating_data();
	if ( ! $data ) {
		return '';
	}
	$html = '<div class="gd-ratings">';
	foreach ( $data as $brand => $d ) {
		$inner  = '<div class="gd-rating__head">' . gd_brand_mark( $brand ) . '</div>';
		$inner .= '<div class="gd-rating__score"><strong>' . esc_html( number_format_i18n( $d['rating'], 1 ) ) . '</strong>' . gd_rating_symbols( $d['rating'], $d['style'] ) . '</div>';
		$inner .= '<span class="gd-rating__count">' . esc_html( $d['count'] ? sprintf( _n( '%s review', '%s reviews', $d['count'], 'gd-starter' ), number_format_i18n( $d['count'] ) ) : __( 'Rated by customers', 'gd-starter' ) ) . '</span>';
		$inner .= '<span class="gd-rating__cta">' . esc_html( $d['cta'] ) . ' ' . gd_icon( 'arrow' ) . '</span>';
		$html  .= $d['url']
			? '<a class="gd-rating gd-rating--' . esc_attr( $brand ) . '" href="' . esc_url( $d['url'] ) . '" target="_blank" rel="noopener">' . $inner . '</a>'
			: '<div class="gd-rating gd-rating--' . esc_attr( $brand ) . '">' . $inner . '</div>';
	}
	return $html . '</div>';
}
add_shortcode( 'gd_rating_cards', 'gd_rating_cards_html' );

/** Large line icons for service tiles (48x48 grid, stroke-only; styled by the direction). */
function gd_service_icon( string $name ): string {
	$paths = [
		'home'        => '<path d="M6 22 24 8l18 14"/><path d="M10 19v19h28V19"/><path d="M20 38V27h8v11"/><path d="M32 12V7h4v9"/>',
		'auto'        => '<path d="M8 28l4-11a3 3 0 0 1 3-2h18a3 3 0 0 1 3 2l4 11"/><rect x="5" y="28" width="38" height="10" rx="1"/><circle cx="13" cy="38" r="3.5"/><circle cx="35" cy="38" r="3.5"/><path d="M11 33h5M32 33h5"/>',
		'emergency'   => '<path d="M27 4 12 27h11l-3 17 16-24H25z"/>',
		'landscaping' => '<path d="M24 44V26"/><path d="M24 26c-9 0-15-6-15-15 9 0 15 6 15 15z"/><path d="M24 30c0-8 5-13 14-13 0 8-5 13-14 13z"/><path d="M8 44h32"/>',
		'junk'        => '<path d="M4 34h8V18h18v16h4"/><path d="M30 24h8l6 6v4h-4"/><circle cx="12" cy="36" r="3.5"/><circle cx="36" cy="36" r="3.5"/><path d="M16 36h16"/><path d="M14 18v-4h10"/>',
		'appliance'   => '<rect x="12" y="4" width="24" height="40" rx="2"/><path d="M12 20h24"/><path d="M30 10v4M30 26v6"/>',
	];
	if ( empty( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="gd-svc-icon gd-svc-icon--' . esc_attr( $name ) . '" viewBox="0 0 48 48" aria-hidden="true">' . $paths[ $name ] . '</svg>';
}
