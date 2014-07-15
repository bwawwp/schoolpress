<?php
/**
 * Holds Theme My Login User Moderation Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_User_Moderation
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_User_Moderation_Admin' ) ) :
/**
 * Theme My Login User Moderation Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_User_Moderation_Admin extends Theme_My_Login_Abstract {
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
	 * Loads the module
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'tml_activate_user-moderation/user-moderation.php',   array( &$this, 'activate'  ) );
		add_action( 'tml_uninstall_user-moderation/user-moderation.php',  array( &$this, 'uninstall' ) );

		add_action( 'tml_modules_loaded', array( &$this, 'modules_loaded' ) );

		if ( is_multisite() )
			return;

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		add_action( 'load-users.php',   array( &$this, 'load_users_page'  )        );
		add_filter( 'user_row_actions', array( &$this, 'user_row_actions' ), 10, 2 );

		add_action( 'delete_user', array( &$this, 'deny_user' ) );
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
		return Theme_My_Login_User_Moderation::default_options();
	}

	/**
	 * Activates the module
	 *
	 * Callback for "tml_activate_user-moderation/user-moderation.php" hook in method Theme_My_Login_Admin::activate_module()
	 *
	 * @see Theme_My_Login_Admin::activate_module()
	 * @since 6.0
	 * @access public
	 *
	 * @param object $theme_my_login Reference to global $theme_my_login object
	 */
	public function activate() {
		if ( is_multisite() ) {
			add_settings_error( $this->options_key, 'invalid_module', __( 'User Moderation is not currently compatible with multisite.', 'theme-my-login' ) );
			return;
		}
		add_role( 'pending', 'Pending', array() );
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_user-moderation/user-moderation.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
		remove_role( 'pending' );
	}

	/**
	 * Disables the module if multisite
	 *
	 * @since 6.3
	 * @access public
	 */
	public function modules_loaded() {
		if ( is_multisite() ) {
			$theme_my_login_admin = Theme_My_Login_Admin::get_object();

			$active_modules = $theme_my_login_admin->get_option( 'active_modules' );
			$active_modules = array_values( array_diff( $active_modules, array( 'user-moderation/user-moderation.php' ) ) );

			$theme_my_login_admin->set_option( 'active_modules', $active_modules );
			$theme_my_login_admin->save_options();
			return;
		}
	}

	/**
	 * Adds "Moderation" to Theme My Login menu
	 *
	 * Callback for "admin_menu" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login User Moderation Settings', 'theme-my-login' ),
			__( 'Moderation', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		add_settings_section( 'general', null, '__return_false', $this->options_key );

		add_settings_field( 'type', __( 'Moderation Type', 'theme-my-login' ), array( &$this, 'settings_field_moderation_type' ), $this->options_key, 'general' );
	}

	/**
	 * Registers options group
	 *
	 * Callback for "admin_init" hook
	 *
	 * @since 6.0
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
			'title'       => __( 'Theme My Login User Moderation Settings', 'theme-my-login' ),
			'options_key' => $this->options_key
		) );
	}

	/**
	 * Renders Moderation Type settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_moderation_type() {
		?>
		<input name="<?php echo $this->options_key; ?>[type]" type="radio" id="<?php echo $this->options_key; ?>_type_none" value="none"<?php checked( $this->get_option( 'type' ), 'none' ); ?> />
		<label for="<?php echo $this->options_key; ?>_type_none"><?php _e( 'None', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Check this option to require no moderation.', 'theme-my-login' ); ?></p>

		<input name="<?php echo $this->options_key; ?>[type]" type="radio" id="<?php echo $this->options_key; ?>_type_email" value="email" <?php checked( $this->get_option( 'type' ), 'email' ); ?> />
		<label for="<?php echo $this->options_key; ?>_type_email"><?php _e( 'E-mail Confirmation', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Check this option to require new users to confirm their e-mail address before they may log in.', 'theme-my-login' ); ?></p>

		<input name="<?php echo $this->options_key; ?>[type]" type="radio" id="<?php echo $this->options_key; ?>_type_admin" value="admin" <?php checked( $this->get_option( 'type' ), 'admin' ); ?> />
		<label for="<?php echo $this->options_key; ?>_type_admin"><?php _e( 'Admin Approval', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Check this option to require new users to be approved by an administrator before they may log in.', 'theme-my-login' ); ?></p>
		<?php
	}

	/**
	 * Sanitizes settings
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array $settings Posted settings
	 * @return array Sanitized settings
	 */
	public function save_settings( $settings ) {
		return array(
			'type' => in_array( $settings['type'], array( 'none', 'email', 'admin' ) ) ? $settings['type'] : 'none'
		);
	}

	/**
	 * Attaches actions/filters explicitly to users.php
	 *
	 * Callback for "load-users.php" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function load_users_page() {
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

		// Is there an action?
		if ( isset( $_GET['action'] ) ) {

			// Is it a sanctioned action?
			if ( in_array( $_GET['action'], array( 'approve', 'resendactivation' ) ) ) {

				// Is there a user ID?
				$user = isset( $_GET['user'] ) ? $_GET['user'] : '';

				// No user ID?
				if ( ! $user || ! current_user_can( 'edit_user', $user ) )
					wp_die( __( 'You can&#8217;t edit that user.', 'theme-my-login' ) );

				// Where did we come from?
				$redirect_to = isset( $_REQUEST['wp_http_referer'] ) ? remove_query_arg( array( 'wp_http_referer', 'updated', 'delete_count' ), stripslashes( $_REQUEST['wp_http_referer'] ) ) : 'users.php';

				switch ( $_GET['action'] ) {
					case 'approve' :
						check_admin_referer( 'approve-user' );

						if ( ! self::approve_user( $user ) )
							wp_die( __( 'You can&#8217;t edit that user.' ) );

						$redirect_to = add_query_arg( 'update', 'approve', $redirect_to );
						break;
					case 'resendactivation' :
						check_admin_referer( 'resend-activation' );

						do_action( 'tml_user_activation_resend', $user );

						if ( ! Theme_My_Login_User_Moderation::new_user_activation_notification( $user ) )
							wp_die( __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) );

						$redirect_to = add_query_arg( 'update', 'sendactivation', $redirect_to );
						break;
				}
				wp_redirect( $redirect_to );
				exit;
			}
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
		if ( isset( $_GET['update'] ) && in_array( $_GET['update'], array( 'approve', 'sendactivation' ) ) ) {
			echo '<div id="message" class="updated fade"><p>';
			if ( 'approve' == $_GET['update'] )
				_e( 'User approved.', 'theme-my-login' );
			elseif ( 'sendactivation' == $_GET['update'] )
				_e( 'Activation sent.', 'theme-my-login' );
			echo '</p></div>';
		}
	}

	/**
	 * Adds "Approve" link for each pending user on users.php
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

		if ( $current_user->ID != $user_object->ID ) {
			if ( in_array( 'pending', (array) $user_object->roles ) ) {
				switch ( $this->get_option( 'type' ) ) {
					case 'email' :
						// Add "Resend Activation" link
						$actions['resend-activation'] = sprintf( '<a href="%1$s">%2$s</a>',
							add_query_arg( 'wp_http_referer',
								urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ),
								wp_nonce_url( "users.php?action=resendactivation&amp;user=$user_object->ID", 'resend-activation' )
							),
							__( 'Resend Activation', 'theme-my-login' )
						);
						break;
					case 'admin' :
						// Add "Approve" link
						$actions['approve-user'] = sprintf( '<a href="%1$s">%2$s</a>',
							add_query_arg( 'wp_http_referer',
								urlencode( esc_url( stripslashes( $_SERVER['REQUEST_URI'] ) ) ),
								wp_nonce_url( "users.php?action=approve&amp;user=$user_object->ID", 'approve-user' ) 
							),
							__( 'Approve', 'theme-my-login' )
						);
						break;
				}
			}
		}
		return $actions;
	}

	/**
	 * Handles activating a new user by admin approval
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User's ID
	 * @return bool Returns false if not a valid user
	 */
	public static function approve_user( $user_id ) {
		global $wpdb, $current_site;

		$user_id = (int) $user_id;

		// Get user by ID
		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE ID = %d", $user_id ) );
		if ( empty( $user ) )
			return false;

		do_action( 'approve_user', $user->ID );

		// Clear the activation key if there is one
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => '' ), array( 'ID' => $user->ID ) );

		$approval_role = apply_filters( 'tml_approval_role', get_option( 'default_role' ), $user->ID );

		// Set user role
		$user_object = new WP_User( $user->ID );
		$user_object->set_role( $approval_role );
		unset( $user_object );

		// Check for plaintext pass
		if ( ! $user_pass = get_user_meta( $user->ID, 'user_pass', true ) ) {
			$user_pass = wp_generate_password();
			wp_set_password( $user_pass, $user->ID );
		}

		// Delete plaintext pass
		delete_user_meta( $user->ID, 'user_pass' );

		if ( is_multisite() ) {
			$blogname = $current_site->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message  = sprintf( __( 'You have been approved access to %s', 'theme-my-login' ), $blogname         ) . "\r\n\r\n";
		$message .= sprintf( __( 'Username: %s',                        'theme-my-login' ), $user->user_login ) . "\r\n";
		$message .= sprintf( __( 'Password: %s',                        'theme-my-login' ), $user_pass        ) . "\r\n\r\n";
		$message .= site_url( 'wp-login.php', 'login' ) . "\r\n";	

		$title    = sprintf( __( '[%s] Registration Approved', 'theme-my-login' ), $blogname );

		$title    = apply_filters( 'user_approval_notification_title',   $title,   $user->ID             );
		$message  = apply_filters( 'user_approval_notification_message', $message, $user_pass, $user->ID );

		if ( $message && ! wp_mail( $user->user_email, $title, $message ) )
			  die( '<p>' . __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) . '</p>' );

		return true;
	}

	/**
	 * Called upon deletion of a user in the "Pending" role
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param int $user_id User's ID
	 */
	public function deny_user( $user_id ) {
		global $current_site;

		$user_id = (int) $user_id;

		$user = new WP_User( $user_id );
		if ( ! in_array( 'pending', (array) $user->roles ) )
			return;

		do_action( 'deny_user', $user->ID );

		if ( is_multisite() ) {
			$blogname = $current_site->site_name;
		} else {
			// The blogname option is escaped with esc_html on the way into the database in sanitize_option
			// we want to reverse this for the plain text arena of emails.
			$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = sprintf( __( 'You have been denied access to %s', 'theme-my-login' ), $blogname );
		$title   = sprintf( __( '[%s] Registration Denied',          'theme-my-login' ), $blogname );

		$title   = apply_filters( 'user_denial_notification_title',   $title,   $user_id );
		$message = apply_filters( 'user_denial_notification_message', $message, $user_id );

		if ( $message && ! wp_mail( $user->user_email, $title, $message ) )
			  die( '<p>' . __( 'The e-mail could not be sent.' ) . "<br />\n" . __( 'Possible reason: your host may have disabled the mail() function...' ) . '</p>' );
	}
}

Theme_My_Login_User_Moderation_Admin::get_object();

endif;

