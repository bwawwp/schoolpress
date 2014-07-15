<?php
/**
 * Plugin Name: Custom Redirection
 * Description: Enabling this module will initialize custom redirection. You will then have to configure the settings via the "Redirection" tab.
 *
 * Holds Theme My Login Custom Redirection class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Redirection
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Redirection' ) ) :
/**
 * Theme My Login Custom Redirection class
 *
 * Adds the ability to redirect users when logging in/out based upon their "user role".
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Redirection extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_redirection';

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
	 * Called on Theme_My_Login_Abstract::__construct
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'login_form',      array( &$this, 'login_form'      )        );
		add_filter( 'login_redirect',  array( &$this, 'login_redirect'  ), 10, 3 );
		add_filter( 'logout_redirect', array( &$this, 'logout_redirect' ), 10, 3 );
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
		foreach ( $wp_roles->get_names() as $role => $label ) {
			if ( 'pending' != $role ) {
				$options[$role] = array(
					'login_type' => 'default',
					'login_url' => '',
					'logout_type' => 'default',
					'logout_url' => ''
				);
			}
		}
		return $options;
	}

	/**
	 * Adds "_wp_original_referer" field to login form
	 *
	 * Callback for "login_form" hook in file "login-form.php", included by method Theme_My_Login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 */
	public function login_form() {
		$template =& Theme_My_Login::get_object()->get_active_instance();
		echo wp_original_referer_field( false, $template->get_option( 'instance' ) ? 'current' : 'previous' ) . "\n";
	}

	/**
	 * Handles login redirection
	 *
	 * Callback for "login_redirect" hook in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @param string $request Requested redirect
	 * @param WP_User|WP_Error WP_User if user logged in, WP_Error otherwise
	 * @return string New redirect
	 */
	public function login_redirect( $redirect_to, $request, $user ) {
		// Determine the correct referer
		if ( ! $http_referer = wp_get_original_referer() )
			$http_referer = wp_get_referer();

		// Remove some arguments that may be present and shouldn't be
		$http_referer = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce', 'reauth' ), $http_referer );

		// Make sure $user object exists and is a WP_User instance
		if ( ! is_wp_error( $user ) && is_a( $user, 'WP_User' ) ) {
			if ( is_multisite() && empty( $user->roles ) ) {
				$user->roles = array( 'subscriber' );
			}

			$user_role = reset( $user->roles );

			$redirection = $this->get_option( $user_role, array() );

			if ( 'referer' == $redirection['login_type'] ) {
				// Send 'em back to the referer
				$redirect_to = $http_referer;
			} elseif ( 'custom' == $redirection['login_type'] ) {
				// Send 'em to the specified URL
				$redirect_to = $redirection['login_url'];

				// Allow a few user specific variables
				$redirect_to = Theme_My_Login_Common::replace_vars( $redirect_to, $user->ID, array(
					'%user_id%' => $user->ID
				) );
			}
		}

		// If a redirect is requested, it takes precedence
		if ( ! empty( $request ) && admin_url() != $request && admin_url( 'profile.php' ) != $request )
			$redirect_to = $request;

		// Make sure $redirect_to isn't empty
		if ( empty( $redirect_to ) )
			$redirect_to = get_option( 'home' );

		return $redirect_to;
	}

	/**
	 * Handles logout redirection
	 *
	 * Callback for "logout_redirect" hook in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @param string $request Requested redirect
	 * @param WP_User|WP_Error WP_User if user logged in, WP_Error otherwise
	 * @return string New redirect
	 */
	public function logout_redirect( $redirect_to, $request, $user ) {
		// Determine the correct referer
		if ( ! $http_referer = wp_get_original_referer() )
			$http_referer = wp_get_referer();

		// Remove some arguments that may be present and shouldn't be
		$http_referer = remove_query_arg( array( 'instance', 'action', 'checkemail', 'error', 'loggedout', 'registered', 'redirect_to', 'updated', 'key', '_wpnonce' ), $http_referer );

		// Make sure $user object exists and is a WP_User instance
		if ( ! is_wp_error( $user ) && is_a( $user, 'WP_User' ) ) {
			if ( is_multisite() && empty( $user->roles ) ) {
				$user->roles = array( 'subscriber' );
			}

			$user_role = reset( $user->roles );

			$redirection = $this->get_option( $user_role, array() );

			if ( 'referer' == $redirection['logout_type'] ) {
				// Send 'em back to the referer
				$redirect_to = $http_referer;
			} elseif ( 'custom' == $redirection['logout_type'] ) {
				// Send 'em to the specified URL
				$redirect_to = $redirection['logout_url'];

				// Allow a few user specific variables
				$redirect_to = Theme_My_Login_Common::replace_vars( $redirect_to, $user->ID, array(
					'%user_id%' => $user->ID
				) );
			}
		}

		// Make sure $redirect_to isn't empty or pointing to an admin URL (causing an endless loop)
		if ( empty( $redirect_to ) || false !== strpos( $redirect_to, 'wp-admin' ) )
			$redirect_to = add_query_arg( 'loggedout', 'true', wp_login_url() );

		return $redirect_to;
	}
}

Theme_My_Login_Custom_Redirection::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/custom-redirection-admin.php' );


