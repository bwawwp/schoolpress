<?php
/**
 * Template for displaying Class Archive pages
**/ 
?>
<?php get_header(); ?>
	<div class="alt_wrap">
		<div class="container">
			<h2>SchoolPress is an Open Source App for Classes, Teachers and Students.</h2>
			<p>Browse open classes below &mdash; Or, <a href="/start-a-class/">start your own class</a>.</p>
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