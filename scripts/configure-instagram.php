<?php
/**
 * One-shot: set the Instagram feed to Apify mode and fetch now.
 *   APIFY_TOKEN=apify_api_xxx IG_HANDLE=brand_handle wp eval-file scripts/configure-instagram.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit( "Run via wp eval-file\n" ); }
$token  = getenv( 'APIFY_TOKEN' );
$handle = ltrim( getenv( 'IG_HANDLE' ) ?: '', '@' );
if ( ! $token ) { WP_CLI::error( 'Set APIFY_TOKEN=… before the command.' ); }

$o = get_option( 'gd_instagram_options', [] );
$o['source']             = 'apify';
$o['apify_token']        = $token;
$o['handle']             = $handle;
$o['apify_limit']        = (int) ( getenv( 'IG_LIMIT' ) ?: 12 );
$o['apify_refresh_days'] = 7;
update_option( 'gd_instagram_options', $o );
delete_option( 'gd_instagram_last_refresh' );
if ( ! wp_next_scheduled( GD_IG_CRON ) ) { wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', GD_IG_CRON ); }

WP_CLI::log( "Fetching @$handle via Apify…" );
WP_CLI::success( gd_ig_apify_refresh( $o['apify_limit'] ) );
