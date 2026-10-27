<!-- wp:group {"align":"full","className":"gd-fixlist"} -->
<div class="wp-block-group alignfull gd-fixlist"><!-- wp:html -->
<ul class="gd-wrap">
<?php for ( $i = 1; $i <= 12; $i++ ) { if ( gd_copy( "fixlist.$i" ) !== '' ) { echo '<li>' . gd_copy( "fixlist.$i" ) . '</li>'; } } ?>
</ul>
<!-- /wp:html --></div>
<!-- /wp:group -->
