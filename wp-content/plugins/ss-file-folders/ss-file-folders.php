<?php
	/*
	Plugin Name: SS File Folders
	Plugin URI: http://www.strangerstudios.com/wp/ss-file-folders/
	Description: Adds a [file-folder] shortcode which will show links to the parent page (../), any sub page, and any attached file.
	Version: .1
	Author: Stranger Studios
	Author URI: http://www.strangerstudios.com
	*/
	
	global $ssff_options;
	$ssff_options['display'] = "table";
	
	//shortcode function
	function ssff_shortcode_handler($atts, $content=null, $code="") {
	global $post, $ssff_options;
			
	// $atts    ::= array of attributes
	// $content ::= text within enclosing form of shortcode element
	// $code    ::= the shortcode found, when == callback name
	// examples: [file-folders]
	
	//no params yet, but keeping this here for reference in case we do
	extract(shortcode_atts(array(
		'exclude' => NULL
	), $atts));
		
	//make sure we have our	css loaded
	wp_enqueue_style("ss-file-folders", plugins_url("css/ss-file-folders.css", __FILE__), NULL, ".1");
	
	
	// our return string
	
	/*
		Breadcrumbs
	*/	
	$r = "<p class='breadcrumbs'>";
	
	$breadcrumbs = get_post_ancestors($post->ID);				
	if($breadcrumbs)
	{
		$breadcrumbs = array_reverse($breadcrumbs);
		foreach ($breadcrumbs as $crumb)
		{
			$r .= '<a href="' . get_permalink($crumb) . '">' . get_the_title($crumb) . '</a>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;';
		}
		$r .=  $post->post_title;
	}	
	
	$r .= '</p>';
		
	if($ssff_options['display'] == "table")
		$r .= "<table class='ss-file-folders'><thead><tr><th width='50'><!--thumb--></th><th>Name</th><th>File</th><th>Date Modified</th></tr></thead><tbody>";
	else
		$r .= "<ul class='ss-file-folders'>";
	
	/*
		Link to Parent
	*/
	if(!empty($post->post_parent) && $post->post_parent != $post->ID)
	{
		if($ssff_options['display'] == "table")
		{			
			$r .= "<tr><td class='ss-file-folders_thumb'><a href='" . get_permalink($post->post_parent) . "'><img src='" . plugins_url("images/icon_folder-prev.png", __FILE__) . "' width='32' /></a></td><td class='ss-file-folders_prev' colspan='3'><a href='" . get_permalink($post->post_parent) . "'>..</a></td></tr>";
		}
		else
		{
			$r .= "<li class='ss-file-folders_prev'><strong><a href='" . get_permalink($post->post_parent) . "'>..</a></strong></li>";
		}
	}

	/*
		Link to sub folders.
	*/
		
	//get posts
	query_posts(array("post_type"=>$post->post_type, "showposts"=>-1, "orderby"=>"post_title", "post_parent"=>$post->ID, "order"=>"ASC", "post__not_in"=>$exclude));
  
	//the Loop					
	if ( have_posts() ) : while ( have_posts() ) : the_post();	
		if($ssff_options['display'] == "table")
		{
			$r .= "<tr>";
			
			$r .= "<td class='ss-file-folders_thumb'>";
			$r .= '<a href="' . get_permalink() . '"><img src="' . plugins_url("images/icon_folder-explore.png", __FILE__) . '" width="32" /></a>';			
			$r .= "</td>";
			
			$r .= '<td colspan="2">
				<strong><a href="' . get_permalink() . '">' . the_title('','',false) . '</a></strong>					
			</td>';
									
			$r .= '<td>' . get_the_modified_date("m/d/Y");
					
			$r .= "</td>";

			$r .= '</tr>';
		}
		else
			$r .= '<li><strong><a href="' . get_permalink() . '">' . the_title('','',false) . '</a></strong></li>';            
	endwhile; endif;	
	
	//Reset Query
	wp_reset_query();	
	
	/*
		Link to files.
	*/
	//get posts
	query_posts(array("post_type"=>"attachment", "post_status" => "inherit", "showposts"=>-1, "orderby"=>"post_title", "post_parent"=>$post->ID, "order"=>"ASC", "post__not_in"=>$exclude));
  
	//the Loop					
	if ( have_posts() ) : while ( have_posts() ) : the_post();	
		if($ssff_options['display'] == "table")
		{
			$r .= "<tr>";
			
			$r .= "<td class='ss-file-folders_thumb'>";
			if($thumb = wp_get_attachment_image($post->ID, array( 32, 32 ), true))
				$r .= '<a href="' . wp_get_attachment_url($post->ID) . '">' . $thumb . '</a>';			
			
			$r .= "</td>";
			
			$r .= '<td>					
				<a href="' . wp_get_attachment_url($post->ID) . '">' . the_title('','',false) . '</a>';
			
			$r .= "</td>";
			
			$r .= '<td>		
				<a href="' . wp_get_attachment_url($post->ID) . '" class="ssff-file-link">' . ssff_getFilenameFromGuid($post->guid) . '</a>';
			
			$r .= "</td>";
			
			$r .= '<td>' . date("m/d/Y", strtotime($post->post_date));
					
			$r .= "</td>";
			
			$r .= '</tr>';						
		}
		else
			$r .= '<li><a href="' . get_the_guid() . '">' . the_title('','',false) . '</a></li>';            
	endwhile; endif;	
	
	//Reset Query
	wp_reset_query();
	
	if($ssff_options['display'] == "table")
		$r .= "</tbody></table>";
	else
		$r .= "</ul>";
	
	return $r;
}
add_shortcode('file-folders', 'ssff_shortcode_handler');

/*
	Sets the "main_post_id" so we know which page we are looking at.
*/
function ssff_wp()
{
	global $post, $ssff_options;	
	if(!empty($post->ID))
		$ssff_options['main_post_id'] = $post->ID;
}
add_action("wp", "ssff_wp");

/*
	So we know if we are outputting the body yet or not
*/
function ssff_body_class($class)
{
	global $post, $ssff_options;
	
	if(strpos($post->post_content, "[file-folders]") !== false)
	{
		$ssff_options['in_body'] = true;
		$class[] = "page-ss-file-folders";
	}
		
	return $class;
}
add_filter("body_class", "ssff_body_class");

/*
	Function to change title of page to Files
*/
function ssff_the_title($title, $post_id)
{
	global $ssff_options;	
	if(!empty($ssff_options['in_body']) && $post_id == $ssff_options['main_post_id'])
	{
		$title = "Files";
	}
	
	return $title;
}
//add_action("the_title", "ssff_the_title", 10, 2);
	
/*
	Gets a filename from a guid
*/
function ssff_getFilenameFromGuid($guid)
{
	//generally we want everything after the last slash
	$parts = explode("/", $guid);
	if($parts)
		return $parts[count($parts) - 1];
	else
		return $guid;
}		