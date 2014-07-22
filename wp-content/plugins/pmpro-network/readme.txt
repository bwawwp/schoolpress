=== Paid Memberships Pro Network Site Helper ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, network sites, wpmu
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: .3.3.1

Sample Network/Multisite Setup for Sites Running Paid Memberships Pro. This plugin requires the Paid Memberships Pro plugin, which can be found in the WordPress repository.

== Description ==

With the Paid Memberships Pro plugin and this plugin activated, new users will be able to choose a site name and title at checkout. A site will be created for them after registering. If they cancel their membership or have it removed, the site will be deactivated. If they sign up for a membership again, the site will be reactivated.

== Installation ==

1. Make sure you have the Paid Memberships Pro plugin installed and activated.
1. Make sure you have properly configured Network Sites on your WP install.
1. Upload the `pmpro-network` directory to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.

To use site credits:

1. Set the "PMPRO_NETWORK_MANAGE_SITES_SLUG" constant in the pmpro-network.php file.
2. Add code like this to your main site's active theme's functions.php or another custom plugin:

`
//set site credits for levels 1-3
function pmpro_multi_pmpron_site_credits($credits, $user_id, $level_id)
{	
	if($level_id == 1)
		$credits = 1;
	elseif($level_id == 2)
		$credits = 3;
	elseif($level_id == 3)
		$credits = 9999;

	return $credits;
}
add_filter("pmpron_site_credits", "pmpro_multi_pmpron_site_credits", 10, 3);
`

== Frequently Asked Questions ==

= I found a bug in the plugin. =

Please post it in the GitHub issue tracker here: https://github.com/strangerstudios/pmpro-network/issues

= I need help installing, configuring, or customizing the plugin. =

Please visit our premium support site at http://www.paidmembershipspro.com for more documentation and our support forums.

== Changelog ==
= .3.3.1 =
* Fixed some warnings.

= .3.3 =
* Fixed bug where we weren't checking $pmpro_network_non_site_levels global in pmpron_update_site_after_checkout.

= .3.2 =
* Added "site credits" field to profile for admins to override.

= .3.1 =
* Won't deactivate sites when changing levels on a network admin.
* Checking for blog_ids before showing sites table on manage sites page.
* Fixed pmpron_pmpro_after_change_membership_level to expect level_id instead of level object

= .3 =
* Added ability for users to register multiple sites on the network. Storing blog ids in pmpron_blog_ids user meta. The pmpron_blog_id (no s) meta value will still hold the first site created. Create a page with the [pmpron_manage_sites] shortcode on it to create the page to add new sites; use the pmpron_site_credits filter to change the number of site credits given to users signing up.
* Abstracted some of the code around site creation.
* Fixed a potential bug with the check to see if a sitename was already taken.

= .2 =
* Storing some vars in $_SESSION for when using PayPal Express or other offsite payment processors.
* Fixed wp-admin link to new site dashboard on confirmation page.

= .1 =
* Initial version.
