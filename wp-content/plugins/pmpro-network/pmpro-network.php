<?php
/*
Plugin Name: Paid Memberships Pro Network Site Helper
Plugin URI: http://www.paidmembershipspro.com/network-sites/
Description: Sample Network/Multisite Setup for Sites Running Paid Memberships Pro. This plugin requires the Paid Memberships Pro plugin, which can be found in the WordPress repository.
Version: .3.3.1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/
/*	
	Copyright 2011	Stranger Studios	(email : jason@strangerstudios.com)	 
	This code is licensed under the GPLv2.
*/

//set these values here or in a custom plugin
/*
define('PMPRO_NETWORK_MANAGE_SITES_SLUG', '/manage-sites/');	//change to relative path of your manage sites page if you are setting site credits > 1
global $pmpro_network_non_site_levels;
$pmpro_network_non_site_levels = array(1,2,3,4,5,7,9); // change to level id's that should not create a site: e.g. array('1','2','3')
*/

//includes
require_once(dirname(__FILE__) . "/pages/manage-sites.php");

/*
	First we need to add some fields to the checkout page.
*/
//add the fields to the form 
function pmpron_pmpro_checkout_boxes() 
{
	global $current_user, $wpdb, $pmpro_network_non_site_levels;

	// Return if requested level is in non site levels array	
	if ( in_array( $_REQUEST['level'], $pmpro_network_non_site_levels ) )
		return;

	if(!empty($_REQUEST['sitename']))
	{
		$sitename = $_REQUEST['sitename'];
		$sitetitle = $_REQUEST['sitetitle']; 
	}
	elseif(!empty($_SESSION['sitename']))
	{
		$sitename = $_SESSION['sitename'];
		$sitetitle = $_SESSION['sitetitle']; 
	}
?>
	<table id="pmpro_site_fields" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0">
	<thead>
		<tr>
			<th>Site Information</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
			
			<?php
				//check if the user already has a blog
				if($current_user->ID)
				{
					$blog_id = get_user_meta($current_user->ID, "pmpron_blog_id", true);
					$all_blog_ids = pmpron_getBlogsForUser($current_user->ID);
					if(count($all_blog_ids) > 1)
					{
						$blogname = "many";
					}
					elseif($blog_id)
					{
						$user_blog = $wpdb->get_row("SELECT * FROM $wpdb->blogs WHERE blog_id = '" . $blog_id . "' LIMIT 1");						
						$blogname = get_blog_option($blog_id, "blogname");
					}
				}
				
				if($blogname == "many")
				{
				?>
				<div>
					<p>You will be reclaiming your previous sites.</p>
					<input type="hidden" name="blog_id" value="<?php echo $blog_id;?>" />
				</div>
				<?php
				}
				elseif($blogname)
				{
				?>
				<div>
					<p>You will be reclaiming your site <strong><?php echo $blogname;?></strong>.</p>
					<input type="hidden" name="blog_id" value="<?php echo $blog_id;?>" />
				</div>
				<?php
				}
				else
				{
				?>				
				<div>
					<label for="sitename"><?php _e('Site Name') ?></label>
					<input id="sitename" name="sitename" type="text" class="input" size="30" value="<?php echo esc_attr(stripslashes($sitename)); ?>" />				
					<?php
						global $current_site;
						$site_domain = preg_replace( '|^www\.|', '', $current_site->domain );
					
						if ( !is_subdomain_install() )
							$site = $current_site->domain . $current_site->path . __( 'sitename' );
						else
							$site = __( '{site name}' ) . '.' . $site_domain . $current_site->path;

						echo '<div>(<strong>' . sprintf( __('Your address will be %s.'), $site ) . '</strong>) ' . __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed, so choose carefully!' ) . '</div>';						
					?>
				</div>
				<div>
					<label for="sitetitle"><?php _e('Site Title')?></label>
					<input id="sitetitle" name="sitetitle" type="text" class="input" size="30" value="<?php echo esc_attr(stripslashes($sitetitle)); ?>" />
				</div> 
				<?php
				}
				?>			
			
			</td>
		</tr>
	</tbody>
	</table>
<?php
}
add_action('pmpro_checkout_boxes', 'pmpron_pmpro_checkout_boxes');

