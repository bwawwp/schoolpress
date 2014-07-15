<?php
/**
 * Holds Theme My Login Recaptcha Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Recaptcha
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Recaptcha_Admin' ) ) :
/**
 * Theme My Login Recaptcha Admin class
 *
 * @since 6.3
 */
class Theme_My_Login_Recaptcha_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_recaptcha';

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
	 */
	public static function default_options() {
		return Theme_My_Login_Recaptcha::default_options();
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
		add_action( 'tml_uninstall_recaptcha/recaptcha.php', array( &$this, 'uninstall' ) );

		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_recaptcha/recaptcha.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

	/**
	 * Adds "Permalinks" to the Theme My Login menu
	 *
	 * Callback for "admin_menu" hook
	 *
	 * @since 6.3
	 * @access public
	 */
	public function admin_menu() {
		global $theme_my_login;

		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login reCAPTCHA Settings', 'theme-my-login' ),
			__( 'reCAPTCHA', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		add_settings_section( 'general', null, '__return_false', $this->options_key );

		add_settings_field( 'public_key',  __( 'Public Key',  'theme-my-login' ), array( &$this, 'settings_field_public_key'  ), $this->options_key, 'general' );
		add_settings_field( 'private_key', __( 'Private Key', 'theme-my-login' ), array( &$this, 'settings_field_private_key' ), $this->options_key, 'general' );
		add_settings_field( 'theme',       __( 'Theme',       'theme-my-login' ), array( &$this, 'settings_field_theme'       ), $this->options_key, 'general' );
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
		register_setting( $this->options_key, $this->options_key,  array( &$this, 'save_settings' ) );
	}

	/**
	 * Returns available reCAPTCHA themes
	 *
	 * @since 6.3
	 * @access public
	 */
	public function get_themes() {
		$recaptcha_themes = array(
			'red'        => _x( 'Red (Default)', 'recaptcha theme', 'theme-my-login' ),
			'white'      => _x( 'White',         'recaptcha theme', 'theme-my-login' ),
			'blackglass' => _x( 'Black Glass',   'recaptcha theme', 'theme-my-login' ),
			'clean'      => _x( 'Clean',         'recaptcha theme', 'theme-my-login' )
		);
		return apply_filters( 'theme_my_login_recaptcha_themes', $recaptcha_themes );
	}

	/**
	 * Renders the settings page
	 *
	 * Callback for add_submenu_page()
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_page() {
		Theme_My_Login_Admin::settings_page( array(
			'title'       => __( 'Theme My Login reCAPTCHA Settings', 'theme-my-login' ),
			'options_key' => $this->options_key
		) );
	}

	/**
	 * Renders the Public Key field.
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_public_key() {
		?>
		<input name="theme_my_login_recaptcha[public_key]" type="text" id="theme_my_login_recaptcha_public_key" value="<?php echo esc_attr( $this->get_option( 'public_key' ) ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Renders the Private Key field.
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_private_key() {
		?>
		<input name="theme_my_login_recaptcha[private_key]" type="text" id="theme_my_login_recaptcha_private_key" value="<?php echo esc_attr( $this->get_option( 'private_key' ) ); ?>" class="regular-text" />
		<?php
	}

	/**
	 * Renders the Theme field.
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_theme() {
		?>
		<select name="theme_my_login_recaptcha[theme]" id="theme_my_login_recaptcha_theme">
		<?php foreach ( $this->get_themes() as $theme => $theme_name ) : ?>
			<option value="<?php echo $theme; ?>"<?php selected( $this->get_option( 'theme' ), $theme ); ?>><?php echo $theme_name; ?></option>
		<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Sanitizes module settings
	 *
	 * Callback for register_setting()
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param string|array $input Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $input ) {
		$output = $defaults = self::default_options();

		$output['public_key']  = strip_tags( $input['public_key'] );
		$output['private_key'] = strip_tags( $input['private_key'] );
		if ( in_array( $input['theme'], array_keys( $this->get_themes() ) ) )
			$output['theme'] = $input['theme'];

		return $output;
	}
}

Theme_My_Login_Recaptcha_Admin::get_object();

endif;

