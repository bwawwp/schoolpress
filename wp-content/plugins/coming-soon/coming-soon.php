<?php
/*
 * Plugin Name:       Coming Soon
 * Plugin URI:        http://www.seedprod.com
 * Description:       Coming Soon, Maintenance Mode pages in minutes
 * Version:           4.0.3
 * Author:            SeedProd
 * Author URI:        http://www.seedprod.com
 * Text Domain:       coming-soon
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * Copyright 2012  John Turner (email : john@seedprod.com, twitter : @johnturner)
 */

/**
 * Default Constants
 */
define( 'SEED_CSP4_SHORTNAME', 'seed_csp4' ); // Used to reference namespace functions.
define( 'SEED_CSP4_SLUG', 'coming-soon/coming-soon.php' ); // Used for settings link.
define( 'SEED_CSP4_TEXTDOMAIN', 'coming-soon' ); // Your textdomain
define( 'SEED_CSP4_PLUGIN_NAME', __( 'Coming Soon', 'coming-soon' ) ); // Plugin Name shows up on the admin settings screen.
define( 'SEED_CSP4_VERSION', '4.0.2'); // Plugin Version Number. Recommend you use Semantic Versioning http://semver.org/
define( 'SEED_CSP4_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); // Example output: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/seed_csp4/
define( 'SEED_CSP4_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // Example output: http://localhost:8888/wordpress/wp-content/plugins/seed_csp4/
define( 'SEED_CSP4_TABLENAME', 'seed_csp4_subscribers' );


/**
 * Load Translation
 */
function seed_csp4_load_textdomain() {
    load_plugin_textdomain( 'coming-soon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'seed_csp4_load_textdomain');


/**
 * Upon activation of the plugin, see if we are running the required version and deploy theme in defined.
 *
 * @since 0.1.0
 */
function seed_csp4_activation(){
	require_once( 'inc/default-settings.php' );
	add_option('seed_csp4_settings_content',unserialize($seed_csp4_settings_deafults['seed_csp4_settings_content']));
	add_option('seed_csp4_settings_design',unserialize($seed_csp4_settings_deafults['seed_csp4_settings_design']));
	add_option('seed_csp4_settings_advanced',unserialize($seed_csp4_settings_deafults['seed_csp4_settings_advanced']));
}
register_activation_hook( __FILE__, 'seed_csp4_activation' );


/***************************************************************************
 * Load Required Files
 ***************************************************************************/

// Global
global $seed_csp4_settings;

require_once( 'framework/get-settings.php' );
$seed_csp4_settings = seed_csp4_get_settings();

require_once( 'inc/class-seed-csp4.php' );
add_action( 'plugins_loaded', array( 'SEED_CSP4', 'get_instance' ) );

if( is_admin() ) {
// Admin Only
	require_once( 'inc/config-settings.php' );
    require_once( 'framework/framework.php' );
    add_action( 'plugins_loaded', array( 'SEED_CSP4_ADMIN', 'get_instance' ) );
} else {
// Public only

}