//update the user after checkout
function pmpron_update_site_after_checkout($user_id)
{
	global $current_user, $current_site, $pmpro_network_non_site_levels;
	
	if(isset($_REQUEST['sitename']))
	{   
		//new site, on-site checkout
		$sitename = $_REQUEST['sitename'];
		$sitetitle = $_REQUEST['sitetitle'];
		$blog_id = intval($_REQUEST['blog_id']);
	}
	elseif(isset($_REQUEST['blog_id']))
	{
		//reclaiming, on-site checkout
		$blog_id = intval($_REQUEST['blog_id']);
	}
	elseif(isset($_SESSION['sitename']))
	{   
		//new site, off-site checkout
		$sitename = $_SESSION['sitename'];
		$sitetitle = $_SESSION['sitetitle'];
		$blog_id = intval($_SESSION['blog_id']);
	}	
	elseif(isset($_SESSION['blog_id']))
	{
		//reclaiming, off-site checkout
		$blog_id = intval($_SESSION['blog_id']);
	}	
	
	if($blog_id)
	{
		//reclaiming, first check that this id is associated with the user	
		$all_blog_ids = pmpron_getBlogsForUser($user_id);
		if(in_array($blog_id, $all_blog_ids))
		{
			//activate the blog
			update_blog_status( $blog_id, 'deleted', '0' );
			do_action( 'activate_blog', $blog_id );
		}		
		else
		{
			//uh oh, were they trying to claim someone else's blog?
			return new WP_Error('pmpron_reactivation_failed', __('<strong>ERROR</strong>: Site reactivation failed.'));
		}
	}
	elseif(!in_array( $_REQUEST['level'], $pmpro_network_non_site_levels ))
	{ 
		$blog_id = pmpron_addSite($sitename, $sitetitle);
		if(is_wp_error($blog_id))
			return $blog_id;
	}
	
	//clear session vars
	unset($_SESSION['sitename']);
	unset($_SESSION['sitetitle']);
	unset($_SESSION['blog_id']);
}
add_action('pmpro_after_checkout', 'pmpron_update_site_after_checkout');

/*
	Add "manage sites" link to member links
*/
function pmpron_pmpro_member_links_top()
{	
	global $current_user;
	$credits = $current_user->pmpron_site_credits;
	$manage_post = get_page_by_path(PMPRO_NETWORK_MANAGE_SITES_SLUG);
	if(!empty($credits) && !empty($manage_post))
	{
		?>
		<li><a href="<?php echo home_url(PMPRO_NETWORK_MANAGE_SITES_SLUG);?>"><?php _e('Manage Sites') ?></a></li>
		<?php
	}	
}
add_filter("pmpro_member_links_top", "pmpron_pmpro_member_links_top");

/*
	Function to add a site.
	Takes sitename and sitetitle
	Returns blog_id
*/
function pmpron_addSite($sitename, $sitetitle)
{
	global $current_user, $current_site;
	
	//figure out the new domain	
	$site_domain = preg_replace( '|^www\.|', '', $current_site->domain );

	if ( !is_subdomain_install() )
	{
		$site = $current_site->domain;
		$path = $current_site->path . $sitename;
	}
	else
	{
		$site = $sitename . '.' . $site_domain;
		$path = $current_site->path;
	}

	//alright create the blog
	$meta = apply_filters('signup_create_blog_meta', array ('lang_id' => 'en', 'public' => 0));
	$blog_id = wpmu_create_blog($site, $path, $sitetitle, $current_user->ID, $meta);
	
	do_action("pmpro_network_new_site", $blog_id, $current_user->ID);

	if ( is_a($blog_id, "WP_Error") ) {
		return new WP_Error('blogcreate_failed', __('<strong>ERROR</strong>: Site creation failed.'));
	}
			
	//save array of all blog ids
	$blog_ids = pmpron_getBlogsForUser($current_user->ID);	
	if(!in_array($blog_id, $blog_ids))
	{
		$blog_ids[] = $blog_id;
		update_user_meta($current_user->ID, "pmpron_blog_ids", $blog_ids);
		
		//if this is the first site, set it as the main site
		if(count($blog_ids) == 1)
			update_user_meta($current_user->ID, "pmpron_blog_id", $blog_id);	
	}				
	
	do_action('wpmu_activate_blog', $blog_id, $current_user->ID, $current_user->user_pass, $sitetitle, $meta);
	
	return $blog_id;
}

/*
These bits are required for PayPal Express only.
*/
function pmpron_pmpro_paypalexpress_session_vars()
{
	//save our added fields in session while the user goes off to PayPal
	$_SESSION['sitename'] = $_REQUEST['sitename'];
	$_SESSION['sitetitle'] = $_REQUEST['sitetitle'];
	$_SESSION['blog_id'] = $_REQUEST['blog_id'];
}
add_action("pmpro_paypalexpress_session_vars", "pmpron_pmpro_paypalexpress_session_vars");

