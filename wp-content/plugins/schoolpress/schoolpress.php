<?php
/*
Plugin Name: SchoolPress
Plugin URI: http://schoolpress.me
Description: Core SchoolPress Plugin
Version: .6
Author: Jason Coleman, Brian Mesenlehner
*/

//version constant
define("SP_VERSION", ".6");
define("PMPRO_NETWORK_MANAGE_SITES_SLUG", "manage-sites");

/*
	Includes
*/
define("SP_DIR", dirname(__FILE__));
require_once(SP_DIR . "/includes/functions.php");			//misc functions used by the plugin
require_once(SP_DIR . "/scheduled/crons.php");				//crons for expiring members, sending expiration emails, etc

//Loading Classes
require_once(SP_DIR . "/classes/class.SPClass.php");		//class for Class
require_once(SP_DIR . "/classes/class.SPAssignment.php");	//class for Assignments
require_once(SP_DIR . "/classes/class.SPSubmission.php");	//class for Assignments
require_once(SP_DIR . "/classes/class.SPStudent.php");		//class for Student
require_once(SP_DIR . "/classes/class.SPTeacher.php");		//class for Teacher
require_once(SP_DIR . "/classes/class.SPSchool.php");		//class for School

//Loading Page Templates
require_once(SP_DIR . "/pages/my_classes.php");
require_once(SP_DIR . "/pages/edit_class.php");
require_once(SP_DIR . "/pages/edit_assignment.php");

//Activation
function sp_activation()
{
	add_action('init', array('SPClass', 'createVisibilities'), 20);
}
register_activation_hook(__FILE__, 'sp_activation');