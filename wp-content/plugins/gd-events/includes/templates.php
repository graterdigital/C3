<?php
defined( 'ABSPATH' ) || exit;

/** Theme can override with single-event.php / archive-event.php; otherwise the plugin templates render inside the theme's header/footer. */
add_filter( 'template_include', function ( string $template ) {
	if ( is_singular( 'event' ) && ! locate_template( 'single-event.php' ) ) {
		return GD_EVENTS_DIR . 'templates/single-event.php';
	}
	if ( is_post_type_archive( 'event' ) && ! locate_template( 'archive-event.php' ) ) {
		return GD_EVENTS_DIR . 'templates/archive-event.php';
	}
	return $template;
} );

/** Reusable single-event body. */
function gd_events_single_content( int $post_id = 0 ): void {
	$post_id = $post_id ?: get_the_ID();
	$m       = gd_events_get_meta( $post_id );
	?>
	<article <?php post_class( 'gd-event-single' ); ?>>
		<div class="gd-wrap gd-event-single__layout">
			<div class="gd-event-single__main entry-content">
				<?php if ( $m['is_past'] ) : ?>
					<p class="gd-eyebrow"><?php esc_html_e( 'Past event', 'gd-events' ); ?></p>
				<?php endif; ?>
				<?php echo apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ); ?>
				<?php if ( has_post_thumbnail( $post_id ) ) : ?>
					<figure class="gd-event-single__poster"><?php echo get_the_post_thumbnail( $post_id, 'large', [ 'loading' => 'lazy' ] ); ?></figure>
				<?php endif; ?>
			</div>
			<aside class="gd-event-single__aside">
				<div class="gd-event-single__card">
					<dl>
						<?php if ( $m['when'] ) : ?><div><dt><?php esc_html_e( 'When', 'gd-events' ); ?></dt><dd><?php echo esc_html( $m['when'] ); ?></dd></div><?php endif; ?>
						<?php if ( $m['venue'] || $m['address'] ) : ?>
							<div><dt><?php esc_html_e( 'Where', 'gd-events' ); ?></dt><dd><?php echo esc_html( $m['venue'] ); ?><?php if ( $m['address'] ) : ?><br><a href="<?php echo esc_url( $m['maps_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $m['address'] ); ?></a><?php endif; ?></dd></div>
						<?php endif; ?>
						<?php if ( $m['cost'] ) : ?><div><dt><?php esc_html_e( 'Cost', 'gd-events' ); ?></dt><dd><?php echo esc_html( $m['cost'] ); ?></dd></div><?php endif; ?>
						<?php if ( $m['organizer'] ) : ?><div><dt><?php esc_html_e( 'Hosted with', 'gd-events' ); ?></dt><dd><?php echo esc_html( $m['organizer'] ); ?></dd></div><?php endif; ?>
					</dl>
					<?php if ( $m['link'] && ! $m['is_past'] ) : ?>
						<a class="gd-btn gd-btn--primary" href="<?php echo esc_url( $m['link'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $m['link_label'] ); ?> <?php echo gd_events_icon( 'arrow' ); ?></a>
					<?php endif; ?>
					<?php if ( $m['start_dt'] && ! $m['is_past'] ) : ?>
						<a class="gd-btn gd-btn--ghost" href="<?php echo esc_url( gd_events_gcal_url( $post_id, $m ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Add to Google Calendar', 'gd-events' ); ?></a>
					<?php endif; ?>
					<a class="gd-link" href="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>">← <?php esc_html_e( 'All events', 'gd-events' ); ?></a>
				</div>
			</aside>
		</div>
	</article>
	<?php
}

function gd_events_gcal_url( int $post_id, array $m ): string {
	$fmt = fn( DateTimeImmutable $d ) => $d->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
	$start = $m['start_dt'];
	$end   = $m['end_dt'] ?: $start->modify( '+2 hours' );
	if ( $m['all_day'] ) {
		$dates = $start->format( 'Ymd' ) . '/' . ( $m['end_dt'] ?: $start )->modify( '+1 day' )->format( 'Ymd' );
	} else {
		$dates = $fmt( $start ) . '/' . $fmt( $end );
	}
	return add_query_arg( [
		'action'   => 'TEMPLATE',
		'text'     => rawurlencode( get_the_title( $post_id ) ),
		'dates'    => $dates,
		'details'  => rawurlencode( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_id ) ?: get_post_field( 'post_content', $post_id ) ), 40 ) . ' ' . get_permalink( $post_id ) ),
		'location' => rawurlencode( trim( $m['venue'] . ( $m['address'] ? ', ' . $m['address'] : '' ), ', ' ) ),
	], 'https://calendar.google.com/calendar/render' );
}
