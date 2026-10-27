<!-- wp:group {"align":"full","className":"gd-section gd-section--ruled"} -->
<div class="wp-block-group alignfull gd-section gd-section--ruled"><!-- wp:group {"align":"wide","className":"gd-section__head"} -->
<div class="wp-block-group alignwide gd-section__head"><!-- wp:group -->
<div class="wp-block-group"><!-- wp:heading {"className":"gd-section__title"} -->
<h2 class="wp-block-heading gd-section__title"><?php echo gd_copy( 'steps.heading' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group --><!-- wp:paragraph -->
<p><?php echo gd_copy( 'steps.intro' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><!-- wp:html -->
<ol class="gd-steps gd-wrap">
<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
<li><h3><?php echo gd_copy( "steps.$i.title" ); ?></h3><p><?php echo gd_copy( "steps.$i.text" ); ?></p></li>
<?php endfor; ?>
</ol>
<!-- /wp:html --></div>
<!-- /wp:group -->
<!-- wp:cover {"url":"<?php echo esc_url( gd_img( 'band.jpg' ) ); ?>","dimRatio":30,"minHeight":60,"minHeightUnit":"vh","align":"full","className":"gd-photo-band"} -->
<div class="wp-block-cover alignfull gd-photo-band" style="min-height:60vh"><img class="wp-block-cover__image-background" alt="Volunteer laying new flooring" src="<?php echo esc_url( gd_img( 'band.jpg' ) ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container">
<!-- wp:paragraph {"className":"gd-photo-band__caption"} -->
<p class="gd-photo-band__caption"><?php echo gd_copy( 'band.caption' ); ?></p>
<!-- /wp:paragraph -->
</div></div>
<!-- /wp:cover -->