//require the fields and check for dupes
function pmpron_pmpro_registration_checks($pmpro_continue_registration)
{
	if (!$pmpro_continue_registration)
		return $pmpro_continue_registration;

	global $pmpro_msg, $pmpro_msgt, $current_site, $current_user, $pmpro_network_non_site_levels;
	
	if(!empty($_REQUEST['sitename']))
		$sitename = $_REQUEST['sitename'];
	else
		$sitename = "";
		
	if(!empty($_REQUEST['sitetitle']))
		$sitetitle = $_REQUEST['sitetitle'];
	else
		$sitetitle = "";
		
	if(!empty($_REQUEST['blog_id']))
		$blog_id = $_REQUEST['blog_id'];
	else
		$blog_id = "";

	// Return if requested level is in non site levels array
	if ( in_array( $_REQUEST['level'], $pmpro_network_non_site_levels ) )
		return $pmpro_continue_registration;

	if($sitename && $sitetitle)
	{
		if(pmpron_checkSiteName($sitename))
		{
			//all good
			return true;	
		}
		else
		{
			//error set in checkSiteName
			return false;	
		}
	}
	elseif($blog_id)
	{
		//check that the blog id matches the user meta
		$meta_blog_id = get_user_meta($current_user->ID, "pmpron_blog_id", true);
		if($meta_blog_id != $blog_id)
		{
			$pmpro_msg = "There was an error finding your old site. Make sure you are logged in. Contact the site owner for help signing up or reactivating your site.";
			$pmpro_msgt = "pmpro_error";
			return false;
		}
		else
		{
			//all good
			return true;	
		}
	}
	else
	{
		$pmpro_msg = "You must enter a site name and title now.";
		$pmpro_msgt = "pmpro_error";
		return false;
	}
}
add_filter("pmpro_registration_checks", "pmpron_pmpro_registration_checks");

/*
	Checks if a domain/site name is available.
*/
function pmpron_checkSiteName($sitename)
{
	global $pmpro_msg, $pmpro_msgt, $current_site;
	
	//they entered something. is it available		
	$site_domain = preg_replace( '|^www\.|', '', $current_site->domain );		
	if ( !is_subdomain_install() )
	{
		$site = $current_site->domain;
		$path = $current_site->path . "/" . $sitename;
	}
	else
	{
		$site = $sitename . '.' . $site_domain;
		$path = $current_site->path;
	}
	$domain = preg_replace( '/\s+/', '', sanitize_user( $site, true ) );

	if ( is_subdomain_install() )
		$domain = str_replace( '@', '', $domain );
	
	if ( empty($path) )
		$path = '/';

	// Check if the domain has been used already. We should return an error message.
	if ( domain_exists($domain, $path) )
	{
		//dupe
		$pmpro_msg = "That site name is already in use.";
		$pmpro_msgt = "pmpro_error";
		return false;
	}
	else
	{
		//looks good
		return true;
	}	
}

/*
	Shows how to change some of the blog settings on site creation.
*/
function pmpron_new_blogs_settings($blog_id) 
{
    global $wpdb;
	
	//change the default theme
	/*
	update_blog_option($blog_id, 'current_theme', 'Your Theme Name');
	update_blog_option($blog_id, 'template', 'your-theme-directory');
	update_blog_option($blog_id, 'stylesheet', 'your-theme-directory');
	*/
	
	//change the subtitle "blogdescription"
	update_blog_option($blog_id, 'blogdescription', 'Change your subtitle');			
				
	//change the category 1 to "general" (pet peeve of mine)
	$sqlQuery = "UPDATE " . $wpdb->prefix . $blog_id . "_terms SET name = 'General', slug = 'general' WHERE term_id = 1 LIMIT 1";			
	$wpdb->query($sqlQuery);
	
	//make the blog public
	$sqlQuery = "UPDATE $wpdb->blogs SET public = 1 WHERE blog_id = '" . $blog_id . "' LIMIT 1";		
	$wpdb->query($sqlQuery);
	
	//add some other categories		
	/*
	wls_add_category($blog_id, "Books", "books");
	wls_add_category($blog_id, "Events", "events");
	wls_add_category($blog_id, "Food", "food");
	wls_add_category($blog_id, "News and Interest", "news");
	*/		
}

//actions
add_action('wpmu_new_blog', 'pmpron_new_blogs_settings');

/*
	Update the confirmation message to show links to the new site.
*/
function pmpron_pmpro_confirmation_message($message, $invoice)
{
	global $current_user, $wpdb;
	
	//where is the user's site?
	$blog_id = get_user_meta($current_user->ID, "pmpron_blog_id", true);
	
	if($blog_id)
	{
		//get the site address
		$address = "http://" . $wpdb->get_var("SELECT CONCAT(domain, path) FROM $wpdb->blogs WHERE blog_id = '" . $blog_id . "' LIMIT 1");
		$message .= "<p>Visit your new site here: <a href=\"" . $address . "\">" . $address . "</a></p>";
		$message .= "<p>Manage your new site here: <a href=\"" . $address . "wp-admin/\">" . $address . "wp-admin/</a></p>";
	}

	return $message;
}
add_filter("pmpro_confirmation_message", "pmpron_pmpro_confirmation_message", 10, 2);

