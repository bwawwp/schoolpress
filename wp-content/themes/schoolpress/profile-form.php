<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login profile" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'profile' ); ?>
	<?php $template->the_errors(); ?>
	<form class="form-horizontal" id="your-profile" action="<?php $template->the_action_url( 'profile' ); ?>" method="post">
		<?php wp_nonce_field( 'update-user_' . $current_user->ID ); ?>
		<div class="form-group">
			<input type="hidden" name="from" value="profile" />
			<input type="hidden" name="checkuser_id" value="<?php echo $current_user->ID; ?>" />
		</div>

		<?php if ( has_action( 'personal_options' ) ) : ?>

		<div class="panel">
			<h3 class="panel-heading"><?php _e( 'Personal Options' ); ?></h3>
			<?php do_action( 'personal_options', $profileuser ); ?>
		</div>

		<?php endif; ?>

		<?php do_action( 'profile_personal_options', $profileuser ); ?>

		<div class="panel">
			<h3 class="panel-heading"><?php _e( 'Name' ); ?></h3>
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="user_login"><?php _e( 'Username' ); ?></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $profileuser->user_login ); ?>" disabled="disabled" class="form-control" />  <p class="help-block"><?php _e( 'Your username cannot be changed.', 'theme-my-login' ); ?></p></div>
			</div>
	
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="first_name"><?php _e( 'First Name' ); ?></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ); ?>" class="form-control" /></div>
			</div>
	
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="last_name"><?php _e( 'Last Name' ); ?></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ); ?>" class="form-control" /></div>
			</div>
	
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="nickname"><?php _e( 'Nickname' ); ?>  <p class="help-block"><?php _e( '(required)' ); ?></p></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ); ?>" class="form-control" /></div>
			</div>
	
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="display_name"><?php _e( 'Display name publicly as' ); ?></label>
				<div class="col-sm-9 col-lg-9">
					<select class="form-control" name="display_name" id="display_name">
					<?php
						$public_display = array();
						$public_display['display_nickname']  = $profileuser->nickname;
						$public_display['display_username']  = $profileuser->user_login;
	
						if ( ! empty( $profileuser->first_name ) )
							$public_display['display_firstname'] = $profileuser->first_name;
	
						if ( ! empty( $profileuser->last_name ) )
							$public_display['display_lastname'] = $profileuser->last_name;
	
						if ( ! empty( $profileuser->first_name ) && ! empty( $profileuser->last_name ) ) {
							$public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
							$public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
						}
	
						if ( ! in_array( $profileuser->display_name, $public_display ) )// Only add this if it isn't duplicated elsewhere
							$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;
	
						$public_display = array_map( 'trim', $public_display );
						$public_display = array_unique( $public_display );
	
						foreach ( $public_display as $id => $item ) {
					?>
						<option <?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
					<?php
						}
					?>
					</select>
				</div>
			</div>
		</div>

	
		<div class="panel">
			<h3 class="panel-heading"><?php _e( 'Contact Info' ); ?></h3>
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="email"><?php _e( 'E-mail' ); ?>  <p class="help-block"><?php _e( '(required)' ); ?></p></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ); ?>" class="form-control" /></div>
			</div>
	
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="url"><?php _e( 'Website' ); ?></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="url" id="url" value="<?php echo esc_attr( $profileuser->user_url ); ?>" class="regular-text code" /></div>
			</div>
	
			<?php
				foreach ( _wp_get_user_contactmethods() as $name => $desc ) {
			?>
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="<?php echo $name; ?>"><?php echo apply_filters( 'user_'.$name.'_label', $desc ); ?></label>
				<div class="col-sm-9 col-lg-9"><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $profileuser->$name ); ?>" class="form-control" /></div>
			</div>
			<?php
				}
			?>
		</div>
	
		<div class="panel">
			<h3 class="panel-heading"><?php _e( 'About Yourself' ); ?></h3>
			<div class="form-group">
				<label class="col-sm-3 col-lg-3 control-label" for="description"><?php _e( 'Biographical Info' ); ?></label>
				<div class="col-sm-9 col-lg-9"><textarea name="description" id="description" class="form-control" rows="5" cols="30"><?php echo esc_html( $profileuser->description ); ?></textarea><br />
				 <p class="help-block"><?php _e( 'Share a little biographical information to fill out your profile. This may be shown publicly.' ); ?></p></div>
			</div>
	
			<?php
			$show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );
			if ( $show_password_fields ) :
			?>
			<div id="password">
				<div class="form-group">
					<label class="col-sm-3 col-lg-3 control-label" for="pass1"><?php _e( 'New Password' ); ?></label>
					<div class="col-sm-9 col-lg-9">
						<input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" class="form-control" />
						<p class="help-block"><?php _e( 'If you would like to change the password type a new one. Otherwise leave this blank.' ); ?></p>
					</div>
				</div>
				<div class="form-group">
					<div class="col-lg-9 col-lg-offset-3">
						<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" class="form-control" />  
						<p class="help-block"><?php _e( 'Type your new password again.' ); ?></p><br />
						<div id="pass-strength-result"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>
						<div class="clearfix"></div>
						<p class="help-block indicator-hint"><?php _e( 'Hint: The password should be at least seven characters long. To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).' ); ?></p>
					</div>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<?php do_action( 'show_user_profile', $profileuser ); ?>

		<div class="form-group">
			<div class="col-lg-offset-3 col-lg-10">
				<input type="hidden" name="action" value="profile" />
				<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>" />
				<input type="submit" class="btn btn-primary btn-lg" value="<?php esc_attr_e( 'Update Profile' ); ?>" name="submit" />
			</div>
		</div>
	</form>
</div>
