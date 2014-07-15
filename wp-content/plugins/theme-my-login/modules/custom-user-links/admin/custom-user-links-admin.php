<?php
/**
 * Holds Theme My Login Custom User Links Admin class
 *
 * @package Theme_My_Login
 * @subpackage Theme_My_Login_Custom_User_Links
 * @since 6.0
 */

if ( ! class_exists( 'Theme_My_Login_Custom_User_Links_Admin' ) ) :
/**
 * Theme My Login Custom User Links Admin class
 *
 * @since 6.0
 */
class Theme_My_Login_Custom_User_Links_Admin extends Theme_My_Login_Abstract {
	/**
	 * Holds options key
	 *
	 * @since 6.3
	 * @access protected
	 * @var string
	 */
	protected $options_key = 'theme_my_login_user_links';

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
		add_action( 'tml_uninstall_custom-user-links/custom-user-links.php', array( &$this, 'uninstall' ) );
	
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );

		add_action( 'load-tml_page_theme_my_login_user_links', array( &$this, 'load_settings_page' ) );

		add_action( 'wp_ajax_add-user-link',    array( &$this, 'add_user_link_ajax' ) );
		add_action( 'wp_ajax_delete-user-link', array( &$this, 'delete_user_link_ajax' ) );
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
		return Theme_My_login_Custom_User_Links::default_options();
	}

	/**
	 * Uninstalls the module
	 *
	 * Callback for "tml_uninstall_custom-user-links/custom-user-links.php" hook in method Theme_My_Login_Admin::uninstall()
	 *
	 * @see Theme_My_Login_Admin::uninstall()
	 * @since 6.3
	 * @access public
	 */
	public function uninstall() {
		delete_option( $this->options_key );
	}

	/**
	 * Adds "User Links" to Theme My Login menu
	 *
	 * @since 6.0
	 * @access public
	 */
	public function admin_menu() {
		global $wp_roles;

		add_submenu_page(
			'theme_my_login',
			__( 'Theme My Login Custom User Links Settings', 'theme-my-login' ),
			__( 'User Links', 'theme-my-login' ),
			'manage_options',
			$this->options_key,
			array( &$this, 'settings_page' )
		);

		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' != $role )
				add_meta_box( $role, translate_user_role( $role_name ), array( &$this, 'user_links_meta_box' ), 'tml_page_' . $this->options_key, 'normal' );
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
		wp_enqueue_style(  'tml-custom-user-links-admin', plugins_url( 'theme-my-login/modules/custom-user-links/admin/css/custom-user-links-admin.css' ) );
		wp_enqueue_script( 'tml-custom-user-links-admin', plugins_url( 'theme-my-login/modules/custom-user-links/admin/js/custom-user-links-admin.js' ), array( 'wp-lists', 'postbox', 'jquery-ui-sortable' ) );
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
			<h2><?php echo esc_html_e( 'Theme My Login Custom User Links Settings', 'theme-my-login' ); ?></h2>
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
	 * Outputs user links admin menu for specified role
	 *
	 * Callback for add_settings_section()
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Arguments passed in by add_settings_section()
	 */
	public function user_links_meta_box( $object, $box ) {
		$role  = $box['id'];
		$links = $this->get_option( $role, array() );
		?>
	<div id="ajax-response-<?php echo $role; ?>" class="ajax-response"></div>

	<table id="<?php echo $role; ?>-link-table"<?php if ( empty( $links ) ) echo ' style="display: none;"'; ?> class="sortable user-links">
		<thead>
		<tr>
			<th class="left"><?php _e( 'Title' ); ?></th>
			<th><?php _e( 'URL' ); ?></th>
			<th></th>
		</tr>
		</thead>
		<tbody id="<?php echo $role; ?>-link-list" class="list:user-link" data-wp-lists="list:user-link"><?php
		if ( empty( $links ) ) {
			echo '<tr><td></td></tr>';
		} else {
			$count = 0;
			foreach ( $links as $key => $link ) {
				$link['id'] = $key;
				echo self::get_link_row( $link, $role );
			}
		} ?>
		</tbody>
	</table>

	<table id="new-<?php echo $role; ?>-link" class="new-link">
	<tbody>
	<tr>
		<td class="left"><input id="new_user_link[<?php echo $role; ?>][title]" name="new_user_link[<?php echo $role; ?>][title]" type="text" size="20" /></td>
		<td class="center"><input id="new_user_link[<?php echo $role; ?>][url]" name="new_user_link[<?php echo $role; ?>][url]" type="text" size="20" /></td>
		<td class="submit">
			<?php submit_button( __( 'Add Link' ), "add:$role-link-list:new-$role-link", "add_new_user_link[$role]", false, array( 'id' => "new-$role-link-submit", 'data-wp-lists' => "add:$role-link-list:new-$role-link" ) ); ?>
			<?php wp_nonce_field( 'add-user-link', '_ajax_nonce-add-user-link', false ); ?>
		</td>
	</tr>
	</tbody>
	</table>
<?php
	}

	/**
	 * Outputs a link row to the table
	 *
	 * @since 6.0
	 * @access private
	 *
	 * @param array $link Link data
	 * @param string $role Name of user role
	 * @return sring Link row
	 */
	private static function get_link_row( $link, $role ) {
		$r = '';

		$delete_nonce = wp_create_nonce( 'delete-user-link_' . $link['id'] );
		$update_nonce = wp_create_nonce( 'add-user-link' );

		$r .= "\n\t\t<tr id='$role-link-{$link['id']}'>";
		$r .= "\n\t\t\t<td class='left'><label class='screen-reader-text' for='user_links[$role][{$link['id']}][title]'>" . __( 'Title' ) . "</label><input name='user_links[$role][{$link['id']}][title]' id='user_links[$role][{$link['id']}][title]' type='text' size='20' value='{$link['title']}' /></td>";
		$r .= "\n\t\t\t<td class='center'><label class='screen-reader-text' for='user_links[$role][{$link['id']}][url]'>" . __( 'URL' ) . "</label><input name='user_links[$role][{$link['id']}][url]' id='user_links[$role][{$link['id']}][url]' type='text' size='20' value='{$link['url']}' /></td>";
		$r .= "\n\t\t\t<td class='submit'>";
		$r .= "\n\t\t\t\t";
		$r .= get_submit_button( __( 'Delete' ), "delete:$role-link-list:$role-link-{$link['id']}::_ajax_nonce=$delete_nonce deletelink", "deletelink[{$link['id']}]", false, array( 'data-wp-lists' => "delete:$role-link-list:$role-link-{$link['id']}::_ajax_nonce=$delete_nonce" ) );
		$r .= "\n\t\t\t\t";
		$r .= get_submit_button( __( 'Update' ), "add:$role-link-list:$role-link-{$link['id']}::_ajax_nonce-add-user-link=$update_nonce updatelink", "$role-link-{$link['id']}-submit", false, array( 'data-wp-lists' => "add:$role-link-list:$role-link-{$link['id']}::_ajax_nonce-add-user-link=$update_nonce" ) );
		$r .= "\n\t\t\t\t";
		$r .= wp_nonce_field( 'change-user-link', '_ajax_nonce', false, false );
		$r .= "\n\t\t\t</td>";
		$r .= "\n\t\t</tr>";
		return $r;
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
		global $wp_roles;

		// Bail-out if doing AJAX because it has it's own saving routine
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
			return $settings;

		foreach ( $wp_roles->get_names() as $role => $role_name ) {
			if ( 'pending' == $role )
				continue;

			$settings[$role] = array();

			// Handle updating/deleting of links
			if ( ! empty( $_POST['user_links'] ) && ! empty( $_POST['user_links'][$role] ) ) {
				foreach ( (array) $_POST['user_links'][$role] as $key => $link ) {
					$clean_title = wp_kses( $link['title'], null );
					$clean_url   = wp_kses( $link['url'],   null );
					if ( ! empty( $clean_title ) && ! empty( $clean_url ) && ! isset( $_POST['delete_user_link'][$role][$key] ) ) {
						$settings[$role][] = array(
							'title' => $clean_title,
							'url'   => $clean_url
						);
					}
				}
			}

			// Handle new links
			if ( ! empty( $_POST['new_user_link'] ) && ! empty( $_POST['new_user_link'][$role] ) ) {
				$clean_title = wp_kses( $_POST['new_user_link'][$role]['title'], null );
				$clean_url   = wp_kses( $_POST['new_user_link'][$role]['url'],   null );
				if ( ! empty( $clean_title ) && ! empty( $clean_url ) ) {
					$settings[$role][] = array(
						'title' => $clean_title,
						'url'   => $clean_url
					);
				}
			}
		}

		return $settings;
	}

	/**
	 * AJAX handler for adding/updating a link
	 *
	 * Callback for "wp_ajax_add-user-link" hook in file "wp-admin/admin-ajax.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function add_user_link_ajax() {
		if ( ! current_user_can( 'manage_options' ) )
			die( '-1' );

		check_ajax_referer( 'add-user-link', '_ajax_nonce-add-user-link' );

		if ( isset( $_POST['new_user_link'] ) ) {
			foreach ( $_POST['new_user_link'] as $user_role => $link ) {
				if ( is_array( $link ) && ! empty( $link ) ) {
					$clean_title = wp_kses( $link['title'], null );
					$clean_url   = wp_kses( $link['url'],   null );

					if ( empty( $clean_title ) || empty( $clean_url ) )
						wp_die( -1 );

					$links = $this->get_option( $user_role );

					$links[] = array(
						'title' => $clean_title,
						'url'   => $clean_url
					);

					$this->set_option( $user_role, $links );

					$link_row       = end( $links );
					$link_row['id'] = key( $links );

					$x = new WP_Ajax_Response( array(
						'what' => $user_role . '-link',
						'id' => $link_row['id'],
						'data' => self::get_link_row( $link_row, $user_role ),
						'position' => 1,
						'supplemental' => compact( 'user_role' )
					) );
				}
			}
		} else {
			foreach ( $_POST['user_links'] as $user_role => $link ) {
				$id = key( $link );

				$clean_title = wp_kses( $link[$id]['title'], null );
				$clean_url   = wp_kses( $link[$id]['url'],   null );

				if ( empty( $clean_title ) || empty( $clean_url ) )
					wp_die( -1 );

				if ( ! $link = $this->get_option( array( $user_role, $id ) ) )
					wp_die( 0 );

				$link = array(
					'title' => $clean_title,
					'url'   => $clean_url
				);

				$this->set_option( array( $user_role, $id ), $link );

				$link['id'] = $id;

				$x = new WP_Ajax_Response( array(
					'what' => $user_role . '-link',
					'id' => $id,
					'old_id' => $id,
					'data' => self::get_link_row( $link, $user_role ),
					'position' => 0,
					'supplemental' => compact( 'user_role' )
				) );
			}
		}
		$this->save_options();

		$x->send();
	}

	/**
	 * AJAX handler for deleting a link
	 *
	 * Callback for "wp_ajax_delete-user-link" hook in file "wp-admin/admin-ajax.php"
	 *
	 * @since 6.0
	 * @access public
	 */
	public function delete_user_link_ajax() {
		if ( ! current_user_can( 'manage_options' ) )
			wp_die( -1 );

		$user_role = isset( $_POST['user_role'] ) ? $_POST['user_role'] : '';
		if ( empty( $user_role ) )
			wp_die( -1 );

		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;

		check_ajax_referer( "delete-user-link_$id" );

		if ( $this->get_option( array( $user_role, $id ) ) ) {
			$this->delete_option( array( $user_role, $id ) );
			$this->save_options();
			wp_die( 1 );
		}
		wp_die( 0 );
	}
}

Theme_My_Login_Custom_User_Links_Admin::get_object();

endif;

