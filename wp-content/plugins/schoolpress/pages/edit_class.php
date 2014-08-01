<?php
	/*
		Preheader
	*/
	function sp_edit_class_preheader()
	{
		if(!is_admin())
		{
			global $post, $current_user;
			if(!empty($post->post_content) && strpos($post->post_content, "[sp_edit_class]") !== false)
			{
				/*
					Preheader operations here.
				*/

				//make sure user is logged in and a Teacher
				if(!pmpro_hasMembershipLevel(array(2,3)))
				{
					wp_redirect('http://schoolpress.me/membership-account/membership-levels/');
					exit;
				}
				
				//adding a class?
				if(!empty($_POST['editclass']))
				{					
					//get values
					$class_name = $_REQUEST['class_name'];
					$class_description = $_REQUEST['class_description'];
					$class_department = $_REQUEST['class_department'];
					$class_semester = $_REQUEST['class_semester'];
					$class_enrollment = $_REQUEST['class_enrollment'];
					
					//check values
					if(empty($class_name) || empty($class_description) || empty($class_department) || empty($class_semester))
					{
						sp_setMessage("Please complete all fields.", "error");
					}
					else
					{					
						//adding or updating?
						
						//add class
						$class = new SPCLass(array('name'=>$class_name, 'description'=>$class_description, 'department'=>$class_department, 'semester'=>$class_semester, 'enrollment'=>$class_enrollment));
												
						if(!empty($class))
						{
							//redirect to the class page
							wp_redirect(get_permalink($class->id));
							exit;
						}
						else
							sp_setMessage("Error adding class.", "error");
					}
				}
			}
		}
	}
	add_action("wp", "sp_edit_class_preheader", 1);	
	
	/*
		Shortcode Wrapper
	*/
	function sp_edit_class_shortcode($atts, $content=null, $code="")
	{
		//get values
		if(!empty($_POST['editclass']))
		{
			$class_name = $_REQUEST['class_name'];
			$class_description = $_REQUEST['class_description'];
			$class_department = $_REQUEST['class_department'];
			$class_semester = $_REQUEST['class_semester'];
			$class_enrollment = $_REQUEST['class_enrollment'];
		}
		elseif(!empty($_REQUEST['edit']))
		{
			$class = new SPClass(intval($_REQUEST['edit']));
						
			if(!empty($class))
			{
				$class_name = $class->name;
				$class_description = $class->description;
				$class_department = $class->department_id;
				$class_semester = $class->semester_id;
				$class_enrollment = $class->enrollment;
			}
			else
			{
				$class_name = "";
				$class_description = "";
				$class_department = "";
				$class_semester = "";
				$class_enrollment = "";
			}
		}
		else
		{
			$class_name = "";
			$class_description = "";
			$class_department = "";
			$class_semester = "";
			$class_enrollment = "";
		}
		
		ob_start();
		?>
		<?php sp_showMessage();?>
		<form class="form form-horizontal" method="post">
			<div class="form-group">
				<label for="class_name" class="col-sm-2 control-label">Class Name</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo esc_attr($class_name); ?>">
				</div>
			</div>
			<div class="form-group">
				<label for="class_description" class="col-sm-2 control-label">Description</label>
				<div class="col-sm-10">
					<textarea class="form-control" id="class_description" name="class_description"><?php echo esc_attr($class_description); ?></textarea>
				</div>
			</div>
			<div class="form-group">
				<label for="class_department" class="col-sm-2 control-label">Department</label>
				<div class="col-sm-10">
					<select class="form-control" id="class_department" name="class_department">
					<?php
						$terms = get_terms("department",array('hide_empty' => 0));
						 if ( !empty( $terms ) && !is_wp_error( $terms ) ){
							 foreach ( $terms as $term ) {
							   echo "<option " . selected($class_department, $term->term_id) . " value='" . intval($term->term_id) . "' >" . $term->name . "</option>";
							 }
						 }
					?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="class_semester" class="col-sm-2 control-label">Semester</label>
				<div class="col-sm-10">
					<select class="form-control" id="class_semester" name="class_semester">
					<?php
						$terms = get_terms("semester",array('hide_empty'=>0,'orderby'=>'ID','order'=>'DESC'));
						 if ( !empty( $terms ) && !is_wp_error( $terms ) ){
							 foreach ( $terms as $term ) {
							   echo "<option " . selected($class_semester, $term->term_id) . " value='" . intval($term->term_id) . "' >" . $term->name . "</option>";
							 }
						 }
					?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<label for="class_enrollment">
						<input type="checkbox" id="class_enrollment" name="class_enrollment" value="1" <?php checked($class_enrollment, 1); ?> /> Allow any student to join this class. (Leave unchecked for invite-only.)
					</label>
				</div>
			</div>
			<p class="text-center">
				<input type="hidden" name="editclass" value="1" />
				<button type="submit" class="btn btn-default">Submit</button>
			</p>
		</form>
		<?php
		$temp_content = ob_get_contents();
		ob_end_clean();
		return $temp_content;			
	}
	add_shortcode("sp_edit_class", "sp_edit_class_shortcode");
	
