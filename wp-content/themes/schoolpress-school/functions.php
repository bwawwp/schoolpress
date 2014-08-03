<?php
//run on init
function startbox_child_init()
{
	//enqueue stylesheets and scripts
	wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/bootstrap/css/bootstrap.min.css', NULL, '3.0');
	wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/bootstrap/js/bootstrap.min.js', NULL, '3.0');
	wp_enqueue_style('fontawesome', get_stylesheet_directory_uri() . "/font-awesome/css/font-awesome.min.css", NULL, NULL, "all");
	
	//no startbox breadcrumbs
	remove_filter ('bbp_no_breadcrumb', '__return_true;');
	
	//no prev/next from startbox. removing all actions cause we can't pinpoint the sb_post_nav method.
	remove_all_actions( 'sb_after_content' );
}
add_action("init", "startbox_child_init");

//fonts
function load_fonts() {
	wp_register_style('googleFonts_sanchez', 'http://fonts.googleapis.com/css?family=Sanchez:400italic,400');
	wp_enqueue_style( 'googleFonts_sanchez');
	wp_register_style('googleFonts_opensans', 'http://fonts.googleapis.com/css?family=Open+Sans:400,700');
	wp_enqueue_style( 'googleFonts_opensans');
}
add_action('wp_print_styles', 'load_fonts');

//set viewport
function sp_wp_head_responsive() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
}
add_action('wp_head','sp_wp_head_responsive');

//apply shortcodes in widgets
add_filter('widget_text', 'do_shortcode');

//Show posts of 'classes' post types on home page
add_action( 'pre_get_posts', 'add_my_post_types_to_query' );

if ( function_exists( 'add_image_size' ) ) { 
	add_image_size( 'class-thumb', 100, 150, false );
}

//show classes in searches
function add_my_post_types_to_query( $query ) {
	//ignore dashboard
	if(is_admin())
		return $query;
	
	//checks
	if ( is_home() && $query->is_main_query() )
		$query->set( 'post_type', array( 'class' ) );
	elseif ( is_author() && $query->is_main_query() )
		$query->set( 'post_type', array( 'class' ) );
	elseif(!empty($_REQUEST['s']))
		$query->set( 'post_type', array( 'class' ) );
	elseif(!empty($query->query_vars['department']))
		$query->set( 'post_type', array( 'class' ) );
		
	return $query;
}

/*
	Our breadcrumbs function
*/
function getBreadcrumbs()
{
	global $posts, $post;
	if(is_page() && !is_front_page())
	{
	?>			
	<ul class="breadcrumb">
		<li><a href="<?php echo home_url()?>">Classes</a></li>
			<?php
				$breadcrumbs = get_post_ancestors($post->ID);				
				if($breadcrumbs)
				{
					$breadcrumbs = array_reverse($breadcrumbs);
					foreach ($breadcrumbs as $crumb)
					{
						?>
						<li><a href="<?php echo get_permalink($crumb); ?>"><?php echo get_the_title($crumb); ?></a></li>
						<?php
					}
				}				
			?>
			
			<?php 
				if(function_exists("pmpro_getOption") && is_page( array(pmpro_getOption('cancel_page_id'), pmpro_getOption('billing_page_id'), pmpro_getOption('confirmation_page_id'), pmpro_getOption('invoice_page_id') ) ) ) 
				{ 
					?>
					<li><a href="<?php get_permalink(pmpro_getOption('account_page_id')); ?>"><?php echo get_the_title(pmpro_getOption('account_page_id')); ?></a></li>
					<?php 
				} 
			?>
			<li class="active"><?php the_title(); ?></li>
		  </ul>
		<?php
	}
	elseif(is_archive())
	{
	?>
	<ul class="breadcrumb">
		<li><a href="<?php echo get_option('home'); ?>/">Classes</a></li>
		<?php 
			if(get_option('page_for_posts'))
			{
				?>
				<li><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>"><?php echo get_the_title(get_option('page_for_posts')); ?></a></li>
				<?php
			}
		?>
		<li class="active">
		<?php 
			if(empty($post) && !empty($posts[0]))
				$post = $posts[0]; // Hack. Set $post so that the_date() works. 
		?>
		<?php /* If this is a category archive */ if (is_tax()) { single_term_title(); ?>
		
		<?php /* If this is a daily archive */ } elseif (is_day()) { the_time('F jS, Y'); ?>
	
		<?php /* If this is a monthly archive */ } elseif (is_month()) { the_time('F, Y'); ?>
		
		<?php /* If this is a yearly archive */ } elseif (is_year()) { the_time('Y'); ?>
	
		<?php /* If this is an author archive */ } elseif (is_author()) { ?>

		<?php						
			if(get_query_var('author_name')) :
				$curauth = get_user_by('slug', get_query_var('author_name'));
			else :
				$curauth = get_userdata(get_query_var('author'));
			endif;
			?>
			Articles by <?php echo $curauth->display_name; ?>
	
		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>Blog<?php } ?>

		Archive</li>	
	</ul>
	<?php
	}
	elseif(is_attachment())
	{
	?>
	<ul class="breadcrumb">
		<li><a href="<?php echo get_option('home'); ?>/">Classes</a></li>
		<?php
			global $post;
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();
			while ($parent_id) {
			  $page = get_page($parent_id);
			  $breadcrumbs[] = '<a href="'.get_permalink($page->ID).'" title="">'.get_the_title($page->ID).'</a>';
			  $parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			foreach ($breadcrumbs as $crumb) echo ' <li>'.$crumb.'</li>';
		?>
		<li class="active"><?php the_title(); ?></li>
	</ul>
	<?php
	}
	elseif(is_single())
	{
	?>
	<ul class="breadcrumb">
		<li><a href="<?php echo get_option('home'); ?>/">Classes</a></li>
		<?php 
			if(get_option('page_for_posts'))
			{
				?>
				<li><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>"><?php echo get_the_title(get_option('page_for_posts')); ?></a></li>
				<?php
			}
		?>
		<li class="active"><?php the_title(); ?></li>
	</ul>
	<?php
	}
	elseif(is_search())
	{
		global $s;
	?>
	<ul class="breadcrumb">
		<li><a href="<?php echo get_option('home'); ?>/">Classes</a></li>
		<?php 
			if(get_option('page_for_posts'))
			{
				?>
				<li><a href="<?php echo get_permalink(get_option('page_for_posts')); ?>"><?php echo get_the_title(get_option('page_for_posts')); ?></a></li>
				<?php
			}
		?>
		<li class="active">Search Results For '<?php echo stripslashes($s); ?>'</li>
	</ul>
<?php
	}
}
