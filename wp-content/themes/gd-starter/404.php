<?php
get_header();
gd_page_hero( __( 'That trail doesn’t exist', 'gd-starter' ), '404', __( 'The page you’re looking for has moved or never was. Try one of these.', 'gd-starter' ) );
?>
<section class="gd-section">
	<div class="gd-wrap gd-404">
		<a class="gd-btn gd-btn--primary" href="<?php echo esc_url( home_url( '/treks/' ) ); ?>"><?php esc_html_e( 'See the treks', 'gd-starter' ); ?></a>
		<a class="gd-btn gd-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'gd-starter' ); ?></a>
	</div>
</section>
<?php get_footer(); ?>
