<?php
get_header();
while ( have_posts() ) :
	the_post();
	gd_page_hero( get_the_title(), get_the_date() );
	?>
	<article <?php post_class( 'gd-page' ); ?>>
		<div class="gd-wrap gd-page__content entry-content"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;
get_footer();
