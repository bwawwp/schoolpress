<?php
//levels 1 and 2 don't give you a site on the network
global $pmpro_network_non_site_levels;
$pmpro_network_non_site_levels = array(1,2);

/*
	This code was used to lock down the site when in closed alpha.
*/
function my_template_redirect()
{
	global $current_user;
	$okay_pages = array('oops','login','lostpassword','resetpass','logout',pmpro_getOption('billing_page_id'), pmpro_getOption('account_page_id'), pmpro_getOption('levels_page_id'), pmpro_getOption('checkout_page_id'), pmpro_getOption('confirmation_page_id'));
 
	//if the user doesn't have a membership, send them home				
	if(!$current_user->ID 
		&& !is_page($okay_pages) 
		&& !strpos($_SERVER['REQUEST_URI'], "login"))
	{		
		wp_redirect(home_url("wp-login.php?redirect_to=" . urlencode($_SERVER['REQUEST_URI'])));
	}	
	elseif(is_page() 
			&& !is_page($okay_pages) 
			&& !$current_user->membership_level->ID)
	{		
		//change this to wp_redirect(pmpro_url("levels")); to redirect to the levels page.
		wp_redirect(wp_login_url());
	}
}
//add_action('template_redirect', 'my_template_redirect');

//enqueue stylesheets and scripts
function startbox_child_init()
{
	wp_enqueue_style('bootstrap', get_stylesheet_directory_uri() . '/bootstrap/css/bootstrap.min.css', NULL, '3.0');
	wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/bootstrap/js/bootstrap.min.js', NULL, '3.0');
	wp_enqueue_style('fontawesome', get_stylesheet_directory_uri() . "/font-awesome/css/font-awesome.min.css", NULL, NULL, "all");
}
add_action("init", "startbox_child_init");

function load_fonts() {
	wp_register_style('googleFonts_sanchez', 'http://fonts.googleapis.com/css?family=Sanchez:400italic,400');
	wp_enqueue_style( 'googleFonts_sanchez');
	wp_register_style('googleFonts_opensans', 'http://fonts.googleapis.com/css?family=Open+Sans:400,700');
	wp_enqueue_style( 'googleFonts_opensans');
}
add_action('wp_print_styles', 'load_fonts');

function sp_wp_head_responsive() {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
}
add_action('wp_head','sp_wp_head_responsive');

//apply shortcodes in widgets
add_filter('widget_text', 'do_shortcode');