<?php
/** Services grid — six tiles. Photos: assets/img/svc-*.jpg · copy: content/copy.json services.N.* */
$services = [ [ 'svc-home.jpg', 'home' ], [ 'svc-auto.jpg', 'auto' ], [ 'svc-emergency.jpg', 'emergency' ], [ 'svc-landscaping.jpg', 'landscaping' ], [ 'svc-junk.jpg', 'junk' ], [ 'svc-appliance.jpg', 'appliance' ] ];
?>
<!-- wp:group {"align":"full","className":"gd-section"} -->
<div class="wp-block-group alignfull gd-section"><!-- wp:group {"align":"wide","className":"gd-section__head"} -->
<div class="wp-block-group alignwide gd-section__head"><!-- wp:group -->
<div class="wp-block-group"><!-- wp:heading {"className":"gd-section__title"} -->
<h2 class="wp-block-heading gd-section__title"><?php echo gd_copy( 'services.heading' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group --><!-- wp:paragraph -->
<p><?php echo gd_copy( 'services.intro' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><!-- wp:html -->
<ul class="gd-services gd-wrap">
<?php foreach ( $services as $i => [ $img, $icon ] ) : $n = $i + 1; ?>
<li><img src="<?php echo esc_url( gd_img( $img, 'gd-card' ) ); ?>" alt="" loading="lazy"><?php echo gd_service_icon( $icon ); ?><h3><?php echo gd_copy( "services.$n.title" ); ?></h3><p><?php echo gd_copy( "services.$n.text" ); ?></p></li>
<?php endforeach; ?>
</ul>
<!-- /wp:html --></div>
<!-- /wp:group -->
