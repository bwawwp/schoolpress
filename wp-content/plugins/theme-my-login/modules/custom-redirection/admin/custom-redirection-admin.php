<?php
/**
 * Holds Theme My Login Custom Redirection Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Redirection
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Redirection_Admin' ) ) :
/**
 * Theme My Login Custom Redirection Admin class
 *
 * @since 6.3
 */
class Theme_My_Login_Custom_Redirection_Admin extends Theme_My_Login_Abstract {
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
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		add_action( 'tml_uninstall_custom-redirection/custom-redirection.php', array( &$this, 'uninstall' ) );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		add_action( 'load-tml_page_theme_my_login_redirection', array( &$this, 'load_settings_page' ) );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 */
	public static function default_options() {
		return Theme_My_Login_Custom_Redirection::default_options();
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_custom-email/custom-email.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

	/**
	 * Adds "Redirection" tab to Theme My Login menu
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		global $wp_roles;

		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Custom Redirection Settings', 'theme-my-login' ),
			__( 'Redirection', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' != $role )
				add_meta_box( $role, translate_user_role( $role_name ), array( &$this, 'redirection_meta_box' ), 'tml_page_' . $this->options_key, 'normal' );
		}
	}

	/**
	 * Registers options group
	 *
	 * Callback for "admin_init" hook
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_init() {
		register_setting( $this->options_key, $this->options_key );
	}

	/**
	 * Loads admin styles and scripts
	 *
	 * Callback for "load-settings_page_theme-my-login" hook in file "wp-admin/admin.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function load_settings_page() {
		wp_enqueue_script( 'tml-custom-redirection-admin', plugins_url( 'theme-my-login/modules/custom-redirection/admin/js/custom-redirection-admin.js' ), array( 'postbox' ) );
	}

	/**
	 * Renders settings page
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_page() {
		global $current_screen;
		?>
		<div class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php echo esc_html_e( 'Theme My Login Custom Redirection Settings', 'theme-my-login' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( $this->options_key );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				?>
				<div id="<?php echo $this->options_key; ?>" class="metabox-holder">
					<?php do_meta_boxes( $current_screen->id, 'normal', null ); ?>
				</div>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Outputs redirection admin menu for specified role
	 *
	 * Callback for add_submenu_page()
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param array $args Arguments passed in from add_submenu_page()
	 */
	public function redirection_meta_box( $object, $box ) {
		$role = $box['id'];
		?>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php _e( 'Log in' ); ?></th>
				<td>
					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][login_type]" type="radio" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_login_type_default" value="default"<?php checked( 'default', $this->get_option( array( $role, 'login_type' ) ) ); ?> /> <label for="<?php echo $this->options_key; ?>_<?php echo $role; ?>_login_type_default"><?php _e( 'Default', 'theme-my-login' ); ?></label>
					<p class="description"><?php _e( 'Check this option to send the user to their WordPress Dashboard/Profile.', 'theme-my-login' ); ?></p>

					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][login_type]" type="radio" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_login_type_referer" value="referer"<?php checked( 'referer', $this->get_option( array( $role, 'login_type' ) ) ); ?> /> <label for="<?php echo $this->options_key; ?>_<?php echo $role; ?>_login_type_referer"><?php _e( 'Referer', 'theme-my-login' ); ?></label>
					<p class="description"><?php _e( 'Check this option to send the user back to the page they were visiting before logging in.', 'theme-my-login' ); ?></p>

					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][login_type]" type="radio" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_login_type_custom" value="custom"<?php checked( 'custom', $this->get_option( array( $role, 'login_type' ) ) ); ?> />
					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][login_url]" type="text" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_login_url" value="<?php echo $this->get_option( array( $role, 'login_url' ) ); ?>" class="regular-text" />
					<p class="description"><?php _e( 'Check this option to send the user to a custom location, specified by the textbox above.', 'theme-my-login' ); ?></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Log out' ); ?></th>
				<td>
					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][logout_type]" type="radio" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_logout_type_default" value="default"<?php checked( 'default', $this->get_option( array( $role, 'logout_type' ) ) ); ?> /> <label for="<?php echo $this->options_key; ?>_<?php echo $role; ?>_logout_type_default"><?php _e( 'Default', 'theme-my-login' ); ?></label><br />
					<p class="description"><?php _e( 'Check this option to send the user to the log in page, displaying a message that they have successfully logged out.', 'theme-my-login' ); ?></p>

					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][logout_type]" type="radio" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_logout_type_referer" value="referer"<?php checked( 'referer', $this->get_option( array( $role, 'logout_type' ) ) ); ?> /> <label for="<?php echo $this->options_key; ?>_<?php echo $role; ?>_logout_type_referer"><?php _e( 'Referer', 'theme-my-login' ); ?></label><br />
					<p class="description"><?php _e( 'Check this option to send the user back to the page they were visiting before logging out. (Note: If the previous page being visited was an admin page, this can have unexpected results.)', 'theme-my-login' ); ?></p>

					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][logout_type]" type="radio" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_logout_type_custom" value="custom"<?php checked( 'custom', $this->get_option( array( $role, 'logout_type' ) ) ); ?> />
					<input name="<?php echo $this->options_key; ?>[<?php echo $role; ?>][logout_url]" type="text" id="<?php echo $this->options_key; ?>_<?php echo $role; ?>_logout_url" value="<?php echo $this->get_option( array( $role, 'logout_url' ) ); ?>" class="regular-text" />
					<p class="description"><?php _e( 'Check this option to send the user to a custom location, specified by the textbox above.', 'theme-my-login' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}
}

Theme_My_Login_Custom_Redirection_Admin::get_object();

endif;

