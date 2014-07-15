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
			sp_showMessage("Invites sent.", "success");		
		}		
	}
		
	//form
?>
<form action="" method="post">
	<input type="hidden" name="invite" value="1" />
	<p>Enter email addresses to invite students to your class. Enter one email address per line.</p>
	
	<textarea name="invite_emails" rows="5" cols="50"><?php echo esc_textarea($invite_emails);?></textarea>
	<br /><br />
	
	<p>
		<input type="submit" value="Send Invites" />
	</p>
</form>
