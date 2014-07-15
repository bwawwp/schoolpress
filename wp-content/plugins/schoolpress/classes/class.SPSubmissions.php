<?php
/*
	Class Wrapper for SchoolPress Submissions CPT
	These are submissions students make to class Submissions.
	/wp-content/plugins/schoolpress/classes/class.SPSubmissions.php
*/

//run the Submission init on init
add_action( 'init', array( 'SPSubmission', 'init' ) );

//class definition
class SPSubmission {	
	/*
		Submission structure: Assignment ID, Title, Description, Date Submitted		
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
		if(!empty($this->post)) {
			$this->id = $this->post->ID;
			$this->post_id = $this->post->ID;
			$this->title = $this->post->post_title;
			$this->teacher_id = $this->post->post_author;
			$this->date_submitted = $this->post->date_published;
		}

		//return post id if found or false if not
		if(!empty($this->id))
			return $this->id;
		else
			return false;
	}
	
	//register CPT and Taxonomies on init
	function init() {
		//Submission CPT
		$labels = array(
			'name'               => 'Submissions',
			'singular_name'      => 'Submission',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Submission',
			'edit_item'          => 'Edit Submission',
			'new_item'           => 'New Submission',
			'all_items'          => 'All Submissions',
			'view_item'          => 'View Submission',
			'search_items'       => 'Search Submissions',
			'not_found'          => 'No Submissions found',
			'not_found_in_trash' => 'No Submissions found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Submissions'
		);
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'menu_icon' 		   => 'dashicons-welcome-write-blog',
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'submission' ),
			'capability_type'	   => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'custom-fields' )			
		);
		register_post_type( 'submission', $args );				
	}
}