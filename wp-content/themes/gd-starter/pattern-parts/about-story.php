<!-- wp:paragraph {"className":"gd-lede"} -->
<p class="gd-lede"><?php echo gd_copy( 'about.lede' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'about.why.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'about.why.p1' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'about.why.p2' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:html -->
<div class="gd-strip-photos alignwide">
<img src="<?php echo esc_url( gd_img( 'story-kitchen.jpg', 'gd-card' ) ); ?>" alt="Finished kitchen cabinets installed by a volunteer" loading="lazy">
<img src="<?php echo esc_url( gd_img( 'story-pantry.jpg', 'gd-card' ) ); ?>" alt="Custom built-in shelving" loading="lazy">
<img src="<?php echo esc_url( gd_img( 'story-ac.jpg', 'gd-card' ) ); ?>" alt="Recharging a vehicle air-conditioning system" loading="lazy">
</div>
<!-- /wp:html -->
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'about.who.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'about.who.p1' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'about.who.p2' ); ?></p>
<!-- /wp:paragraph -->
<?php include GD_THEME_DIR . '/pattern-parts/funding-loop.php'; ?>
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'about.partners.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'about.partners.text' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:html -->
<ul class="gd-partners gd-partners--about alignwide">
<?php foreach ( gd_site_config()['partners'] ?? [] as $p ) :
	$inner = ! empty( $p['logo'] )
		? '<img src="' . esc_url( gd_img( $p['logo'], 'medium' ) ) . '" alt="' . esc_attr( $p['name'] ) . '" title="' . esc_attr( $p['name'] ) . '" loading="lazy">'
		: '<span class="gd-chip">' . esc_html( $p['name'] ) . '</span>';
	echo '<li>' . ( ! empty( $p['url'] ) ? '<a href="' . esc_url( $p['url'] ) . '" target="_blank" rel="noopener">' . $inner . '</a>' : $inner ) . '</li>' . "\n";
endforeach; ?>
</ul>
<!-- /wp:html -->
<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'about.next.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'about.next.p1' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:paragraph -->
<p><strong><?php echo gd_copy( 'about.next.p2' ); ?></strong></p>
<!-- /wp:paragraph -->
