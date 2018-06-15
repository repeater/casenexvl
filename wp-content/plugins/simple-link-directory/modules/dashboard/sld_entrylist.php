<?php 

if(isset($_GET['did']) and $_GET['did']!=''){

    $this->delete_subscriber_profile($_GET['did']);
    echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">Your Item has been Deleted sucessfully. <br/></div>';

}
$s       = 1;
$rows     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE %d and user_id=".$current_user->ID, $s ) );

?>
<h2><?php _e('Link List Page', 'qc-opd') ?></h2>
<div class="qc_sld_table_area">
  <div class="qc_sld_table">
	
	<div class="qc_sld_row sld_header">
	  
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('Image', 'qc-opd') ?>
	  </div>
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('Link Title', 'qc-opd') ?>
	  </div>

	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('Link Subtitle', 'qc-opd'); ?>
	  </div>
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('Category', 'qc-opd'); ?>
	  </div>
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('List', 'qc-opd'); ?>
	  </div>

        <div class="qc_sld_cell qc_sld_table_head">
			<?php _e('Package', 'qc-opd'); ?>
        </div>

	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('Status', 'qc-opd'); ?>
	  </div>
	  
	  <div class="qc_sld_cell qc_sld_table_head">
		<?php _e('Action', 'qc-opd'); ?>
	  </div>
	</div>
<?php
$c=0;
foreach($rows as $row):
$c++;
?>

	<div class="qc_sld_row">
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('Image', 'qc-opd') ?></div>
		<a href="<?php echo $row->item_link; ?>" target="_blank" title="<?php echo $row->item_link; ?>"><?php 
			echo $this->getImage($row->image_url); 
		?></a>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('Link Title', 'qc-opd') ?></div>
		<?php echo $row->item_title; ?>
	  </div>
	 
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('Link Subtitle', 'qc-opd') ?></div>
		<?php echo ( $row->item_subtitle ); ?>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('Category', 'qc-opd') ?></div>
		<?php echo ($row->category) ?>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('SLD List', 'qc-opd') ?></div>
		<?php echo get_the_title( $row->sld_list ); ?>
	  </div>

        <div class="qc_sld_cell">
            <div class="sld_responsive_head"><?php echo __('Package', 'qc-opd') ?></div>
			<?php
                if($row->package_id=='0'){
                    echo 'Free';
                }else{
	                $package     = $wpdb->get_row("SELECT * FROM $package_table WHERE 1 and id=".$row->package_id );
	                echo $package->title;
                }
            ?>
        </div>

	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('Status', 'qc-opd') ?></div>
		<?php echo $this->getStatus($row->approval) ?>
	  </div>
	  
	  <div class="qc_sld_cell">
	  <div class="sld_responsive_head"><?php echo __('Action', 'qc-opd') ?></div>
		<a href="<?php echo esc_url( add_query_arg( array('action'=>'entryedit','id'=>$row->id), $url ) ); ?>"><button class="entry_list_edit"><?php echo __('Edit', 'qc-opd') ?></button></a>
		
		<a title="delete" class="delete" onclick="return confirm('Are you sure to delete this Record?')" href="<?php echo esc_url( add_query_arg( array('action'=>'entrylist','did'=>$row->id), $url ) ); ?>"><button class="entry_list_delete"><?php echo __('Delete', 'qc-opd') ?></button></a>
	  </div>
	  
	</div>
  <?php 
  endforeach;
  ?>

  </div>

</div>