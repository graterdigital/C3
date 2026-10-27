<?php $img = gd_img( 'intro.jpg' ); ?>
<!-- wp:group {"align":"full","className":"gd-section"} -->
<div class="wp-block-group alignfull gd-section"><!-- wp:columns {"align":"wide","className":"gd-feature"} -->
<div class="wp-block-columns alignwide gd-feature"><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:image {"sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="<?php echo esc_url( $img ); ?>" alt="Volunteer on a ladder finishing drywall in a neighbor’s home"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column --><!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'intro.heading' ); ?></h2>
<!-- /wp:heading --><!-- wp:paragraph -->
<p><?php echo gd_copy( 'intro.p1' ); ?></p>
<!-- /wp:paragraph --><!-- wp:paragraph -->
<p><?php echo gd_copy( 'intro.p2' ); ?></p>
<!-- /wp:paragraph --><!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/about/"><?php echo gd_copy( 'intro.cta' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
