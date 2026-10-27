<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'gd-starter' ); ?></a>

<header class="gd-header" data-gd-header>
	<div class="gd-wrap gd-header__inner">
		<div class="gd-header__brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="gd-header__wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><span><?php bloginfo( 'name' ); ?></span></a>
			<?php endif; ?>
		</div>

		<nav class="gd-nav" id="gd-nav" aria-label="<?php esc_attr_e( 'Primary', 'gd-starter' ); ?>">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'gd-nav__list',
				'depth'          => 2,
				'fallback_cb'    => function () {
					echo '<ul class="gd-nav__list">';
					foreach ( gd_site_config()['nav'] ?? [] as $label => $url ) {
						echo '<li><a href="' . esc_url( home_url( $url ) ) . '">' . esc_html( $label ) . '</a></li>';
					}
					echo '</ul>';
				},
			] );
			?>
			<div class="gd-nav__cta">
				<?php if ( ! empty( gd_site_config()['whatsapp'] ) ) : ?>
				<a class="gd-btn gd-btn--whatsapp-sm" href="<?php echo esc_url( gd_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo gd_icon( 'whatsapp' ); ?> WhatsApp</a>
				<?php endif; ?>
				<a class="gd-btn gd-btn--primary gd-btn--sm" href="<?php echo esc_url( home_url( gd_site_config()['cta_url'] ?? '/contact/' ) ); ?>"><?php echo esc_html( gd_site_config()['cta_label'] ?? 'Get in touch' ); ?></a>
			</div>
		</nav>

		<button class="gd-header__toggle" aria-expanded="false" aria-controls="gd-nav" data-gd-nav-toggle>
			<?php echo gd_icon( 'menu' ) . gd_icon( 'close' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'gd-starter' ); ?></span>
		</button>
	</div>
</header>

<main id="main" class="gd-main">
