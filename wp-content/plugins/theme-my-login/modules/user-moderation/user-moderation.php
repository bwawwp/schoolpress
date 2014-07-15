<?php
/**
 * Plugin Name: User Moderation
 * Description: Enabling this module will initialize user moderation. You will then have to configure the settings via the "Moderation" tab.
 *
 * Holds Theme My Login User Moderation class
 *
 * @packagae Theme_My_Login
 * @subpackage Theme_My_Login_User_Moderation
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_User_Moderation' ) ) :
/**
 * Theme My Login User Moderation class
 *
 * Adds the ability to require users to confirm their e-mail address or be activated by an administrator before becoming active on the site.
 *
 * @since 6.0
 */
class Theme_My_Login_User_Moderation extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_moderation';

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
		return array(
			'type' => 'none'
		);
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		if ( is_multisite() )
			return;

		if ( in_array( $this->get_option( 'type' ), array( 'admin', 'email' ) ) ) {

			add_action( 'register_post',         array( &$this, 'register_post'         )      );
			add_filter( 'registration_redirect', array( &$this, 'registration_redirect' ), 100 );

			add_action( 'authenticate',         array( &$this, 'authenticate'         ), 100, 3 );
			add_filter( 'allow_password_reset', array( &$this, 'allow_password_reset' ),  10, 2 );

			add_action( 'tml_request',            array( &$this, 'action_messages'    )        );
			add_action( 'tml_new_user_activated', array( &$this, 'new_user_activated' ), 10, 2 );

			if ( 'email' == $this->get_option( 'type' ) ) {
				add_action( 'tml_request_activate',       array( &$this, 'user_activation' ) );
				add_action( 'tml_request_sendactivation', array( &$this, 'send_activation' ) );
			}
		}
	}

	/**
	 * Applies user moderation upon registration
	 *
	 * @since 6.0
	 * @access public
	 */
	public function register_post() {
		// Remove default new user notification
		if ( has_action( 'tml_new_user_registered', 'wp_new_user_notification' ) )
			remove_action( 'tml_new_user_registered', 'wp_new_user_notification', 10, 2 );

		// Remove Custom Email new user notification
		if ( class_exists( 'Theme_My_Login_Custom_Email' ) ) {
			$custom_email = Theme_My_Login_Custom_Email::get_object();
			if ( has_action( 'tml_new_user_registered', array( &$custom_email, 'new_user_notification' ) ) )
				remove_action( 'tml_new_user_registered', array( &$custom_email, 'new_user_notification' ), 10, 2 );
		}

		// Moderate user upon registration
		add_action( 'tml_new_user_registered', array( &$this, 'moderate_user' ), 100, 2 );
	}

	/**
	 * Changes the registration redirection based upon moderaton type
	 *
	 * Callback for "registration_redirect" hook in method Theme_My_Login_Template::get_redirect_url()
	 *
	 * @see Theme_My_Login_Template::get_redirect_url()
	 * @since 6.0
	 * @access public
	 *
	 * @param string $redirect_to Default redirect
	 * @return string URL to redirect to
	 */
	public function registration_redirect( $redirect_to ) {

		$redirect_to = Theme_My_Login::get_page_link( 'login' );

		switch ( $this->get_option( 'type' ) ) {
			case 'email' :
				$redirect_to = add_query_arg( 'pending', 'activation', $redirect_to );
				break;
			case 'admin' :
				$redirect_to = add_query_arg( 'pending', 'approval', $redirect_to );
				break;
		}

		return $redirect_to;
	}

	/**
	 * Blocks "pending" users from loggin in
	 *
	 * Callback for "authenticate" hook in function wp_authenticate()
	 *
	 * @see wp_authenticate()
	 * @since 6.0
	 * @access public
	 *
	 * @param WP_User $user WP_User object
	 * @param string $username Username posted
	 * @param string $password Password posted
	 * @return WP_User|WP_Error WP_User if the user can login, WP_Error otherwise
	 */
	public function authenticate( $user, $username, $password ) {
		global $wpdb;

		$cap_key = $wpdb->prefix . 'capabilities';

		if ( $userdata = get_user_by( 'login', $username ) ) {
			if ( array_key_exists( 'pending', (array) $userdata->$cap_key ) ) {
				if ( 'email' == $this->get_option( 'type' ) ) {
					return new WP_Error( 'pending', sprintf(
						__( '<strong>ERROR</strong>: You have not yet confirmed your e-mail address. <a href="%s">Resend activation</a>?', 'theme-my-login' ),
						Theme_My_Login::get_page_link( 'login', array( 'action' => 'sendactivation', 'login' => $username ) )
					) );
				} else {
					return new WP_Error( 'pending', __( '<strong>ERROR</strong>: Your registration has not yet been approved.', 'theme-my-login' ) );
				}
			}
		}
		return $user;
	}

	/**
	 * Blocks "pending" users from resetting their password
	 *
	 * Callback for "allow_password_reset" in method Theme_My_Login::retrieve_password()
	 *
	 * @see Theme_My_Login::retrieve_password()
	 * @since 6.0
	 * @access public
	 *
	 * @param bool $allow Default setting
	 * @param int $user_id User ID
	 * @return bool Whether to allow password reset or not
	 */
	public function allow_password_reset( $allow, $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( in_array( 'pending', (array) $user->roles ) )
			return false;
		return $allow;
	}

	/**
	 * Handles display of various action/status messages
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	public function action_messages( &$theme_my_login ) {
		if ( isset( $_GET['pending'] ) ) {
			switch ( $_GET['pending'] ) {
				case 'activation' :
					$theme_my_login->errors->add( 'pending_activation', __( 'Your registration was successful but you must now confirm your email address before you can log in. Please check your email and click on the link provided.', 'theme-my-login' ), 'message' );
					break;
				case 'approval' :
					$theme_my_login->errors->add( 'pending_approval', __( 'Your registration was successful but you must now be approved by an administrator before you can log in. You will be notified by e-mail once your account has been reviewed.', 'theme-my-login' ), 'message' );
					break;
			}
		}

		if ( isset( $_GET['activation'] ) ) {
			switch ( $_GET['activation'] ) {
				case 'complete' :
					if ( class_exists( 'Theme_My_Login_Custom_Passwords' ) )
						$theme_my_login->errors->add( 'activation_complete', __( 'Your account has been activated. You may now log in.', 'theme-my-login' ), 'message' );
					else
						$theme_my_login->errors->add( 'activation_complete', __( 'Your account has been activated. Please check your e-mail for your password.', 'theme-my-login' ), 'message' );
					break;
				case 'invalidkey' :
					$theme_my_login->errors->add( 'invalid_key', __( '<strong>ERROR</strong>: Sorry, that key does not appear to be valid.', 'theme-my-login' ) );
					break;
			}
		}

		if ( isset( $_GET['sendactivation'] ) ) {
			switch ( $_GET['sendactivation'] ) {
				case 'failed' :
					$theme_my_login->errors->add( 'sendactivation_failed', __('<strong>ERROR</strong>: Sorry, the activation e-mail could not be sent.', 'theme-my-login' ) );
					break;
				case 'sent' :
					$theme_my_login->errors->add( 'sendactivation_sent', __( 'The activation e-mail has been sent to the e-mail address with which you registered. Please check your email and click on the link provided.', 'theme-my-login' ), 'message' );
					break;
			}
		}
	}

	/**
	 * Applies moderation to a newly registered user
	 *
	 * Callback for "register_post" hook in method Theme_My_Login::register_new_user()
	 *
	 * @see Theme_My_Login::register_new_user()
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id The user's ID
	 * @param string $user_pass The user's password
	 */
	public function moderate_user( $user_id, $user_pass ) {
		global $wpdb;

		// Set user role to "pending"
		$user = new WP_User( $user_id );

		// Make sure user isn't already "Pending"
		if ( in_array( 'pending', (array) $user->roles ) )
			return;

		// Set user to "Pending" role
		$user->set_role( 'pending' );

		// Temporarily save plaintext pass
		if ( isset( $_POST['user_pass'] ) )
			update_user_meta( $user_id, 'user_pass', $_POST['user_pass'] );

		// Send appropriate e-mail depending on moderation type
		if ( 'email' == $this->get_option( 'type' ) ) {
			// Generate an activation key
			$key = wp_generate_password( 20, false );

			// Set the activation key for the user
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user->user_login ) );

			// Send activation e-mail
			self::new_user_activation_notification( $user_id, $key );
		} elseif ( 'admin' == $this->get_option( 'type' ) ) {
			// Send approval e-mail
			self::new_user_approval_admin_notification( $user_id );
		}
	}

	/**
	 * Handles "activate" action for login page
	 *
	 * Callback for "tml_request_activate" hook in method Theme_My_Login::the_request();
	 *
	 * @see Theme_My_Login::the_request();
	 * @since 6.0
	 * @access public
	 */
	public function user_activation() {
		// Attempt to activate the user
		$errors = self::activate_new_user( $_GET['key'], $_GET['login'] );

		$redirect_to = Theme_My_Login_Common::get_current_url();

		// Make sure there are no errors
		if ( ! is_wp_error( $errors ) )
			$redirect_to = add_query_arg( 'activation', 'complete',   $redirect_to );
		else
			$redirect_to = add_query_arg( 'activation', 'invalidkey', $redirect_to );

		wp_redirect( $redirect_to );
		exit;
	}

	/**
	 * Handles "send_activation" action for login page
	 *
	 * Callback for "tml_request_send_activation" hook in method Theme_My_Login::the_request();
	 *
	 * @see Theme_My_Login::the_request();
	 * @since 6.0
	 * @access public
	 */
	public static function send_activation() {
		global $wpdb;

		$login = isset( $_GET['login'] ) ? trim( $_GET['login'] ) : '';

		if ( ! $user_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_login = %s", $login ) ) ) {
			$redirect_to = Theme_My_Login_Common::get_current_url( array( 'sendactivation' => 'failed' ) );
			wp_redirect( $redirect_to );
			exit;
		}

		do_action( 'tml_user_activation_resend', $user_id );

		$user = new WP_User( $user_id );

		if ( in_array( 'pending', (array) $user->roles ) ) {
			// Send activation e-mail
			self::new_user_activation_notification( $user->ID );
			// Now redirect them
			$redirect_to = Theme_My_Login_Common::get_current_url( array( 'sendactivation' => 'sent' ) );
			wp_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Handles activating a new user by user email confirmation
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $key Hash to validate sending confirmation email
	 * @param string $login User's username for logging in
	 * @return bool|WP_Error True if successful, WP_Error otherwise
	 */
	public static function activate_new_user( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( empty( $login ) || ! is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		// Validate activation key
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login ) );
		if ( empty( $user ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		do_action( 'tml_user_activation_post', $user->user_login, $user->user_email );

		// Allow plugins to short-circuit process and send errors
		$errors = new WP_Error();
		$errors = apply_filters( 'tml_user_activation_errors', $errors, $user->user_login, $user->user_email );

		// Return errors if there are any
		if ( $errors->get_error_code() )
			return $errors;

		// Clear the activation key
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'user_login' => $login ) );

		// Set user role
		$user_object = new WP_User( $user->ID );
		$user_object->set_role( get_option( 'default_role' ) );

		// Check for plaintext pass
		if ( ! $user_pass = get_user_meta( $user->ID, 'user_pass', true ) ) {
			$user_pass = wp_generate_password();
			wp_set_password( $user_pass, $user->ID );
		}

		// Delete plaintext pass
		delete_user_meta( $user->ID, 'user_pass' );

		do_action( 'tml_new_user_activated', $user->ID, $user_pass );

		return true;
	}

	/**
	 * Calls the "tml_new_user_registered" hook
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id The user's ID
	 * @param string $user_pass The user's password
	 */
	public function new_user_activated( $user_id, $user_pass ) {
		do_action( 'tml_new_user_registered', $user_id, $user_pass );
	}

	/**
	 * Notifies a pending user to activate their account
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id The user's ID
	 * @param string $key The unique activation key
	 */
	public static function new_user_activation_notification( $user_id, $key = '' ) {
		global $wpdb, $current_site;

		$user = new WP_User( $user_id );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		if ( empty( $key ) ) {
			$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );
			if ( empty( $key ) ) {
				$key = wp_generate_password( 20, false );
				$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
			}
		}

		if ( is_multisite() ) {
			$blogname = $current_site->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}			

		$activation_url = add_query_arg( array( 'action' => 'activate', 'key' => $key, 'login' => rawurlencode( $user_login ) ), wp_login_url() );

		$title    = sprintf( __( '[%s] Activate Your Account', 'theme-my-login' ), $blogname );
		$message  = sprintf( __( 'Thanks for registering at %s! To complete the activation of your account please click the following link: ', 'theme-my-login' ), $blogname ) . "\r\n\r\n";
		$message .=  $activation_url . "\r\n";

		$title   = apply_filters( 'user_activation_notification_title',   $title,   $user_id );
		$message = apply_filters( 'user_activation_notification_message', $message, $activation_url, $user_id );

		return wp_mail( $user_email, $title, $message );
	}

	/**
	 * Notifies the administrator of a pending user needing approval
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id The user's ID
	 */
	public static function new_user_approval_admin_notification( $user_id ) {
		global $current_site;

		$user = new WP_User( $user_id );

		$user_login = stripslashes( $user->user_login );
		$user_email = stripslashes( $user->user_email );

		if ( is_multisite() ) {
			$blogname = $current_site->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$title = sprintf( __( '[%s] New User Awaiting Approval', 'theme-my-login' ), $blogname );

		$message  = sprintf( __( 'New user requires approval on your blog %s:', 'theme-my-login' ), $blogname ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s', 'theme-my-login' ), $user_login ) . "\r\n";
		$message .= sprintf( __( 'E-mail: %s', 'theme-my-login' ), $user_email ) . "\r\n\r\n";
		$message .= __( 'To approve or deny this user:', 'theme-my-login' ) . "\r\n";
		$message .= admin_url( 'users.php?role=pending' );

		$title   = apply_filters( 'user_approval_admin_notification_title',   $title,   $user_id );
		$message = apply_filters( 'user_approval_admin_notification_message', $message, $user_id );

		$to = apply_filters( 'user_approval_admin_notification_mail_to', get_option( 'admin_email' ) );

		@wp_mail( $to, $title, $message );
	}
}

Theme_My_Login_User_Moderation::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/user-moderation-admin.php' );

