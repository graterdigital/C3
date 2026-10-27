<?php
defined( 'ABSPATH' ) || exit;

/**
 * Apify source: scrape a PUBLIC profile's recent posts with apify/instagram-scraper.
 * No Meta app, no OAuth. Images are sideloaded into the Media Library because
 * Instagram CDN URLs are signed and expire within days.
 *
 * Mode is chosen in Settings → Instagram Feed → Source.
 */

function gd_ig_apify_fetch( int $limit = 12 ): array|WP_Error {
	$token = trim( (string) gd_ig_get( 'apify_token' ) );
	$user  = ltrim( trim( (string) gd_ig_get( 'handle' ) ), '@' );
	if ( ! $token || ! $user ) {
		return new WP_Error( 'gd_ig_apify_config', 'Apify token or Instagram handle missing.' );
	}
	$res = wp_remote_post( 'https://api.apify.com/v2/acts/apify~instagram-scraper/run-sync-get-dataset-items?timeout=120&maxTotalChargeUsd=0.5', [
		'timeout' => 130,
		'headers' => [ 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ],
		'body'    => wp_json_encode( [
			'directUrls'    => [ 'https://www.instagram.com/' . $user . '/' ],
			'resultsType'   => 'posts',
			'resultsLimit'  => max( 1, min( 50, $limit ) ),
			'addParentData' => false,
		] ),
	] );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$code = wp_remote_retrieve_response_code( $res );
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( $code >= 400 || ! is_array( $body ) ) {
		return new WP_Error( 'gd_ig_apify_http', 'Apify HTTP ' . $code . ': ' . substr( wp_remote_retrieve_body( $res ), 0, 300 ) );
	}
	if ( isset( $body[0]['error'] ) ) {
		return new WP_Error( 'gd_ig_apify_empty', $body[0]['errorDescription'] ?? $body[0]['error'] );
	}

	$items = [];
	foreach ( $body as $p ) {
		if ( empty( $p['displayUrl'] ) || empty( $p['shortCode'] ) ) {
			continue;
		}
		$items[] = [
			'id'        => (string) $p['shortCode'],
			'type'      => match ( $p['type'] ?? 'Image' ) { 'Video' => 'VIDEO', 'Sidecar' => 'CAROUSEL_ALBUM', default => 'IMAGE' },
			'remote'    => $p['displayUrl'],
			'permalink' => $p['url'] ?? ( 'https://www.instagram.com/p/' . $p['shortCode'] . '/' ),
			'caption'   => (string) ( $p['caption'] ?? '' ),
			'timestamp' => (string) ( $p['timestamp'] ?? '' ),
		];
	}
	usort( $items, fn( $a, $b ) => strcmp( $b['timestamp'], $a['timestamp'] ) );
	return $items;
}

/** Sideload a post image once; returns attachment id (0 on failure). Keyed by shortcode. */
function gd_ig_attachment_for( array $item ): int {
	$map = get_option( 'gd_instagram_media_map', [] );
	if ( ! empty( $map[ $item['id'] ] ) && get_post( $map[ $item['id'] ] ) ) {
		return (int) $map[ $item['id'] ];
	}
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = download_url( $item['remote'], 60 );
	if ( is_wp_error( $tmp ) ) {
		return 0;
	}
	$alt = wp_trim_words( wp_strip_all_tags( $item['caption'] ), 15, '' );
	$id  = media_handle_sideload( [ 'name' => 'instagram-' . $item['id'] . '.jpg', 'tmp_name' => $tmp ], 0, $alt, [
		'post_title'   => $alt ?: 'Instagram ' . $item['id'],
		'post_excerpt' => $item['caption'],
	] );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_gd_ig_shortcode', $item['id'] );
	update_post_meta( $id, '_gd_ig_permalink', $item['permalink'] );
	$map[ $item['id'] ] = (int) $id;
	update_option( 'gd_instagram_media_map', $map, false );
	return (int) $id;
}

/**
 * Refresh: scrape, sideload new images, store a renderable list (local URLs) in an option.
 * Returns a summary string.
 */
function gd_ig_apify_refresh( int $limit = 12 ): string {
	$items = gd_ig_apify_fetch( $limit );
	if ( is_wp_error( $items ) ) {
		update_option( 'gd_instagram_last_refresh', [ 'time' => current_time( 'mysql' ), 'summary' => 'Error: ' . $items->get_error_message() ], false );
		return 'Error: ' . $items->get_error_message();
	}
	$out = [];
	$new = 0;
	foreach ( $items as $it ) {
		$before = get_option( 'gd_instagram_media_map', [] );
		$att    = gd_ig_attachment_for( $it );
		if ( ! $att ) {
			continue;
		}
		if ( ! isset( $before[ $it['id'] ] ) ) {
			$new++;
		}
		$out[] = [
			'id'        => $it['id'],
			'type'      => $it['type'],
			'image'     => wp_get_attachment_image_url( $att, 'gd-square' ) ?: wp_get_attachment_url( $att ),
			'srcset'    => wp_get_attachment_image_srcset( $att, 'gd-square' ) ?: '',
			'permalink' => $it['permalink'],
			'caption'   => $it['caption'],
			'timestamp' => $it['timestamp'],
		];
	}
	if ( $out ) {
		update_option( 'gd_instagram_last_good', $out, false );
		delete_transient( 'gd_instagram_feed' );
	}
	$summary = sprintf( '%d posts, %d new images', count( $out ), $new );
	update_option( 'gd_instagram_last_refresh', [ 'time' => current_time( 'mysql' ), 'summary' => $summary ], false );
	return $summary;
}

/** Weekly cron for the Apify mode (reuses the plugin's daily cron hook; refreshes when stale). */
add_action( GD_IG_CRON, function () {
	if ( gd_ig_get( 'source', 'apify' ) !== 'apify' ) {
		return;
	}
	$last = get_option( 'gd_instagram_last_refresh', [] );
	$days = max( 1, (int) gd_ig_get( 'apify_refresh_days', 7 ) );
	if ( empty( $last['time'] ) || ( time() - strtotime( $last['time'] ) ) > $days * DAY_IN_SECONDS ) {
		gd_ig_apify_refresh( (int) gd_ig_get( 'apify_limit', 12 ) );
	}
} );

add_action( 'admin_post_gd_instagram_apify_refresh', function () {
	check_admin_referer( 'gd_ig_apify_refresh' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Nope.' );
	}
	gd_ig_settings_redirect( 'Instagram refresh: ' . gd_ig_apify_refresh( (int) gd_ig_get( 'apify_limit', 12 ) ) );
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'gd-instagram refresh', fn() => WP_CLI::log( gd_ig_apify_refresh( (int) gd_ig_get( 'apify_limit', 12 ) ) ) );
}
