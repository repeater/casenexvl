<?php 
 //code for form data entry
if(isset($_GET['id']) && $_GET['id']!=''){
$current_user = wp_get_current_user();
	if(isset($_POST['uid']) and $_POST['uid']!=''){
		
		$uid = $_POST['uid'];
		$sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$uid;
		$pdata = $wpdb->get_row($sql);
		
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
			$imageurl = $pdata->image_url;
		}


		$qc_sld_category = sanitize_text_field($_POST['qc_sld_category']);
		$qc_sld_list = sanitize_text_field($_POST['qc_sld_list']);
		$datetime = date('Y-m-d H:i:s');
		$package_id = $_POST['package_id'];
		//Image delete code
		
		
		$wpdb->update(
			$table,
			array(
				'item_title'  => $item_title,
				'item_link'   => $item_link,
				'item_subtitle' => $item_subtitle,
				'category'   => $qc_sld_category,
				'sld_list'  => $qc_sld_list,
				'user_id'=> $current_user->ID,
				'image_url'=> $imageurl,
				'description'=>$item_description,
				'nofollow'=> $item_no_follow,
				'approval'=> 3
			),
			array( 'id' => $uid),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				
				'%s',
				'%d'
			),
			array( '%d')
		);
		wp_reset_query();
		if(in_array('administrator',$current_user->roles)){
			$this->approve_subscriber_profile($uid);
			echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'.__('Your link has been updated sucessfully.','qc-opd').' <br/></div>';
        }else{
			
			$this->sld_edit_item_notification($current_user->ID, $item_title);
			if(sld_get_option('sld_enable_auto_approval')=='on'){

				$this->approve_subscriber_profile($uid);
				echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'.__('Your link has been updated sucessfully.','qc-opd').' <br/></div>';
			}else{
				echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 10px;">'.__('Your link has been updated! Waiting for approval.','qc-opd').' <br/></div>';
			}
        }

		if(!empty($errors)){
			foreach($errors as $error){
				echo '<div style="color: red;border: 1px solid red;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;font-size: 15px;margin-top: 15px;">'.$error.'</div>';
			}
		}
	}


$recid = sanitize_text_field($_GET['id']);
$s = 1;
$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE %d and id=$recid", $s ) );

