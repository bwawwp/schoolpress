<?php
/**
 * Holds the Theme My Login template class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Template' ) ) :
/*
 * Theme My Login template class
 *
 * This class contains properties and methods common to displaying output.
 *
 * @since 6.0
 */
class Theme_My_Login_Template extends Theme_My_Login_Abstract {
	/**
	 * Holds active instance flag
	 *
	 * @since 6.3
	 * @access private
	 * @var bool
	 */
	private $is_active = false;

	/**
	 * Constructor
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $options Instance options
	 */
	public function __construct( $options = '' ) {
		$options = wp_parse_args( $options );
		$options = shortcode_atts( self::default_options(), $options );

		$this->set_options( $options );
	}

	/**
	 * Retrieves default options
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return array Default options
	 */
	public static function default_options() {
		return array(
			'instance'              => 0,
			'default_action'        => '',
			'login_template'        => '',
			'register_template'     => '',
			'lostpassword_template' => '',
			'resetpass_template'    => '',
			'user_template'         => '',
			'show_title'            => true,
			'show_log_link'         => true,
			'show_reg_link'         => true,
			'show_pass_link'        => true,
			'logged_in_widget'      => true,
			'logged_out_widget'     => true,
			'show_gravatar'         => true,
			'gravatar_size'         => 50,
			'before_widget'         => '',
			'after_widget'          => '',
			'before_title'          => '',
			'after_title'           => ''
		);
	}

	/**
	 * Displays output according to current action
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return string HTML output
	 */
	public function display( $action = '' ) {
		if ( empty( $action ) )
			$action = $this->get_option( 'default_action' );

		ob_start();
		echo $this->get_option( 'before_widget' );
		if ( $this->get_option( 'show_title' ) )
			echo $this->get_option( 'before_title' ) . $this->get_title( $action ) . $this->get_option( 'after_title' ) . "\n";
		// Is there a specified template?
		if ( has_action( 'tml_display_' . $action ) ) {
			do_action_ref_array( 'tml_display_' . $action, array( &$this ) );
		} else {
			$template = array();
			if ( is_user_logged_in() && 'login' == $action ) {
				if ( $this->get_option( 'user_template' ) )
					$template[] = $this->get_option( 'user_template' );
				$template[] = 'user-panel.php';
			} else {
				switch ( $action ) {
					case 'lostpassword':
					case 'retrievepassword':
						if ( $this->get_option( 'lostpassword_template' ) )
							$template[] = $this->get_option( 'lostpassword_template' );
						$template[] = 'lostpassword-form.php';
						break;
					case 'resetpass':
					case 'rp':
						if ( $this->get_option( 'resetpass_template' ) )
							$template[] = $this->get_option( 'resetpass_template' );
						$template[] = 'resetpass-form.php';
						break;
					case 'register':
						if ( $this->get_option( 'register_template' ) )
							$template[] = $this->get_option( 'register_template' );
						$template[] = 'register-form.php';
						break;
					case 'login':
					default :
						if ( $this->get_option( 'login_template' ) )
							$template[] = $this->get_option( 'login_template' );
						$template[] = 'login-form.php';
				}
			}
			$this->get_template( $template );
		}
		echo $this->get_option( 'after_widget' ) . "\n";
		$output = ob_get_contents();
		ob_end_clean();
		return apply_filters_ref_array( 'tml_display', array( $output, $action, &$this ) );
	}

	/**
	 * Returns action title
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action to retrieve. Defaults to current action.
	 * @return string Title of $action
	 */
	public function get_title( $action = '' ) {
		if ( empty( $action ) )
			$action = $this->get_option( 'default_action' );

		if ( is_admin() )
			return;

		if ( is_user_logged_in() && 'login' == $action && $action == $this->get_option( 'default_action' ) ) {
			$title = sprintf( __( 'Welcome, %s', 'theme-my-login' ), wp_get_current_user()->display_name );
		} else {
			if ( $page_id = Theme_My_Login::get_page_id( $action ) ) {
				$title = get_post_field( 'post_title', $page_id );
			} else {
				switch ( $action ) {
					case 'register':
						$title = __( 'Register' );
						break;
					case 'lostpassword':
					case 'retrievepassword':
					case 'resetpass':
					case 'rp':
						$title = __( 'Lost Password' );
						break;
					case 'login':
					default:
						$title = __( 'Log In' );
				}
			}
		}
		return apply_filters( 'tml_title', $title, $action );
	}

	/**
	 * Outputs action title
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action to retieve. Defaults to current action.
	 */
	public function the_title( $action = '' ) {
		echo $this->get_title( $action );
	}

