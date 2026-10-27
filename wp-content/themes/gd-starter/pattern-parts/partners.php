<?php $partners = gd_site_config()['partners'] ?? []; ?>
<!-- wp:group {"align":"full","className":"gd-section"} -->
<div class="wp-block-group alignfull gd-section"><!-- wp:group {"align":"wide","className":"gd-section__head"} -->
<div class="wp-block-group alignwide gd-section__head"><!-- wp:group -->
<div class="wp-block-group"><!-- wp:heading {"className":"gd-section__title"} -->
<h2 class="wp-block-heading gd-section__title"><?php echo gd_copy( 'partners.heading' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group --><!-- wp:paragraph -->
<p><?php echo gd_copy( 'partners.intro' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --><!-- wp:html -->
<ul class="gd-partners gd-wrap">
<?php foreach ( $partners as $p ) :
	$inner = ! empty( $p['logo'] )
		? '<img src="' . esc_url( gd_img( $p['logo'], 'medium' ) ) . '" alt="' . esc_attr( $p['name'] ) . '" title="' . esc_attr( $p['name'] ) . '" loading="lazy">'
		: '<span class="gd-chip">' . esc_html( $p['name'] ) . '</span>';
	echo '<li>' . ( ! empty( $p['url'] ) ? '<a href="' . esc_url( $p['url'] ) . '" target="_blank" rel="noopener">' . $inner . '</a>' : $inner ) . '</li>' . "\n";
endforeach; ?>
</ul>
<!-- /wp:html --></div>
<!-- /wp:group -->
