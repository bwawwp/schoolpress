<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<h2><?php printf( __( 'Get your own %s account in seconds' ), $current_site->site_name ); ?></h2>

<form id="setupform" method="post" action="<?php $template->the_action_url( 'register' ); ?>">
	<input type="hidden" name="action" value="register" />
	<input type="hidden" name="stage" value="validate-user-signup" />
	<?php do_action( 'signup_hidden_fields' ); ?>

	<label for="user_name<?php $template->the_instance(); ?>"><?php _e( 'Username:' ); ?></label>
	<?php if ( $errmsg = $errors->get_error_message( 'user_name' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>

	<input name="user_name" type="text" id="user_name<?php $template->the_instance(); ?>" value="<?php echo esc_attr( $user_name ); ?>" maxlength="60" /><br />
	<span class="hint"><?php _e( '(Must be at least 4 characters, letters and numbers only.)' ); ?></span>

	<label for="user_email<?php $template->the_instance(); ?>"><?php _e( 'Email&nbsp;Address:' ); ?></label>
	<?php if ( $errmsg = $errors->get_error_message( 'user_email' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>
	
	<input name="user_email" type="text" id="user_email<?php $template->the_instance(); ?>" value="<?php echo esc_attr( $user_email ); ?>" maxlength="200" /><br />
	<span class="hint"><?php _e( 'We send your registration email to this address. (Double-check your email address before continuing.)' ); ?></span>
	<?php if ( $errmsg = $errors->get_error_message( 'generic' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>

	<?php do_action( 'signup_extra_fields', $errors ); ?>

	<p>
	<?php if ( $active_signup == 'blog' ) { ?>
		<input id="signupblog<?php $template->the_instance(); ?>" type="hidden" name="signup_for" value="blog" />
	<?php } elseif ( $active_signup == 'user' ) { ?>
		<input id="signupblog<?php $template->the_instance(); ?>" type="hidden" name="signup_for" value="user" />
	<?php } else { ?>
		<input id="signupblog<?php $template->the_instance(); ?>" type="radio" name="signup_for" value="blog" <?php if ( ! isset( $_POST['signup_for'] ) || $_POST['signup_for'] == 'blog' ) { ?>checked="checked"<?php } ?> />
		<label class="checkbox" for="signupblog"><?php _e( 'Gimme a site!' ); ?></label>
		<br />
		<input id="signupuser<?php $template->the_instance(); ?>" type="radio" name="signup_for" value="user" <?php if ( isset( $_POST['signup_for'] ) && $_POST['signup_for'] == 'user' ) { ?>checked="checked"<?php } ?> />
		<label class="checkbox" for="signupuser"><?php _e( 'Just a username, please.' ); ?></label>
	<?php } ?>
	</p>

	<p class="submit"><input type="submit" name="submit" class="submit" value="<?php esc_attr_e( 'Next' ); ?>" /></p>
</form>
<?php $template->the_action_links( array( 'register' => false ) ); ?>
