<?php
/**
 * Plugin Name: Themed Profiles
 * Description: Enabling this module will initialize and enable themed profiles. You will then have to configure the settings via the "Themed Profiles" tab.
 *
 * Holds Theme My Login Themed Profiles class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Themed_Profiles
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Themed_Profiles' ) ) :
/**
 * Theme My Login Themed Profiles class
 *
 * Allows users to edit profile on the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login_Themed_Profiles extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_themed_profiles';

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
		foreach ( $wp_roles->get_names() as $role => $label ) {
			if ( 'pending' != $role ) {
				$options[$role] = array(
					'theme_profile'  => true,
					'restrict_admin' => false
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
		add_action( 'tml_modules_loaded', array( &$this, 'modules_loaded' ) );

		add_action( 'init',              array( &$this, 'init'              ) );
		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
		add_filter( 'show_admin_bar',    array( &$this, 'show_admin_bar'    ) );

		add_action( 'tml_request_profile', array( &$this, 'tml_request_profile' ) );
		add_action( 'tml_display_profile', array( &$this, 'tml_display_profile' ) );
	}

	/**
	 * Adds filters to site_url() and admin_url()
	 *
	 * Callback for "tml_modules_loaded" in file "theme-my-login.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function modules_loaded() {
		add_filter( 'site_url',  array( &$this, 'site_url' ), 10, 3 );
		add_filter( 'admin_url', array( &$this, 'site_url' ), 10, 2 );
	}

	/**
	 * Redirects "profile.php" to themed profile page
	 *
	 * Callback for "init" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function init() {
		global $current_user, $pagenow;

        if ( is_user_logged_in() && is_admin() ) {
			$redirect_to = Theme_My_Login::get_page_link( 'profile' );

			$user_role = reset( $current_user->roles );
			if ( is_multisite() && empty( $user_role ) )
				$user_role = 'subscriber';

			if ( 'profile.php' == $pagenow && ! isset( $_REQUEST['page'] ) ) {
				if ( $this->get_option( array( $user_role, 'theme_profile' ) ) ) {
					if ( ! empty( $_GET ) )
						$redirect_to = add_query_arg( (array) $_GET, $redirect_to );
					wp_redirect( $redirect_to );
					exit;
				}
			} else {
				if ( $this->get_option( array( $user_role, 'restrict_admin' ) ) ) {
					if ( ! defined( 'DOING_AJAX' ) ) {
						wp_redirect( $redirect_to );
						exit;
					}
				}
			}
        }
	}

	/**
	 * Redirects login page to profile if user is logged in
	 *
	 * Callback for "template_redirect" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function template_redirect() {
		$theme_my_login = Theme_My_Login::get_object();

		if ( Theme_My_Login::is_tml_page() ) {
			switch ( $theme_my_login->request_action ) {
				case 'profile' :
					// Redirect to login page if not logged in
					if ( ! is_user_logged_in() ) {
						$redirect_to = Theme_My_Login::get_page_link( 'login', 'reauth=1' );
						wp_redirect( $redirect_to );
						exit;
					}
					break;
				case 'logout' :
					// Allow logout action
					break;
				case 'register' :
					// Allow register action if multisite
					if ( is_multisite() )
						break;
				default :
					// Redirect to profile for any other action if logged in
					if ( is_user_logged_in() ) {
						$redirect_to = Theme_My_Login::get_page_link( 'profile' );
						wp_redirect( $redirect_to );
						exit;
					}
			}
		}
	}

	/**
	 * Hides admin bar is admin is restricted
	 *
	 * Callback for "show_admin_bar" hook
	 *
	 * @since 6.2
	 * @access public
	 */
	public function show_admin_bar( $show_admin_bar ) {
		global $current_user;

		$user_role = reset( $current_user->roles );
		if ( is_multisite() && empty( $user_role ) )
			$user_role = 'subscriber';

		if ( $this->get_option( array( $user_role, 'restrict_admin' ) ) )
			return false;

		return $show_admin_bar;
	}

	/**
	 * Handles profile action
	 *
	 * Callback for "tml_request_profile" in method Theme_My_Login::the_request()
	 *
	 * @see Theme_My_Login::the_request()
	 * @since 6.0
	 * @access public
	 */
	public function tml_request_profile() {
		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		require_once( ABSPATH . 'wp-admin/includes/misc.php' );

		define( 'IS_PROFILE_PAGE', true );

		load_textdomain( 'default', WP_LANG_DIR . '/admin-' . get_locale() . '.mo' );

		register_admin_color_schemes();

		wp_enqueue_style( 'password-strength', plugins_url( 'theme-my-login/modules/themed-profiles/themed-profiles.css' ) );

		wp_enqueue_script( 'user-profile' );

		$current_user = wp_get_current_user();

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'update-user_' . $current_user->ID );

			if ( ! current_user_can( 'edit_user', $current_user->ID ) )
				wp_die( __( 'You do not have permission to edit this user.' ) );

			do_action( 'personal_options_update', $current_user->ID );

			$errors = edit_user( $current_user->ID );

			if ( ! is_wp_error( $errors ) ) {
				$args = array( 'updated' => 'true' );
				if ( ! empty( $_REQUEST['instance'] ) )
					$args['instance'] = $_REQUEST['instance'];
				$redirect = add_query_arg( $args );
				wp_redirect( $redirect );
				exit;
			} else {
				Theme_My_Login::get_object()->errors = $errors;
			}
		}
	}

	/**
	 * Outputs profile form HTML
	 *
	 * Callback for "tml_display_profile" hook in method Theme_My_login_Template::display()
	 *
	 * @see Theme_My_Login_Template::display()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $template Reference to $theme_my_login_template object
	 */
	public function tml_display_profile( &$template ) {
		global $current_user, $profileuser, $_wp_admin_css_colors, $wp_version;

		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		require_once( ABSPATH . 'wp-admin/includes/misc.php' );

		if ( isset( $_GET['updated'] ) && 'true' == $_GET['updated'] )
			Theme_My_Login::get_object()->errors->add( 'profile_updated', __( 'Profile updated.' ), 'message' );

		$current_user = wp_get_current_user();
		$profileuser  = get_user_to_edit( $current_user->ID );

		$user_role = reset( $profileuser->roles );
		if ( is_multisite() && empty( $user_role ) )
			$user_role = 'subscriber';

		$_template = array();

		// Allow template override via shortcode or template tag args
		if ( ! empty( $template->options['profile_template'] ) )
			$_template[] = $template->options['profile_template'];

		// Allow role template overrid via shortcode or template tag args
		if ( ! empty( $template->options["profile_template_{$user_role}"] ) )
			$_template[] = $template->options["profile_template_{$user_role}"];

		// Role template
		$_template[] = "profile-form-{$user_role}.php";

		// Default template
		$_template[] = 'profile-form.php';

		// Load template
		$template->get_template( $_template, true, compact( 'current_user', 'profileuser', 'user_role', '_wp_admin_css_colors', 'wp_version' ) );
	}

	/**
	 * Changes links from "profile.php" to themed profile page
	 *
	 * Callback for "site_url" hook
	 *
	 * @see site_url()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $url The generated link
	 * @param string $path The specified path
	 * @param string $orig_scheme The original connection scheme
	 * @return string The filtered link
	 */
	public function site_url( $url, $path, $orig_scheme = '' ) {
		global $current_user, $pagenow;

		if ( 'profile.php' != $pagenow && strpos( $url, 'profile.php' ) !== false ) {
			$user_role = reset( $current_user->roles );
			if ( is_multisite() && empty( $user_role ) )
				$user_role = 'subscriber';

			if ( $user_role && ! $this->get_option( array( $user_role, 'theme_profile' ) ) )
				return $url;
					
			$parsed_url = parse_url( $url );

			$url = Theme_My_Login::get_page_link( 'profile' );

			if ( isset( $parsed_url['query'] ) )
				$url = add_query_arg( array_map( 'rawurlencode', wp_parse_args( $parsed_url['query'] ) ), $url );
		}
		return $url;
	}
}

Theme_My_Login_Themed_Profiles::get_object();

endif;

if ( is_admin() )
	include_once( dirname( 	__FILE__ ) . '/admin/themed-profiles-admin.php' );

