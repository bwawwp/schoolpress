<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php if ( $template->options['show_gravatar'] ) : ?>
	<div class="tml-user-avatar"><?php $template->the_user_avatar(); ?></div>
	<?php endif; ?>

	<?php $template->the_user_links(); ?>
	
	<?php do_action( 'tml_user_panel' ); ?>
</div>
