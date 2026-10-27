<?php
/**
 * Default page template. The front page and pages that opt in to "canvas" render
 * the block content edge-to-edge; everything else gets a page hero + reading column.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$canvas = is_front_page() || get_post_meta( get_the_ID(), '_gd_canvas', true );
	if ( $canvas ) :
		?>
		<article <?php post_class( 'gd-canvas' ); ?>><?php the_content(); ?></article>
		<?php
	else :
		gd_page_hero( get_the_title(), '', has_excerpt() ? get_the_excerpt() : '' );
		?>
		<article <?php post_class( 'gd-page' ); ?>>
			<div class="gd-wrap gd-page__content entry-content"><?php the_content(); ?></div>
		</article>
		<?php
	endif;
endwhile;
get_footer();
