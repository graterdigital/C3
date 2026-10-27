<?php
/** Generic fallback: blog index / archives / search. */
get_header();
gd_page_hero( is_home() ? get_the_title( get_option( 'page_for_posts' ) ) ?: __( 'Journal', 'gd-starter' ) : wp_strip_all_tags( get_the_archive_title() ) );
?>
<div class="gd-wrap gd-posts">
	<?php if ( have_posts() ) : ?>
		<div class="gd-posts__grid">
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'gd-post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?><a class="gd-post-card__media" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'gd-card', [ 'loading' => 'lazy' ] ); ?></a><?php endif; ?>
					<div class="gd-post-card__body">
						<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing here yet.', 'gd-starter' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
