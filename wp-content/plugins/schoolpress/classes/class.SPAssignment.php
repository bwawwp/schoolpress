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
		if ( !empty( $post_id ) )
			$this->getPost( $post_id );
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
		$this->required = $this->post->_schoolpress_assignment_is_required;
		$this->due_date = $this->post->due_date;
		}

		//return post id if found or false if not
		if ( !empty( $this->id ) )
			return $this->id;
		else
			return false;
	}
	
	//register CPT and Taxonomies on init
	function init() {
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
	
}

//run the Assignment init on init
add_action( 'init', array( 'SPAssignment', 'init' ) );