<?php
defined( 'ABSPATH' ) || exit;

/** Business details editable in Appearance → Customize → Business details. */
function gd_business_fields(): array {
	// Per-site defaults live in config/site.php so the theme itself stays generic.
	$site = gd_site_config();
	return [
		'phone'          => [ 'Phone (display)',                 $site['phone'] ?? '' ],
		'phone_e164'     => [ 'Phone (digits only, for links)',  $site['phone_e164'] ?? '' ],
		'whatsapp_text'  => [ 'WhatsApp pre-filled message',     $site['whatsapp_text'] ?? 'Hi, I have a question' ],
		'email'          => [ 'Email',                           $site['email'] ?? get_option( 'admin_email' ) ],
		'address'        => [ 'Address line',                    $site['address'] ?? '' ],
		'facebook'       => [ 'Facebook URL',                    $site['facebook'] ?? '' ],
		'instagram'      => [ 'Instagram URL',                   $site['instagram'] ?? '' ],
		'tripadvisor'    => [ 'TripAdvisor URL',                 $site['tripadvisor'] ?? '' ],
		'tagline'        => [ 'Footer tagline',                  $site['tagline'] ?? '' ],
		'founded'        => [ 'Founded (year)',                  $site['founded'] ?? '' ],
		'google_url'     => [ 'Google reviews URL',              $site['google_url'] ?? '' ],
		'google_write_url'=> [ 'Google "write a review" URL',    $site['google_write_url'] ?? '' ],
		'google_rating'  => [ 'Google rating (fallback)',        $site['google_rating'] ?? '' ],
		'google_count'   => [ 'Google review count (fallback)',  $site['google_count'] ?? '' ],
	];
}

/** Site-specific constants: edit config/site.php, never the theme files. */
function gd_site_config(): array {
	static $c = null;
	return $c ??= file_exists( GD_THEME_DIR . '/config/site.php' ) ? (array) include GD_THEME_DIR . '/config/site.php' : [];
}

function gd_get( string $key ): string {
	$f = gd_business_fields();
	return (string) get_theme_mod( 'gd_' . $key, $f[ $key ][1] ?? '' );
}

function gd_whatsapp_url( string $text = '' ): string {
	return 'https://wa.me/' . gd_get( 'phone_e164' ) . '?text=' . rawurlencode( $text ?: gd_get( 'whatsapp_text' ) );
}
/** Plugins build WhatsApp links with a default number; swap in the Customizer value. */
add_filter( 'gd_whatsapp_url', fn( $url ) => preg_replace( '~wa\.me/\d+~', 'wa.me/' . gd_get( 'phone_e164' ), $url ) );

add_action( 'customize_register', function ( WP_Customize_Manager $wp ) {
	$wp->add_section( 'gd_business', [ 'title' => __( 'Business details', 'gd-starter' ), 'priority' => 25 ] );
	foreach ( gd_business_fields() as $key => [ $label, $default ] ) {
		$wp->add_setting( 'gd_' . $key, [ 'default' => $default, 'sanitize_callback' => str_contains( $key, 'url' ) || in_array( $key, [ 'facebook', 'instagram', 'tripadvisor' ], true ) ? 'esc_url_raw' : 'sanitize_text_field' ] );
		$wp->add_control( 'gd_' . $key, [ 'label' => $label, 'section' => 'gd_business', 'type' => 'text' ] );
	}
	// Official brand logos for the rating cards (upload the SVG/PNG from each brand's press kit).
	foreach ( [ 'tripadvisor' => 'TripAdvisor logo (official asset)', 'google' => 'Google logo (official asset)' ] as $k => $label ) {
		$wp->add_setting( 'gd_logo_' . $k, [ 'default' => 0, 'sanitize_callback' => 'absint' ] );
		$wp->add_control( new WP_Customize_Media_Control( $wp, 'gd_logo_' . $k, [ 'label' => $label, 'section' => 'gd_business', 'mime_type' => 'image' ] ) );
	}
} );