	/**
	 * Returns plugin errors
	 *
	 * @since 6.0
	 * @access public
	 */
	public function get_errors() {
		global $error;

		$theme_my_login = Theme_My_Login::get_object();

		$wp_error =& $theme_my_login->errors;

		if ( empty( $wp_error ) )
			$wp_error = new WP_Error();

		// Incase a plugin uses $error rather than the $errors object
		if ( ! empty( $error ) ) {
			$wp_error->add('error', $error);
			unset($error);
		}

		$output = '';
		if ( $this->is_active() ) {
			if ( $wp_error->get_error_code() ) {
				$errors = '';
				$messages = '';
				foreach ( $wp_error->get_error_codes() as $code ) {
					$severity = $wp_error->get_error_data( $code );
					foreach ( $wp_error->get_error_messages( $code ) as $error ) {
						if ( 'message' == $severity )
							$messages .= '    ' . $error . "<br />\n";
						else
							$errors .= '    ' . $error . "<br />\n";
					}
				}
				if ( ! empty( $errors ) )
					$output .= '<p class="error">' . apply_filters( 'login_errors', $errors ) . "</p>\n";
				if ( ! empty( $messages ) )
					$output .= '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
			}
		}
		return $output;
	}

	/**
	 * Prints plugin errors
	 *
	 * @since 6.0
	 * @access public
	 */
	public function the_errors() {
		echo $this->get_errors();
	}

	/**
	 * Returns requested action URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @return string The requested action URL
	 */
	public function get_action_url( $action = '' ) {

		$instance = $this->get_option( 'instance' );

		if ( $action == $this->get_option( 'default_action' ) ) {
			$args = array();
			if ( $instance )
				$args['instance'] = $instance;
			$url = Theme_My_Login_Common::get_current_url( $args );
		} else {
			$url = Theme_My_Login::get_page_link( $action );
		}

		// Respect FORCE_SSL_LOGIN
		if ( 'login' == $action && force_ssl_login() )
			$url = preg_replace( '|^http://|', 'https://', $url );

		return apply_filters( 'tml_action_url', $url, $action, $instance );
	}

	/**
	 * Outputs requested action URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 */
	public function the_action_url( $action = 'login' ) {
		echo esc_url( $this->get_action_url( $action ) );
	}

	/**
	 * Returns the action links
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Optionally specify which actions to include/exclude. By default, all are included.
	 */
	public function get_action_links( $args = '' ) {
		$args = wp_parse_args( $args, array(
			'login'        => true,
			'register'     => true,
			'lostpassword' => true
		) );
		
		$action_links = array();
		if ( $args['login'] && $this->get_option( 'show_log_link' ) ) {
			$action_links[] = array(
				'title' => $this->get_title( 'login' ),
				'url'   => $this->get_action_url( 'login' )
			);
		}
		if ( $args['register'] && $this->get_option( 'show_reg_link' ) && get_option( 'users_can_register' ) ) {
			$action_links[] = array(
				'title' => $this->get_title( 'register' ),
				'url'   => $this->get_action_url( 'register' )
			);
		}
		if ( $args['lostpassword'] && $this->get_option( 'show_pass_link' ) ) {
			$action_links[] = array(
				'title' => $this->get_title( 'lostpassword' ),
				'url'   => $this->get_action_url( 'lostpassword' )
			);
		}
		return apply_filters( 'tml_action_links', $action_links, $args );
	}

	/**
	 * Outputs the action links
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Optionally specify which actions to include/exclude. By default, all are included.
	 */
	public function the_action_links( $args = '' ) {
		if ( $action_links = $this->get_action_links( $args ) ) {
			echo '<ul class="tml-action-links">' . "\n";
			foreach ( (array) $action_links as $link ) {
				echo '<li><a href="' . esc_url( $link['url'] ) . '" rel="nofollow">' . esc_html( $link['title'] ) . '</a></li>' . "\n";
			}
			echo '</ul>' . "\n";
		}
	}

	/**
	 * Returns logged-in user links
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @return array Logged-in user links
	 */
	public static function get_user_links() {
		$user_links = array(
			array(
				'title' => __( 'Dashboard' ),
				'url'   => admin_url() ),
			array(
				'title' => __( 'Profile' ),
				'url'   => admin_url( 'profile.php' )
			)
		);
		return apply_filters( 'tml_user_links', $user_links );
	}

