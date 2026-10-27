<?php
defined( 'ABSPATH' ) || exit;

function gd_ig_auth_url(): string {
	$state = wp_create_nonce( 'gd_ig_oauth' );
	return add_query_arg( [
		'client_id'     => gd_ig_get( 'app_id' ),
		'redirect_uri'  => gd_ig_redirect_uri(),
		'response_type' => 'code',
		'scope'         => 'instagram_business_basic',
		'state'         => $state,
	], 'https://www.instagram.com/oauth/authorize' );
}

function gd_ig_settings_redirect( string $msg ): void {
	wp_safe_redirect( add_query_arg( [ 'page' => 'gd-instagram', 'gd_ig' => rawurlencode( $msg ) ], admin_url( 'options-general.php' ) ) );
	exit;
}

/** OAuth callback: code → short-lived → long-lived token. */
add_action( 'admin_post_gd_instagram_callback', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Please log in to WordPress first, then connect Instagram again.' );
	}
	if ( ! isset( $_GET['code'] ) || ! wp_verify_nonce( $_GET['state'] ?? '', 'gd_ig_oauth' ) ) {
		gd_ig_settings_redirect( 'Instagram did not return a valid code. ' . sanitize_text_field( wp_unslash( $_GET['error_description'] ?? '' ) ) );
	}
	$code = rtrim( sanitize_text_field( wp_unslash( $_GET['code'] ) ), '#_' );

	$res = wp_remote_post( 'https://api.instagram.com/oauth/access_token', [
		'timeout' => 20,
		'body'    => [
			'client_id'     => gd_ig_get( 'app_id' ),
			'client_secret' => gd_ig_get( 'app_secret' ),
			'grant_type'    => 'authorization_code',
			'redirect_uri'  => gd_ig_redirect_uri(),
			'code'          => $code,
		],
	] );
	$body = is_wp_error( $res ) ? [] : json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['access_token'] ) ) {
		gd_ig_settings_redirect( 'Token exchange failed: ' . ( is_wp_error( $res ) ? $res->get_error_message() : wp_remote_retrieve_body( $res ) ) );
	}

	$long = gd_ig_exchange_long_lived( $body['access_token'] );
	if ( is_wp_error( $long ) ) {
		gd_ig_settings_redirect( 'Long-lived token exchange failed: ' . $long->get_error_message() );
	}
	gd_ig_set( [
		'access_token' => $long['access_token'],
		'expires_at'   => time() + (int) ( $long['expires_in'] ?? 60 * DAY_IN_SECONDS ),
		'user_id'      => (string) ( $body['user_id'] ?? '' ),
	] );
	gd_ig_store_profile();
	delete_transient( 'gd_instagram_feed' );
	gd_ig_settings_redirect( 'Instagram connected.' );
} );

function gd_ig_exchange_long_lived( string $short ): array|WP_Error {
	$res = wp_remote_get( add_query_arg( [
		'grant_type'    => 'ig_exchange_token',
		'client_secret' => gd_ig_get( 'app_secret' ),
		'access_token'  => $short,
	], 'https://graph.instagram.com/access_token' ), [ 'timeout' => 20 ] );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	return ! empty( $body['access_token'] ) ? $body : new WP_Error( 'gd_ig_exchange', wp_remote_retrieve_body( $res ) );
}

/** Refresh the long-lived token (valid 60 days; refreshable once it's >24h old). */
function gd_ig_refresh_token(): bool|WP_Error {
	$token = gd_ig_get( 'access_token' );
	if ( ! $token ) {
		return new WP_Error( 'gd_ig_no_token', 'Not connected.' );
	}
	$res = wp_remote_get( add_query_arg( [ 'grant_type' => 'ig_refresh_token', 'access_token' => $token ], 'https://graph.instagram.com/refresh_access_token' ), [ 'timeout' => 20 ] );
	if ( is_wp_error( $res ) ) {
		return $res;
	}
	$body = json_decode( wp_remote_retrieve_body( $res ), true );
	if ( empty( $body['access_token'] ) ) {
		return new WP_Error( 'gd_ig_refresh', wp_remote_retrieve_body( $res ) );
	}
	gd_ig_set( [ 'access_token' => $body['access_token'], 'expires_at' => time() + (int) ( $body['expires_in'] ?? 60 * DAY_IN_SECONDS ), 'last_refresh' => time() ] );
	return true;
}

/** Daily cron: refresh when fewer than 30 days remain (and token is older than a day). */
add_action( GD_IG_CRON, function () {
	$expires = (int) gd_ig_get( 'expires_at' );
	if ( gd_ig_get( 'access_token' ) && $expires && $expires - time() < 30 * DAY_IN_SECONDS ) {
		gd_ig_refresh_token();
	}
} );

add_action( 'admin_post_gd_instagram_refresh_now', function () {
	check_admin_referer( 'gd_ig_refresh' );
	$r = gd_ig_refresh_token();
	gd_ig_settings_redirect( is_wp_error( $r ) ? 'Refresh failed: ' . $r->get_error_message() : 'Token refreshed.' );
} );
add_action( 'admin_post_gd_instagram_clear_cache', function () {
	check_admin_referer( 'gd_ig_clear' );
	delete_transient( 'gd_instagram_feed' );
	gd_ig_settings_redirect( 'Feed cache cleared.' );
} );
add_action( 'admin_post_gd_instagram_disconnect', function () {
	check_admin_referer( 'gd_ig_disconnect' );
	gd_ig_set( [ 'access_token' => '', 'expires_at' => 0, 'user_id' => '', 'username' => '' ] );
	delete_transient( 'gd_instagram_feed' );
	gd_ig_settings_redirect( 'Disconnected.' );
} );
