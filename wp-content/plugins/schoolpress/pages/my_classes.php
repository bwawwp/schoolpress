<?php
	/*
		Preheader
	*/
	function sp_my_classes_preheader()
	{
		if(!is_admin())
		{
			global $post, $current_user;
			if(!empty($post->post_content) && strpos($post->post_content, "[sp_my_classes]") !== false)
			{
				/*
					Preheader operations here.
				*/				
			}
		}
	}
	add_action("wp", "sp_my_classes_preheader", 1);	
	
	/*
		Shortcode Wrapper
	*/
	function sp_my_classes_shortcode($atts, $content=null, $code="")
	{			
		ob_start();
		global $current_user;
		?>
		<div class="column one_half">
			<h3>Classes I Teach</h3>
			<?php
				$teacher = new SPTeacher($current_user->ID);
				$teacher->getClassesForTeacher();
				if(!empty($teacher->classes))
				{
					foreach($teacher->classes as $teacher->singleclass)
					{
						global $post;
						$post = $teacher->singleclass;
						setup_postdata( $post );
						get_template_part( 'loop', 'class' );
					}
				}
			?>
		</div>
		<div class="column one_half last">

		</div>
		<div class="clear"></div>
		<?php
		$temp_content = ob_get_contents();
		ob_end_clean();
		return $temp_content;			
	}
	add_shortcode("sp_my_classes", "sp_my_classes_shortcode");
	