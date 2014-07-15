<?php
/**
 * Plugin Name: Security
 * Description: Enabling this module will initialize security. You will then have to configure the settings via the "Security" tab.
 *
 * Holds Theme My Login Security class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Security
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Security' ) ) :
/**
 * Theme My Login Security module class
 *
 * Adds options to help protect your site.
 *
 * @since 6.0
 */
class Theme_My_Login_Security extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_security';

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
	 * @since 6.0
	 * @access public
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array(
			'private_site'  => 0,
			'private_login' => 0,
			'failed_login'  => array(
				'threshold'               => 5,
				'threshold_duration'      => 1,
				'threshold_duration_unit' => 'hour',
				'lockout_duration'        => 24,
				'lockout_duration_unit'   => 'hour'
			)
		);
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'init',               array( &$this, 'init'              ) );
		add_action( 'template_redirect',  array( &$this, 'template_redirect' ) );
		add_action( 'tml_request_unlock', array( &$this, 'request_unlock'    ) );
		add_action( 'tml_request',        array( &$this, 'action_messages'   ) );

		add_action( 'authenticate',         array( &$this, 'authenticate'         ), 100, 3 );
		add_filter( 'allow_password_reset', array( &$this, 'allow_password_reset' ),  10, 2 );

		add_action( 'show_user_profile', array( &$this, 'show_user_profile' ) );
		add_action( 'edit_user_profile', array( &$this, 'show_user_profile' ) );

		add_filter( 'show_admin_bar', array( &$this, 'show_admin_bar' ) );
	}

	/**
	 * Sets a 404 error for wp-login.php if it's disabled
	 *
	 * @since 6.3
	 * @access public
	 */
	public function init() {
		global $wp_query, $pagenow;

		if ( 'wp-login.php' == $pagenow && $this->get_option( 'private_login' ) ) {
			$pagenow = 'index.php';
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
			if ( ! $template = get_404_template() )
				$template = 'index.php';
			include( $template );
			exit;
		}
	}

	/**
	 * Blocks entire site if user is not logged in and private site is enabled
	 *
	 * Callback for "template_redirect" hook in the file wp-settings.php
	 *
	 * @since 6.2
	 * @access public
	 */
	public function template_redirect() {
		if ( $this->get_option( 'private_site' ) ) {
			if ( ! ( is_user_logged_in() || Theme_My_Login::is_tml_page() ) ) {
				$redirect_to = apply_filters( 'tml_security_private_site_redirect', wp_login_url( $_SERVER['REQUEST_URI'], true ) );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}
	}

	/**
	 * Handles "unlock" action for login page
	 *
	 * Callback for "tml_request_activate" hook in method Theme_My_Login::the_request();
	 *
	 * @see Theme_My_Login::the_request();
	 * @since 6.3
	 */
	public function request_unlock() {
		$user = self::check_user_unlock_key( $_GET['key'], $_GET['login'] );

		$redirect_to = Theme_My_Login_Common::get_current_url();

		if ( is_wp_error( $user ) ) {
			$redirect_to = add_query_arg( 'unlock', 'invalidkey', $redirect_to );
			wp_redirect( $redirect_to );
			exit;
		}

		self::unlock_user( $user->ID );

		$redirect_to = add_query_arg( 'unlock', 'complete', $redirect_to );
		wp_redirect( $redirect_to );
		exit;
	}

	/**
	 * Handles display of various action/status messages
	 *
	 * Callback for "tml_request" hook in Theme_My_Login::the_request()
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	public function action_messages( &$theme_my_login ) {
		if ( isset( $_GET['unlock'] ) && 'complete' == $_GET['unlock'] )
			$theme_my_login->errors->add( 'unlock_complete', __( 'Your account has been unlocked. You may now log in.', 'theme-my-login' ), 'message' );
	}

	/**
	 * Validates a user unlock key
	 *
	 * @since 6.3
	 *
	 * @param string $key Unlock key
	 * @param string $login User login
	 * @return WP_User|WP_Error WP_User object on success, WP_Error object on failure
	 */
	public static function check_user_unlock_key( $key, $login ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || ! is_string( $key ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( empty( $login ) || ! is_string( $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( ! $user = get_user_by( 'login', $login ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		if ( $key != self::get_user_unlock_key( $user->ID ) )
			return new WP_Error( 'invalid_key', __( 'Invalid key' ) );

		return $user;
	}

	/**
	 * Blocks locked users from logging in
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
		// Make sure user exists
		if ( ! $userdata = get_user_by( 'login', $username ) )
			return $user;

		// Current time
		$time = time();

		if ( self::is_user_locked( $userdata->ID ) ) {
			if ( $expiration = self::get_user_lock_expiration( $userdata->ID ) ) {
				if ( $time > $expiration )
					self::unlock_user( $userdata->ID );
				else
					return new WP_Error( 'locked_account', sprintf( __( '<strong>ERROR</strong>: This account has been locked because of too many failed login attempts. You may try again in %s.', 'theme-my-login' ), human_time_diff( $time, $expiration ) ) );
			} else {
				return new WP_Error( 'locked_account', __( '<strong>ERROR</strong>: This account has been locked.', 'theme-my-login' ) );
			}
		} elseif ( is_wp_error( $user ) && 'incorrect_password' == $user->get_error_code() ) {
			// Get the attempts
			$attempts = self::get_failed_login_attempts( $userdata->ID );

			// Get the first valid attempt
			$first_attempt = reset( $attempts );

			// Get the relative duration
			$duration = $first_attempt['time'] + self::get_seconds_from_unit( $this->get_option( array( 'failed_login', 'threshold_duration' ) ), $this->get_option( array( 'failed_login', 'threshold_duration_unit' ) ) );

			// If current time is less than relative duration time, we're still within the defensive zone
			if ( $time < $duration ) {
				// Log this attempt
				self::add_failed_login_attempt( $userdata->ID, $time );
				// If failed attempts reach treshold, lock the account
				if ( self::get_failed_login_attempt_count( $userdata->ID ) >= $this->get_option( array( 'failed_login', 'threshold' ) ) ) {
					// Create new expiration
					$expiration = $time + self::get_seconds_from_unit( $this->get_option( array( 'failed_login', 'lockout_duration' ) ), $this->get_option( array( 'failed_login', 'lockout_duration_unit' ) ) );
					self::lock_user( $userdata->ID, $expiration );
					return new WP_Error( 'locked_account', sprintf( __( '<strong>ERROR</strong>: This account has been locked because of too many failed login attempts. You may try again in %s.', 'theme-my-login' ), human_time_diff( $time, $expiration ) ) );
				}
			} else {
				// Clear the attempts
				self::reset_failed_login_attempts( $userdata->ID );
				// Log this attempt
				self::add_failed_login_attempt( $userdata->ID, $time );
			}
		}
		return $user;
	}

	/**
	 * Blocks locked users from resetting their password, if locked by admin
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
		if ( self::is_user_locked( $user_id ) && ! self::get_user_lock_expiration( $user_id ) )
			$allow = false;
		return $allow;
	}

	/**
	 * Displays failed login attempts on users profile for administrators
	 *
	 * @since 6.2
	 * @access public
	 *
	 * @param object $profileuser User object
	 */
	public function show_user_profile( $profileuser ) {
		if ( ! current_user_can( 'manage_users' ) )
			return;

		if ( $failed_login_attempts = self::get_failed_login_attempts( $profileuser->ID ) ) : ?>
			<h3><?php _e( 'Failed Login Attempts', 'theme-my-login' ); ?></h3>

			<table class="form-table">
			<tr>
				<th scope="col"><?php _e( 'IP Address', 'theme-my-login' ); ?></th>
				<th scope="col"><?php _e( 'Date' ); ?></th>
			</tr>
			<?php foreach ( $failed_login_attempts as $attempt ) :
				$t_time = date_i18n( __( 'Y/m/d g:i:s A' ), $attempt['time'] );

				$time_diff = time() - $attempt['time'];

				if ( $time_diff > 0 && $time_diff < 24*60*60 )
					$h_time = sprintf( __( '%s ago' ), human_time_diff( $attempt['time'] ) );
				else
					$h_time = date_i18n( __( 'Y/m/d' ), $attempt['time'] );
			?>
			<tr>
				<td><?php echo $attempt['ip']; ?></td>
				<td><abbr title="<?php echo $t_time; ?>"><?php echo $h_time; ?></abbr></td>
			</tr>
			<?php endforeach; ?>
			</table>
		<?php endif;
	}

	/**
	 * Shows admin bar for wp-login.php when it is disabled
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param bool $show True to show admin bar, false to hide
	 * @return bool True to show admin bar, false to hide
	 */
	public function show_admin_bar( $show ) {
		global $pagenow;

		if ( is_user_logged_in() && 'wp-login.php' == $pagenow && $this->get_option( 'private_login' ) )
			return true;
		return $show;
	}

	/**
	 * Locks a user
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int|WP_User $user User ID ir WP_User object
	 * @param int $expires When the lock expires, in seconds from current time
	 */
	public static function lock_user( $user, $expires = 0 ) {
		if ( is_object( $user ) )
			$user = $user->ID;

		$user = (int) $user;

		do_action( 'tml_lock_user', $user );

		$security = self::get_security_meta( $user );

		$security['is_locked']       = true;
		$security['lock_expiration'] = absint( $expires );
		$security['unlock_key']      = wp_generate_password( 20, false );

		update_user_meta( $user, 'theme_my_login_security', $security );

		if ( $expires )
			self::user_lock_notification( $user );
	}

	/**
	 * Unlocks a user
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int|WP_User $user User ID or WP_User object
	 */
	public static function unlock_user( $user ) {
		if ( is_object( $user ) )
			$user = $user->ID;

		$user = (int) $user;

		do_action( 'tml_unlock_user', $user );

		$security = self::get_security_meta( $user );

		$security['is_locked']             = false;
		$security['lock_expiration']       = 0;
		$security['unlock_key']            = '';
		$security['failed_login_attempts'] = array();

		return update_user_meta( $user, 'theme_my_login_security', $security );
	}

	/**
	 * Determine if a user is locked or not
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int|WP_User $user User ID or WP_User object
	 * @return bool True if user is locked, false if not
	 */
	public static function is_user_locked( $user ) {
		if ( is_object( $user ) )
			$user = $user->ID;

		$user = (int) $user;

		$security = self::get_security_meta( $user );

		// If "is_locked" is not set, there is no lock
		if ( ! $security['is_locked'] )
			return false;

		// If "lock_expires" is not set, there is a lock but no expiry
		if ( ! $expires = self::get_user_lock_expiration( $user ) )
			return true;

		// We have a lock with an expiry
		$time = time();
		if ( $time > $expires ) {
			self::unlock_user( $user );
			return false;
		}

		return true;
	}

	/**
	 * Get a user's security meta
	 *
	 * @since 6.0
	 * @access protected
	 *
	 * @param int $user_id User ID
	 * @return array User's security meta
	 */
	protected static function get_security_meta( $user_id ) {
		$defaults = array(
			'is_locked'             => false,
			'lock_expiration'       => 0,
			'unlock_key'            => '',
			'failed_login_attempts' => array()
		);
		$meta = get_user_meta( $user_id, 'theme_my_login_security', true );
		if ( ! is_array( $meta ) )
			$meta = array();

		return array_merge( $defaults, $meta );
	}

	/**
	 * Get a user's failed login attempts
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User ID
	 * @return array User's failed login attempts
	 */
	public static function get_failed_login_attempts( $user_id ) {
		$security_meta = self::get_security_meta( $user_id );
		return $security_meta['failed_login_attempts'];
	}

	/**
	 * Reset a user's failed login attempts
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User ID
	 */
	public static function reset_failed_login_attempts( $user_id ) {
		$security_meta = self::get_security_meta( $user_id );
		$security_meta['failed_login_attempts'] = array();
		return update_user_meta( $user_id, 'theme_my_login_security', $security_meta );
	}

	/**
	 * Get a user's failed login attempt count
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User ID
	 * @return int Number of user's failed login attempts
	 */
	public static function get_failed_login_attempt_count( $user_id ) {
		return count( self::get_failed_login_attempts( $user_id ) );
	}

	/**
	 * Add a failed login attempt to a user
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User ID
	 * @param int $time Time of attempt, in seconds
	 * @param string $ip IP address of attempt
	 */
	public static function add_failed_login_attempt( $user_id, $time = '', $ip = '' ) {
		$security_meta = self::get_security_meta( $user_id );
		if ( ! is_array( $security_meta['failed_login_attempts'] ) )
			$security_meta['failed_login_attempts'] = array();

		$time = absint( $time );

		if ( empty( $time ) )
			$time = time();

		if ( empty( $ip ) )
			$ip = $_SERVER['REMOTE_ADDR'];

		$security_meta['failed_login_attempts'][] = array( 'time' => $time, 'ip' => $ip );

		return update_user_meta( $user_id, 'theme_my_login_security', $security_meta );
	}

	/**
	 * Get user's lock expiration time
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User ID
	 * @return int User's lock expiration time
	 */
	public static function get_user_lock_expiration( $user_id ) {
		$security_meta = self::get_security_meta( $user_id );
		return apply_filters( 'tml_user_lock_expiration', absint( $security_meta['lock_expiration'] ), $user_id );
	}

	/**
	 * Get a user's unlock key
	 *
	 * @since 6.3
	 *
	 * @param int $user_id User ID
	 * @return string User's unlock key
	 */
	public static function get_user_unlock_key( $user_id ) {
		$security_meta = self::get_security_meta( $user_id );
		return apply_filters( 'tml_user_unlock_key', $security_meta['unlock_key'], $user_id );
	}

	/**
	 * Get number of secongs from days, hours and minutes
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $value Number of $unit
	 * @param string $unit Can be either "day", "hour" or "minute"
	 * @return int Number of seconds
	 */
	public static function get_seconds_from_unit( $value, $unit = 'minute' ) {
		switch ( $unit ) {
			case 'day' :
				$value = $value * 24 * 60 * 60;
				break;
			case 'hour' :
				$value = $value * 60 * 60;
				break;
			case 'minute' :
				$value = $value * 60;
				break;
		}
		return $value;
	}

	/**
	 * Sends a user a notification that their account has been locked
	 *
	 * @since 6.3
	 *
	 * @param int $user_id User ID
	 */
	public static function user_lock_notification( $user_id ) {
		global $wpdb, $current_site;

		if ( apply_filters( 'send_user_lock_notification', true ) ) {
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

			$unlock_url = add_query_arg( array( 'action' => 'unlock', 'key' => self::get_user_unlock_key( $user->ID ), 'login' => rawurlencode( $user_login ) ), wp_login_url() );

			$title    = sprintf( __( '[%s] Account Locked', 'theme-my-login' ), $blogname );
			$message  = sprintf( __( 'For your security, your account has been locked because of too many failed login attempts. To unlock your account please click the following link: ', 'theme-my-login' ), $blogname ) . "\r\n\r\n";
			$message .=  $unlock_url . "\r\n";

			if ( $user->has_cap( 'administrator' ) ) {
				$message .= "\r\n";
				$message .= __( 'The following attempts resulted in the lock:', 'theme-my-login' ) . "\r\n\r\n";
				foreach ( self::get_failed_login_attempts( $user->ID ) as $attempt ) {
					$time = date_i18n( __( 'Y/m/d g:i:s A' ), $attempt['time'] );
					$message .= $attempt['ip'] . "\t" . $time . "\r\n";
				}
			}

			$title   = apply_filters( 'user_lock_notification_title',   $title,   $user_id );
			$message = apply_filters( 'user_lock_notification_message', $message, $unlock_url, $user_id );

			wp_mail( $user_email, $title, $message );
		}
	}
}

Theme_My_Login_Security::get_object();
	
endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/security-admin.php' );

