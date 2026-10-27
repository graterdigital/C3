<?php
defined( 'ABSPATH' ) || exit;

function gd_events_register_post_type(): void {
	register_post_type( 'event', [
		'labels' => [
			'name'               => __( 'Events', 'gd-events' ),
			'singular_name'      => __( 'Event', 'gd-events' ),
			'add_new_item'       => __( 'Add New Event', 'gd-events' ),
			'edit_item'          => __( 'Edit Event', 'gd-events' ),
			'all_items'          => __( 'All Events', 'gd-events' ),
			'search_items'       => __( 'Search Events', 'gd-events' ),
			'not_found'          => __( 'No events found', 'gd-events' ),
		],
		'public'        => true,
		'has_archive'   => 'events',
		'rewrite'       => [ 'slug' => 'events', 'with_front' => false ],
		'menu_icon'     => 'dashicons-calendar-alt',
		'menu_position' => 5,
		'show_in_rest'  => true,
		'supports'      => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ],
		'template'      => [ [ 'core/paragraph', [ 'placeholder' => 'What’s happening, who it’s for, what to bring…' ] ] ],
	] );
}
add_action( 'init', 'gd_events_register_post_type' );

/**
 * Archive query: upcoming (soonest first) then past (most recent first) — handled in the template,
 * so the main query just fetches everything ordered by start date.
 */
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'event' ) ) {
		return;
	}
	$q->set( 'meta_key', '_gd_start' );
	$q->set( 'orderby', 'meta_value' );
	$q->set( 'order', 'DESC' );
	$q->set( 'posts_per_page', -1 );
} );

/** Admin list: sort by start date, show it as a column. */
add_filter( 'manage_event_posts_columns', function ( array $c ) {
	$out = [];
	foreach ( $c as $k => $v ) {
		$out[ $k ] = $v;
		if ( $k === 'title' ) {
			$out['gd_start'] = __( 'When', 'gd-events' );
			$out['gd_venue'] = __( 'Where', 'gd-events' );
		}
	}
	unset( $out['date'] );
	return $out;
} );
add_action( 'manage_event_posts_custom_column', function ( $col, $id ) {
	$m = gd_events_get_meta( $id );
	if ( $col === 'gd_start' ) {
		echo esc_html( $m['when'] ?: '—' );
		if ( $m['is_past'] ) {
			echo ' <span class="dashicons dashicons-backup" title="Past" style="color:#999"></span>';
		}
	}
	if ( $col === 'gd_venue' ) {
		echo esc_html( $m['venue'] ?: '—' );
	}
}, 10, 2 );
add_filter( 'manage_edit-event_sortable_columns', fn( $c ) => $c + [ 'gd_start' => 'gd_start' ] );
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() && $q->is_main_query() && $q->get( 'post_type' ) === 'event' && in_array( $q->get( 'orderby' ), [ 'gd_start', '' ], true ) ) {
		$q->set( 'meta_key', '_gd_start' );
		$q->set( 'orderby', 'meta_value' );
		if ( ! $q->get( 'order' ) ) {
			$q->set( 'order', 'DESC' );
		}
	}
} );
