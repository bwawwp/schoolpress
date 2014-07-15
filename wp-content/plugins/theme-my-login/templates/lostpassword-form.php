<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'lostpassword' ); ?>
	<?php $template->the_errors(); ?>
	<form name="lostpasswordform" id="lostpasswordform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'lostpassword' ); ?>" method="post">
		<p>
			<label for="user_login<?php $template->the_instance(); ?>"><?php _e( 'Username or E-mail:' ); ?></label>
			<input type="text" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" />
		</p>

		<?php do_action( 'lostpassword_form' ); ?>

		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Get New Password' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'lostpassword' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="action" value="lostpassword" />
		</p>
	</form>
	<?php $template->the_action_links( array( 'lostpassword' => false ) ); ?>
</div>
