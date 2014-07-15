<?php
/*
	Student Wrapper for SchoolPress Student User Type
	/wp-content/plugins/schoolpress/classes/class.SPStudent.php
*/

//load hooks/etc on init
add_action('init', array('SPStudent', 'sp_init'));

//class
class SPStudent extends WP_User {
	//load hooks on init
	static function sp_init()
	{
		add_action('pmpro_confirmation_message', array('SPStudent', 'pmpro_confirmation_message'));
	}
	
	//Get the classes I'm Teaching
	function getClassesForStudent()
	{		
		//need a user id for this
		if(empty($this->ID))
			return false;
		
		//get corresponding class ids for buddypress groups this user is a member of
		$class_ids = array();
		if(bp_has_groups(array('user_id'=> $this->ID)))
		{
			while(bp_groups())
			{				
				bp_the_group();				
				$class_id = groups_get_groupmeta(bp_get_group_id(), "class_id", true);
				if($class_id)
					$class_ids[] = $class_id;
			}
		}
		
		//get classes
		$classes = get_posts( array(				
				'post__in' => $class_ids,
				'post_type' => 'class',
				'post_status' => 'published',
				'posts_per_page' => -1				
			) );
			
		//remove classes I'm teaching		
		foreach($classes as $key => $class)
		{
			if($class->post_author == $this->ID)
				unset($classes[$key]);
		}

		//make sure classes is an array at least
		if ( empty( $classes ) )
			$this->classes = array();				
		else
			$this->classes = $classes;
		
		return $this->classes;
	}
	
	//show classes in confirmation message
	static function pmpro_confirmation_message($message)
	{
		/*
			need getClassesForStudent to get classes across sites to do this
		*/		
	
		return $message;
	}
}