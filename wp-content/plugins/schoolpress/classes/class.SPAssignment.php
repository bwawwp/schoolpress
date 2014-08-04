<?php
/*
	Class Wrapper for SchoolPress Assignment CPT
	/wp-content/plugins/schoolpress/classes/class.SPAssignment.php
*/
class SPAssignment {	
	/*
		Assignment structure: Class ID, Title, Description, Due Date
		Taxonomy: Subject
		Has (sub)CPT for Submissions
	*/
	
	//constructor can take a $post_id
	function __construct( $post_id = NULL ) {
		if(!empty($post_id) && is_array($post_id))
		{
			//assuming we want to add a class
			$values = $post_id;		
			
			return $this->addAssignment(
				$values['title'],
				$values['due_date'],
				$values['required'],
				$values['description'],
				$values['class_id']
			);
		}
		elseif(!empty( $post_id))
		{
			//probably the class id, get class		
			$this->getPost( $post_id );
			
			return $this->id;
		}		
	}

	//get the associated post and prepopulate some properties
	function getPost( $post_id ) {
		//get post
		$this->post = get_post( $post_id );

		//set some properties for easy access
		if ( !empty( $this->post ) ) {
			$this->id = $this->post->ID;
			$this->post_id = $this->post->ID;
			$this->title = $this->post->post_title;
			$this->teacher_id = $this->post->post_author;
			$this->content = $this->post->post_content;
			$this->description = $this->post->post_content;
			$this->required = $this->post->required;
			$this->due_date = $this->post->due_date;
			$this->class_id = $this->post->post_parent;
		}

		//return post id if found or false if not
		if ( !empty( $this->id ) )
			return $this->id;
		else
			return false;
	}
	
	//add a new assignment
	function addAssignment($title, $due_date, $required, $description, $class_id, $user_id = NULL)
	{		
		//default to current user
		if(empty($user_id))
		{
			global $current_user;
			$user_id = $current_user->ID;
		}
				
		//make sure we have values
		if(empty($title) || empty($user_id))
			return false;
				
		//add CPT post
		$insert = array(
			'post_title' => $title,
			'post_content' => $description,
			'post_name' => sanitize_title($title),
			'post_author' => $user_id,
			'post_parent' => $class_id,
			'post_status' => 'publish',
			'post_type' => 'assignment',
			'comment_status' => 'closed',
			'ping_status' => 'closed',			
			);		
		$assignment_post_id = wp_insert_post( $insert );								
		
		//force update the post parent, not
		
		//add meta fields to class
		update_post_meta($assignment_post_id, "class_id", $class_id);
		update_post_meta($assignment_post_id, "due_date", $due_date);
		update_post_meta($assignment_post_id, "required", $required);
		
		$this->getPost($assignment_post_id);
			
		return $assignment_post_id;
	}
	
	//edit a class
	function editAssignment($title, $due_date, $required, $description)
	{				
		//make sure we have an id
		if(empty($this->id))
			return false;
	
		//make sure we have values
		if(empty($title))
			return false;		
		
		//update post
		$post = array(
			'ID' => $this->post_id,
			'post_title' => $title,
			'post_content' => $description,
			'post_name' => sanitize_title($title),			
			);
		wp_update_post($post);
				
		//add meta fields to class
		update_post_meta($this->post_id, "due_date", $due_date);
		update_post_meta($this->post_id, "required", $required);
				
		$this->getPost($this->post_id);
		
		return $this->id;
	}
	
	//get class
	function getClass()
	{
		if(empty($this->class_id))
			return false;
		else
			return new SPClass($this->class_id);
	}
	
	//register CPT and Taxonomies on init, setup hooks
	function init() {
		/*
			Hooks and filters.
		*/
		//update things when the post is saved in admin
		add_action('save_post', array('SPAssignment', 'save_post'), 20);
		
		//protect assignments for class members
		add_filter('template_redirect', array('SPAssignment', 'template_redirect'));
		
		//assignment CPT
		$labels = array(
			'name'               => 'Assignments',
			'singular_name'      => 'Assignment',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Assignment',
			'edit_item'          => 'Edit Assignment',
			'new_item'           => 'New Assignment',
			'all_items'          => 'All Assignments',
			'view_item'          => 'View Assignment',
			'search_items'       => 'Search Assignments',
			'not_found'          => 'No assignments found',
			'not_found_in_trash' => 'No assignments found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Assignments'
		);
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'menu_icon' 		   => 'dashicons-welcome-write-blog',
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'assignment' ),
			'capability_type'	   => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'custom-fields' )
		);
		register_post_type( 'assignment', $args );				
	}
	
	//is a user the teacher who created this assignment?
	function isTeacher($user_id = NULL)
	{
		//assume current user
		if(empty($user_id))
		{
			global $current_user;
			$user_id = $current_user->ID;
		}
		
		if(empty($user_id))
			return false;
			
		if($this->teacher_id == $user_id)
			return true;
		else
			return false;
	}
	
	/*
		Get related submissions.
		Set $force to true to force the method to get children again.
	*/
	function getSubmissions($force = false)
	{
		//need a post ID to do this
		if ( empty( $this->id ) )
			return array();

		//did we get them already?
		if ( !empty( $this->submissions ) && !$force )
			return $this->submissions;

		//okay get submissions
		$this->submissions = get_children( array(
				'post_parent' => $this->id,
				'post_type' => 'submission',
				'post_status' => 'published'
			) );

		//make sure submissions is an array at least
		if ( empty( $this->submissions ) )
			$this->submissions = array();

		return $this->submissions;
	}
	
	/*
		Do some stuff when an Assignment CPT is saved in the admin.
	*/
	function save_post($post_id)
	{		
		//only want to do this when saving in the admin
		if(!is_admin())
			return;
		
		//only worried about our CPT
		if(get_post_type($post_id) != 'assignment')
			return;

		//get post to work with
		$post = get_post($post_id);
			
		//update post_parent to match the class_id if set.
		$class_id = get_post_meta($post_id, "class_id", true);				
		if(!empty($class_id))
		{
			//already set?
			if($post->post_parent != $class_id)
				wp_update_post(array("ID"=>$post_id, "post_parent"=>$class_id));
		}
		else
		{
			//already empty?
			if(!empty($post->post_parent))		
				wp_update_post(array("ID"=>$post_id, "post_parent"=>0));
		}
	}
	
	/*
		Only let class members view assignments.
	*/
	static function template_redirect()
	{
		global $post;
		
		//only worried about our CPT
		if(empty($post) || get_post_type($post->ID) != 'assignment')
			return;
			
		//check that current user is a class member
		$assignment = new SPAssignment($post->ID);
		
		if(!empty($assignment))
		{
			$class = $assignment->getClass();
			if(!$class->isMember())
			{
				wp_redirect(get_permalink($class->id));
				exit;
			}
		}
	}
}

//run the Assignment init on init
add_action( 'init', array( 'SPAssignment', 'init' ) );