<?php $cfg = gd_site_config(); ?>
<!-- wp:paragraph {"className":"gd-lede"} -->
<p class="gd-lede"><?php echo gd_copy( 'donate.lede' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:html -->
<ul class="gd-give alignwide">
<li><?php echo gd_icon( 'star' ); ?><h3><?php echo gd_copy( 'donate.money.title' ); ?></h3><p><?php echo gd_copy( 'donate.money.text' ); ?></p><a class="gd-btn gd-btn--primary" href="<?php echo esc_url( $cfg['donate_url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $cfg['donate_label'] ); ?></a></li>
<li><?php echo gd_icon( 'check' ); ?><h3><?php echo gd_copy( 'donate.materials.title' ); ?></h3><p><?php echo gd_copy( 'donate.materials.text' ); ?></p><a class="gd-btn gd-btn--ghost" href="/contact/"><?php echo gd_copy( 'donate.materials.cta' ); ?></a></li>
<li><?php echo gd_icon( 'pin' ); ?><h3><?php echo gd_copy( 'donate.time.title' ); ?></h3><p><?php echo gd_copy( 'donate.time.text' ); ?></p><a class="gd-btn gd-btn--ghost" href="/get-involved/#volunteer"><?php echo gd_copy( 'donate.time.cta' ); ?></a></li>
</ul>
<!-- /wp:html -->
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'donate.where.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'donate.where.p1' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'donate.where.p2' ); ?> <a href="/contact/">Get in touch.</a></p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'donate.hire.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'donate.hire.text' ); ?> <a href="/contact/">Ask for a quote.</a></p>
<!-- /wp:paragraph -->
