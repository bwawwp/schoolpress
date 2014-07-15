<?php
/**
 * Holds the Theme My Login Admin class
 *
 * @package Theme_My_Login
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Admin' ) ) :
/**
 * Theme My Login Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login';

	/**
	 * Returns singleton instance
	 *
	 * @since 6.3
	 * @access public
	 * @return Theme_My_Login
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
		return Theme_My_Login::default_options();
	}

	/**
	 * Loads object
	 *
	 * @since 6.3
	 * @access public
	 */
	protected function load() {
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ), 8 );

		register_uninstall_hook( WP_PLUGIN_DIR . '/theme-my-login/theme-my-login.php', array( 'Theme_My_Login_Admin', 'uninstall' ) );
	}

	/**
	 * Builds plugin admin menu and pages
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		add_menu_page(
			__( 'Theme My Login Settings', 'theme-my-login' ),
			__( 'TML', 'theme-my-login' ),
			'manage_options',
			'theme_my_login',
			array( 'Theme_My_Login_Admin', 'settings_page' )
		);

		add_submenu_page(
			'theme_my_login',
			__( 'General', 'theme-my-login' ),
			__( 'General', 'theme-my-login' ),
			'manage_options',
			'theme_my_login',
			array( 'Theme_My_Login_Admin', 'settings_page' )
		);

		// General section
		add_settings_section( 'general',    __( 'General', 'theme-my-login'    ), '__return_false', $this->options_key );
		add_settings_section( 'modules',    __( 'Modules', 'theme-my-login'    ), '__return_false', $this->options_key );

		// General fields
		add_settings_field( 'enable_css',  __( 'Stylesheet',   'theme-my-login' ), array( &$this, 'settings_field_enable_css'  ), $this->options_key, 'general' );
		add_settings_field( 'email_login', __( 'E-mail Login', 'theme-my-login' ), array( &$this, 'settings_field_email_login' ), $this->options_key, 'general' );
		add_settings_field( 'modules',     __( 'Modules',      'theme-my-login' ), array( &$this, 'settings_field_modules'     ), $this->options_key, 'modules' );
	}

	/**
	 * Registers TML settings
	 *
	 * This is used because register_setting() isn't available until the "admin_init" hook.
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_init() {
		register_setting( 'theme_my_login', 'theme_my_login',  array( &$this, 'save_settings' ) );

		if ( version_compare( $this->get_option( 'version', 0 ), Theme_My_Login::version, '<' ) )
			$this->install();
	}

	/**
	 * Renders the settings page
	 *
	 * @since 6.0
	 * @access public
	 */
	public static function settings_page( $args = '' ) {
		extract( wp_parse_args( $args, array(
			'title'       => __( 'Theme My Login Settings', 'theme-my-login' ),
			'options_key' => 'theme_my_login'
		) ) );
		?>
		<div id="<?php echo $options_key; ?>" class="wrap">
			<?php screen_icon( 'options-general' ); ?>
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( $options_key );
					do_settings_sections( $options_key );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders Stylesheet settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_enable_css() {
		?>
		<input name="theme_my_login[enable_css]" type="checkbox" id="theme_my_login_enable_css" value="1"<?php checked( 1, $this->get_option( 'enable_css' ) ); ?> />
		<label for="theme_my_login_enable_css"><?php _e( 'Enable "theme-my-login.css"', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'In order to keep changes between upgrades, you can store your customized "theme-my-login.css" in your current theme directory.', 'theme-my-login' ); ?></p>
        <?php
	}

	/**
	 * Renders E-mail Login settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_email_login() {
		?>
		<input name="theme_my_login[email_login]" type="checkbox" id="theme_my_login_email_login" value="1"<?php checked( 1, $this->get_option( 'email_login' ) ); ?> />
		<label for="theme_my_login_email_login"><?php _e( 'Enable e-mail address login', 'theme-my-login' ); ?></label>
		<p class="description"><?php _e( 'Allows users to login using their e-mail address in place of their username.', 'theme-my-login' ); ?></p>
    	<?php
	}

	/**
	 * Renders Modules settings field
	 *
	 * @since 6.3
	 * @access public
	 */
	public function settings_field_modules() {
		foreach ( get_plugins( '/theme-my-login/modules' ) as $path => $data ) {
			$id = sanitize_key( $data['Name'] );
		?>
		<input name="theme_my_login[active_modules][]" type="checkbox" id="theme_my_login_active_modules_<?php echo $id; ?>" value="<?php echo $path; ?>"<?php checked( in_array( $path, (array) $this->get_option( 'active_modules' ) ) ); ?> />
		<label for="theme_my_login_active_modules_<?php echo $id; ?>"><?php printf( __( 'Enable %s', 'theme-my-login' ), $data['Name'] ); ?></label><br />
		<?php if ( $data['Description'] ) : ?>
		<p class="description"><?php echo $data['Description']; ?></p>
		<?php endif;
		}
	}

	/**
	 * Sanitizes TML settings
	 *
	 * This is the callback for register_setting()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param string|array $settings Settings passed in from filter
	 * @return string|array Sanitized settings
	 */
	public function save_settings( $settings ) {
		$settings['enable_css']     = ! empty( $settings['enable_css']   );
		$settings['email_login']    = ! empty( $settings['email_login']  );
		$settings['active_modules'] = isset( $settings['active_modules'] ) ? (array) $settings['active_modules'] : array();

		// If we have modules to activate
		if ( $activate = array_diff( $settings['active_modules'], $this->get_option( 'active_modules', array() ) ) ) {
			foreach ( $activate as $module ) {
				if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
					include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
				do_action( 'tml_activate_' . $module );
			}
		}

		// If we have modules to deactivate
		if ( $deactivate = array_diff( $this->get_option( 'active_modules', array() ), $settings['active_modules'] ) ) {
			foreach ( $deactivate as $module ) {
				do_action( 'tml_deactivate_' . $module );
			}
		}

		$settings = wp_parse_args( $settings, $this->get_options() );

		return $settings;
	}

	/**
	 * Installs TML
	 *
	 * @since 6.0
	 * @access public
	 */
	public function install() {
		global $wpdb;

		// Current version
		$version = $this->get_option( 'version', Theme_My_Login::version );

		// Check if legacy page exists
		if ( $page_id = $this->get_option( 'page_id' ) ) {
			$page = get_page( $page_id );
		} else {
			$page = get_page_by_title( 'Login' );
		}

		// 4.4 upgrade
		if ( version_compare( $version, '4.4', '<' ) ) {
			remove_role( 'denied' );
		}

		// 6.0 upgrade
		if ( version_compare( $version, '6.0', '<' ) ) {
			// Replace shortcode
			if ( $page ) {
				$page->post_content = str_replace( '[theme-my-login-page]', '[theme-my-login]', $page->post_content );
				wp_update_post( $page );
			}
		}

		// 6.3 upgrade
		if ( version_compare( $version, '6.3.3', '<' ) ) {
			// Delete obsolete options
			$this->delete_option( 'page_id'     );
			$this->delete_option( 'show_page'   );
			$this->delete_option( 'initial_nag' );
			$this->delete_option( 'permalinks'  );
			$this->delete_option( 'flush_rules' );

			// Move options to their own rows
			foreach ( $this->get_options() as $key => $value ) {
				if ( in_array( $key, array( 'active_modules' ) ) )
					continue;

				if ( ! is_array( $value ) )
					continue;

				update_option( "theme_my_login_{$key}", $value );

				$this->delete_option( $key );
			}

			// Maybe create login page?
			if ( $page ) {
				// Make sure the page is not in the trash
				if ( 'trash' == $page->post_status )
					wp_untrash_post( $page->ID );

				update_post_meta( $page->ID, '_tml_action', 'login' );
			}
		}

		// 6.4 upgrade
		if ( version_compare( $version, '6.3.7', '<' ) ) {
			// Convert TML pages to regular pages
			$wpdb->update( $wpdb->posts, array( 'post_type' => 'page' ), array( 'post_type' => 'tml_page' ) );

			// Get rid of stale rewrite rules
			flush_rewrite_rules( false );
		}

		// Setup default pages
		foreach ( Theme_My_Login::default_pages() as $action => $title ) {
			if ( ! $page_id = Theme_My_Login::get_page_id( $action ) ) {
				$page_id = wp_insert_post( array(
					'post_title'     => $title,
					'post_name'      => $action,
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_content'   => '[theme-my-login]',
					'comment_status' => 'closed',
					'ping_status'    => 'closed'
				) );
				update_post_meta( $page_id, '_tml_action', $action );
			}
		}

		// Activate modules
		foreach ( $this->get_option( 'active_modules', array() ) as $module ) {
			if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
				include_once( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );
			do_action( 'tml_activate_' . $module );
		}

		$this->set_option( 'version', Theme_My_Login::version );
		$this->save_options();
	}

	/**
	 * Wrapper for multisite uninstallation
	 *
	 * @since 6.1
	 * @access public
	 */
	public static function uninstall() {
		global $wpdb;

		if ( is_multisite() ) {
			if ( isset( $_GET['networkwide'] ) && ( $_GET['networkwide'] == 1 ) ) {
				$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::_uninstall();
				}
				restore_current_blog();
				return;
			}	
		}
		self::_uninstall();
	}

	/**
	 * Uninstalls TML
	 *
	 * @since 6.0
	 * @access protected
	 */
	protected static function _uninstall() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// Run module uninstall hooks
		$modules = get_plugins( '/theme-my-login/modules' );
		foreach ( array_keys( $modules ) as $module ) {
			$module = plugin_basename( trim( $module ) );

			if ( file_exists( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module ) )
				@include ( WP_PLUGIN_DIR . '/theme-my-login/modules/' . $module );

			do_action( 'tml_uninstall_' . $module );
		}

		// Get pages
		$pages = get_posts( array(
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => '_tml_action',
			'posts_per_page' => -1
		) );

		// Delete pages
		foreach ( $pages as $page ) {
			wp_delete_post( $page->ID, true );
		}

		// Delete options
		delete_option( 'theme_my_login' );
		delete_option( 'widget_theme-my-login' );
	}
}
endif; // Class exists

