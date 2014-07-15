<?php
/**
 * Template for displaying Class Archive pages
**/ 
?>
<?php get_header(); ?>
	<div class="alt_wrap">
		<div class="container">
			<?php getBreadcrumbs(); ?>
			<h2>
				<?php /* If this is a category archive */ if (is_tax()) { single_term_title(); echo ' Classes'; ?>
				
				<?php /* If this is a daily archive */ } elseif (is_day()) { echo 'Classes Added on '; the_time('F jS, Y'); ?>
			
				<?php /* If this is a monthly archive */ } elseif (is_month()) { echo 'Classes Added in '; the_time('F, Y'); ?>
				
				<?php /* If this is a yearly archive */ } elseif (is_year()) { echo 'Classes Added in '; the_time('Y'); ?>
			
				<?php /* If this is an author archive */ } elseif (is_author()) { ?>
		
				<?php						
					if(get_query_var('author_name')) :
						$curauth = get_user_by('slug', get_query_var('author_name'));
					else :
						$curauth = get_userdata(get_query_var('author'));
					endif;
					?>
					Classes Taught by <?php echo $curauth->display_name; ?>
				<?php } ?>				
			</h2>
		</div>
	</div>
	<div id="container">
		<div id="content">
			<?php
				if ( have_posts() ) the_post();
				do_action( 'sb_before_content' );
				if ( have_posts() ) rewind_posts();

				while ( have_posts() ) : the_post();

					get_template_part( 'loop', 'class' );

				endwhile;

				do_action( 'sb_after_content' );
			?>

		</div><!-- #content .hfeed -->
		<?php get_sidebar(); ?>
		<div class="clear"></div>
	</div><!-- #container -->

<?php get_footer(); ?>