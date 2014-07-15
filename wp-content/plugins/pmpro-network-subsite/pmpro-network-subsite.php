<?php
/*
Plugin Name: Paid Memberships Pro Network Subsite Helper
Plugin URI: http://www.paidmembershipspro.com/add-ons/pmpro-network-subsites/
Description: Replacement for Paid Memberships Pro meant to be run on a network site, pointing to another network site's PMPro install for membership checks/etc.
Version: .2
Author: Stranger Studios
Author URI: http://www.strangerstudios.com
*/
/*	
	Copyright 2011	Stranger Studios	(email : jason@strangerstudios.com)	 
	This code is licensed under the GPLv2.
*/

define('PMPRO_NETWORK_MAIN_DB_PREFIX', 'wp');

/*
	Make sure this plugin loads after Paid Memberships Pro
*/
function pmpron_subsite_activated_plugin() 
{
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	
	//load plugins
	$active_plugins = get_option('active_plugins');
	
	//where am I?
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	
	//move to end
	array_splice($active_plugins, $this_plugin_key, 1);
	$active_plugins[] = $this_plugin;
	
	//update option
	update_option('active_plugins', $active_plugins);	
}
add_action("activated_plugin", "pmpron_subsite_activated_plugin");

/*
	Now update wpdb tables.
	
	(Updated again in init to get all cases.)
*/
global $wpdb;
$wpdb->pmpro_memberships_users = PMPRO_NETWORK_MAIN_DB_PREFIX . "_pmpro_memberships_users";
$wpdb->pmpro_membership_levels = PMPRO_NETWORK_MAIN_DB_PREFIX . "_pmpro_membership_levels";

//get levels again
function pmpron_init_get_levels()
{
	global $wpdb, $membership_levels;
	$membership_levels = $wpdb->get_results( "SELECT * FROM {$wpdb->pmpro_membership_levels}", OBJECT );
}
add_action('init', 'pmpron_init_get_levels', 1);

/*
	Hide admin stuff
*/
function pmpron_subsite_init()
{
	//remove admin pages
	remove_action('admin_menu', 'pmpro_add_pages');
	remove_action('admin_bar_menu', 'pmpro_admin_bar_menu');
	
	//remove membership level from edit users page
	remove_action( 'show_user_profile', 'pmpro_membership_level_profile_fields' );
	remove_action( 'edit_user_profile', 'pmpro_membership_level_profile_fields' );
	remove_action( 'profile_update', 'pmpro_membership_level_profile_fields_update' );
	
	//update wpdb tables again
	global $wpdb;
	$wpdb->pmpro_memberships_users = PMPRO_NETWORK_MAIN_DB_PREFIX . "_pmpro_memberships_users";
	$wpdb->pmpro_membership_levels = PMPRO_NETWORK_MAIN_DB_PREFIX . "_pmpro_membership_levels";
}
add_action("init", "pmpron_subsite_init", 15);
