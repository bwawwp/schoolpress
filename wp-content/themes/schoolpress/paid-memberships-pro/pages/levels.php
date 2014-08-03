<?php 
global $wpdb, $pmpro_msg, $pmpro_msgt, $pmpro_levels, $current_user, $pmpro_currency_symbol;
if($pmpro_msg)
{
?>
<div class="message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
<?php
}
?>

	<div id="pmpro_levels">
	
	<?php	
		$count = 0;
		foreach($pmpro_levels as $level)
		{
		  if(isset($current_user->membership_level->ID))
			  $current_level = ($current_user->membership_level->ID == $level->id);
		  else
			  $current_level = false;
		?>
		<div id="pmpro_level-<?php echo $level->id; ?>" class="column one_half pmpro_level<?php if($current_level == $level) { ?> pmpro_level-active<?php } if($level->id == '2') { ?> last<?php } ?>">
			<h2><?php echo $level->name; ?></h2>
			<p class="pmpro_level-price">						
				<?php  /*
					if(pmpro_isLevelFree($level)) 
						echo 'Free';
					else 
					{ 
						global $pmpro_currency_symbol;
						echo $pmpro_currency_symbol . intval($level->initial_payment);
					}	
					*/			
				?>
			</p> <!-- end pmpro_level-price -->		
			<div class="pmpro_level-description">
			<?php 
				if(!empty($level->description))
					echo apply_filters("the_content", stripslashes($level->description));
			?>	
			</div>
			<div class="pmpro_level-select">
			<?php if(empty($current_user->membership_level->ID)) { ?>
				<a class="pmpro_btn" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Select', 'Choose a level from levels page', 'pmpro');?></a>               
			<?php } elseif ( !$current_level ) { ?>                	
				<a class="pmpro_btn" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php _e('Select', 'Choose a level from levels page', 'pmpro');?></a>       			
			<?php } elseif($current_level) { ?>      
				<a class="pmpro_btn pmpro_btn-disabled" href="<?php echo pmpro_url("account")?>"><?php _e('Your&nbsp;Level', 'pmpro');?></a>
			<?php } ?>
			</div>		
		</div> <!-- end pmpro_level -->
		<?php
		}
		?>		
		<div class="clear"></div>
	</div>  <!-- end #pmpro_levels -->