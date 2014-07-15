<?php get_header(); ?>
	<?php do_action( 'sb_before_content' ); ?>
	<div class="alt_wrap">
		<div class="container">
			<?php getBreadcrumbs(); ?>
			<?php do_action( 'sb_page_title' ); ?>
		</div>
	</div>
		
	<div id="container">
		<div id="content">
			<?php
				if (have_posts()) :
					while ( have_posts() ) : the_post();
						get_template_part( 'loop', 'search' );
					endwhile;
				else :
			?>
			<div id="post-0" class="post noresults">
				<div class="entry-content">
					<p><?php _e('Sorry, but nothing matched your search criteria. Please try again with some different keywords.', 'startbox') ?></p>
				</div>

				<?php get_template_part( 'searchform' ); ?>

			</div><!-- .post -->

		<?php endif; ?>

		</div><!-- #content -->
		<?php get_sidebar(); ?>
		<div class="clear"></div>
	</div><!-- #container -->
	<?php do_action( 'sb_after_content' ); ?>
<?php get_footer(); ?>