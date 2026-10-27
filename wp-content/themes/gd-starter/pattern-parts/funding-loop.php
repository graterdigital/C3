<!-- wp:heading -->
<h2 class="wp-block-heading"><?php echo gd_copy( 'loop.heading' ); ?></h2>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'loop.intro' ); ?></p>
<!-- /wp:paragraph -->
<!-- wp:html -->
<ol class="gd-loop alignwide">
<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
<li><strong><?php echo gd_copy( "loop.$i.title" ); ?></strong><span><?php echo gd_copy( "loop.$i.text" ); ?></span></li>
<?php endfor; ?>
</ol>
<!-- /wp:html -->
<!-- wp:paragraph -->
<p><?php echo gd_copy( 'loop.outro' ); ?></p>
<!-- /wp:paragraph -->
