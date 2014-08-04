<?php
/*
	Class Wrapper for SchoolPress Classes CPT
	/wp-content/plugins/schoolpress/classes/class.SPClass.php
*/
class SPClass {
	//constructor can take a $post_id
	function __construct( $post_id = NULL ) 
	{
		if(!empty($post_id) && is_array($post_id))
		{
			//assuming we want to add a class
			$values = $post_id;			
			return $this->addClass(
				$values['name'],
				$values['description'],
				$values['department'],
				$values['semester'],
				$values['enrollment']				
			);
		}
		elseif(!empty( $post_id))
		{
			//probably the class id, get class		
			$this->getPost( $post_id );
			$this->getGroup();
			
			return $this->id;
		}
	}
	
	//get the associated post and prepopulate some properties
	function getPost($post_id)
	{
		//get CPT post
		$this->post = get_post($post_id);				

		//set some properties for easy access
		if ( !empty( $this->post ) ) {
			$this->id = $this->post->ID;
			$this->post_id = $this->post->ID;
			$this->name = $this->post->post_title;
			$this->title = $this->post->post_title;
			$this->teacher_id = $this->post->post_author;
			$this->description = $this->post->post_content;
			$this->content = $this->post->post_content;
			
			//get taxonomies
			$this->departments = wp_get_post_terms($this->id, "department");
			$this->semesters = wp_get_post_terms($this->id, "semester");
			
			//get meta
			$this->enrollment = $this->class_enrollment;
			$this->group_id = $this->post->group_id;
			
			//pull out one of each taxonomy as the main one
			if(!empty($this->departments))
			{
				$this->department = $this->departments[0];
				$this->department_id = $this->department->term_id;
			}				
			if(!empty($this->semesters))
			{
				$this->semester = $this->semesters[0];
				$this->semester_id = $this->semester->term_id;
			}
		}

		//return post id if found or false if not
		if ( !empty( $this->post ) )
			return $this->post->ID;
		else
			return false;
	}
	
	/*
		Get a class by Forum ID
	*/
	function getClassByForumID($forum_id)
	{
		global $wpdb;
		
		$class_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'forum_id' AND meta_value = '" . $forum_id . "' LIMIT 1");
		
		if(!empty($class_id))
			$this->getPost($class_id);
		else
			return false;
	}
	
	//add a new class
	function addClass($name, $description, $department, $semester, $enrollment, $user_id = NULL)
	{		
		//default to current user
		if(empty($user_id))
		{
			global $current_user;
			$user_id = $current_user->ID;
		}
				
		//make sure we have values
		if(empty($name) || empty($user_id))
			return false;
		
		//add BuddyPress Group
		$group = array(
			'creator_id' => $user_id,
			'name' => $name,
			'description' => $description,
			'enable_forum' => 1,
			'status' => 'private'
		);
		$group_id = groups_create_group($group);	
		
		//add the forum		
		$forum_id = bbp_insert_forum(array(
			'post_author' => $user_id,
			'post_title' => $name,
			'post_content' => $description
		));
				
		//add CPT post
		$insert = array(
			'post_title' => $name,
			'post_content' => $description,
			'post_name' => sanitize_title($name),
			'post_author' => $user_id,
			'post_status' => 'publish',
			'post_type' => 'class',
			'comment_status' => 'closed',
			'ping_status' => 'closed',			
			);
		$class_post_id = wp_insert_post( $insert );				
		
		//add taxonomies
		wp_set_object_terms($class_post_id, intval($department), "department");
		wp_set_object_terms($class_post_id, intval($semester), "semester");
		
		//add meta fields to class
		update_post_meta($class_post_id, "class_enrollment", $enrollment);
		update_post_meta($class_post_id, "group_id", $group_id);
		update_post_meta($class_post_id, "forum_id", $forum_id);
		
		//update group meta
		groups_update_groupmeta( $group_id, 'forum_id', $forum_id );
		groups_update_groupmeta( $group_id, 'class_id', $class_post_id );		
		
		$this->getPost($class_post_id);
		$this->getGroup();
		
		return $class_post_id;
	}
	
