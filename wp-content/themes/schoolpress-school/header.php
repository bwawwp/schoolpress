<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes() ?>>
<head profile="http://gmpg.org/xfn/11">
	<title><?php wp_title(); ?></title>
	<meta http-equiv="content-type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	
	<!-- BEGIN wp_head() -->
	<?php wp_head(); ?>
	<!-- END wp_head() -->
</head>

<body <?php body_class(); ?>>
<?php do_action( 'sb_before' ); ?>
<div id="wrap" class="hfeed">
	
	<?php do_action( 'sb_before_header' ); ?>
	
	<?php if ( has_action( 'sb_header' ) ) { ?>
		<div id="header">
			<?php
				if ($user_ID) 
				{ 
					global $current_user;					
					?>					  
					<div class="btn-group pull-right">
						<a class="btn btn-info dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fa fa-user"></i>
							<?php echo preg_replace("/\@.*/", "", $current_user->display_name)?>
						</a>
						 <ul class="dropdown-menu">							
							<li><a href="/your-profile/">My Profile</a></li>
							<li class="divider"></li>
							<li><a href="<?php echo wp_logout_url( ); ?> " title="Log Out"><small>Log Out</small></a></li>
						</ul>
					</div>
					<?php /*<div class="pull-right"><?php get_search_form(); ?></div> */ ?>
					<?php
				  }
				  else
				  {
					if(!is_page('login'))
					{
						?>
						<a href="/login/">Log In</a>
						<?php
					}
				}
				?>	
			<?php do_action( 'sb_header' ); ?>
			<div class="clear"></div>
		</div><!-- #header -->
	<?php } ?>
	
	<?php do_action( 'sb_after_header' ); ?>
	
	<div id="container_wrap">
		<?php do_action( 'sb_before_container' ); ?>