<?php
/**
 * Holds Theme My Login Custom E-mail Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_Email
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_Email_Admin' ) ) :
/**
 * Theme My Login Custom E-mail Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_Email_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_email';

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
	 * Called by Theme_My_Login_Abstract::__construct()
	 *
	 * @see Theme_My_Login_Abstract::__construct()
	 * @since 6.0
	 * @access protected
	 */
	protected function load() {
		add_action( 'tml_uninstall_custom-email/custom-email.php', array( &$this, 'uninstall' ) );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		add_action( 'load-tml_page_theme_my_login_email', array( &$this, 'load_settings_page' ) );

		add_action( 'user_register', array( &$this, 'user_register' ) );
	}

	/**
	 * Returns default options
	 *
	 * @since 6.3
	 * @access public
	 */
	public static function default_options() {
		return Theme_My_Login_Custom_Email::default_options();
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
	 * Adds "E-mail" to the Theme My Login menu
	 *
	 * Callback for "admin_menu" hook
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {

		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Custom E-mail Settings', 'theme-my-login' ),
			__( 'E-mail', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		add_meta_box( 'new_user',       __( 'New User',          'theme-my-login' ), array( &$this, 'new_user_meta_box' ),       'tml_page_' . $this->options_key, 'normal' );
		add_meta_box( 'new_user_admin', __( 'New User Admin',    'theme-my-login' ), array( &$this, 'new_user_admin_meta_box' ), 'tml_page_' . $this->options_key, 'normal' );
		add_meta_box( 'retrieve_pass',  __( 'Retrieve Password', 'theme-my-login' ), array( &$this, 'retrieve_pass_meta_box' ),  'tml_page_' . $this->options_key, 'normal' );
		add_meta_box( 'reset_pass',     __( 'Reset Password',    'theme-my-login' ), array( &$this, 'reset_pass_meta_box' ),     'tml_page_' . $this->options_key, 'normal' );

		// Check for User Moderation module
		if ( class_exists( 'Theme_My_Login_User_Moderation' ) ) {
			add_meta_box( 'user_activation',     __( 'User Activation',     'theme-my-login' ), array( &$this, 'user_activation_meta_box' ),     'tml_page_' . $this->options_key, 'normal' );
			add_meta_box( 'user_approval',       __( 'User Approval',       'theme-my-login' ), array( &$this, 'user_approval_meta_box' ),       'tml_page_' . $this->options_key, 'normal' );
			add_meta_box( 'user_approval_admin', __( 'User Approval Admin', 'theme-my-login'),  array( &$this, 'user_approval_admin_meta_box' ), 'tml_page_' . $this->options_key, 'normal' );
			add_meta_box( 'user_denial',         __( 'User Denial',         'theme-my-login' ), array( &$this, 'user_denial_meta_box' ),         'tml_page_' . $this->options_key, 'normal' );
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
		register_setting( $this->options_key, $this->options_key, array( &$this, 'save_settings' ) );
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
		wp_enqueue_script( 'tml-custom-email-admin', plugins_url( 'theme-my-login/modules/custom-email/admin/js/custom-email-admin.js' ), array( 'postbox' ) );
	}

	/**
	 * Renders settings page
	 *
	 * Callback for add_submenu_page()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_page() {
		global $current_screen;
		?>
		<div class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php echo esc_html_e( 'Theme My Login Custom E-mail Settings', 'theme-my-login' ); ?></h2>
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
	 * Renders New User Notification settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function new_user_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a new user upon registration.', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_new_user_mail_from_name" value="<?php echo $this->get_option( array( 'new_user', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][mail_from]" type="text" id="<?php echo $this->options_key; ?>_new_user_mail_from" value="<?php echo $this->get_option( array( 'new_user', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[new_user][mail_content_type]" id="<?php echo $this->options_key; ?>_new_user_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'new_user', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'new_user', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][title]" type="text" id="<?php echo $this->options_key; ?>_new_user_title" value="<?php echo $this->get_option( array( 'new_user', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_pass%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[new_user][message]" id="<?php echo $this->options_key; ?>_new_user_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'new_user', 'message' ) ); ?></textarea></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders New User Admin Notification settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function new_user_admin_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below, upon new user registration.', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_to"><?php _e( 'To', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_mail_to]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_mail_to" value="<?php echo $this->get_option( array( 'new_user', 'admin_mail_to' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_mail_from_name" value="<?php echo $this->get_option( array( 'new_user', 'admin_mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_mail_from]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_mail_from" value="<?php echo $this->get_option( array( 'new_user', 'admin_mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[new_user][admin_mail_content_type]" id="<?php echo $this->options_key; ?>_new_user_admin_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'new_user', 'admin_mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'new_user', 'admin_mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[new_user][admin_title]" type="text" id="<?php echo $this->options_key; ?>_new_user_admin_title" value="<?php echo $this->get_option( array( 'new_user', 'admin_title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_new_user_admin_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[new_user][admin_message]" id="<?php echo $this->options_key; ?>_new_user_admin_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'new_user', 'admin_message' ) ); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input name="<?php echo $this->options_key; ?>[new_user][admin_disable]" type="checkbox" id="<?php echo $this->options_key; ?>_new_user_admin_disable" value="1"<?php checked( 1, $this->get_option( array( 'new_user', 'admin_disable' ) ) ); ?> />
					<label for="<?php echo $this->options_key; ?>_new_user_admin_disable"><?php _e( 'Disable Admin Notification', 'theme-my-login' ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders Retrieve Password settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function retrieve_pass_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a user when they attempt to recover their password.', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %reseturl% or else the user will not be able to recover their password!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[retrieve_pass][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_retrieve_pass_mail_from_name" value="<?php echo $this->get_option( array( 'retrieve_pass', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[retrieve_pass][mail_from]" type="text" id="<?php echo $this->options_key; ?>_retrieve_pass_mail_from" value="<?php echo $this->get_option( array( 'retrieve_pass', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[retrieve_pass][mail_content_type]" id="<?php echo $this->options_key; ?>_retrieve_pass_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'retrieve_pass', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'retrieve_pass', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[retrieve_pass][title]" type="text" id="<?php echo $this->options_key; ?>_retrieve_pass_title" value="<?php echo $this->get_option( array( 'retrieve_pass', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_retrieve_pass_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %reseturl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[retrieve_pass][message]" id="<?php echo $this->options_key; ?>_retrieve_pass_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'retrieve_pass', 'message' ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders Reset Password settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function reset_pass_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below, upon user password change.', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_to"><?php _e( 'To', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_to]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_to" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_mail_to' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from_name" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_from]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_from" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[reset_pass][admin_mail_content_type]" id="<?php echo $this->options_key; ?>_reset_pass_admin_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'reset_pass', 'admin_mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'reset_pass', 'admin_mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[reset_pass][admin_title]" type="text" id="<?php echo $this->options_key; ?>_reset_pass_admin_title" value="<?php echo $this->get_option( array( 'reset_pass', 'admin_title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_reset_pass_admin_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[reset_pass][admin_message]" id="<?php echo $this->options_key; ?>_reset_pass_admin_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'reset_pass', 'admin_message' ) ); ?></textarea>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input name="<?php echo $this->options_key; ?>[reset_pass][admin_disable]" type="checkbox" id="<?php echo $this->options_key; ?>_reset_pass_admin_disable" value="1"<?php checked( 1, $this->get_option( array( 'reset_pass', 'admin_disable' ) ) ); ?> />
					<label for="<?php echo $this->options_key; ?>_reset_pass_admin_disable"><?php _e( 'Disable Admin Notification', 'theme-my-login' ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Activation settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function user_activation_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a new user upon registration when "E-mail Confirmation" is checked for "User Moderation".', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %activateurl% or else the user will not be able to activate their account!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_activation][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_activation_mail_from_name" value="<?php echo $this->get_option( array( 'user_activation', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_activation][mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_activation_mail_from" value="<?php echo $this->get_option( array( 'user_activation', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_activation][mail_content_type]" id="<?php echo $this->options_key; ?>_user_activation_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_activation', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_activation', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_activation][title]" type="text" id="<?php echo $this->options_key; ?>_user_activation_title" value="<?php echo $this->get_option( array( 'user_activation', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_activation_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %activateurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_activation][message]" id="<?php echo $this->options_key; ?>_user_activation_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_activation', 'message' ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Approval settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function user_approval_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a new user upon admin approval when "Admin Approval" is checked for "User Moderation".', 'theme-my-login' ); ?>
			<?php _e( 'Please be sure to include the variable %user_pass% if using default passwords or else the user will not know their password!', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_approval_mail_from_name" value="<?php echo $this->get_option( array( 'user_approval', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_approval_mail_from" value="<?php echo $this->get_option( array( 'user_approval', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_approval][mail_content_type]" id="<?php echo $this->options_key; ?>_user_approval_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][title]" type="text" id="<?php echo $this->options_key; ?>_user_approval_title" value="<?php echo $this->get_option( array( 'user_approval', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %loginurl%, %user_login%, %user_email%, %user_pass%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_approval][message]" id="<?php echo $this->options_key; ?>_user_approval_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_approval', 'message' ) ); ?></textarea></td>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Approval Admin settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function user_approval_admin_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to the e-mail address or addresses (multiple addresses may be separated by commas) specified below upon user registration when "Admin Approval" is checked for "User Moderation".', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_to"><?php _e( 'To', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_mail_to]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_to" value="<?php echo $this->get_option( array( 'user_approval', 'admin_mail_to' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_from_name" value="<?php echo $this->get_option( array( 'user_approval', 'admin_mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_from" value="<?php echo $this->get_option( array( 'user_approval', 'admin_mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_approval][admin_mail_content_type]" id="<?php echo $this->options_key; ?>_user_approval_admin_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_approval', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_title"><?php _e( 'Subject', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_approval][admin_title]" type="text" id="<?php echo $this->options_key; ?>_user_approval_admin_title" value="<?php echo $this->get_option( array( 'user_approval', 'admin_title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_approval_admin_message"><?php _e( 'Message', 'theme-my-login' ); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %pendingurl%, %user_login%, %user_email%, %user_ip%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_approval][admin_message]" id="<?php echo $this->options_key; ?>_user_approval_admin_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_approval', 'admin_message' ) ); ?></textarea></td>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">&nbsp;</th>
				<td>
					<input name="<?php echo $this->options_key; ?>[user_approval][admin_disable]" type="checkbox" id="<?php echo $this->options_key; ?>_user_approval_admin_disable" value="1"<?php checked( 1, $this->get_option( array( 'user_approval', 'admin_disable' ) ) ); ?> />
					<label for="<?php echo $this->options_key; ?>_user_approval_admin_disable"><?php _e( 'Disable Admin Notification', 'theme-my-login' ); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Renders User Denial settings section
	 *
	 * This is the callback for add_meta_box()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function user_denial_meta_box() {
		?>
		<p class="description">
			<?php _e( 'This e-mail will be sent to a user who is deleted/denied when "Admin Approval" is checked for "User Moderation" and the user\'s role is "Pending".', 'theme-my-login' ); ?>
			<?php _e( 'If any field is left empty, the default will be used instead.', 'theme-my-login' ); ?>
		</p>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_mail_from_name"><?php _e( 'From Name', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_denial][mail_from_name]" type="text" id="<?php echo $this->options_key; ?>_user_denial_mail_from_name" value="<?php echo $this->get_option( array( 'user_denial', 'mail_from_name' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_mail_from"><?php _e( 'From E-mail', 'theme-my-login' ); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_denial][mail_from]" type="text" id="<?php echo $this->options_key; ?>_user_denial_mail_from" value="<?php echo $this->get_option( array( 'user_denial', 'mail_from' ) ); ?>" class="regular-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_mail_content_type"><?php _e( 'E-mail Format', 'theme-my-login' ); ?></label></th>
				<td>
					<select name="<?php echo $this->options_key; ?>[user_denial][mail_content_type]" id="<?php echo $this->options_key; ?>_user_denial_mail_content_type">
						<option value="plain"<?php selected( $this->get_option( array( 'user_denial', 'mail_content_type' ) ), 'plain' ); ?>><?php _e( 'Plain Text', 'theme-my-login' ); ?></option>
						<option value="html"<?php  selected( $this->get_option( array( 'user_denial', 'mail_content_type' ) ), 'html' ); ?>><?php  _e( 'HTML', 'theme-my-login' ); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_title"><?php _e('Subject', 'theme-my-login'); ?></label></th>
				<td><input name="<?php echo $this->options_key; ?>[user_denial][title]" type="text" id="<?php echo $this->options_key; ?>_user_denial_title" value="<?php echo $this->get_option( array( 'user_denial', 'title' ) ); ?>" class="large-text" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="<?php echo $this->options_key; ?>_user_denial_message"><?php _e('Message', 'theme-my-login'); ?></label></th>
				<td>
					<p class="description"><?php _e( 'Available Variables', 'theme-my-login' ); ?>: %blogname%, %siteurl%, %user_login%, %user_email%</p>
					<textarea name="<?php echo $this->options_key; ?>[user_denial][message]" id="<?php echo $this->options_key; ?>_user_denial_message" class="large-text" rows="10"><?php echo $this->get_option( array( 'user_denial', 'message' ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	public function user_register( $user_id ) {
		$screen = get_current_screen();

		if ( 'user' == $screen->base && 'add' == $screen->action ) {
			do_action( 'tml_new_user_registered', $user_id, isset( $_POST['send_password'] ) ? $_POST['pass1'] : '' );

			if ( current_user_can( 'list_users' ) )
				$redirect = 'users.php?update=add&id=' . $user_id;
			else
				$redirect = add_query_arg( 'update', 'add', 'user-new.php' );
			wp_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Sanitizes settings
	 *
	 * Callback for register_setting()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $settings ) {
		$settings['new_user']['admin_disable']   = ! empty( $settings['new_user']['admin_disable']   );
		$settings['reset_pass']['admin_disable'] = ! empty( $settings['reset_pass']['admin_disable'] );

		if ( class_exists( 'Theme_My_Login_User_Moderation' ) )
			$settings['user_approval']['admin_disable'] = isset( $settings['user_approval']['admin_disable'] );

		$settings = Theme_My_Login_Common::array_merge_recursive( $this->get_options(), $settings );

		return $settings;
	}
}

Theme_My_Login_Custom_Email_Admin::get_object();

endif;

