<?php /** Hero — split: flat colour block with the headline, photo tile beside it. Copy: content/copy.json hero.* */ $img = gd_img( 'hero-tall.jpg' ); ?>
<!-- wp:html -->
<div class="gd-hero-split alignfull">
	<div class="gd-hero-split__text">
		<p class="gd-eyebrow"><?php echo gd_copy( 'hero.eyebrow' ); ?></p>
		<h1><?php echo gd_copy( 'hero.headline' ); ?></h1>
		<p class="gd-hero__lede"><?php echo gd_copy( 'hero.lede' ); ?></p>
		<div class="wp-block-buttons"><div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/get-involved/#request-help"><?php echo gd_copy( 'hero.cta1' ); ?></a></div><div class="wp-block-button is-style-outline gd-btn-outline-light"><a class="wp-block-button__link wp-element-button" href="/get-involved/#volunteer"><?php echo gd_copy( 'hero.cta2' ); ?></a></div></div>
	</div>
	<div class="gd-hero-split__media"><img src="<?php echo esc_url( $img ); ?>" alt="A C3 volunteer repairing a window on a neighbor’s home" fetchpriority="high"></div>
</div>
<!-- /wp:html -->