	//edit a class
	function editClass($name, $description, $department, $semester, $enrollment)
	{		
		//make sure we have an id
		if(empty($this->id))
			return false;
	
		//make sure we have values
		if(empty($name))
			return false;
		
		//update BuddyPress Group
		$group = array(
			'ID' => $this->group_id,
			'post_title' => $name,
			'post_name' => sanitize_title($name),
			'post_content' => $description		
		);
		wp_update_post($group);
		
		//update the forum		
		$forum = array(
			'ID' => $this->forum_id,
			'post_title' => $name,
			'post_name' => sanitize_title($name),
			'post_content' => $description,	
		);
		wp_update_post($forum);
		
		//update post
		$post = array(
			'ID' => $this->post_id,
			'post_title' => $name,
			'post_content' => $description,
			'post_name' => sanitize_title($name),			
			);
		wp_update_post($post);
		
		//add taxonomies
		wp_set_object_terms($this->post_id, intval($department), "department");
		wp_set_object_terms($this->post_id, intval($semester), "semester");
		
		//add meta fields to class
		update_post_meta($this->post_id, "class_enrollment", $enrollment);
				
		$this->getPost($this->post_id);
		$this->getGroup();
		
		return $this->id;
	}
	
	//get associated group
	function getGroup($group_id = NULL)
	{
		if(empty($group_id) && !empty($this->post))
		{
			$group_id = $this->post->group_id;
		}
			
		//get group
		$this->group = groups_get_group(array('group_id'=>$group_id));
				
		if(!empty($this->group))
		{
			//get forum id
			$this->forum_id = groups_get_groupmeta( $this->group->id, 'forum_id' );
			if(is_array($this->forum_id)) $this->forum_id = $this->forum_id[0];
			
			//return group id
			return $this->group->id;
		}
		else
		{			
			return false;
		}
	}
	
	//is a user the teacher of this class?
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
	
	//is a user a student of this class?
	function isStudent($user_id = NULL)
	{
		//assume current user
		if(empty($user_id))
		{
			global $current_user;
			$user_id = $current_user->ID;
		}
		
		if(empty($user_id))
			return false;
		
		$students = $this->getStudents();
		
		if(empty($students))
			return false;
		
		foreach($students as $student)
			if($student->ID == $user_id)
				return true;
				
		return false;
	}
	
	//is a student or teacher?
	function isMember($user_id = NULL)
	{
		return $this->isTeacher($user_id) || $this->isStudent($user_id);
	}
	
	//get list of students for this class
	function getStudents($force = false)
	{
		//check cache
		if(isset($this->students) && !$force)
			return $this->students;
			
		//have a group?
		if(empty($this->group))
			return false;
				
		$r = groups_get_group_members(array('group_id'=>$this->group->id, 'exclude_admins_mods'=>true));
		$this->students = $r['members'];
				
		return $this->students;
	}
	
	//get a formatted list of students
	function getStudentsList()
	{
		$this->getStudents();
		
		$r = "<ul>\n";
				
		if(!empty($this->students))
		{
			foreach($this->students as $student)
			{
				$r .= "<li>" . $student->display_name . "</li>\n";
			}
		}
			
		$r .= "</ul>\n";
		
		return $r;
	}
		
	/*
		Add invites to the class.
	*/
	function addInvites($emails)
	{
		//make sure emails are passed
		if(empty($emails))
			return false;
			
		//make sure it's an array of unique, lowercase, emails
		if(!is_array($emails))
		{
			$emails = sp_convertEmailStringToArray($emails);
		}
				
		$error_emails = array();
		$okay_emails = array();
		
		foreach($emails as $email)
		{
			//if this doesn't look like an email add to error_emails
			if(!is_email($email))
			{
				$error_emails[] = $email;
			}
						
			//if the user already exists, add them to the class
			$email_user = get_user_by("email", $email);
			if(!empty($email_user))
			{
				//add the user to the BuddyPress group
				groups_join_group($this->group->id, $email_user->ID);
				
				//send added email
				$body = "You have been added to the " . $this->name . " class at SchoolPress.me.\n\n" . get_permalink($this->id);
				wp_mail($email, "SchoolPress - You have been added to a class.", $body);
			}
			else
			{
				//attach email to this group so we add the user after registration
				groups_add_groupmeta($this->group->id, "sp_invites", $email);
			
				//send invite email
				$body = "You have been invited to the " . $this->name . " class at SchoolPress.me.\n\nFollow this link to register for this class:\n" . get_permalink($this->id);
				wp_mail($email, "SchoolPress - You have been invited to a class. Register now.", $body);
			}
			
			$okay_emails[] = $email;
		}
		
		//reset student list
		$this->getStudents(true);
		
		//return true or array of broken emails
		if(empty($error_emails))
			return true;
		else
			return $error_emails;
	}		
	
