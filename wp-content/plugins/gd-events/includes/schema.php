<?php
defined( 'ABSPATH' ) || exit;

/** schema.org Event JSON-LD on single event pages. */
add_action( 'wp_head', function () {
	if ( ! is_singular( 'event' ) ) {
		return;
	}
	$id = get_queried_object_id();
	$m  = gd_events_get_meta( $id );
	if ( ! $m['start_dt'] ) {
		return;
	}
	$data = [
		'@context'            => 'https://schema.org',
		'@type'               => 'Event',
		'name'                => get_the_title( $id ),
		'description'         => has_excerpt( $id ) ? get_the_excerpt( $id ) : wp_trim_words( wp_strip_all_tags( get_post_field( 'post_content', $id ) ), 50 ),
		'url'                 => get_permalink( $id ),
		'startDate'           => $m['start_dt']->format( DATE_ATOM ),
		'eventStatus'         => 'https://schema.org/EventScheduled',
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'organizer'           => [ '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'url' => home_url( '/' ) ],
	];
	if ( $m['end_dt'] ) {
		$data['endDate'] = $m['end_dt']->format( DATE_ATOM );
	}
	if ( $m['venue'] || $m['address'] ) {
		$data['location'] = [ '@type' => 'Place', 'name' => $m['venue'] ?: $m['address'], 'address' => $m['address'] ?: $m['venue'] ];
	}
	if ( has_post_thumbnail( $id ) ) {
		$data['image'] = get_the_post_thumbnail_url( $id, 'full' );
	}
	if ( $m['cost'] !== '' ) {
		$free = (bool) preg_match( '/^\s*free/i', $m['cost'] );
		$data['offers'] = [ '@type' => 'Offer', 'url' => $m['link'] ?: get_permalink( $id ), 'price' => $free ? '0' : $m['cost'], 'priceCurrency' => 'USD', 'availability' => 'https://schema.org/InStock' ];
		$data['isAccessibleForFree'] = $free;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 20 );
