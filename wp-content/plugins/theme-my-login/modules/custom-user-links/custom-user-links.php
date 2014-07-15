<?php
/**
 * Plugin Name: Custom User Links
 * Description: Enabling this module will initialize custom user links. You will then have to configure the settings via the "User Links" tab.
 *
 * Holds Theme My Login Custom User Links class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_User_Links
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_User_Links' ) ) :
/**
 * Theme My Login Custom User Links module class
 *
 * Adds the ability to define custom links to display to a user when logged in based upon their "user role".
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_User_Links extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_user_links';

	/**
	 * Returns singleton instance
	 *
	 * @since 6.3
	 * @access public
	 * @return object
	 */
	public static function get_object( $class = null ) {
		return parent::get_object( __CLASS__ );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		global $wp_roles;

		if ( empty( $wp_roles ) )
			$wp_roles = new WP_Roles;

		$options = array();
		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' != $role ) {
				$options[$role] = array(
					array(
						'title' => __( 'Dashboard' ),
						'url'   => admin_url()
					),
					array(
						'title' => __( 'Profile' ),
						'url'   => admin_url( 'profile.php' )
					)
				);
			}
		}
		return $options;
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_filter( 'tml_user_links', array( &$this, 'get_user_links' ) );
	}

	/**
	 * Gets the user links for the current user's role
	 *
	 * Callback for "tml_user_links" hook in method Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 *
	 * @param array $links Default user links
	 * @return array New user links
	 */
	public function get_user_links( $links = array() ) {
		if ( ! is_user_logged_in() )
			return $links;

		$current_user = wp_get_current_user();
		if ( is_multisite() && empty( $current_user->roles ) )
			$current_user->roles = array( 'subscriber' );

		foreach( (array) $current_user->roles as $role ) {
			if ( $links = $this->get_option( $role ) );
				break;
		}

		// Define and allow filtering of replacement variables
		$replacements = apply_filters( 'tml_custom_user_links_variables', array(
			'%user_id%'  => $current_user->ID,
			'%username%' => $current_user->user_nicename
		) );

		// Replace variables in link
		foreach ( (array) $links as $key => $link ) {
			$links[$key]['url'] = Theme_My_Login_Common::replace_vars( $link['url'], $current_user->ID, $replacements );
		}

		return $links;
	}
}

Theme_My_Login_Custom_User_Links::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/custom-user-links-admin.php' );

