<?php
defined( 'ABSPATH' ) || exit;

function gd_ig_store_profile(): void {
	$res = wp_remote_get( add_query_arg( [
		'fields'       => 'username,media_count,followers_count,profile_picture_url',
		'access_token' => gd_ig_get( 'access_token' ),
	], 'https://graph.instagram.com/me' ), [ 'timeout' => 15 ] );
	$body = is_wp_error( $res ) ? [] : json_decode( wp_remote_retrieve_body( $res ), true );
	if ( ! empty( $body['username'] ) ) {
		gd_ig_set( [
			'username'        => $body['username'],
			'handle'          => $body['username'],
			'followers_count' => (int) ( $body['followers_count'] ?? 0 ),
			'profile_picture' => $body['profile_picture_url'] ?? '',
		] );
	}
}

/**
 * Get recent media, cached. Falls back to the last good response if the API fails,
 * so a token hiccup never blanks the home page.
 *
 * @return array[] [ id, type, image, permalink, caption, timestamp ]
 */
function gd_ig_get_media( int $limit = 12 ): array {
	$limit  = max( 1, min( 50, $limit ) );
	if ( gd_ig_get( 'source', 'apify' ) === 'apify' ) {
		$stored = get_option( 'gd_instagram_last_good', [] );
		if ( ! $stored && gd_ig_get( 'apify_token' ) && ! get_option( 'gd_instagram_last_refresh' ) ) {
			gd_ig_apify_refresh( max( $limit, 12 ) ); // first ever render: populate synchronously
			$stored = get_option( 'gd_instagram_last_good', [] );
		}
		return array_slice( is_array( $stored ) ? $stored : [], 0, $limit );
	}
	$cached = get_transient( 'gd_instagram_feed' );
	if ( is_array( $cached ) && count( $cached ) >= $limit ) {
		return array_slice( $cached, 0, $limit );
	}
	$token = gd_ig_get( 'access_token' );
	if ( ! $token ) {
		return [];
	}
	$res = wp_remote_get( add_query_arg( [
		'fields'       => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp,children{media_type,media_url,thumbnail_url}',
		'limit'        => max( $limit, 12 ),
		'access_token' => $token,
	], 'https://graph.instagram.com/me/media' ), [ 'timeout' => 20 ] );

	$body = is_wp_error( $res ) ? [] : json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['data'] ) || ! is_array( $body['data'] ) ) {
		$fallback = get_option( 'gd_instagram_last_good', [] );
		return array_slice( $fallback, 0, $limit );
	}

	$items = [];
	foreach ( $body['data'] as $m ) {
		$image = $m['media_type'] === 'VIDEO' ? ( $m['thumbnail_url'] ?? '' ) : ( $m['media_url'] ?? '' );
		if ( ! $image && ! empty( $m['children']['data'][0] ) ) {
			$c     = $m['children']['data'][0];
			$image = $c['media_type'] === 'VIDEO' ? ( $c['thumbnail_url'] ?? '' ) : ( $c['media_url'] ?? '' );
		}
		if ( ! $image ) {
			continue;
		}
		$items[] = [
			'id'        => $m['id'],
			'type'      => $m['media_type'],
			'image'     => $image,
			'permalink' => $m['permalink'] ?? '',
			'caption'   => $m['caption'] ?? '',
			'timestamp' => $m['timestamp'] ?? '',
		];
	}
	set_transient( 'gd_instagram_feed', $items, (int) gd_ig_get( 'cache_hours', 6 ) * HOUR_IN_SECONDS );
	update_option( 'gd_instagram_last_good', $items, false );
	return array_slice( $items, 0, $limit );
}
