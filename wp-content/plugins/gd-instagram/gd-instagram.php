<?php
/**
 * Plugin Name: {{SITE_NAME}} – Instagram Feed
 * Description: Clean Instagram grid. Two sources: Apify scrape of a public profile (no Meta app needed) or the official Instagram API with Instagram Login. Cached, images stored locally, block + shortcode.
 * Version: 1.1.0
 * Author: Grater Digital
 * Text Domain: gd-instagram
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'GD_IG_VERSION', '1.1.0' );
define( 'GD_IG_DIR', plugin_dir_path( __FILE__ ) );
define( 'GD_IG_URL', plugin_dir_url( __FILE__ ) );
define( 'GD_IG_CRON', 'gd_instagram_refresh_token' );

require_once GD_IG_DIR . 'includes/settings.php';
require_once GD_IG_DIR . 'includes/auth.php';
require_once GD_IG_DIR . 'includes/api.php';
require_once GD_IG_DIR . 'includes/render.php';
require_once GD_IG_DIR . 'includes/apify.php';

register_activation_hook( __FILE__, function () {
	if ( ! wp_next_scheduled( GD_IG_CRON ) ) {
		wp_schedule_event( time() + DAY_IN_SECONDS, 'daily', GD_IG_CRON );
	}
} );
register_deactivation_hook( __FILE__, fn() => wp_clear_scheduled_hook( GD_IG_CRON ) );

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'gd-instagram', GD_IG_URL . 'assets/instagram.css', [], GD_IG_VERSION );
} );