	/*
		Get related assignments.
		Set $force to true to force the method to get children again.
	*/
	function getAssignments($force = false)
	{
		//need a post ID to do this
		if ( empty( $this->id ) )
			return array();

		//did we get them already?
		if ( !empty( $this->assignments ) && !$force )
			return $this->assignments;

		//okay get assignments
		$this->assignments = get_children( array(
				'post_parent' => $this->id,
				'post_type' => 'assignment',
				'post_status' => 'published'
			) );

		//make sure assignments is an array at least
		if ( empty( $this->assignments ) )
			$this->assignments = array();

		return $this->assignments;
	}
	
	//register CPT and Taxonomies on init, other hook setups
	function init() {
			
		if(!empty($_REQUEST['test']))
		{
			wp_mail('jason+test1@strangerstudios.com', 'Testing... ' . time(), 'Testing...' . time());
		}	
				
		/*
			Hooks and filters.
		*/
		//enroll in invited classes when registering
		add_action('user_register', array('SPClass', 'user_register_classes'));		
		
		//cleanup on delete
		add_action('before_delete_post', array('SPClass', 'before_delete_post'));
		
		//CTP columns
		add_filter('manage_edit-class_columns', array('SPClass', 'class_columns')) ;
		add_action('manage_class_posts_custom_column', array('SPClass', 'manage_class_columns'), 10, 2);
		
		//search filters
		add_filter('pre_get_posts', array('SPClass', 'pre_get_posts'), 20);				
	
		//protect class forums
		add_filter('template_redirect', array('SPClass', 'template_redirect'));
	
		//assignment CPT
		$labels = array(
			'name'               => 'Classes',
			'singular_name'      => 'Class',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Class',
			'edit_item'          => 'Edit Class',
			'new_item'           => 'New Class',
			'all_items'          => 'All Classes',
			'view_item'          => 'View Class',
			'search_items'       => 'Search Classes',
			'not_found'          => 'No classes found',
			'not_found_in_trash' => 'No classes found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Classes'
		);
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'menu_icon' 		   => 'dashicons-book-alt',
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'class' ),
			'capability_type'	   => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'author', 'custom-fields' ),
			'taxonomies'		   => array('semester','department'),
			'rewrite'			   => array('slug'=>'classes')
		);
		register_post_type( 'class', $args );

		//semester taxonomy
		register_taxonomy(
			'semester',
			'class',
			array(
				'label' => __( 'Semesters' ),
				'rewrite' => array( 'slug' => 'semester' ),
				'hierarchical' => false
			)
		);
		
		//department taxonomy
		register_taxonomy(
			'department',
			'class',
			array(
				'label' => __( 'Departments' ),
				'rewrite' => array( 'slug' => 'department' ),
				'hierarchical' => true
			)
		);
		
		//visibility taxonomy
		register_taxonomy(
			'visibility',
			'class',
			array(
				'label' => __( 'Visibility' ),
				'rewrite' => array( 'slug' => 'visibility' ),
				'hierarchical' => true
			)
		);		
	}
	
	/*
		Make sure visibility taxonomies are in place.
	*/
	static function createVisibilities()
	{
		//check if they already exist
		$visibilities = get_terms('visibility', 'hide_empty=0');
				
		if(empty($visibilities) && false)
		{
			wp_insert_term( 'browse', 'visibility');
			wp_insert_term( 'homepage', 'visibility');
			wp_insert_term( 'search', 'visibility');
		}
	}
	
	/*
		Add users to classes after registering
	*/
	static function user_register_classes($user_id)
	{
		global $wpdb;
		
		//get user's email address
		$user = get_userdata($user_id);
		
		//get all invites by email
		$sqlQuery = "SELECT * FROM " . $wpdb->prefix . "bp_groups_groupmeta WHERE meta_value = '" . esc_sql(strtolower($user->user_email)) . "' ";		
		$invites = $wpdb->get_results($sqlQuery);
				
		if(!empty($invites))
		{
			foreach($invites as $invite)
			{										
				//add to group
				groups_join_group($invite->group_id, $user_id);
				
				//remove meta value
				groups_delete_groupmeta($invite->group_id, "sp_invites", strtolower($user->user_email));
			}
		}
	}
	
	/*
		Add a student to this class.
		Just join the BuddyPress group.
	*/
	function joinClass($user_id = NULL)
	{
		if(empty($user_id))
		{
			global $current_user;
			$user_id = $current_user->ID;
		}
		
		if(empty($this->group) || empty($this->group->id) || empty($user_id))
			return false;
		else
			return groups_join_group($this->group->id, $user_id);
	}
	
	/*
		Clean up on before deleting.
	*/
	//delete function
	function deleteMe()
	{		
		//delete corresponding group
		groups_delete_group($this->group_id);

		//delete corresponding forum
		wp_delete_post($this->forum_id);
		
		//delete assignments
		$assignments = $this->getAssignments();
		if(!empty($assignments))
		{
			foreach($assignments as $assignment)
			{
				wp_delete_post($assignment->id);
			}
		}				
	}
	
	//hook
	static function before_delete_post($post_id)
	{		
		$post = get_post($post_id);
		if($post->post_type == "class")
		{
			$class = new SPClass($post_id);
			$class->deleteMe();
		}
	}
	
	//magic method, get post meta
    function __get( $key ) {
        return get_post_meta($this->id, $key, true);
    }
	
	/*
		Manage columns for SPClass CPT
	*/
	//setup columns
	static function class_columns( $columns ) {
		$columns = array(		
			'cb' => true,
			'title' => __( 'Class' ),
			'author' => __( 'Teacher' ),
			'department' => __( 'Department' )
		);
		return $columns;
	}
	//content of columns
	static function manage_class_columns( $column, $post_id ) {
		global $post;
		switch( $column ) {
			case 'department' :
				/* Get the post departments. */
				$departments = get_the_terms( $post_id, 'department' );
				foreach($departments as $department) {
					$department_links[] = $department->name;
				}
				echo join( ", ", $department_links );
				break;
				
			/* Just break out of the switch statement for everything else. */
			default :
				break;
		}
	}
	
	/*
		Filter classes off of homepage and search based on visibility taxonomy.
	*/
	static function pre_get_posts($query)
	{
		//make sure we're in the frontend
		if(!is_admin())
		{			
			//make sure it's a class query
			if(!empty($query->query_vars['post_type']) && ($query->query_vars['post_type'] == 'class' || (is_array($query->query_vars['post_type']) && in_array('class', $query->query_vars['post_type']))))
			{				
				//on homepage, filter for homepage
				if(is_front_page())
				{
					$query->set('tax_query', array(array('taxonomy'=>'visibility', 'field'=>'slug', 'terms'=>array('homepage'))));
				}
				
				//in search, filter for search
				if(!empty($_REQUEST['s']))
				{					
					$query->set('tax_query', array(array('taxonomy'=>'visibility', 'field'=>'slug', 'terms'=>array('search'))));
				}
				
				//archive/browse view, filter for browse
				if(!empty($query->query_vars['department']))
				{
					$query->set('tax_query', array(array('taxonomy'=>'visibility', 'field'=>'slug', 'terms'=>array('browse'))));
				}
			
			}
		}
		
		return $query;
	}	

	/*
		Protect class forums.
	*/
	static function template_redirect()
	{		
		if(!function_exists('bbp_get_forum_id'))
			return;
		
		$forum_id = bbp_get_forum_id();
		
		// Is this even a forum page at all?
		if( ! bbp_is_forum_archive() && ! empty( $forum_id ) && pmpro_bbp_is_forum() ) {
			//is there a class for this forum?
			$class = new SPClass();
			$class->getClassByForumID($forum_id);
			
			//class? make sure the current user is a member
			if(!empty($class->id) && !$class->isMember())
			{
				wp_redirect(get_permalink($class->id));
				exit;
			}
		}
	}
}

//run the Class init on init
add_action( 'init', array( 'SPClass', 'init' ) );