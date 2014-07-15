<?php
/**
 * Template for displaying all single class posts
**/

global $post;
$class = new SPClass($post->ID);
$class->getStudents();

//$class->group->id;
?>
<?php get_header(); ?>
	<?php if ( have_posts() ) the_post(); ?>
	<div id="post-<?php the_ID() ?>" <?php post_class(); ?>>
		<div class="alt_wrap">
			<div class="container">
				<?php getBreadcrumbs(); ?>
				<div class="column three_fourths">
					<h2><?php do_action( 'sb_page_title' ); ?></h2>
					<p>By <?php the_author(); ?> | <i class="fa fa-graduation-cap"></i> <?php echo count($class->students) . " " . _n("Student", "Students", count($class->students));?></p>
					<hr />
					<?php the_content(); ?>
					<hr />
					<?php
						$departments = get_the_terms( $post->ID, 'department' );
						if ( $departments && ! is_wp_error( $departments ) ) : 
							echo '<div class="column one_half">';
							$department_links = array();					
							foreach ( $departments as $department ) {
								$department_links[] = '<span class="label label-info">' . $department->name . '</span>';
							}
							echo 'Department: ' . join( ", ", $department_links );
							echo '</div>';
						 endif;
					?>
					<?php
						echo '<div class="column one_half last">';
						echo 'Semester: <span class="label label-info">' . $class->semester->name . '</span>';
						echo '</div>';
					?>
				</div>
				<div class="column one_fourth last">
					<?php echo get_the_post_thumbnail( $post->ID, 'medium' ); ?>
				</div>
			</div>
		</div>
		<div id="container">
			<div id="content">
				<?php do_action( 'sb_before_content' ); ?>
					
					<?php do_action( 'sb_before_post_content' ); ?>
	
					<div class="entry-content">
						
						<?php wp_link_pages( array( 'before' => '<div class="entry-pages cb">' . __( 'Pages:', 'startbox' ), 'after' => '</div>' ) ); ?>

						<?php 							
							if(!empty($_REQUEST['invite']))
							{
								get_template_part('class', 'invite');
							}
							else
							{
								//default
								$class->getAssignments(); 
								if(!empty($class->assignments))
								{
									?>
									<h3>Assignments</h3>
									<table class="assignments table table-bordered table-striped">
										<thead>
											<tr>
												<th>Name</th>
												<th>Due</th>
												<th width="20%">Status</th>
												<th>Action</th>
											</tr>
										</thead>
										<tbody>
										<?php
											foreach($class->assignments as $class->assignment)
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
													<td><a href="<?php echo get_permalink($assignment->ID); ?>">View Assignment</a></td>
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
					<hr />
					<?php
						//must be in the group to view this						
						if(bp_group_is_member($class->group))
						{
					?>
						<?php 						
							$forum_id = $class->forum_id;
							if(!empty($forum_id))
							{
								?>
								<h3>Recent Discussion <a class="btn btn-info btn-xs" href="<?php echo bbp_get_forum_permalink($forum_id); ?>">View All</a></h3>
								<?php echo do_shortcode('[bbp-topic-index id="' . $forum_id . '"]'); ?>
								<?php
							}
						?>
						<?php do_action( 'sb_after_post_content' ); ?>
						<div class="entry-footer">
							<?php //do_action( 'sb_post_footer' ); ?>
						</div>
					<?php
						}
					?>

				<?php //comments_template('', true); ?>
			</div><!-- #content -->
			
			<?php
				//must be in the group to view this				
				if(bp_group_is_member($class->group))
				{
			?>
			<div id="primary" class="aside">
				<div class="well">
					<h3>
						Students
						<small><a href="?invite=1">+ Invite</a></small>
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
