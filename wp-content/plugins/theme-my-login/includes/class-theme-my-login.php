<?php
/**
 * Holds the Theme My Login class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login' ) ) :
/*
 * Theme My Login class
 *
 * This class contains properties and methods common to the front-end.
 *
 * @since 6.0
 */
class Theme_My_Login extends Theme_My_Login_Abstract {
	/**
	 * Holds plugin version
	 *
	 * @since 6.3.2
	 * @const string
	 */
	const version = '6.3.8';

	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login';

	/**
	 * Holds errors object
	 *
	 * @since 6.0
	 * @access public
	 * @var object
	 */
	public $errors;

	/**
	 * Holds current page being requested
	 *
	 * @since 6.3
	 * @access public
	 * @var string
	 */
	public $request_page;

	/**
	 * Holds current action being requested
	 *
	 * @since 6.0
	 * @access public
	 * @var string
	 */
	public $request_action;

	/**
	 * Holds current instance being requested
	 *
	 * @since 6.0
	 * @access public
	 * @var int
	 */
	public $request_instance;

	/**
	 * Holds loaded instances
	 *
	 * @since 6.3
	 * @access protected
	 * @var array
	 */
	protected $loaded_instances = array();

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
		return apply_filters( 'tml_default_options', array(
			'enable_css'     => true,
			'email_login'    => true,
			'active_modules' => array()
		) );
	}

	/**
	 * Returns default pages
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default pages
	 */
	public static function default_pages() {
		return apply_filters( 'tml_default_pages', array(
			'login'        => __( 'Log In'         ),
			'logout'       => __( 'Log Out'        ),
			'register'     => __( 'Register'       ),
			'lostpassword' => __( 'Lost Password'  ),
			'resetpass'    => __( 'Reset Password' )
		) );
	}

	/**
	 * Loads the plugin
	 *
	 * @since 6.0
	 * @access public
	 */
	protected function load() {

		$this->load_instance();

		add_action( 'plugins_loaded',          array( &$this, 'plugins_loaded'          ) );
		add_action( 'init',                    array( &$this, 'init'                    ) );
		add_action( 'widgets_init',            array( &$this, 'widgets_init'            ) );
		add_action( 'wp',                      array( &$this, 'wp'                      ) );
		add_action( 'template_redirect',       array( &$this, 'template_redirect'       ) );
		add_action( 'wp_enqueue_scripts',      array( &$this, 'wp_enqueue_scripts'      ) );
		add_action( 'wp_head',                 array( &$this, 'wp_head'                 ) );
		add_action( 'wp_footer',               array( &$this, 'wp_footer'               ) );
		add_action( 'wp_print_footer_scripts', array( &$this, 'wp_print_footer_scripts' ) );
		add_action( 'wp_authenticate',         array( &$this, 'wp_authenticate'         ) );

		add_filter( 'site_url',               array( &$this, 'site_url'               ), 10, 3 );
		add_filter( 'logout_url',             array( &$this, 'logout_url'             ), 10, 2 );
		add_filter( 'single_post_title',      array( &$this, 'single_post_title'      )        );
		add_filter( 'the_title',              array( &$this, 'the_title'              ), 10, 2 );
		add_filter( 'wp_setup_nav_menu_item', array( &$this, 'wp_setup_nav_menu_item' )        );
		add_filter( 'wp_list_pages_excludes', array( &$this, 'wp_list_pages_excludes' )        );
		add_filter( 'page_link',              array( &$this, 'page_link'              ), 10, 2 );

		add_action( 'tml_new_user_registered',   'wp_new_user_notification', 10, 2 );
		add_action( 'tml_user_password_changed', 'wp_password_change_notification' );

		add_shortcode( 'theme-my-login', array( &$this, 'shortcode' ) );
	}


	/************************************************************************************************************************
	 * Actions
	 ************************************************************************************************************************/

	/**
	 * Loads active modules
	 *
	 * @since 6.3
	 * @access public
	 */
	public function plugins_loaded() {
		foreach ( $this->get_option( 'active_modules', array() ) as $module ) {
			if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
				include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
		}
		do_action_ref_array( 'tml_modules_loaded', array( &$this ) );
	}

	/**
	 * Initializes the plugin
	 *
	 * @since 6.0
	 * @access public
	 */
	public function init() {
		self::load_textdomain();

		$this->errors = new WP_Error();

		if ( ! is_admin() && $this->get_option( 'enable_css' ) )
			wp_enqueue_style( 'theme-my-login', self::get_stylesheet(), false, $this->get_option( 'version' ) );
	}

	/**
	 * Registers the widget
	 *
	 * @since 6.0
	 * @access public
	 */
	public function widgets_init() {
		if ( class_exists( 'Theme_My_Login_Widget' ) )
			register_widget( 'Theme_My_Login_Widget' );
	}

	/**
	 * Used to add/remove filters from login page
	 *
	 * @since 6.1.1
	 * @access public
	 */
	public function wp() {
		if ( self::is_tml_page() ) {
			do_action( 'login_init' );

			remove_action( 'wp_head', 'feed_links',                       2 );
			remove_action( 'wp_head', 'feed_links_extra',                 3 );
			remove_action( 'wp_head', 'rsd_link'                            );
			remove_action( 'wp_head', 'wlwmanifest_link'                    );
			remove_action( 'wp_head', 'parent_post_rel_link',            10 );
			remove_action( 'wp_head', 'start_post_rel_link',             10 );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
			remove_action( 'wp_head', 'rel_canonical'                       );

			// Don't index any of these forms
			add_action( 'login_head', 'wp_no_robots' );

			if ( force_ssl_admin() && ! is_ssl() ) {
				if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
					wp_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ) );
					exit;
				} else {
					wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
					exit;
				}
			}
		}
	}

	/**
	 * Proccesses the request
	 *
	 * Callback for "template_redirect" hook in template-loader.php
	 *
	 * @since 6.3
	 * @access public
	 */
	public function template_redirect() {
		$this->request_action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : '';
		if ( ! $this->request_action && self::is_tml_page() )
			$this->request_action = self::get_page_action( get_the_id() );
		$this->request_instance = isset( $_REQUEST['instance'] ) ? sanitize_key( $_REQUEST['instance'] ) : 0;

		do_action_ref_array( 'tml_request', array( &$this ) );

		// allow plugins to override the default actions, and to add extra actions if they want
		do_action( 'login_form_' . $this->request_action );

		if ( has_action( 'tml_request_' . $this->request_action ) ) {
			do_action_ref_array( 'tml_request_' . $this->request_action, array( &$this ) );
		} else {
			$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
			switch ( $this->request_action ) {
				case 'postpass' :
					global $wp_hasher;

					if ( empty( $wp_hasher ) ) {
						require_once( ABSPATH . 'wp-includes/class-phpass.php' );
						// By default, use the portable hash from phpass
						$wp_hasher = new PasswordHash( 8, true );
					}

					// 10 days
					setcookie( 'wp-postpass_' . COOKIEHASH, $wp_hasher->HashPassword( stripslashes( $_POST['post_password'] ) ), time() + 864000, COOKIEPATH );

					wp_safe_redirect( wp_get_referer() );
					exit;

					break;
				case 'logout' :
					check_admin_referer( 'log-out' );

					$user = wp_get_current_user();

					wp_logout();

					$redirect_to = apply_filters( 'logout_redirect', site_url( 'wp-login.php?loggedout=true' ), isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );
					wp_safe_redirect( $redirect_to );
					exit;
					break;
				case 'lostpassword' :
				case 'retrievepassword' :
					if ( $http_post ) {
						$this->errors = self::retrieve_password();
						if ( ! is_wp_error( $this->errors ) ) {
							$redirect_to = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : site_url( 'wp-login.php?checkemail=confirm' );
							wp_safe_redirect( $redirect_to );
							exit;
						}
					}

					if ( isset( $_REQUEST['error'] ) && 'invalidkey' == $_REQUEST['error'] )
						$this->errors->add( 'invalidkey', __( 'Sorry, that key does not appear to be valid.' ) );

					do_action( 'lost_password' );
					break;
				case 'resetpass' :
				case 'rp' :
					$user = self::check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

					if ( is_wp_error( $user ) ) {
						$redirect_to = site_url( 'wp-login.php?action=lostpassword&error=invalidkey' );
						wp_redirect( $redirect_to );
						exit;
					}

					if ( isset( $_POST['pass1'] ) && $_POST['pass1'] != $_POST['pass2'] ) {
						$this->errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
					} elseif ( isset( $_POST['pass1'] ) && ! empty( $_POST['pass1'] ) ) {
						self::reset_password( $user, $_POST['pass1'] );

						$redirect_to = site_url( 'wp-login.php?resetpass=complete' );
						wp_safe_redirect( $redirect_to );
						exit;
					}

					wp_enqueue_script( 'utils' );
					wp_enqueue_script( 'user-profile' );
					break;
				case 'register' :
					if ( ! get_option( 'users_can_register' ) ) {
						$redirect_to = site_url( 'wp-login.php?registration=disabled' );
						wp_redirect( $redirect_to );
						exit;
					}

					$user_login = '';
					$user_email = '';
					if ( $http_post ) {
						$user_login = $_POST['user_login'];
						$user_email = $_POST['user_email'];

						$this->errors = self::register_new_user( $user_login, $user_email );
						if ( ! is_wp_error( $this->errors ) ) {
							$redirect_to = ! empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : site_url( 'wp-login.php?checkemail=registered' );
							wp_safe_redirect( $redirect_to );
							exit;
						}
					}
					break;
				case 'login' :
				default:
					$secure_cookie = '';
					$interim_login = isset( $_REQUEST['interim-login'] );

					// If the user wants ssl but the session is not ssl, force a secure cookie.
					if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
						$user_name = sanitize_user( $_POST['log'] );
						if ( $user = get_user_by( 'login', $user_name ) ) {
							if ( get_user_option( 'use_ssl', $user->ID ) ) {
								$secure_cookie = true;
								force_ssl_admin( true );
							}
						}
					}

					if ( ! empty( $_REQUEST['redirect_to'] ) ) {
						$redirect_to = $_REQUEST['redirect_to'];
						// Redirect to https if user wants ssl
						if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) )
							$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
					} else {
						$redirect_to = admin_url();
					}

					$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

					// If the user was redirected to a secure login form from a non-secure admin page, and secure login is required but secure admin is not, then don't use a secure
					// cookie and redirect back to the referring non-secure admin page.  This allows logins to always be POSTed over SSL while allowing the user to choose visiting
					// the admin via http or https.
					if ( ! $secure_cookie && is_ssl() && force_ssl_login() && ! force_ssl_admin() && ( 0 !== strpos( $redirect_to, 'https' ) ) && ( 0 === strpos( $redirect_to, 'http' ) ) )
						$secure_cookie = false;

					if ( $http_post && isset( $_POST['log'] ) ) {

						$user = wp_signon( '', $secure_cookie );

						$redirect_to = apply_filters( 'login_redirect', $redirect_to, isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '', $user );

						if ( ! is_wp_error( $user ) && ! $reauth ) {
							if ( ( empty( $redirect_to ) || $redirect_to == 'wp-admin/' || $redirect_to == admin_url() ) ) {
								// If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
								if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) )
									$redirect_to = user_admin_url();
								elseif ( is_multisite() && ! $user->has_cap( 'read' ) )
									$redirect_to = get_dashboard_url( $user->ID );
								elseif ( ! $user->has_cap( 'edit_posts' ) )
									$redirect_to = admin_url( 'profile.php' );
							}
							wp_safe_redirect( $redirect_to );
							exit;
						}

						$this->errors = $user;
					}

					// Clear errors if loggedout is set.
					if ( ! empty( $_GET['loggedout'] ) || $reauth )
						$this->errors = new WP_Error();

					// Some parts of this script use the main login form to display a message
					if		( isset( $_GET['loggedout'] ) && true == $_GET['loggedout'] )
						$this->errors->add( 'loggedout', __( 'You are now logged out.' ), 'message' );
					elseif	( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
						$this->errors->add( 'registerdisabled', __( 'User registration is currently not allowed.' ) );
					elseif	( isset( $_GET['checkemail'] ) && 'confirm' == $_GET['checkemail'] )
						$this->errors->add( 'confirm', __( 'Check your e-mail for the confirmation link.' ), 'message' );
					elseif ( isset( $_GET['resetpass'] ) && 'complete' == $_GET['resetpass'] )
						$this->errors->add( 'password_reset', __( 'Your password has been reset.' ), 'message' );
					elseif	( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
						$this->errors->add( 'registered', __( 'Registration complete. Please check your e-mail.' ), 'message' );
					elseif	( $interim_login )
						$this->errors->add( 'expired', __( 'Your session has expired. Please log-in again.' ), 'message' );
					elseif ( strpos( $redirect_to, 'about.php?updated' ) )
						$this->errors->add('updated', __( '<strong>You have successfully updated WordPress!</strong> Please log back in to experience the awesomeness.' ), 'message' );
					elseif	( $reauth )
						$this->errors->add( 'reauth', __( 'Please log in to continue.', 'theme-my-login' ), 'message' );

					// Clear any stale cookies.
					if ( $reauth )
						wp_clear_auth_cookie();
					break;
			} // end switch
		} // endif has_filter()
	}

	/**
	 * Calls "login_enqueue_scripts" on login page
	 *
	 * Callback for "wp_enqueue_scripts" hook
	 *
	 * @since 6.3
	 */
	public function wp_enqueue_scripts() {
		if ( self::is_tml_page() )
			do_action( 'login_enqueue_scripts' );
	}

	/**
	 * Calls "login_head" hook on login page
	 *
	 * Callback for "wp_head" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function wp_head() {
		if ( self::is_tml_page() ) {
			// This is already attached to "wp_head"
			remove_action( 'login_head', 'wp_print_head_scripts', 9 );

			do_action( 'login_head' );
		}
	}

	/**
	 * Calls "login_footer" hook on login page
	 *
	 * Callback for "wp_footer" hook
	 *
	 * @since 6.3
	 */
	public function wp_footer() {
		if ( self::is_tml_page() ) {
			// This is already attached to "wp_footer"
			remove_action( 'login_footer', 'wp_print_footer_scripts', 20 );

			do_action( 'login_footer' );
		}
	}

	/**
	 * Prints javascript in the footer
	 *
	 * @since 6.0
	 * @access public
	 */
	public function wp_print_footer_scripts() {
		if ( ! self::is_tml_page() )
			return;

		switch ( $this->request_action ) {
			case 'lostpassword' :
			case 'retrievepassword' :
			case 'register' :
			?>
<script type="text/javascript">
try{document.getElementById('user_login').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
			case 'resetpass' :
			case 'rp' :
			?>
<script type="text/javascript">
try{document.getElementById('pass1').focus();}catch(e){}
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
			case 'login' :
				$user_login = '';
				if ( isset($_POST['log']) )
					$user_login = ( 'incorrect_password' == $this->errors->get_error_code() || 'empty_password' == $this->errors->get_error_code() ) ? esc_attr( stripslashes( $_POST['log'] ) ) : '';
			?>
<script type="text/javascript">
function wp_attempt_focus() {
setTimeout( function() {
try {
<?php if ( $user_login ) { ?>
d = document.getElementById('user_pass');
<?php } else { ?>
d = document.getElementById('user_login');
<?php } ?>
d.value = '';
d.focus();
} catch(e){}
}, 200 );
}
wp_attempt_focus();
if(typeof wpOnload=='function')wpOnload()
</script>
<?php
				break;
		}
	}

	/**
	 * Handles e-mail address login
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $username Username or email
	 * @param string $password User's password
	 */
	public function wp_authenticate( &$user_login ) {
		global $wpdb;
		if ( is_email( $user_login ) && $this->get_option( 'email_login' ) ) {
			if ( $found = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE user_email = %s", $user_login ) ) )
				$user_login = $found;
		}
		return;
	}


	/************************************************************************************************************************
	 * Filters
	 ************************************************************************************************************************/

	/**
	 * Rewrites URL's containing wp-login.php created by site_url()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $url The URL
	 * @param string $path The path specified
	 * @param string $orig_scheme The current connection scheme (HTTP/HTTPS)
	 * @param int $blog_id Blog ID
	 * @return string The modified URL
	 */
	public function site_url( $url, $path, $orig_scheme ) {
		global $pagenow;

		if ( 'wp-login.php' != $pagenow && false !== strpos( $url, 'wp-login.php' ) && ! isset( $_REQUEST['interim-login'] ) ) {
			parse_str( parse_url( $url, PHP_URL_QUERY ), $query );

			$action = isset( $query['action'] ) ? $query['action'] : 'login';

			$url = self::get_page_link( $action, $query );

			if ( 'https' == strtolower( $orig_scheme ) )
				$url = preg_replace( '|^http://|', 'https://', $url );
		}
		return $url;
	}

	/**
	 * Filters logout URL to allow for logout permalink
	 *
	 * This is needed because WP doesn't pass the action parameter to site_url
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $logout_url Logout URL
	 * @param string $redirect Redirect URL
	 * @return string Logout URL
	 */
	public function logout_url( $logout_url, $redirect ) {
		$logout_url = self::get_page_link( 'logout' );
		if ( $redirect )
			$logout = add_query_arg( 'redirect_to', urlencode( $redirect ), $logout_url );
		return $logout_url;
	}

	/**
	 * Changes single_post_title() to reflect the current action
	 *
	 * Callback for "single_post_title" hook in single_post_title()
	 *
	 * @see single_post_title()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $title The current post title
	 * @return string The modified post title
	 */
	function single_post_title( $title ) {
		if ( self::is_tml_page( 'login' ) && is_user_logged_in() )
			$title = $this->get_instance()->get_title( 'login' );
		return $title;
	}

	/**
	 * Changes the_title() to reflect the current action
	 *
	 * Callback for "the_title" hook in the_title()
	 *
	 * @see the_title()
	 * @since 6.0
	 * @acess public
	 *
	 * @param string $title The current post title
	 * @param int $post_id The current post ID
	 * @return string The modified post title
	 */
	public function the_title( $title, $post_id = 0 ) {
		if ( is_admin() )
			return $title;

		if ( self::is_tml_page( 'login', $post_id ) && is_user_logged_in() ) {
			if ( in_the_loop() )
				$title = $this->get_instance()->get_title( 'login' );
		}
		return $title;
	}

	/**
	 * Alters menu item title & link according to whether user is logged in or not
	 *
	 * Callback for "wp_setup_nav_menu_item" hook in wp_setup_nav_menu_item()
	 *
	 * @see wp_setup_nav_menu_item()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $menu_item The menu item
	 * @return object The (possibly) modified menu item
	 */
	public function wp_setup_nav_menu_item( $menu_item ) {
		if ( is_admin() )
			return $menu_item;

		if ( 'page' == $menu_item->object && self::is_tml_page( 'login', $menu_item->object_id ) ) {
			if ( is_user_logged_in() ) {
				$menu_item->title = $this->get_instance()->get_title( 'logout' );
				$menu_item->url   = wp_logout_url();
			}
		}
		return $menu_item;
	}

	/**
	 * Excludes pages from wp_list_pages
	 *
	 * @since 6.3.7
	 *
	 * @param array $exclude Page IDs to exclude
	 * @return array Page IDs to exclude
	 */
	public function wp_list_pages_excludes( $exclude ) {
		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => '_tml_action',
			'posts_per_page' => -1
		) );
		$pages = wp_list_pluck( $pages, 'ID' );

		return array_merge( $exclude, $pages );
	}

	/**
	 * Adds nonce to logout link
	 *
	 * @since 6.3.7
	 *
	 * @param string $link Page link
	 * @param int $post_id Post ID
	 * @return string Page link
	 */
	public function page_link( $link, $post_id ) {
		if ( self::is_tml_page( 'logout', $post_id ) )
			$link = add_query_arg( '_wpnonce', wp_create_nonce( 'log-out' ), $link );
		return $link;
	}


	/************************************************************************************************************************
	 * Utilities
	 ************************************************************************************************************************/

	/**
	 * Handler for "theme-my-login" shortcode
	 *
	 * Optional $atts contents:
	 *
	 * - instance - A unqiue instance ID for this instance.
	 * - default_action - The action to display. Defaults to "login".
	 * - login_template - The template used for the login form. Defaults to "login-form.php".
	 * - register_template - The template used for the register form. Defaults to "register-form.php".
	 * - lostpassword_template - The template used for the lost password form. Defaults to "lostpassword-form.php".
	 * - resetpass_template - The template used for the reset password form. Defaults to "resetpass-form.php".
	 * - user_template - The templated used for when a user is logged in. Defalts to "user-panel.php".
	 * - show_title - True to display the current title, false to hide. Defaults to true.
	 * - show_log_link - True to display the login link, false to hide. Defaults to true.
	 * - show_reg_link - True to display the register link, false to hide. Defaults to true.
	 * - show_pass_link - True to display the lost password link, false to hide. Defaults to true.
	 * - logged_in_widget - True to display the widget when logged in, false to hide. Defaults to true.
	 * - logged_out_widget - True to display the widget when logged out, false to hide. Defaults to true.
	 * - show_gravatar - True to display the user's gravatar, false to hide. Defaults to true.
	 * - gravatar_size - The size of the user's gravatar. Defaults to "50".
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $atts Attributes passed from the shortcode
	 * @return string HTML output from Theme_My_Login_Template->display()
	 */
	public function shortcode( $atts = '' ) {
		static $did_main_instance = false;

		$atts = wp_parse_args( $atts );

		if ( self::is_tml_page() && in_the_loop() && is_main_query() && ! $did_main_instance ) {
			$instance = $this->get_instance();

			if ( ! empty( $this->request_instance ) )
				$instance->set_active( false );

			if ( ! empty( $this->request_action ) )
				$atts['default_action'] = $this->request_action;

			if ( ! isset( $atts['show_title'] ) )
				$atts['show_title'] = false;

			foreach ( $atts as $option => $value ) {
				$instance->set_option( $option, $value );
			}

			$did_main_instance = true;
		} else {
			$instance = $this->load_instance( $atts );
		}
		return $instance->display();
	}

	/**
	 * Determines if $action is for $page
	 *
	 * @since 6.3
	 *
	 * @param string $action The action to check
	 * @param int|object Post ID or object
	 * @return bool True if $action is for $page, false otherwise
	 */
	public static function is_tml_page( $action = '', $page = '' ) {
		if ( ! $page = get_post( $page ) )
			return false;

		if ( 'page' != $page->post_type )
			return false;

		if ( ! $page_action = self::get_page_action( $page->ID ) )
			return false;

		if ( empty( $action ) || $action == $page_action )
			return true;

		return false;
	}

	/**
	 * Returns link for a login page
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string $action The action
	 * @param string|array $query Optional. Query arguments to add to link
	 * @return string Login page link with optional $query arguments appended
	 */
	public static function get_page_link( $action, $query = '' ) {
		$page_id = self::get_page_id( $action );

		if ( $page_id ) {
			$link = get_permalink( $page_id );
		} elseif ( $page_id = self::get_page_id( 'login' ) ) {
			$link = add_query_arg( 'action', $action, get_permalink( $page_id ) );
		} else {
			// Remove site_url filter so we can use wp-login.php
			remove_filter( 'site_url', array( self::get_object(), 'site_url' ), 10, 3 );

			$link = site_url( "wp-login.php?action=$action" );
		}

		if ( ! empty( $query ) ) {
			$args = wp_parse_args( $query );

			if ( isset( $args['action'] ) && $action == $args['action'] )
				unset( $args['action'] );

			$link = add_query_arg( array_map( 'rawurlencode', $args ), $link );
		}

		// Respect FORCE_SSL_LOGIN
		if ( 'login' == $action && force_ssl_login() )
			$link = preg_replace( '|^http://|', 'https://', $link );

		return apply_filters( 'tml_page_link', $link, $action, $query );
	}

	/**
	 * Retrieves a page ID for an action
	 *
	 * @since 6.3
	 *
	 * @param string $action The action
	 * @return int|bool The page ID if exists, false otherwise
	 */
	public static function get_page_id( $action ) {
		global $wpdb;

		if ( 'rp' == $action )
			$action = 'resetpass';
		elseif ( 'retrievepassword' == $action )
			$action = 'lostpassword';

		if ( ! $page_id = wp_cache_get( $action, 'tml_page_ids' ) ) {
			$page_id = $wpdb->get_var( $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pmeta ON p.ID = pmeta.post_id WHERE p.post_type = 'page' AND pmeta.meta_key = '_tml_action' AND pmeta.meta_value = %s", $action ) );
			if ( ! $page_id )
				return null;
			wp_cache_add( $action, $page_id, 'tml_page_ids' );
		}
		return $page_id;
	}

	/**
	 * Get the action for a page
	 *
	 * @since 6.3
	 *
	 * @param int|object Post ID or object
	 * @return string|bool Action name if exists, false otherwise
	 */
	public static function get_page_action( $page ) {
		if ( ! $page = get_post( $page ) )
			return false;

		return get_post_meta( $page->ID, '_tml_action', true );
	}

	/**
	 * Enqueues the specified sylesheet
	 *
	 * First looks in theme/template directories for the stylesheet, falling back to plugin directory
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $file Filename of stylesheet to load
	 * @return string Path to stylesheet
	 */
	public static function get_stylesheet( $file = 'theme-my-login.css' ) {
		if ( file_exists( get_stylesheet_directory() . '/' . $file ) )
			$stylesheet = get_stylesheet_directory_uri() . '/' . $file;
		elseif ( file_exists( get_template_directory() . '/' . $file ) )
			$stylesheet = get_template_directory_uri() . '/' . $file;
		else
			$stylesheet = plugins_url( '/theme-my-login/' . $file );
		return $stylesheet;
	}

	/**
	 * Retrieves active instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return object Instance object
	 */
	public function get_active_instance() {
		return $this->get_instance( (int) $this->request_instance );
	}

	/**
	 * Retrieves a loaded instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param int $id Instance ID
	 * @return object Instance object

	 */
	public function get_instance( $id = 0 ) {
		if ( isset( $this->loaded_instances[$id] ) )
			return $this->loaded_instances[$id];
	}

	/**
	 * Sets an instance object
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param object $object Instance object
	 */
	public function set_instance( $object ) {
		$this->loaded_instances[] =& $object;
	}

	/**
	 * Instantiates an instance
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array|string $args Array or query string of arguments

	 * @return object Instance object
	 */
	public function load_instance( $args = '' ) {
		$args['instance'] = count( $this->loaded_instances );

		$instance = new Theme_My_Login_Template( $args );

		if ( $args['instance'] == $this->request_instance ) {
			$instance->set_active();
			$instance->set_option( 'default_action', $this->request_action );
		}

		$this->loaded_instances[] = $instance;

		return $instance;
	}

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the plugin folder
	 * will be removed on plugin updates. If you're creating custom
	 * translation files, please use the global language folder.
	 *
	 * @since 6.3
	 *
	 * @return bool True on success, false on failure
	 */
	private static function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale',  get_locale(), 'theme-my-login' );
		$mofile = sprintf( 'theme-my-login-%s.mo', $locale );

		// Look in global /wp-content/languages/theme-my-login folder
		if ( file_exists( WP_LANG_DIR . '/theme-my-login/' . $mofile ) ) {
			return load_textdomain( 'theme-my-login', WP_LANG_DIR . '/theme-my-login/' . $mofile );

		// Look in local /wp-content/plugins/theme-my-login/language folder
		} elseif ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/language/' . $mofile ) ) {
			return load_textdomain( 'theme-my-login', WP_PLUGIN_DIR . '/theme-my-login/language/' . $mofile );
		}

		// Nothing found
		return false;
	}

	/**
	 * Handles sending password retrieval email to user.
	 *
	 * @since 6.0
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @return bool|WP_Error True: when finish. WP_Error on error
	 */
	public static function retrieve_password() {
		global $wpdb, $current_site;

		$errors = new WP_Error();

		if ( empty( $_POST['user_login'] ) ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Enter a username or e-mail address.' ) );
		} else if ( strpos( $_POST['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['user_login'] ) );
			if ( empty( $user_data ) )
				$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: There is no user registered with that email address.' ) );
		} else {
			$login = trim( $_POST['user_login'] );
			$user_data = get_user_by( 'login', $login );
		}

		do_action( 'lostpassword_post' );

		if ( $errors->get_error_code() )
			return $errors;

		if ( ! $user_data ) {
			$errors->add( 'invalidcombo', __( '<strong>ERROR</strong>: Invalid username or e-mail.' ) );
			return $errors;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		do_action( 'retreive_password', $user_login );  // Misspelled and deprecated
		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );

		if ( ! $allow )
			return new WP_Error( 'no_password_reset', __( 'Password reset is not allowed for this user' ) );
		else if ( is_wp_error( $allow ) )
			return $allow;

		$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
		if ( empty( $key ) ) {
			// Generate something random for a key...
			$key = wp_generate_password( 20, false );
			do_action( 'retrieve_password_key', $user_login, $key );
			// Now insert the new md5 key into the db
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
		}
		$message = __( 'Someone requested that the password be reset for the following account:' ) . "\r\n\r\n";
		$message .= network_home_url( '/' ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s' ), $user_login ) . "\r\n\r\n";
		$message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
		$message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
		$message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";

		if ( is_multisite() ) {
			$blogname = $current_site->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] Password Reset' ), $blogname );

		$title = apply_filters( 'retrieve_password_title', $title, $user_data->ID );
		$message = apply_filters( 'retrieve_password_message', $message, $key, $user_data->ID );

		if ( $message && ! wp_mail( $user_email, $title, $message ) )
			wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) );

		return true;
	}

	/**
	 * Retrieves a user row based on password reset key and login
	 *
	 * @since 6.1.1
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 * @param string $login The user login
	 *
	 * @return object|WP_Error
	 */
	public static function check_password_reset_key( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( empty( $login ) || ! is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );

		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		return $user;
	}

	/**
	 * Handles resetting the user's password.
	 *
	 * @since 6.0
	 * @access public
	 * @uses $wpdb WordPress Database object
	 *
	 * @param string $key Hash to validate sending user's password
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );

		do_action_ref_array( 'tml_user_password_changed', array( &$user ) );
	}

	/**
	 * Handles registering a new user.
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $user_login User's username for logging in
	 * @param string $user_email User's email address to send password and add
	 * @return int|WP_Error Either user's ID or error on failure.
	 */
	public static function register_new_user( $user_login, $user_email ) {
		$errors = new WP_Error();

		$sanitized_user_login = sanitize_user( $user_login );
		$user_email = apply_filters( 'user_registration_email', $user_email );

		// Check the username
		if ( $sanitized_user_login == '' ) {
			$errors->add( 'empty_username', __( '<strong>ERROR</strong>: Please enter a username.' ) );
		} elseif ( ! validate_username( $user_login ) ) {
			$errors->add( 'invalid_username', __( '<strong>ERROR</strong>: This username is invalid because it uses illegal characters. Please enter a valid username.' ) );
			$sanitized_user_login = '';
		} elseif ( username_exists( $sanitized_user_login ) ) {
			$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered, please choose another one.' ) );
		}

		// Check the e-mail address
		if ( '' == $user_email ) {
			$errors->add( 'empty_email', __( '<strong>ERROR</strong>: Please type your e-mail address.' ) );
		} elseif ( ! is_email( $user_email ) ) {
			$errors->add( 'invalid_email', __( '<strong>ERROR</strong>: The email address isn&#8217;t correct.' ) );
			$user_email = '';
		} elseif ( email_exists( $user_email ) ) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ) );
		}

		do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

		$errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

		if ( $errors->get_error_code() )
			return $errors;

		$user_pass = apply_filters( 'tml_user_registration_pass', wp_generate_password( 12, false ) );
		$user_id = wp_create_user( $sanitized_user_login, $user_pass, $user_email );
		if ( ! $user_id ) {
			$errors->add( 'registerfail', sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you... please contact the <a href="mailto:%s">webmaster</a> !' ), get_option( 'admin_email' ) ) );
			return $errors;
		}

		update_user_option( $user_id, 'default_password_nag', true, true ); //Set up the Password change nag.

		do_action( 'tml_new_user_registered', $user_id, $user_pass );

		return $user_id;
	}
}
endif; // Class exists