	/**
	 * Outputs logged-in user links
	 *
	 * @since 6.0
	 * @access public
	 */
	public function the_user_links() {
		echo '<ul class="tml-user-links">';
		foreach ( (array) self::get_user_links() as $link ) {
			echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['title'] ) . '</a></li>' . "\n";
		}
		echo '<li><a href="' . wp_logout_url() . '">' . self::get_title( 'logout' ) . '</a></li>' . "\n";
		echo '</ul>';
	}

	/**
	 * Displays user avatar
	 *
	 * @since 6.0
	 * @access public
	 */
	public function the_user_avatar( $size = '' ) {
		if ( empty( $size ) )
			$size = $this->get_option( 'gravatar_size', 50 );

		$current_user = wp_get_current_user();

		echo get_avatar( $current_user->ID, $size );
	}

	/**
	 * Returns template message for requested action
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @return string The requested template message
	 */
	public static function get_action_template_message( $action = '' ) {
		switch ( $action ) {
			case 'register':
				$message = __( 'Register For This Site' );
				break;
			case 'lostpassword':
				$message = __( 'Please enter your username or email address. You will receive a link to create a new password via email.' );
				break;
			case 'resetpass':
				$message = __( 'Enter your new password below.' );
				break;
			default:
				$message = '';
		}
		$message = apply_filters( 'login_message', $message );

		return apply_filters( 'tml_action_template_message', $message, $action );
	}

	/**
	 * Outputs template message for requested action
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action Action to retrieve
	 * @param string $before_message Text/HTML to add before the message
	 * @param string $after_message Text/HTML to add after the message
	 */
	public function the_action_template_message( $action = 'login', $before_message = '<p class="message">', $after_message = '</p>' ) {
		if ( $message = self::get_action_template_message( $action ) )
			echo $before_message . $message . $after_message;
	}

	/**
	 * Locates specified template
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $template_names The template(s) to locate
	 * @param bool $load If true, the template will be included if found
	 * @param array $args Array of extra variables to make available to template
	 * @return string|bool Template path if found, false if not
	 */
	public function get_template( $template_names, $load = true, $args = array() ) {

		$theme_my_login = Theme_My_Login::get_object();

		// User friendly access to this
		$template =& $this;

		// Easy access to current user
		$current_user = wp_get_current_user();

		extract( apply_filters_ref_array( 'tml_template_args', array( $args, &$this ) ) );

		if ( ! is_array( $template_names ) )
			$template_names = array( $template_names );

		if ( ! $found_template = locate_template( $template_names ) ) {
			foreach ( $template_names as $template_name ) {
				if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/templates/' . $template_name ) ) {
					$found_template = WP_PLUGIN_DIR . '/theme-my-login/templates/' . $template_name;
					break;
				}
			}
		}

		$found_template = apply_filters_ref_array( 'tml_template', array( $found_template, $template_names, &$this ) );

		if ( $load && $found_template ) {
			include( $found_template );
		}

		return $found_template;
	}

	/**
	 * Returns the proper redirect URL according to action
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action
	 * @return string The redirect URL
	 */
	public function get_redirect_url( $action = '' ) {

		$theme_my_login = Theme_My_Login::get_object();

		if ( empty( $action ) )
			$action = $this->get_option( 'default_action' );

		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';

		switch ( $action ) {
			case 'lostpassword' :
			case 'retrievepassword' :
				$url = apply_filters( 'lostpassword_redirect', ! empty( $redirect_to ) ? $redirect_to : Theme_My_Login::get_page_link( 'login', 'checkemail=confirm' ) );
				break;
			case 'register' :
				$url = apply_filters( 'registration_redirect', ! empty( $redirect_to ) ? $redirect_to : Theme_My_Login::get_page_link( 'login', 'checkemail=registered' ) );
				break;
			case 'login' :
			default :
				$url = apply_filters( 'login_redirect', ! empty( $redirect_to ) ? $redirect_to : admin_url(), $redirect_to, null );
		}
		return apply_filters( 'tml_redirect_url', $url, $action );
	}

	/**
	 * Outputs redirect URL
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $action The action
	 */
	public function the_redirect_url( $action = '' ) {
		echo esc_attr( $this->get_redirect_url( $action ) );
	}

	/**
	 * Outputs current template instance ID
	 *
	 * @since 6.0
	 * @access public
	 */
	public function the_instance() {
		if ( $this->get_option( 'instance' ) )
			echo esc_attr( $this->get_option( 'instance' ) );
	}

	/**
	 * Returns requested $value
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $value The value to retrieve
	 * @return string|bool The value if it exists, false if not
	 */
	public function get_posted_value( $value ) {
		if ( $this->is_active() && isset( $_REQUEST[$value] ) )
			return stripslashes( $_REQUEST[$value] );
		return false;
	}

	/**
	 * Outputs requested value
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string $value The value to retrieve
	 */
	public function the_posted_value( $value ) {
		echo esc_attr( $this->get_posted_value( $value ) );
	}

	/**
	 * Returns active status
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @return bool True if instance is active, false if not
	 */
	public function is_active() {
		return $this->is_active;
	}

	/**
	 * Sets active status
	 *
	 * @since 6.3
	 * @access public
	 *
	 * @param bool $active Active status
	 */
	public function set_active( $active = true ) {
		$this->is_active = $active;
	}
}
endif; // Class exists

