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
	<div class="message <?php echo esc_attr($sp_msgt);?>"><?php echo $sp_msg;?></div>
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