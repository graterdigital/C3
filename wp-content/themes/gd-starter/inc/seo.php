<?php
defined( 'ABSPATH' ) || exit;

/**
 * Organization / LocalBusiness schema + basic OG tags.
 * If Yoast / Rank Math is installed, they handle meta; we only add the business entity.
 */
add_action( 'wp_head', function () {
	$logo = get_theme_mod( 'custom_logo' ) ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '';
	$data = [
		'@context'    => 'https://schema.org',
		'@type'       => gd_site_config()['schema_type'] ?? 'LocalBusiness',
		'@id'         => home_url( '/#organization' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'telephone'   => '+' . gd_get( 'phone_e164' ),
		'email'       => gd_get( 'email' ),
		'foundingDate'=> gd_get( 'founded' ),
		'address'     => [ '@type' => 'PostalAddress', 'streetAddress' => gd_get( 'address' ) ],
		'sameAs'      => array_values( array_filter( [ gd_get( 'facebook' ), gd_get( 'instagram' ), gd_get( 'tripadvisor' ) ] ) ),
	];
	if ( $logo ) {
		$data['logo'] = $logo;
	}
	if ( ! gd_get( 'phone_e164' ) ) {
		unset( $data['telephone'] );
	}
	if ( ! gd_get( 'founded' ) ) {
		unset( $data['foundingDate'] );
	}
	if ( ! empty( gd_site_config()['donate_url'] ) ) {
		$data['potentialAction'] = [ '@type' => 'DonateAction', 'target' => gd_site_config()['donate_url'] ];
	}
	if ( function_exists( 'gd_reviews_aggregate' ) ) {
		$agg = gd_reviews_aggregate();
		if ( $agg['count'] >= 5 ) {
			$data['aggregateRating'] = [ '@type' => 'AggregateRating', 'ratingValue' => $agg['average'], 'reviewCount' => $agg['count'] ];
		}
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";

	if ( ! defined( 'WPSEO_VERSION' ) && ! defined( 'RANK_MATH_VERSION' ) ) {
		$title = wp_get_document_title();
		$desc  = is_singular() ? wp_strip_all_tags( get_the_excerpt() ) : get_bloginfo( 'description' );
		$img   = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'gd-hero' ) : $logo;
		printf( '<meta property="og:title" content="%s"><meta property="og:description" content="%s"><meta property="og:type" content="website"><meta property="og:url" content="%s">', esc_attr( $title ), esc_attr( wp_trim_words( $desc, 30 ) ), esc_url( is_singular() ? get_permalink() : home_url( add_query_arg( [] ) ) ) );
		if ( $img ) {
			printf( '<meta property="og:image" content="%s">', esc_url( $img ) );
		}
		echo "\n";
	}
}, 5 );
