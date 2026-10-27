<?php
/**
 * Plugin Name: C3 – Events
 * Description: Event post type (date, time, venue, RSVP link) with an upcoming/past archive, Event schema, a block and a shortcode. No ACF dependency.
 * Version: 1.0.0
 * Author: Grater Digital
 * Text Domain: gd-events
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'GD_EVENTS_VERSION', '1.0.0' );
define( 'GD_EVENTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'GD_EVENTS_URL', plugin_dir_url( __FILE__ ) );

require_once GD_EVENTS_DIR . 'includes/post-type.php';
require_once GD_EVENTS_DIR . 'includes/meta.php';
require_once GD_EVENTS_DIR . 'includes/admin.php';
require_once GD_EVENTS_DIR . 'includes/render.php';
require_once GD_EVENTS_DIR . 'includes/templates.php';
require_once GD_EVENTS_DIR . 'includes/schema.php';

register_activation_hook( __FILE__, function () {
	gd_events_register_post_type();
	flush_rewrite_rules();
} );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/** Styles are small and shared with the theme tokens; load wherever an event list or single is shown. */
add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'gd-events', GD_EVENTS_URL . 'assets/events.css', [], GD_EVENTS_VERSION );
	if ( is_singular( 'event' ) || is_post_type_archive( 'event' ) ) {
		wp_enqueue_style( 'gd-events' );
	}
} );
