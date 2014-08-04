<?php
	/*
		Preheader
	*/
	function sp_edit_assignment_preheader()
	{
		if(!is_admin())
		{
			global $post, $current_user;
			if(!empty($post->post_content) && strpos($post->post_content, "[sp_edit_assignment]") !== false)
			{
				/*
					Preheader operations here.
				*/
				
				//make sure user is logged in and a Teacher
				if(!pmpro_hasMembershipLevel(array(2,3)))
				{
					wp_redirect('http://schoolpress.me/membership/');
					exit;
				}								
								
				//adding an assignment?
				if(!empty($_POST['edit']))
				{					
					$edit = intval($_POST['edit']);
										
					//get values
					$assignment_title = $_REQUEST['assignment_title'];
					$assignment_description = $_REQUEST['assignment_description'];
					
					$due_year = intval($_REQUEST['due_year']);
					$due_month = intval($_REQUEST['due_month']);
					$due_day = intval($_REQUEST['due_day']);
					$assignment_due_date = $due_year . "-" . $due_month . "-" . $due_day;										
					
					if(!empty($_REQUEST['assignment_required']))
						$assignment_required = $_REQUEST['assignment_required'];
					else
						$assignment_required = "";
					$class_id = $_REQUEST['class_id'];
					
					//must specify a class
					if(empty($class_id))
						die("ERROR: Could not figure out which class you wanted to add/edit assignments for.");												
					
					//check values
					if(empty($assignment_title) || empty($assignment_description) || empty($assignment_due_date))
					{
						sp_setMessage("Please complete all fields.", "error");
					}
					else
					{					
						//adding or updating?
						if($edit == -1)
						{													
							//woah, let's make sure they are a teacher							
							if(!pmpro_hasMembershipLevel(array(2,3)))
								die("You do not have permission to do this.");
							
							//add assignment
							$assignment = new SPAssignment(array("title"=>$assignment_title, "due_date"=>$assignment_due_date, "required"=>$assignment_required, "description"=>$assignment_description, 'class_id'=>$class_id));
							if(!empty($assignment))
							{
								//redirect to the class page
								wp_redirect(get_permalink($class_id));
								exit;
							}
							else
								sp_setMessage("Error adding assignment.", "error");
						}
						else
						{							
							//update assignment							
							$assignment = new SPAssignment($edit);
						
							//let's make sure they can edit this assignment
							if(!$assignment->isTeacher() && !current_user_can("manage_options"))
								die("You do not have permission to do this.");
								
							//okay update
							if(!empty($assignment) && $assignment->editAssignment($assignment_title, $assignment_due_date, $assignment_required, $assignment_description))
							{
								sp_setMessage("Assignment updated successfully.", "success");
							}
							else
							{
								sp_setMessage("Error updating assignment.", "error");
							}
						}
					}
				}
				
				//deleting an assignment?
				if(!empty($_REQUEST['delete']))
				{
					$assignment_id = intval($_REQUEST['delete']);
					$assignment = new SPAssignment($assignment_id);
					
					//only teachers and admins can delete classes
					if($assignment->isTeacher() || current_user_can("manage_options"))
					{
						$r = wp_delete_post($assignment->post->ID);
						wp_redirect(get_permalink($assignment->class_id));
						exit;
					}
				}
			}
		}
	}
	add_action("wp", "sp_edit_assignment_preheader", 1);	
	
	/*
		Shortcode Wrapper
	*/
	function sp_edit_assignment_shortcode($atts, $content=null, $code="")
	{	
		//get values
		if(!empty($_POST['edit']))
		{
			$edit = intval($_POST['edit']);			
			
			$assignment_title = stripslashes($_REQUEST['assignment_title']);
			$assignment_description = stripslashes($_REQUEST['assignment_description']);
			
			$due_year = intval($_REQUEST['due_year']);
			$due_month = intval($_REQUEST['due_month']);
			$due_day = intval($_REQUEST['due_day']);
			$assignment_due_date = $due_year . "-" . $due_month . "-" . $due_day;
			
			if(!empty($_REQUEST['assignment_required']))
				$assignment_required = $_REQUEST['assignment_required'];
			else
				$assignment_required = "";
		}
		elseif(!empty($_REQUEST['edit']) && intval($_REQUEST['edit']) > 0)
		{
			$edit = intval($_REQUEST['edit']);
			
			$assignment = new SPAssignment(intval($_REQUEST['edit']));
						
			if(!empty($assignment))
			{
				$assignment_title = $assignment->title;
				$assignment_description = $assignment->description;
				$assignment_due_date = $assignment->due_date;
				$assignment_required = $assignment->required;
			}
			else
			{
				$assignment_title = "";
				$assignment_description = "";
				$assignment_due_date = "";
				$assignment_required = "";
			}
		}
		else
		{
			$edit = -1;
			$assignment_title = "";
			$assignment_description = "";
			$assignment_due_date = "";
			$assignment_required = "";
		}
		
		//get class
		if(!empty($assignment) && !empty($assignment->class_id))
			$class = $assignment->getClass();
		elseif(!empty($_REQUEST['class_id']))
			$class = new SPClass(intval($_REQUEST['class_id']));
		else
			die("ERROR: Could not figure out which class you wanted to add/edit assignments for.");
		
		ob_start();
		?>
		<?php sp_showMessage();?>
		<form class="form form-horizontal" method="post">
			<div class="form-group">
				<label for="assignment_title" class="col-sm-2 control-label">Assignment Title</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="assignment_title" name="assignment_title" value="<?php echo esc_attr($assignment_title); ?>">
				</div>
			</div>			
			<div class="form-group">
				<label for="assignment_due_date" class="col-sm-2 control-label">Due Date</label>
				<div class="col-sm-10">
					<?php
						//split assignment due date into parts
						if(empty($assignment_due_date)) 
							$assignment_due_date = date("Y-m-d", current_time("timestamp"));
						$current_year = date("Y", current_time("timestamp"));
						$due_date_parts = explode("-", $assignment_due_date);
						$selected_due_year = $due_date_parts[0];
						$selected_due_month = $due_date_parts[1];
						$selected_due_day = $due_date_parts[2];
					?>
					<select name="due_month">
						<?php																
							for($i = 1; $i < 13; $i++)
							{
							?>
							<option value="<?php echo $i?>" <?php if($i == $selected_due_month) { ?>selected="selected"<?php } ?>><?php echo date("M", strtotime($i . "/1/" . $current_year, current_time("timestamp")))?></option>
							<?php
							}
						?>
					</select>
					<input name="due_day" type="text" size="2" value="<?php echo esc_attr($selected_due_day)?>" />
					<input name="due_year" type="text" size="4" value="<?php echo esc_attr($selected_due_year)?>" />
					
					<input type="hidden" class="form-control datepicker" id="assignment_due_date" name="assignment_due_date" value="<?php echo esc_attr($assignment_due_date); ?>">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<label for="assignment_required">
						<input type="checkbox" id="assignment_required" name="assignment_required" value="1" <?php checked($assignment_required, 1); ?> /> Required?
					</label>
				</div>
			</div>
			<div class="form-group">
				<label for="assignment_description" class="col-sm-2 control-label">Description</label>
				<div class="col-sm-10">					
					<textarea class="form-control" id="assignment_description" name="assignment_description"><?php echo esc_textarea($assignment_description); ?></textarea>
				</div>
			</div>
						
			<p class="text-center">
				<input type="hidden" name="edit" value="<?php echo $edit;?>" />
				<input type="hidden" name="class_id" value="<?php echo $class->id;?>" />
				<button type="submit" class="pmpro_btn">Submit</button>
				
				<?php
					//delete or cancel
					if(!empty($assignment->id))
					{
					?>
					<a class="btn btn-link" href="javascript:askfirst('Are you sure you want to delete this assignment?', '<?php echo home_url('/edit-assignment/?delete=' . $assignment->id);?>');">Delete</a>
					<?php
					}
					
					?>
					<a class="btn btn-link" href="<?php echo get_permalink($class->id);?>">Cancel</a>
					<?php					
				?>
			</p>
		</form>
		<?php
		$temp_content = ob_get_contents();
		ob_end_clean();
		return $temp_content;			
	}
	add_shortcode("sp_edit_assignment", "sp_edit_assignment_shortcode");
	