?>
<h2><?php echo __('Link Edit Form', 'qc-opd') ?> </h2>
<form action="" method="POST" enctype="multipart/form-data">
	<ul class="sld_form-style-1 sld_width">
		<li><label><?php echo __('Link Title', 'qc-opd') ?> <span class="sld_required">*</span></label><input type="text" name="item_title" class="field-long sld_text_width" value="<?php echo esc_html($row->item_title); ?>" required/></li>
		<li>
			<label><?php echo __('Link (Include http:// or https://)', 'qc-opd') ?> <span class="sld_required">*</span></label>
			<input type="text" name="item_link" class="field-long sld_text_width" value="<?php echo esc_url($row->item_link); ?>" required />
		</li>
		<li>
			<label><?php echo __('Link Subtitle', 'qc-opd') ?> </label>
			<input type="text" name="item_subtitle" class="field-long sld_text_width" value="<?php echo esc_html($row->item_subtitle); ?>"  />
		</li>
		<li>
			<label><?php echo __('Link Long Description', 'qc-opd') ?> </label>
			<textarea class="field-long sld_text_width" name="item_long_description"><?php echo ($row->description); ?></textarea>
		</li>
        <li><label><?php echo __('Select Package', 'qc-opd') ?> <span class="sld_required">*</span></label>
            <select name="package_id">
                <option value="">None</option>
				<?php
	            if(in_array('administrator',$current_user->roles)){
		            echo '<option value="0" selected="selected">Free</option>';
                }else{
		            $submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = 0 and user_id =".$current_user->ID);
		            if(sld_get_option('sld_enable_free_submission')=='on'){
			            if(sld_get_option('sld_free_item_limit')!='' and sld_get_option('sld_free_item_limit') > $submited_item->cnt){
				            if($row->package_id==0){
					            echo '<option value="0" selected="selected">Free</option>';
				            }else{
					            echo '<option value="0">Free</option>';
				            }

			            }
		            }
                }


				?>

				<?php
				$pkglist = $wpdb->get_results("select ppt.id as id, ppt.expire_date as expiredate, pt.title, pt.item as total_item from $package_purchased_table as ppt, $package_table as pt where 1 and ppt.user_id = ".$current_user->ID." and ppt.package_id = pt.id order by ppt.date DESC");

				foreach($pkglist as $r){
					$submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id = ".$r->id." and user_id =".$current_user->ID);
					
					if($row->recurring==1){
						if(trim($row->status)!='cancel' and $r->total_item > $submited_item->cnt){
							if($row->package_id==$r->id){
								echo '<option value="'.$r->id.'" selected="selected">'.$r->title.'</option>';
							}else{
								echo '<option value="'.$r->id.'">'.$r->title.'</option>';
							}

						}
					}else{
						if(strtotime(date('Y-m-d')) < strtotime($r->expiredate) and $r->total_item > $submited_item->cnt){
							if($row->package_id==$r->id){
								echo '<option value="'.$r->id.'" selected="selected">'.$r->title.'</option>';
							}else{
								echo '<option value="'.$r->id.'">'.$r->title.'</option>';
							}

						}
					}
					
				}

				?>
            </select>

        </li>
	<?php if(sld_get_option('sld_image_upload')=='on'){ ?>
		<li>
			<label><?php echo __('Link Image', 'qc-opd') ?></label>
			
			<input type="file" name="sld_link_image" id="sld_link_image" >
			
			<div style="clear:both"></div>
			<div id="sld_preview_img">
				<?php if($row->image_url!=''): ?>
					<span class="sld_remove_bg_image">X</span>
					<img src="<?php echo $row->image_url ?>" alt="">
				<?php endif; ?>
			</div>
			
		</li>
	<?php } ?>
		
		<li>
			<label><?php echo __('Category', 'qc-opd') ?> <span class="sld_required">*</span></label>
			
			<?php 
			$taxonomy = 'sld_cat';
			$terms = get_terms($taxonomy); //
			if ( $terms && !is_wp_error( $terms ) ) :
			?>
				<select id="qc_sld_category" class="sld_text_width" name="qc_sld_category" >
					<?php foreach ( $terms as $term ) { ?>
						<?php if($term->name==$row->category): ?>
							<option value="<?php echo $term->name; ?>"selected="selected"><?php echo esc_attr($term->name); ?></option>
						<?php else: ?>
							<option value="<?php echo $term->name; ?>"><?php echo esc_attr($term->name); ?></option>
						<?php endif; ?>
						
					<?php } ?>
				</select>
			<?php
			endif;
			?>
		</li>
		<li>
			<label><?php echo __('Select List', 'qc-opd') ?> <span class="sld_required">*</span></label>
			<select id="qc_sld_list" class="sld_text_width" name="qc_sld_list" required>
				<?php
					$sld = new WP_Query( array( 
						'post_type' => 'sld',
						'tax_query' => array(
							array (
								'taxonomy' => 'sld_cat',
								'field' => 'slug',
								'terms' => $row->category,
							)
						),
						'posts_per_page' => -1,
						'order' => 'ASC',
						'orderby' => 'menu_order'
						) 
					);
					while( $sld->have_posts() ) : $sld->the_post();
					?>
						<?php if(get_the_ID()==$row->sld_list): ?>
							<option value="<?php echo get_the_ID(); ?>" selected="selected"><?php the_title(); ?></option>
						<?php else: ?>
							<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
						<?php endif; ?>
					<?php
					endwhile;
				?>
			</select>
		</li>
		<?php if(sld_get_option('sld_disable_no_follow')!='on'){ ?>
        <li>
            <label><?php echo __('No Follow', 'qc-opd') ?> </label>
            <input type="checkbox" name="item_no_follow" <?php echo ($row->nofollow==1?'checked="checked"':''); ?> class="" value="1" />
        </li>
        <?php }else{
?>
		<li>
            <label><?php echo __('No Follow', 'qc-opd') ?> </label>
            <input type="checkbox" name="item_no_follow" class="" value="1" <?php echo ($row->nofollow==1?'checked="checked"':''); ?> disabled="" />
        </li>
<?php

        	} ?>
		<li>
			<input type="hidden" name="uid" value="<?php echo $recid; ?>" />
			<input type="submit" class="sld_submit_style" value="<?php echo __('Submit', 'qc-opd') ?>" />
		</li>
	</ul>
</form>
<?php 
}else{
	echo __('<p>Something Went Wrong.</p>','qc-opd');
}
?>