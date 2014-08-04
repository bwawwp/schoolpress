<?php
/**
 * Template for displaying all single class posts
**/

//get some vars
global $post;
$class = new SPClass($post->ID);
$class->getStudents();

//inviting?
if(!empty($_REQUEST['invite']) && !$class->isTeacher())
{
	//non-teacher trying to invite, redirect them back to class
	wp_redirect(get_permalink($post->ID));
	exit;
}

//enrolling?
if(!empty($_REQUEST['enroll']))
{	
	//check for open enrollment
	if($class->enrollment)
	{	
		//enroll the current user
		$class->joinClass();
		
		//redirect to this page to reset somet things
		wp_redirect(get_permalink($post->ID));
		exit;
	}
	else
		wp_die("This class does not have open enrollment.");
}
?>
<?php get_header(); ?>
	<?php if ( have_posts() ) the_post(); ?>
	<div id="post-<?php the_ID() ?>" <?php post_class(); ?>>
		<div class="alt_wrap">
			<div class="container">
				<?php getBreadcrumbs(); ?>
				<?php if(!empty($_REQUEST['invite'])) { ?>					
					<?php do_action( 'sb_page_title' ); ?>
					<p>Invite Students to join your class.</p>
				<?php } else { ?>
				<div class="column three_fourths">					
					<?php do_action( 'sb_page_title' ); ?>
					<p>
						By <?php the_author(); ?> | <?php echo count($class->students) . " " . _n("Student", "Students", count($class->students));?>
						<?php if($class->isTeacher()) { ?> | <a href="/start-a-class/?edit=<?php echo $class->id;?>">Edit</a><?php } ?>
					</p>
					<hr />
					<?php the_content(); ?>
					<hr />
					<?php
						$departments = get_the_terms( $post->ID, 'department' );
						if ( $departments && ! is_wp_error( $departments ) ) : 
							echo '<div class="column one_half">';
							$department_links = array();					
							foreach ( $departments as $department ) {
								$department_links[] = '<span class="label label-default"><a href="/departments/' . $department->slug . '/">' . $department->name . '</a></span>';
							}
							echo 'Department: ' . join( ", ", $department_links );
							echo '</div>';
						 endif;
					?>
					<?php
						echo '<div class="column one_half last">';
						echo 'Semester: <span class="label label-default">' . $class->semester->name . '</span>';
						echo '</div>';
					?>
				</div>
				<div class="column one_fourth last">
					<?php echo get_the_post_thumbnail( $post->ID, 'medium' ); ?>
				</div>
				<?php } //endif empty($_REQUEST['invite'])?>
			</div>
		</div>
		<div id="container">
			<div id="content">
				<?php do_action( 'sb_before_content' ); ?>
					
					<?php do_action( 'sb_before_post_content' ); ?>
	
					<div class="entry-content">
						
						<?php wp_link_pages( array( 'before' => '<div class="entry-pages cb">' . __( 'Pages:', 'startbox' ), 'after' => '</div>' ) ); ?>

						<?php 							
							if(!empty($_REQUEST['invite']) && $class->isTeacher())
							{
								get_template_part('class', 'invite');
							}
							elseif($class->isMember())
							{
								//default, show assignments (move into template part?)
								?>
								<h3>
									Assignments
									<?php if($class->isTeacher()) { ?>
										<a class="btn btn-info btn-xs" href="/edit-assignment/?class_id=<?php echo $class->id;?>"><i class="fa fa-plus"></i> New Assignment</a>
									<?php } ?>
								</h3>
								<?php
								//get assignments								
								$class->getAssignments();								
								if(!empty($class->assignments))
								{
									?>									
									<table class="assignments table table-bordered table-striped">
										<thead>
											<tr>
												<th>Name</th>
												<th>Due</th>
												<th width="20%">Status</th>
												<th></th>
											</tr>
										</thead>
										<tbody>
										<?php
											foreach($class->assignments as $assignment)
											{
												?>
												<tr>
													<td><strong><a href="<?php echo get_permalink($assignment->ID); ?>"><?php echo $assignment->post_title; ?></a></strong></td>
													<td>
														<?php 
															$due_date = get_post_meta($assignment->ID,'due_date',true);
															if(!empty($due_date))
																echo date(get_option('date_format'),strtotime($due_date));
														?>
													</td>
													<td>
													<?php /*
														<span class="label label-success"><i class="glyphicon glyphicon-ok-circle"></i> Submitted</span>
														<span class="label label-danger"><i class="glyphicon glyphicon-ban-circle"></i> Submitted</span>
													*/ ?>
													</td>
													<td>
														<a href="<?php echo get_permalink($assignment->ID); ?>">View Assignment</a>
														<?php if($class->isTeacher()) { ?>
															| <a href="/edit-assignment/?edit=<?php echo $assignment->ID;?>">Edit</a>
														<?php } ?>
													</td>
												</tr>
												<?php
											}
										?>
										</tbody>
									</table>
									<?php 
								//end Assignments
								}
							}
						?>
					</div>
					
					<?php
						if(!empty($_REQUEST['invite']))
						{
							//nothing here for now
						}												
						elseif($class->isMember())
						{									
							$forum_id = $class->forum_id;							
							if(!empty($forum_id))
							{
								?>
								<hr />
								<h3>Recent Discussion <a class="btn btn-info btn-xs" href="<?php echo bbp_get_forum_permalink($forum_id); ?>"><i class="fa fa-eye"></i> View All</a></h3>
								<?php echo do_shortcode('[bbp-single-forum id="' . $forum_id . '"]'); ?>
								<?php
							}
						?>
						<?php do_action( 'sb_after_post_content' ); ?>
						<div class="entry-footer">
							<?php //do_action( 'sb_post_footer' ); ?>
						</div>
					<?php
						}
						elseif($class->enrollment)
						{
							//open enrollment, let the user invite themself in
							if(is_user_logged_in())
							{							
							?>
							<div class="pmpro_message pmpro_alert">This class has open enrollment. <a href="?enroll=1">Click here if you would like to join this class</a>.</div>
							<?php
							}
							else
							{
							?>
							<div class="pmpro_message pmpro_alert">Please <a href="<?php echo wp_login_url();?>">login</a> or <a href="<?php echo network_site_url("/membership/");?>">sign up</a> to join this class.</div>
							<?php
							}
						}
						else
						{
							//closed enrollment
							?>
							<div class="pmpro_message pmpro_danger">This class is closed to the public. If you feel you should be invited to this class, please contact the teacher.</div>
							<?php
						}
					?>

				<?php //comments_template('', true); ?>
			</div><!-- #content -->
			
			<?php
				//must be in the group to view this				
				if($class->isMember())
				{
			?>
			<div id="primary" class="aside">
				<div class="well">
					<h3>
						Students
						<?php if($class->isTeacher()) { ?>
							<a class="btn btn-info btn-xs" href="?invite=1"><i class="fa fa-plus"></i> Invite</a></small>
						<?php } ?>
					</h3>
					<hr />
					<ul class="media-list">
					<?php //This is where it goes 
						$class->getStudentsList();
						foreach ($class->students as $class->student)
						{
							?>
							<li class="media">
								<a class="pull-left" href="#"><?php echo get_avatar($class->student->id, 64); ?> </a>
								<div class="media-body"><h4 class="media-heading"><?php echo $class->student->display_name; ?></h4></div>
							</li>
							<?php
						}
					?>
					</ul>
				</div>
			</div>
			<?php
				}
			?>
			<div class="clear"></div>
		</div><!-- #container -->
	</div><!-- .post -->
	<?php do_action( 'sb_after_content' ); ?>
<?php get_footer(); ?>
