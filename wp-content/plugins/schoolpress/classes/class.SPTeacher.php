<?php
/*
	Teacher Wrapper for SchoolPress Teacher User Type
	/wp-content/plugins/schoolpress/classes/class.SPTeacher.php
*/
class SPTeacher extends SPStudent {
	/*
		Manage the Teacher user type
	*/
	
	//Get the classes I'm Teaching
	function getClassesForTeacher()
	{
		//need a user ID to do this
		if(empty($this->ID))
			return false;
		
		//get classes
		$this->classes = get_posts( array(
				'author' => $this->ID,
				'post_type' => 'class',
				'post_status' => 'published',
				'posts_per_page' => -1
			) );

		//make sure classes is an array at least
		if ( empty( $this->classes ) )
			$this->classes = array();

		return $this->classes;
	}
}