<?php
/** Single event — uses the theme's page hero when available. */
get_header();
while ( have_posts() ) {
	the_post();
	$m = gd_events_get_meta( get_the_ID() );
	if ( function_exists( 'gd_page_hero' ) ) {
		gd_page_hero( get_the_title(), $m['when'] ?: __( 'Event', 'gd-events' ), has_excerpt() ? get_the_excerpt() : '', function_exists( 'gd_img' ) ? gd_img( 'events.jpg' ) : '' );
	} else {
		echo '<h1>' . esc_html( get_the_title() ) . '</h1>';
	}
	gd_events_single_content();
}
get_footer();
