</main>

<?php if ( ! is_page( [ 'contact', 'get-involved' ] ) ) : ?>
<section class="gd-cta-band">
	<div class="gd-wrap gd-cta-band__inner">
		<div>
			<p class="gd-eyebrow gd-eyebrow--light"><?php esc_html_e( 'Ready when you are', 'gd-starter' ); ?></p>
			<h2><?php echo gd_copy( 'footer.cta.heading' ) ?: esc_html( gd_site_config()['cta_heading'] ?? '' ); ?></h2>
			<p><?php echo gd_copy( 'footer.cta.text' ) ?: esc_html( gd_site_config()['cta_text'] ?? '' ); ?></p>
		</div>
		<div class="gd-cta-band__actions">
			<a class="gd-btn gd-btn--light" href="<?php echo esc_url( home_url( gd_site_config()['cta_url'] ?? '/contact/' ) ); ?>"><?php echo esc_html( gd_site_config()['cta_label'] ?? 'Get in touch' ); ?></a>
			<?php if ( ! empty( gd_site_config()['cta_secondary_label'] ) ) : ?>
			<a class="gd-btn gd-btn--outline-light" href="<?php echo esc_url( home_url( gd_site_config()['cta_secondary_url'] ?? '/get-involved/' ) ); ?>"><?php echo esc_html( gd_site_config()['cta_secondary_label'] ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( gd_site_config()['whatsapp'] ) ) : ?>
			<a class="gd-btn gd-btn--whatsapp" href="<?php echo esc_url( gd_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo gd_icon( 'whatsapp' ); ?> WhatsApp us</a>
			<?php endif; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<footer class="gd-footer">
	<div class="gd-wrap gd-footer__grid">
		<div class="gd-footer__brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
				<p class="gd-header__wordmark"><span><?php bloginfo( 'name' ); ?></span></p>
			<?php endif; ?>
			<p class="gd-footer__tagline"><?php echo esc_html( gd_get( 'tagline' ) ); ?></p>
			<?php gd_social_links( 'gd-social gd-social--footer' ); ?>
		</div>

		<div class="gd-footer__col">
			<h3><?php esc_html_e( 'Explore', 'gd-starter' ); ?></h3>
			<?php
			wp_nav_menu( [
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'gd-footer__links',
				'depth'          => 1,
				'fallback_cb'    => function () {
					echo '<ul class="gd-footer__links">';
					foreach ( gd_site_config()['nav'] ?? [] as $label => $url ) {
						echo '<li><a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $label ) . '</a></li>';
					}
					echo '</ul>';
				},
			] );
			?>
		</div>

		<div class="gd-footer__col">
			<h3><?php esc_html_e( 'Contact', 'gd-starter' ); ?></h3>
			<ul class="gd-footer__contact">
				<li><?php echo gd_icon( 'pin' ); ?><span><?php echo esc_html( gd_get( 'address' ) ); ?></span></li>
				<?php if ( gd_get( 'phone' ) ) : ?><li><?php echo gd_icon( 'phone' ); ?><a href="tel:+<?php echo esc_attr( gd_get( 'phone_e164' ) ); ?>"><?php echo esc_html( gd_get( 'phone' ) ); ?></a></li><?php endif; ?>
				<li><?php echo gd_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( gd_get( 'email' ) ); ?>"><?php echo esc_html( gd_get( 'email' ) ); ?></a></li>
			</ul>
			<?php if ( ! empty( gd_site_config()['donate_url'] ) ) : ?>
				<a class="gd-btn gd-btn--primary gd-btn--sm gd-footer__donate" href="<?php echo esc_url( gd_site_config()['donate_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( gd_site_config()['donate_label'] ?? 'Donate' ); ?></a>
			<?php endif; ?>
			<?php if ( function_exists( 'gd_reviews_summary_html' ) ) : ?>
				<div class="gd-footer__reviews"><?php echo gd_reviews_summary_html(); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<div class="gd-wrap gd-footer__bottom">
		<p>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.<?php if ( gd_get( 'founded' ) && gd_get( 'founded' ) !== date_i18n( 'Y' ) ) { echo ' '; printf( esc_html__( 'Since %s.', 'gd-starter' ), esc_html( gd_get( 'founded' ) ) ); } ?></p>
		<p class="gd-footer__credit"><a href="https://graterdigital.com" rel="nofollow noopener" target="_blank">Site by Grater Digital</a></p>
	</div>
</footer>

<?php if ( ! empty( gd_site_config()['whatsapp'] ) ) { gd_whatsapp_fab(); } ?>
<?php wp_footer(); ?>
</body>
</html>
