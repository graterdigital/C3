<!-- wp:html -->
<div class="gd-faq">
<?php for ( $i = 1; $i <= 20; $i++ ) { if ( gd_copy( "faq.q$i" ) !== '' ) { echo '<details><summary>' . gd_copy( "faq.q$i" ) . '</summary><p>' . gd_copy( "faq.a$i" ) . '</p></details>' . "\n"; } } ?>
</div>
<!-- /wp:html -->
