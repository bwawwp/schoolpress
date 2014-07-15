<?php
/**
 * Holds the Theme My Login widget class
 *
 * @package Theme_My_Login
 */

if ( ! class_exists( 'Theme_My_Login_Widget' ) ) :
/*
 * Theme My Login widget class
 *
 * @since 6.0
 */
class Theme_My_Login_Widget extends WP_Widget {
	/**
	 * Constructor
	 *
	 * @since 6.0
	 * @access public
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'widget_theme_my_login',
			'description' => __( 'A login form for your blog.', 'theme-my-login' )
		);
		$this->WP_Widget( 'theme-my-login', __( 'Theme My Login', 'theme-my-login' ), $widget_options );
	}

	/**
	 * Displays the widget
	 *
	 * @since 6.0
	 * @access public
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	public function widget( $args, $instance ) {

		$theme_my_login = Theme_My_Login::get_object();

		$instance = wp_parse_args( $instance, array(
			'default_action'      => 'login',
			'logged_in_widget'    => true,
			'logged_out_widget'   => true,
			'show_title'          => true,
			'show_log_link'       => true,
			'show_reg_link'       => true,
			'show_pass_link'      => true,
			'show_gravatar'       => true,
			'gravatar_size'       => 50
		) );

		// Show if logged in?
		if ( is_user_logged_in() && ! $instance['logged_in_widget'] )
			return;

		// Show if logged out?
		if ( ! is_user_logged_in() && ! $instance['logged_out_widget'] )
			return;

		$args = array_merge( $args, $instance );

		echo $theme_my_login->shortcode( $args );
	}

	/**
	* Updates the widget
	*
	* @since 6.0
	* @access public
	*/
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['default_action']      = in_array( $new_instance['default_action'], array( 'login', 'register', 'lostpassword' ) ) ? $new_instance['default_action'] : 'login';
		$instance['logged_in_widget']    = ! empty( $new_instance['logged_in_widget'] );
		$instance['logged_out_widget']   = ! empty( $new_instance['logged_out_widget'] );
		$instance['show_title']          = ! empty( $new_instance['show_title'] );
		$instance['show_log_link']       = ! empty( $new_instance['show_log_link'] );
		$instance['show_reg_link']       = ! empty( $new_instance['show_reg_link'] );
		$instance['show_pass_link']      = ! empty( $new_instance['show_pass_link'] );
		$instance['show_gravatar']       = ! empty( $new_instance['show_gravatar'] );
		$instance['gravatar_size']       = absint( $new_instance['gravatar_size'] );
		return $instance;
	}

	/**
	* Displays the widget admin form
	*
	* @since 6.0
	* @access public
	*/
	public function form( $instance ) {
		$defaults = array(
			'default_action'      => 'login',
			'logged_in_widget'    => 1,
			'logged_out_widget'   => 1,
			'show_title'          => 1,
			'show_log_link'       => 1,
			'show_reg_link'       => 1,
			'show_pass_link'      => 1,
			'show_gravatar'       => 1,
			'gravatar_size'       => 50,
			'register_widget'     => 1,
			'lostpassword_widget' => 1
		);
		$instance = wp_parse_args( $instance, $defaults );

		$actions = array(
			'login'        => __( 'Login',         'theme-my-login' ),
			'register'     => __( 'Register',      'theme-my-login' ),
			'lostpassword' => __( 'Lost Password', 'theme-my-login' )
		);

		echo '<p>' . __( 'Default Action', 'theme-my-login' ) . '<br /><select name="' . $this->get_field_name( 'default_action' ) . '" id="' . $this->get_field_id( 'default_action' ) . '">';
		foreach ( $actions as $action => $title ) {
			$is_selected = ( $instance['default_action'] == $action ) ? ' selected="selected"' : '';
			echo '<option value="' . $action . '"' . $is_selected . '>' . $title . '</option>';
		}
		echo '</select></p>' . "\n";

		$is_checked = ( empty( $instance['logged_in_widget'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'logged_in_widget' ) . '" type="checkbox" id="' . $this->get_field_id( 'logged_in_widget' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'logged_in_widget' ) . '">' . __( 'Show When Logged In', 'theme-my-login' ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['logged_out_widget'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'logged_out_widget' ) . '" type="checkbox" id="' . $this->get_field_id( 'logged_out_widget' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'logged_out_widget' ) . '">' . __( 'Show When Logged Out', 'theme-my-login' ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_title'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_title' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_title' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_title' ) . '">' . __( 'Show Title', 'theme-my-login' ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_log_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_log_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_log_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_log_link' ) . '">' . __( 'Show Login Link', 'theme-my-login' ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_reg_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_reg_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_reg_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_reg_link' ) . '">' . __( 'Show Register Link', 'theme-my-login' ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_pass_link'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_pass_link' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_pass_link' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_pass_link' ) . '">' . __( 'Show Lost Password Link', 'theme-my-login' ) . '</label></p>' . "\n";

		$is_checked = ( empty( $instance['show_gravatar'] ) ) ? '' : 'checked="checked" ';
		echo '<p><input name="' . $this->get_field_name( 'show_gravatar' ) . '" type="checkbox" id="' . $this->get_field_id( 'show_gravatar' ) . '" value="1" ' . $is_checked . '/> <label for="' . $this->get_field_id( 'show_gravatar' ) . '">' . __( 'Show Gravatar', 'theme-my-login' ) . '</label></p>' . "\n";

		echo '<p>' . __( 'Gravatar Size', 'theme-my-login' ) . ': <input name="' . $this->get_field_name( 'gravatar_size' ) . '" type="text" id="' . $this->get_field_id( 'gravatar_size' ) . '" value="' . $instance['gravatar_size'] . '" size="3" /> <label for="' . $this->get_field_id( 'gravatar_size' ) . '"></label></p>' . "\n";
	}
}
endif; // Class exists

