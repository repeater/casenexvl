<?php 
 //code for form data entry
global $wp;
$current_url =  home_url( $wp->request );
$current_user = wp_get_current_user();
if(isset($_POST['item_title']) and $_POST['item_title']!='' and $_POST['package_id']!=''){


	$item_title = sanitize_text_field($_POST['item_title']);
	$item_link = sanitize_text_field($_POST['item_link']);
	$item_subtitle = sanitize_text_field($_POST['item_subtitle']);
	$item_description = sanitize_text_field($_POST['item_long_description']);
	if(isset($_POST['item_no_follow']) and $_POST['item_no_follow']==1){
	    $item_no_follow = 1;
    }else{
		$item_no_follow = 0;
    }


/* Image upload script */
$file_name = '';
$errors= array();
if(isset($_FILES['sld_link_image']) and $_FILES['sld_link_image']['name']!=''){
  $file_name = $_FILES['sld_link_image']['name'];
  $file_size =$_FILES['sld_link_image']['size'];
  $file_tmp =$_FILES['sld_link_image']['tmp_name'];
  $file_type=$_FILES['sld_link_image']['type'];
  
  $file_ext=strtolower(end(explode('.',$_FILES['sld_link_image']['name'])));
  $custom_name = strtolower(explode('.',$_FILES['sld_link_image']['name'])[0]);
  $file_name = $custom_name.'_'.time().'.'.$file_ext;
  
  $expensions= array("jpeg","jpg","png","gif");
  
  if(in_array($file_ext,$expensions)=== false){
	 $errors[]="Extension not allowed, please choose a JPEG or PNG file.";
  }
  
  if($file_size > 2097152){
	 $errors[]='File size must be excately 2 MB';
  }
  
  if(empty($errors)==true){
	 move_uploaded_file($file_tmp,OCOPD_UPLOAD_DIR."/".$file_name);
  }else{
	  $file_name='';
  }
}



	if($file_name!=''){
		$imageurl = OCOPD_UPLOAD_URL.'/'.$file_name;
    }else{
		$imageurl = '';
    }


	$qc_sld_category = sanitize_text_field($_POST['qc_sld_category']);
	$qc_sld_list = sanitize_text_field($_POST['qc_sld_list']);
	$datetime = date('Y-m-d H:i:s');
	$package_id = $_POST['package_id'];
	
	
		$wpdb->insert(
			$table,
			array(
				'item_title'  => $item_title,
				'item_link'   => $item_link,
				'item_subtitle' => $item_subtitle,
				'category'   => $qc_sld_category,
				'sld_list'  => $qc_sld_list,
				'user_id'=> $current_user->ID,
				'image_url'=> $imageurl,
				'time'=> $datetime,
				'nofollow'=> $item_no_follow,
                'package_id'=>$package_id,
				'description'=>$item_description
			)
		);
		wp_reset_query();

		

    if(in_array('administrator',$current_user->roles)){
        $lastid = $wpdb->insert_id;
        $this->approve_subscriber_profile($lastid);

        echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__('Your list link has been successfully published.','qc-opd').' </div>';
    }else{

	    if(sld_get_option('sld_email_notification')=='on'){
	        $this->sld_new_item_notification($current_user->ID, $item_title);
        }

        if(sld_get_option('sld_enable_auto_approval')=='on'){

            $lastid = $wpdb->insert_id;
            $this->approve_subscriber_profile($lastid);

            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__('Your list link has been successfully published.','qc-opd').' </div>';

        }else{

            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__('Your link has been successfully submitted. We will review your item information before Publishing. Thank you for your patience.','qc-opd').' <br/></div>';

        }
    }

	if(!empty($errors)){
		foreach($errors as $error){
			echo '<div style="color: red;border: 1px solid red;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.$error.'</div>';
		}
	}

}
if($this->allow_item_submit==false){
	echo '<div style="color: red;border: 1px solid red;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.__('You have reached your free link submission limit.','qc-opd').' <br/></div>';
	return;
}

?>

