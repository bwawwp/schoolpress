<?php
/**
 * Plugin Name: reCAPTCHA
 * Description: Enabling this module will initialize reCAPTCHA. You will then have to configure the settings via the "reCAPTCHA" tab.
 *
 * Holds Theme My Login Recaptcha class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Recaptcha
 * @since 6.3
 */

if ( ! class_exists( 'Theme_My_Login_Recaptcha' ) ) :
/**
 * Theme My Login Custom Permalinks class
 *
 * Adds the ability to set permalinks for default actions.
 *
 * @since 6.3
 */
class Theme_My_Login_Recaptcha extends Theme_My_Login_Abstract {
	/**
	 * Holds reCAPTCHA API URI
	 *
	 * @since 6.3
	 * @const string
	 */
	const RECAPTCHA_API_URI = 'www.google.com/recaptcha/api';

	/**
	 * Holds reCAPTCHA API URL
	 *
	 * @since 6.3.7
	 * @var string
	 */
	private $recaptcha_api_url;

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
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array(
			'public_key'  => '',
			'private_key' => '',
			'theme'       => 'red'
		);
	}

	/**
	 * Loads the module
	 *
	 * @since 6.3
	 * @access protected
	 */
	protected function load() {
		if ( ! ( $this->get_option( 'public_key' ) || $this->get_option( 'private_key' ) ) )
			return;

		$this->recaptcha_api_url = ( is_ssl() ? 'https://' : 'http://' ) . self::RECAPTCHA_API_URI;

		add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_scripts' ) );

		add_action( 'register_form',       array( &$this, 'recaptcha_display'   ) );
		add_filter( 'registration_errors', array( &$this, 'registration_errors' ) );

		if ( is_multisite() ) {
			add_action( 'signup_extra_fields',       array( &$this, 'recaptcha_display'    ) );
			add_filter( 'wpmu_validate_user_signup', array( &$this, 'wpmu_validate_signup' ) );
			add_filter( 'wpmu_validate_blog_signup', array( &$this, 'wpmu_validate_signup' ) );
		}
	}

	/**
	 * Enqueues scripts
	 *
	 * @since 6.3
	 */
	function wp_enqueue_scripts() {
		wp_enqueue_script( 'recaptcha', $this->recaptcha_api_url . '/js/recaptcha_ajax.js' );
		wp_enqueue_script( 'theme-my-login-recaptcha', plugins_url( 'theme-my-login/modules/recaptcha/js/recaptcha.js' ), array( 'recaptcha', 'jquery' ) );
		wp_localize_script( 'theme-my-login-recaptcha', 'tmlRecaptcha', array(
			'publickey' => $this->get_option( 'public_key' ),
			'theme'     => $this->get_option( 'theme' )
		) );
	}

	/**
	 * Retrieves reCAPTCHA errors
	 *
	 * @since 6.3
	 *
	 * @param WP_Error $errors WP_Error object
	 * @return WP_Error WP_Error object
	 */
	public function registration_errors( $errors ) {
		$response = $this->recaptcha_validate( $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field'] );
		if ( is_wp_error( $response ) ) {

			$error_code = $response->get_error_message();

			switch ( $error_code ) {
				case 'invalid-site-private-key' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Invalid reCAPTCHA private key.', 'theme-my-login' ), 'invalid-site-private-key' );
					break;
				case 'invalid-request-cookie' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Invalid reCAPTCHA challenge parameter.', 'theme-my-login' ), 'invalid-request-cookie' );
					break;
				case 'incorrect-captcha-sol' :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Incorrect captcha code.', 'theme-my-login' ), 'incorrect-captcha-sol' );
					break;
				case 'recaptcha-not-reachable' :
				default :
					$errors->add( 'recaptcha', __( '<strong>ERROR</strong>: Unable to reach the reCAPTCHA server.', 'theme-my-login' ), 'recaptcha-not-reachable' );
					break;
			}
		}
		return $errors;
	}

	/**
	 * Retrieves reCAPTCHA errors for multisite
	 *
	 * @since 6.3.7
	 *
	 * @param array $result Signup parameters
	 * @return array Signup parameters
	 */
	public function wpmu_validate_signup( $result ) {
		$result['errors'] = $this->registration_errors( $result['errors'] );
		return $result;
	}

	/**
	 * Displays reCAPTCHA
	 *
	 * @since 6.3
	 * @access public
	 */
	public function recaptcha_display( $errors = null ) {
		if ( is_multisite() ) {
			if ( $error = $errors->get_error_message( 'recaptcha' ) ) { ?>
			<p class="error"><?php echo $error; ?></p>
			<?php }
		}
		?>
		<div id="recaptcha">
			<noscript>
				<iframe src="<?php echo self::RECAPTCHA_API_URI; ?>/noscript?k=<?php echo $this->get_option( 'public_key' ); ?>" height="300" width="500" frameborder="0"></iframe><br>
				<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
				<input type="hidden" name="recaptcha_response_field" value="manual_challenge">
			</noscript>
		</div>
		<?php
	}

	/**
	 * Validates reCAPTCHA
	 *
	 * @since 6.3
	 * @access public
	 */
	public function recaptcha_validate( $remote_ip, $challenge, $response ) {
		$response = wp_remote_post( $this->recaptcha_api_url . '/verify', array(
			'body' => array(
				'privatekey' => $this->get_option( 'private_key' ),
				'remoteip'   => $remote_ip,
				'challenge'  => $challenge,
				'response'   => $response
			)
		) );

		$response_code    = wp_remote_retrieve_response_code( $response );
		$response_message = wp_remote_retrieve_response_message( $response );

		if ( 200 == $response_code ) {
			// Parse the response
			list( $is_valid, $error_code ) = array_map( 'trim', explode( "\n", wp_remote_retrieve_body( $response ) ) );

			if ( 'true' == $is_valid )
				return true;

			return new WP_Error( 'recaptcha', $error_code );
		}

		return new WP_Error( 'recaptcha', 'recaptcha-not-reachable' );
	}
}

Theme_My_Login_Recaptcha::get_object();

endif;

if ( is_admin() )
	include_once( dirname( __FILE__ ) . '/admin/recaptcha-admin.php' );

