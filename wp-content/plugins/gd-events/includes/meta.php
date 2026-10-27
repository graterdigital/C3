<?php
defined( 'ABSPATH' ) || exit;

/** Single source of truth for event meta. key => [label, type, extra]. Dates stored as 'Y-m-d H:i' site-local. */
function gd_events_fields(): array {
	return [
		'start'     => [ 'label' => 'Starts',               'type' => 'datetime', 'required' => true ],
		'end'       => [ 'label' => 'Ends',                 'type' => 'datetime', 'help' => 'Optional. Same day assumed if blank.' ],
		'all_day'   => [ 'label' => 'All-day event',        'type' => 'checkbox' ],
		'venue'     => [ 'label' => 'Venue',                'type' => 'text', 'placeholder' => 'Red Light Café' ],
		'address'   => [ 'label' => 'Address',              'type' => 'text', 'placeholder' => '553 Amsterdam Ave NE, Atlanta, GA 30306' ],
		'cost'      => [ 'label' => 'Cost',                 'type' => 'text', 'placeholder' => 'Free · Donations welcome · $10 suggested' ],
		'organizer' => [ 'label' => 'Hosted with',          'type' => 'text', 'placeholder' => 'Black Lion Party GA Chapter' ],
		'link'      => [ 'label' => 'RSVP / tickets / info URL', 'type' => 'url' ],
		'link_label'=> [ 'label' => 'Button label',         'type' => 'text', 'placeholder' => 'RSVP' ],
	];
}

add_action( 'init', function () {
	foreach ( gd_events_fields() as $key => $f ) {
		register_meta( 'post', '_gd_' . $key, [
			'object_subtype'    => 'event',
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => fn() => current_user_can( 'edit_posts' ),
			'sanitize_callback' => fn( $v ) => gd_events_sanitize_field( $key, $v ),
		] );
	}
} );

function gd_events_sanitize_field( string $key, $value ): string {
	$f = gd_events_fields()[ $key ] ?? [ 'type' => 'text' ];
	switch ( $f['type'] ) {
		case 'datetime':
			$value = trim( str_replace( 'T', ' ', (string) $value ) );
			if ( $value === '' ) {
				return '';
			}
			$ts = strtotime( $value );
			return $ts ? gmdate( 'Y-m-d H:i', $ts ) : '';
		case 'checkbox':
			return $value ? '1' : '';
		case 'url':
			return esc_url_raw( (string) $value );
		default:
			return sanitize_text_field( (string) $value );
	}
}

/** All meta for an event plus derived values (timestamps, "when" string, is_past). */
function gd_events_get_meta( int $post_id ): array {
	$out = [];
	foreach ( gd_events_fields() as $key => $f ) {
		$out[ $key ] = (string) get_post_meta( $post_id, '_gd_' . $key, true );
	}
	$tz          = wp_timezone();
	$start       = $out['start'] ? date_create_immutable( $out['start'], $tz ) : null;
	$end         = $out['end'] ? date_create_immutable( $out['end'], $tz ) : null;
	$now         = new DateTimeImmutable( 'now', $tz );
	$out['start_dt'] = $start;
	$out['end_dt']   = $end;
	$out['is_past']  = $start ? ( ( $end ?: $start->setTime( 23, 59 ) ) < $now ) : false;
	$out['all_day']  = $out['all_day'] === '1';

	$when = '';
	if ( $start ) {
		$when = wp_date( 'l, F j, Y', $start->getTimestamp() );
		if ( ! $out['all_day'] ) {
			$when .= ', ' . gd_events_fmt_time( $start );
			if ( $end && $end->format( 'Y-m-d' ) === $start->format( 'Y-m-d' ) ) {
				$when .= '–' . gd_events_fmt_time( $end );
			}
		}
		if ( $end && $end->format( 'Y-m-d' ) !== $start->format( 'Y-m-d' ) ) {
			$when .= ' → ' . wp_date( 'F j', $end->getTimestamp() ) . ( $out['all_day'] ? '' : ', ' . gd_events_fmt_time( $end ) );
		}
	}
	$out['when']       = $when;
	$out['time_range'] = $start && ! $out['all_day'] ? gd_events_fmt_time( $start ) . ( $end && $end->format( 'Y-m-d' ) === $start->format( 'Y-m-d' ) ? '–' . gd_events_fmt_time( $end ) : '' ) : ( $out['all_day'] ? __( 'All day', 'gd-events' ) : '' );
	$out['link_label'] = $out['link_label'] ?: __( 'RSVP / details', 'gd-events' );
	$out['maps_url']   = $out['address'] ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( ( $out['venue'] ? $out['venue'] . ', ' : '' ) . $out['address'] ) : '';
	return $out;
}

function gd_events_fmt_time( DateTimeImmutable $d ): string {
	return wp_date( $d->format( 'i' ) === '00' ? 'g a' : 'g:i a', $d->getTimestamp() );
}

/**
 * Query events. scope: upcoming | past | all. Upcoming = soonest first; past = most recent first.
 */
function gd_events_query( string $scope = 'upcoming', int $limit = -1 ): array {
	$tz    = wp_timezone();
	$today = ( new DateTimeImmutable( 'now', $tz ) )->format( 'Y-m-d 00:00' );
	$args  = [
		'post_type'      => 'event',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_key'       => '_gd_start',
		'orderby'        => 'meta_value',
		'order'          => $scope === 'past' ? 'DESC' : 'ASC',
		'no_found_rows'  => true,
	];
	if ( $scope === 'upcoming' ) {
		$args['meta_query'] = [ [ 'key' => '_gd_start', 'value' => $today, 'compare' => '>=', 'type' => 'CHAR' ] ];
	} elseif ( $scope === 'past' ) {
		$args['meta_query'] = [ [ 'key' => '_gd_start', 'value' => $today, 'compare' => '<', 'type' => 'CHAR' ] ];
	}
	return get_posts( $args );
}