/*
	Set site credits, remove admin access and deactivate a blogs when a user's membership level changes.
*/
function pmpron_pmpro_after_change_membership_level($level_id, $user_id)
{	
	//set site credits		
	if(!pmpro_hasMembershipLevel(NULL, $user_id))
		$site_credits = 0;
	else
		$site_credits = apply_filters("pmpron_site_credits", 1, $user_id, $level_id);	//use this filter to give certain users/levels different # of site credits
	update_user_meta($user_id, "pmpron_site_credits", $site_credits);
	
	//activate user's blogs based on number of site credits they have
	$blog_ids = pmpron_getBlogsForUser($user_id);	
	$n = 0;
	foreach($blog_ids as $blog_id)
	{
		$n++;
		
		if($site_credits >= $n)
		{
			//as long as site_credits > $n, let's make sure this blog is active
			update_blog_status( $blog_id, 'deleted', '0' );
			do_action( 'activate_blog', $blog_id );
		}
		else
		{		
			//don't deactivate admin sites
			if(!user_can("manage_network", $user_id))
			{
				//site credits < $n, so let's deactivate blogs from now on
				do_action( 'deactivate_blog', $blog_id );
				update_blog_status( $blog_id, 'deleted', '1' );			
			}
		}
	}		
}
add_action("pmpro_after_change_membership_level", "pmpron_pmpro_after_change_membership_level", 10, 2);

/*
	Get an array of blog ids for a user.
*/
function pmpron_getBlogsForUser($user_id)
{
	$user = get_userdata($user_id);
	$main_blog_id = $user->pmpron_blog_id;
	$all_blog_ids = $user->pmpron_blog_ids;
		
	if(!empty($all_blog_ids))
		return $all_blog_ids;
	elseif(!empty($main_blog_id))
		return array($main_blog_id);
	else
		return array();
}

/*
	Add link to add new sites for users with multiple site credits
*/
function pmpron_myblogs_allblogs_options()
{
	global $current_user;
	
	//how many sites have they created?	
	$all_blog_ids = pmpron_getBlogsForUser($current_user->ID);	
	$num = count($all_blog_ids);
		
	//how many can they create?
	$site_credits = $current_user->pmpron_site_credits;

	//In case they have sites but no site credit yet. Assume they have $num site credits.
	//This will give 1 site credit to users on sites upgrading pmpro-network from .1/.2 to .3. 
	if(empty($site_credits) && !empty($num))
	{
		$site_credits = $num;
		update_user_meta($current_user->ID, "pmpron_site_credits", $site_credits);
	}	
	?>
	<p><?php _e('Below is a list of all sites you are an owner or member of.') ?>
	<?php
	if(!empty($site_credits))
	{
		?>
		<a href="<?php echo home_url(PMPRO_NETWORK_MANAGE_SITES_SLUG);?>"><?php _e('Click here to Manage Sites you own &raquo;') ?></a>
		<?php
	}
	?>
	</p>
	<?php
}
add_action("myblogs_allblogs_options", "pmpron_myblogs_allblogs_options");

/*
	Add site credits field to profile for admins to adjust
*/
//show fields
function pmpron_profile_fields($profile_user)
{	
	if(current_user_can("manage_network"))
	{		
	?>
		<h3><?php _e("Site Credits"); ?></h3>
		<table class="form-table">
		<tr>
			<th><label for="site_credits"><?php _e("Site Credits"); ?></label></th>
			<td>
				<?php
					//how many sites have they created?	
					$all_blog_ids = pmpron_getBlogsForUser($profile_user->ID);	
					$num = count($all_blog_ids);
						
					//how many can they create?
					$site_credits = $profile_user->pmpron_site_credits;						
				?>
				<input type="text" id="site_credits" name="site_credits" size="5" value="<?php echo $site_credits; ?>" /> <em>currently using <?php echo $num; ?></em>
			</td>
		</tr>
		</table>
	<?php
	}
}
add_action('show_user_profile', 'pmpron_profile_fields');
add_action('edit_user_profile', 'pmpron_profile_fields');

//save fields
function pmpron_profile_fields_update($user_id)
{
	//make sure they can edit
	if ( !current_user_can( 'manage_network') )
		return false;

	//if site credits is there, set it
	if(isset($_POST['site_credits']))
		update_user_meta( $user_id, 'pmpron_site_credits', $_POST['site_credits'] );	
}
add_action('profile_update', 'pmpron_profile_fields_update');
add_action('user_edit_form_tag', 'pmpron_profile_fields_update');
