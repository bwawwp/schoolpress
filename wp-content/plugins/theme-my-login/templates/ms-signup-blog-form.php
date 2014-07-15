<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<form id="setupform" method="post" action="<?php $template->the_action_url( 'register' ); ?>">
	<input type="hidden" name="action" value="register" />
	<input type="hidden" name="stage" value="validate-blog-signup" />
	<input type="hidden" name="user_name" value="<?php echo esc_attr( $user_name ); ?>" />
	<input type="hidden" name="user_email" value="<?php echo esc_attr( $user_email ); ?>" />
	<?php do_action( 'signup_hidden_fields' ); ?>

	<?php if ( ! is_subdomain_install() ) { ?>
	<label for="blogname<?php $template->the_instance(); ?>"><?php _e( 'Site Name:' ); ?></label>
	<?php } else { ?>
	<label for="blogname<?php $template->the_instance(); ?>"><?php _e( 'Site Domain:' ); ?></label>
	<?php } ?>

	<?php if ( $errmsg = $errors->get_error_message( 'blogname' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>

	<?php if ( ! is_subdomain_install() ) { ?>
		<span class="prefix_address"><?php echo $current_site->domain . $current_site->path; ?></span>
		<input name="blogname" type="text" id="blogname<?php $template->the_instance(); ?>" value="<?php echo esc_attr( $blogname ); ?>" maxlength="60" /><br />
	<?php } else { ?>
		<input name="blogname" type="text" id="blogname<?php $template->the_instance(); ?>" value="<?php echo esc_attr( $blogname ); ?>" maxlength="60" />
		<span class="suffix_address"><?php echo ( $site_domain = preg_replace( '|^www\.|', '', $current_site->domain ) ); ?></span><br />
	<?php } ?>

	<?php if ( ! is_user_logged_in() ) {
		if ( ! is_subdomain_install() )
			$site = $current_site->domain . $current_site->path . __( 'sitename' );
		else
			$site = __( 'domain' ) . '.' . $site_domain . $current_site->path;
		echo '<p>(<strong>' . sprintf( __( 'Your address will be %s.' ), $site ) . '</strong>) ' . __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed, so choose carefully!' ) . '</p>';
	} ?>

	<label for="blog_title<?php $template->the_instance(); ?>"><?php _e( 'Site Title:' ); ?></label>
	<?php if ( $errmsg = $errors->get_error_message( 'blog_title' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>
	<input name="blog_title" type="text" id="blog_title<?php $template->the_instance(); ?>" value="<?php echo esc_attr( $blog_title ); ?>" />

	<div id="privacy">
		<p class="privacy-intro">
			<label for="blog_public_on<?php $template->the_instance(); ?>"><?php _e( 'Privacy:' ); ?></label>
			<?php _e( 'Allow search engines to index this site.' ); ?>
			<br style="clear:both" />
			<label class="checkbox" for="blog_public_on<?php $template->the_instance(); ?>">
				<input type="radio" id="blog_public_on<?php $template->the_instance(); ?>" name="blog_public" value="1" <?php if ( ! isset( $_POST['blog_public'] ) || $_POST['blog_public'] == '1' ) { ?>checked="checked"<?php } ?> />
				<strong><?php _e( 'Yes' ); ?></strong>
			</label>
			<label class="checkbox" for="blog_public_off<?php $template->the_instance(); ?>">
				<input type="radio" id="blog_public_off<?php $template->the_instance(); ?>" name="blog_public" value="0" <?php if ( isset( $_POST['blog_public'] ) && $_POST['blog_public'] == '0' ) { ?>checked="checked"<?php } ?> />
				<strong><?php _e( 'No' ); ?></strong>
			</label>
		</p>
	</div>

	<?php do_action( 'signup_blogform', $errors ); ?>

	<p class="submit"><input type="submit" name="submit" class="submit" value="<?php esc_attr_e( 'Signup' ); ?>" /></p>
</form>
<?php $template->the_action_links( array( 'register' => false ) ); ?>
