<?php
/**
 * One-shot: enable the Apify TripAdvisor source and run the first sync.
 *   APIFY_TOKEN=apify_api_xxx wp eval-file scripts/configure-reviews.php
 * Optional env: GD_MIN_RATING (default 4), GD_LANGS (default en), GD_AUTO_PUBLISH (default 1)
 */
if ( ! defined( 'ABSPATH' ) ) { exit( "Run via wp eval-file\n" ); }
$token = getenv( 'APIFY_TOKEN' );
if ( ! $token ) { WP_CLI::error( 'Set APIFY_TOKEN=… before the command.' ); }

$o = get_option( 'gd_reviews_options', [] );
$o['apify_enabled']      = 1;
$o['apify_token']        = $token;
$o['apify_actor']        = 'maxcopell/tripadvisor-reviews';
$o['apify_refresh_days'] = 30;
$o['feed_enabled']       = 0;
$o['feed_langs']         = getenv( 'GD_LANGS' ) ?: 'en';
$o['min_rating']         = (int) ( getenv( 'GD_MIN_RATING' ) ?: 4 );
$o['auto_publish']       = getenv( 'GD_AUTO_PUBLISH' ) === '0' ? 0 : 1;
$o['interval']           = 'daily';
update_option( 'gd_reviews_options', $o );
wp_clear_scheduled_hook( GD_REVIEWS_CRON );
gd_reviews_schedule( 'daily' );

WP_CLI::log( 'Syncing from Apify…' );
WP_CLI::log( gd_reviews_run_sync() );
$agg = gd_reviews_aggregate();
WP_CLI::success( sprintf( 'Reviews on site: %d published · badge shows %s★ from %d reviews.', (int) wp_count_posts( 'gd_review' )->publish, $agg['average'], $agg['count'] ) );
