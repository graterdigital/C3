<?php
/** Events archive: upcoming first, then past. */
get_header();
if ( function_exists( 'gd_page_hero' ) ) {
	gd_page_hero( __( 'Events', 'gd-events' ), __( 'Group events & fundraising', 'gd-events' ), __( 'Free pantry builds, food distribution days with our partners, benefit shows, art & craft days. Come build something with us.', 'gd-events' ), function_exists( 'gd_img' ) ? gd_img( 'events.jpg' ) : '' );
} else {
	echo '<h1>' . esc_html__( 'Events', 'gd-events' ) . '</h1>';
}
?>
<div class="gd-wrap gd-events-archive">
	<h2><?php esc_html_e( 'Upcoming', 'gd-events' ); ?></h2>
	<?php echo gd_events_render( [ 'scope' => 'upcoming' ] ); ?>
	<?php $past = gd_events_query( 'past' ); if ( $past ) : ?>
		<h2><?php esc_html_e( 'Past events', 'gd-events' ); ?></h2>
		<?php echo gd_events_render( [ 'scope' => 'past' ] ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
