<?php
/*
Plugin Name: WP DOC
Plugin URI: http://bwawwp.com/wp-docx/
Description: Add /doc/ to the end of a page or post to download a .docx version.
Version: .1
Author: Stranger Studios
*/

/*
	Register Rewrite Endpoint
*/
//Add /doc/ endpoint on activation.
function wpdoc_activation()
{
	add_rewrite_endpoint('doc', EP_PERMALINK | EP_PAGES);
	flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wpdoc_activation');

//and init in case another plugin flushes, but don't flush cause it's expensive
function wpdoc_init()
{
	add_rewrite_endpoint('doc', EP_PERMALINK | EP_PAGES);
}
add_action('init', 'wpdoc_init');

//flush rewrite rules on deactivation to remove our endpoint
function wpdoc_deactivation()
{
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wpdoc_deactivation');

/*
	Detect /doc/ use and return a .doc file.
*/
function wpdoc_template_redirect()
{
	global $wp_query;
	if(isset($wp_query->query_vars['doc']))
	{
		global $post;
		
		//double check this is a post
		if(empty($post->ID))
			return;
		
		//headers for MS Word
		header("Content-type: application/vnd.ms-word");
		header("Content-Disposition: attachment;Filename=" . $post->post_name . ".doc");
		
		//html
		?>
		<html>
		<body>
		<h1><?php echo $post->post_title; ?></h1>
		<?php
			echo apply_filters('the_content', $post->post_content); 
		?>
		</body>
		</html>
		<?php
		
		exit;
	}
}
add_action('template_redirect', 'wpdoc_template_redirect');