<?php
/*
	Helper functions for SchoolPress.
*/

/*
	Set the sp_msg and sp_msgt globals used to convey error messages/etc.
*/
function sp_setMessage($msg, $msgt = "")
{
	global $sp_msg, $sp_msgt;
	$sp_msg = $msg;
	$sp_msgt = $msgt;
}

/*
	Show the sp_msg value in a div.
*/
function sp_showMessage($msg = NULL, $msgt = NULL)
{		
	global $sp_msg, $sp_msgt;

	if(!empty($msg) || !empty($msgt))
		sp_setMessage($msg, $msgt);
		
	if(!empty($sp_msg))
	{
	?>
	<div class="pmpro_message pmpro_<?php echo esc_attr($sp_msgt);?>"><?php echo $sp_msg;?></div>
	<?php
	}
}

/*
	Convert a string of emails (e.g. "jason@strangerstudios.com, jason@paidmembershipspro.com") into an array.
*/
function sp_convertEmailStringToArray($emails)
{
	//swap commas and semi-colons for new lines
	$emails = str_replace(array(",", ";"), "\n", $emails);
	
	//convert to array
	$emails = explode("\n", $emails);
	
	//remove trailing spaces and make it all lowercase
	$remails = array();
	foreach($emails as $email)
		$remails[] = strtolower(trim($email));
		
	//remove any dupes
	$remails = array_unique($remails);
	
	return $remails;
}

/**
 * From PMPro bbPress: https://github.com/strangerstudios/pmpro-bbpress/blob/master/pmpro-bbpress.php#L122
 * Function to tell if the current forum, topic, or reply is a subpost of the forum_id passed.
 * If no forum_id is passed, it will return true if it is any forum, topic, or reply.
 */
if(!function_exists('pmpro_bbp_is_forum'))
{
	function pmpro_bbp_is_forum( $forum_id = NULL ) {
		global $post;

		if(bbp_is_forum($post->ID))
		{		
			if(!empty($forum_id) && $post->ID == $forum_id)
				return true;
			elseif(empty($forum_id))
				return true;
			else
				return false;
		}
		elseif(bbp_is_topic($post->ID))
		{		
			if(!empty($forum_id) && $post->post_parent == $forum_id)
				return true;
			elseif(empty($forum_id))
				return true;
			else
				return false;
		}
		elseif(bbp_is_reply($post->ID))
		{		
			if(!empty($forum_id) && in_array($forum_id, $post->ancestors))
				return true;
			elseif(empty($forum_id))
				return true;
			else
				return false;
		}
		else
			return false;
	}
}