<h2><?php echo __('Add Your Link', 'qc-opd') ?></h2>
<form action="<?php echo $current_url.'/?action=entry'; ?>" method="POST" enctype="multipart/form-data">
	<ul class="sld_form-style-1 sld_width">

        <li><label><?php echo __('Link Title', 'qc-opd') ?> <span class="sld_required">*</span></label><input type="text" name="item_title" class="field-long sld_text_width" value="" required/></li>
		<li>
			<label><?php echo __('Link (Include http:// or https://)', 'qc-opd') ?> <span class="sld_required">*</span> </label>
			<input type="text" name="item_link" class="field-long sld_text_width" value="" required />
		</li>
		<li>
			<label><?php echo __('Link Subtitle', 'qc-opd') ?> </label>
			<input type="text" name="item_subtitle" class="field-long sld_text_width" value=""  />
		</li>
		<li>
			<label><?php echo __('Link Long Description', 'qc-opd') ?> </label>
			<textarea class="field-long sld_text_width" name="item_long_description"></textarea>
		</li>
        <li><label><?php echo __('Select Package', 'qc-opd') ?> <span class="sld_required">*</span></label>
            <select name="package_id">
				<?php
				$submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = 0 and user_id =".$current_user->ID);
				if(in_array('administrator',$current_user->roles)){
				    ?>
                    <option value="0">Free</option>
                    <?php
                }else{
					if(sld_get_option('sld_enable_free_submission')=='on'){
						if(sld_get_option('sld_free_item_limit')!='' and sld_get_option('sld_free_item_limit') > $submited_item->cnt){
							?>
                            <option value="0">Free</option>
							<?php
						}else{
							?>
                            <option value="0" disabled>Free</option>
							<?php
                        }
					}
                }

				?>

                 <?php
                 $pkglist = $wpdb->get_results("select ppt.id as pid, ppt.package_id as id, ppt.expire_date as expiredate,ppt.recurring,ppt.status, pt.title, pt.item as total_item from $package_purchased_table as ppt, $package_table as pt where 1 and ppt.user_id = ".$current_user->ID." and ppt.package_id = pt.id order by ppt.date DESC");

                 foreach($pkglist as $row){
	                 $submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = ".$row->id." and user_id =".$current_user->ID);
					 if($row->recurring==1){
						
						if(trim($row->status)!='cancel' and $row->total_item > $submited_item->cnt){
							?>
							<option value="<?php echo $row->pid; ?>"><?php echo $row->title; ?></option>
							
							<?php
						}else{
							?>
							<option value="<?php echo $row->pid; ?>" disabled><?php echo $row->title; ?></option>
							<?php
						}
						
					 }else{
						 
						if(strtotime(date('Y-m-d')) < strtotime($row->expiredate) and $row->total_item > $submited_item->cnt){
						 ?>
							 <option value="<?php echo $row->pid; ?>"><?php echo $row->title; ?></option>
						<?php
						 }else{
							 ?>
							 <option value="<?php echo $row->pid; ?>" disabled><?php echo $row->title; ?></option>
							<?php
						 }
						 
					 }
                     
					 
                 }

                 ?>
            </select>
        </li>
        <?php if(sld_get_option('sld_image_upload')=='on'){ ?>
		<li>
			<label><?php echo __('Link Image', 'qc-opd') ?> <span class="sld_required">*</span></label>
			
			<input type="file" name="sld_link_image" id="sld_link_image" required>
			
			<div style="clear:both"></div>
			<div id="sld_preview_img"></div>
			
		</li>
        <?php } ?>
		
		<li>
			<label><?php echo __('Category', 'qc-opd') ?> <span class="sld_required">*</span></label>
			
				<?php 
					$taxonomy = 'sld_cat';
					$terms = get_terms($taxonomy); //
					
					if ( $terms && !is_wp_error( $terms ) ) :
					?>
						<select id="qc_sld_category" class="sld_text_width" name="qc_sld_category" required>
							<option value="" >None</option>
							<?php foreach ( $terms as $term ) { 
							
							?>
								<option value="<?php echo $term->term_id; ?>"><?php echo esc_attr($term->name); ?></option>
							<?php } ?>
						</select>
					<?php
					endif;
				?>
		</li>
		<li>
			<label><?php echo __('Select List', 'qc-opd') ?> <span class="sld_required">*</span></label>
			<select id="qc_sld_list" class="sld_text_width" name="qc_sld_list" required>
				<option value="">None</option>

			</select>
		</li>
<?php if(sld_get_option('sld_disable_no_follow')!='on'){ ?>
        <li>
            <label><?php echo __('No Follow', 'qc-opd') ?> </label>
            <input type="checkbox" name="item_no_follow" class="" value="1" checked />
        </li>
<?php }else{
	?>
	<li>
            <label><?php echo __('No Follow', 'qc-opd') ?> </label>
            <input type="checkbox" name="item_no_follow" class="" value="1" checked disabled="" />
        </li>
	<?php 
	} ?>
        <li>
			<input type="submit" name="submititem" class="sld_submit_style" value="<?php echo __('Submit', 'qc-opd') ?>" />
		</li>
	</ul>
</form>