<?php
/**
 * Holds Theme My Login Security Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Security
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Security_Admin' ) ) :
/**
 * Theme My Login Security Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Security_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @ since 6.3
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
	 * @since 6.3
	 * @access public
	 * @var array
	 */
	public static function default_options() {
		return Theme_My_Login_Security::default_options();
	}

	/**
	 * Loads the module
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'tml_uninstall_security/security.php', array( &$this, 'uninstall' ) );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		add_action( 'load-users.php',   array( &$this, 'load_users_page'  )        );
		add_filter( 'user_row_actions', array( &$this, 'user_row_actions' ), 10, 2 );
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_security/security.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

	/**

	 * Adds "Security" tab to Theme My Login menu
	 *
	 * Callback for "admin_menu" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Security Settings', 'theme-my-login' ),
			__( 'Security', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		add_settings_section( 'general', null, '__return_false', $this->options_key );

		add_settings_field( 'private_site',   __( 'Private Site',   'theme-my-login' ), array( &$this, 'settings_field_private_site'   ), $this->options_key, 'general' );
		add_settings_field( 'private_login',  __( 'Private Login',  'theme-my-login' ), array( &$this, 'settings_field_private_login'  ), $this->options_key, 'general' );
		add_settings_field( 'login_attempts', __( 'Login Attempts', 'theme-my-login' ), array( &$this, 'settings_field_login_attempts' ), $this->options_key, 'general' );
	}

	/**
	 * Registers options group
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_init() {
		register_setting( $this->options_key, $this->options_key, array( &$this, 'save_settings' ) );
	}

	/**
	 * Renders settings page
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_page() {
		Theme_My_Login_Admin::settings_page( array(
			'title'       => __( 'Theme My Login Security Settings', 'theme-my-login' ),
			'options_key' => $this->options_key
		) );
	}

	/**
	 * Renders Private Site settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_private_site() {
		?>
		<input name="<?php echo $this->options_key; ?>[private_site]" type="checkbox" id="<?php echo $this->options_key; ?>_private_site" value="1"<?php checked( $this->get_option( 'private_site' ) ); ?> />
		<label for="<?php echo $this->options_key; ?>_private_site"><?php _e( 'Require users to be logged in to view site', 'theme-my-login' ); ?></label>
		<?php
	}

	/**
	 * Renders Private Login settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_private_login() {
		?>
		<input name="<?php echo $this->options_key; ?>[private_login]" type="checkbox" id="<?php echo $this->options_key; ?>_private_login" value="1"<?php checked( $this->get_option( 'private_login' ) ); ?> />
		<label for="<?php echo $this->options_key; ?>_private_login"><?php _e( 'Disable <tt>wp-login.php</tt>', 'theme-my-login' ); ?></label>
		<?php
	}

	/**
	 * Renders Login Attempts settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_login_attempts() {
		// Units
		$units = array(
			'minute' => __( 'minute(s)', 'theme-my-login' ),
			'hour'   => __( 'hour(s)',   'theme-my-login' ),
			'day'    => __( 'day(s)',    'theme-my-login' )
		);

		// Threshold
		$threshold = '<input type="text" name="' . $this->options_key . '[failed_login][threshold]" id="' . $this->options_key . '_failed_login_threshold" value="' . $this->get_option( array( 'failed_login', 'threshold' ) ) . '" size="1" />';

		// Threshold duration
		$threshold_duration = '<input type="text" name="' . $this->options_key . '[failed_login][threshold_duration]" id="' . $this->options_key . '_failed_login_threshold_duration" value="' . $this->get_option( array( 'failed_login', 'threshold_duration' ) ) . '" size="1" />';

		// Threshold duration unit
		$threshold_duration_unit = '<select name="' . $this->options_key . '[failed_login][threshold_duration_unit]" id="' . $this->options_key . '_failed_login_threshold_duration_unit">';
		foreach ( $units as $unit => $label ) {
			$threshold_duration_unit .= '<option value="' . $unit . '"' . selected( $this->get_option( array( 'failed_login', 'threshold_duration_unit' ) ), $unit, false ) . '>' . $label . '</option>';
		}
		$threshold_duration_unit .= '</select>';

		// Lockout duration
		$lockout_duration = '<input type="text" name="' . $this->options_key . '[failed_login][lockout_duration]" id="' . $this->options_key . '_failed_login_lockout_duration" value="' . $this->get_option( array( 'failed_login', 'lockout_duration' ) ) . '" size="1" />';

		// Lockout duration unit
		$lockout_duration_unit = '<select name="' . $this->options_key . '[failed_login][lockout_duration_unit]" id="' . $this->options_key . '_failed_login_lockout_duration_unit">';
		foreach ( $units as $unit => $label ) {
			$lockout_duration_unit .= '<option value="' . $unit . '"' . selected( $this->get_option( array( 'failed_login', 'lockout_duration_unit' ) ), $unit, false ) . '>' . $label . '</option>';
		}
		$lockout_duration_unit .= '</select>';

		// Output them all
		printf( __( 'After %1$s failed login attempts within %2$s %3$s, lockout the account for %4$s %5$s.', 'theme-my-login' ), $threshold, $threshold_duration, $threshold_duration_unit, $lockout_duration, $lockout_duration_unit );
	}

	/**
	 * Sanitizes settings
	 *
	 * Callback for "tml_save_settings" hook in method Theme_My_Login_Admin::save_settings()
	 *
	 * @see Theme_My_Login_Admin::save_settings()
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $settings ) {
		return array(
			'private_site'  => ! empty( $settings['private_site']  ),
			'private_login' => ! empty( $settings['private_login'] ),
			'failed_login' => array(
				'threshold'               => absint( $settings['failed_login']['threshold'] ),
				'threshold_duration'      => absint( $settings['failed_login']['threshold_duration'] ),
				'threshold_duration_unit' => $settings['failed_login']['threshold_duration_unit'],
				'lockout_duration'        => absint( $settings['failed_login']['lockout_duration'] ),
				'lockout_duration_unit'   => $settings['failed_login']['lockout_duration_unit']
			)
		);
	}

	/**
	 * Attaches actions/filters explicitly to "users.php"
	 *
	 * Callback for "load-users.php" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function load_users_page() {

		$security = Theme_My_Login_Security::get_object();

		wp_enqueue_script( 'tml-security-admin', plugins_url( 'theme-my-login/modules/security/admin/js/security-admin.js' ), array( 'jquery' ) );

		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

		if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'lock', 'unlock' ) ) ) {

			$redirect_to = isset( $_REQUEST['wp_http_referer'] ) ? remove_query_arg( array( 'wp_http_referer', 'updated', 'delete_count' ), stripslashes( $_REQUEST['wp_http_referer'] ) ) : 'users.php';
			$user = isset( $_GET['user'] ) ? $_GET['user'] : '';

			if ( ! $user || ! current_user_can( 'edit_user', $user ) )
				wp_die( __( 'You can&#8217;t edit that user.' ) );

			if ( ! $user = get_userdata( $user ) )
				wp_die( __( 'You can&#8217;t edit that user.' ) );

			if ( 'lock' == $_GET['action'] ) {
				check_admin_referer( 'lock-user_' . $user->ID );

				$security->lock_user( $user );

				$redirect_to = add_query_arg( 'update', 'lock', $redirect_to );
			} elseif ( 'unlock' == $_GET['action'] ) {
				check_admin_referer( 'unlock-user_' . $user->ID );

				$security->unlock_user( $user );

				$redirect_to = add_query_arg( 'update', 'unlock', $redirect_to );
			}

			wp_redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Adds update messages to the admin screen
	 *
	 * Callback for "admin_notices" hook in file admin-header.php
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_notices() {
		if ( isset( $_GET['update'] ) ) {
			if ( 'lock' == $_GET['update'] )
				echo '<div id="message" class="updated fade"><p>' . __( 'User locked.',   'theme-my-login' ) . '</p></div>';
			elseif ( 'unlock' == $_GET['update'] )
				echo '<div id="message" class="updated fade"><p>' . __( 'User unlocked.', 'theme-my-login' ) . '</p></div>';
		}
	}

	/**
	 * Adds "Lock" and "Unlock" links for each pending user on users.php
	 *
	 * Callback for "user_row_actions" hook in {@internal unknown}
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $actions The user actions
	 * @param WP_User $user_object The current user object
	 * @return array The filtered user actions
	 */
	public function user_row_actions( $actions, $user_object ) {
		$current_user = wp_get_current_user();

		$security_meta = isset( $user_object->theme_my_login_security ) ? (array) $user_object->theme_my_login_security : array();

		if ( $current_user->ID != $user_object->ID ) {
			if ( isset( $security_meta['is_locked'] ) && $security_meta['is_locked'] )
				$new_actions['unlock-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url( "users.php?action=unlock&amp;user=$user_object->ID", "unlock-user_$user_object->ID" ) ) . '">' . __( 'Unlock', 'theme-my-login' ) . '</a>';
			else
				$new_actions['lock-user'] = '<a href="' . add_query_arg( 'wp_http_referer', urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ), wp_nonce_url( "users.php?action=lock&amp;user=$user_object->ID", "lock-user_$user_object->ID" ) ) . '">' . __( 'Lock', 'theme-my-login' ) . '</a>';
			$actions = array_merge( $new_actions, $actions );
		}
		return $actions;
	}
}

Theme_My_Login_Security_Admin::get_object();

endif;

