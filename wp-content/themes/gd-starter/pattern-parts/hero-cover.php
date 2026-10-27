<?php /** Hero — core/cover. Set the image in the editor or swap the placeholder below. */ $img = gd_img( 'hero.jpg' ); ?>
<!-- wp:cover {"url":"<?php echo esc_url( $img ); ?>","dimRatio":0,"minHeight":92,"minHeightUnit":"vh","align":"full","className":"gd-hero"} -->
<div class="wp-block-cover alignfull gd-hero" style="min-height:92vh"><img class="wp-block-cover__image-background" alt="A C3 volunteer repairing a window on a neighbor’s home" src="<?php echo esc_url( $img ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container">
<!-- wp:paragraph {"className":"gd-eyebrow gd-hero__eyebrow"} -->
<p class="gd-eyebrow gd-hero__eyebrow">Mutual aid · Marietta &amp; metro Atlanta</p>
<!-- /wp:paragraph -->
<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading">Free quality-of-life repairs for neighbors who need them.</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"className":"gd-hero__lede"} -->
<p class="gd-hero__lede">A broken window, a leaking sink, a car that won’t start — small things that become catastrophic when there’s no money to fix them. C3 pairs people who need a repair with people who have the skills and tools to do it. No income verification. No strings.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-fill"} -->
<div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button" href="/get-involved/">Request a repair</a></div>
<!-- /wp:button --><!-- wp:button {"className":"is-style-outline gd-btn-outline-light"} -->
<div class="wp-block-button is-style-outline gd-btn-outline-light"><a class="wp-block-button__link wp-element-button" href="/get-involved/#volunteer">Volunteer your skills</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->
<!-- wp:html -->
<ul class="gd-hero__proof">
<li><?php echo gd_icon( 'check' ); ?> Home, auto &amp; emergency repairs</li>
<li><?php echo gd_icon( 'check' ); ?> No income verification</li>
<li><?php echo gd_icon( 'check' ); ?> Volunteer-run, community-funded</li>
</ul>
<!-- /wp:html -->
</div></div>
<!-- /wp:cover -->
