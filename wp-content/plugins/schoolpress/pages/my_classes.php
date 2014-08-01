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
		
		//classes I teach
		$teacher = new SPTeacher($current_user->ID);
		$teacher->getClassesForTeacher();
		if(!empty($teacher->classes))
		{
		?>
			<div class="column one_half">
				<h2 class="page-title">Classes I Teach</h2>
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
				<h2 class="page-title">Classes I'm In</h2>
		<?php
		}
		
		//classes I'm In		
		$student = new SPStudent($current_user->ID);
		$student->getClassesForStudent();
		if(!empty($student->classes))
		{
			foreach($student->classes as $student->singleclass)
			{
				global $post;
				$post = $student->singleclass;
				setup_postdata( $post );
				get_template_part( 'loop', 'class' );
			}
		}
		else
		{
		?>
			<div class="pmpro_message pmpro_alert">You are not a member of any classes yet. <a href="<?php echo home_url();?>">Why not join some</a>?</div>
		<?php
		}
		?>
		
		<?php if(!empty($teacher->classes)) { ?></div><?php } ?>
		
		<div class="clear"></div>
		<?php
		$temp_content = ob_get_contents();
		ob_end_clean();
		return $temp_content;			
	}
	add_shortcode("sp_my_classes", "sp_my_classes_shortcode");
	