<?php
	//process invites
	if(!empty($_REQUEST['invite_emails']))
		$invite_emails = $_REQUEST['invite_emails'];
	else
		$invite_emails = "";
	
	if(!empty($invite_emails))
	{
		global $post;
		$class = new SPClass($post->ID);
		$invites = $class->addInvites($invite_emails);		//return array(worked?, emails)
		
		//check for errors
		if(is_array($invites))
		{
			//errror
			$invite_emails = implode("\n", $invites);
			sp_showMessage("There was an error adding the inviting the following emails: " . implode(", ", $invites), "error");			
		}
		else
		{
			$invite_emails = "";
			sp_showMessage("Invites sent. <a href=\"" . get_permalink($post->ID) . "\">Return to Class</a>", "success");		
		}		
	}
		
	//form
?>
<form class="form" action="" method="post">
	<input type="hidden" name="invite" value="1" />
	<p class="pmpro_message pmpro_alert">Enter one email address per line to invite students to your class.</p>
	<div class="form-group">	
		<textarea class="form-control" rows="3" name="invite_emails" rows="5" cols="50"><?php echo esc_textarea($invite_emails);?></textarea>
	</div>
	<div class="form-group">
		<input class="btn btn-info" type="submit" value="Send Invites" />
		<a class="btn btn-link" href="<?php echo get_permalink($post->ID); ?>">Cancel</a>
	</div>
</form>
