<?php

add_action('wp_head', 'qcopd_ajax_ajaxurl');
add_action('admin_head', 'qcopd_ajax_ajaxurl');

function qcopd_ajax_ajaxurl()
{

    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

//Doing ajax action stuff

function upvote_ajax_action_stuff()
{

	//check_ajax_referer( 'quantum_ajax_validation_18', 'security' );
    //Get posted items
    $action = trim($_POST['action']);
    $post_id = trim($_POST['post_id']);
    $meta_title = trim($_POST['meta_title']);
    $meta_link = trim($_POST['meta_link']);
    $li_id = trim($_POST['li_id']);
    $uniqueid = trim($_POST['uniqueid']);
    $security = trim($_POST['security']);
	
	if(isset($_COOKIE['usnidg']) && $_COOKIE['usnidg']!=$security){
		die();
	}
	
    //Check wpdb directly, for all matching meta items
    global $wpdb;
	$utable = $wpdb->prefix.'sld_ip_table';
    $results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key = 'qcopd_list_item01'");

    //Defaults
    $votes = 0;

    $data['votes'] = 0;
    $data['vote_status'] = 'failed';

    $exists = in_array("$uniqueid", $_COOKIE['voted_li']);
	$userip = sld_get_the_user_ip();
    //If li-id not exists in the cookie, then prceed to vote
	
    if (!$exists || sld_get_option('sld_upvote_restrict_by_ip')=='on') {
		
		if(sld_get_option('sld_upvote_restrict_by_ip')=='on'){
			
			$oldate = date('Y-m-d H:i:s',strtotime("-1 days"));
			
			//checking with ip block
			$ipblocks = explode('.',$userip);
			if(sizeof($ipblocks)>2){
				$userid = implode('.',array_pop($ipblocks));
			}
			
			
			$find = $wpdb->get_row("SELECT * FROM $utable WHERE 1 and item_id='".$uniqueid."' and `ip` like '%".$userip."%' and time > '$oldate'");
			
			if(!empty($find)){
				
				$data['cookies'] = $_COOKIE['voted_li'];
				echo json_encode($data);
				die(); // stop executing script
			}
			
		}
		
		if(sld_get_option('sld_upvote_user_login')=='on' && !is_user_logged_in()){
			die();
		}
		
		
        //Iterate through items
        foreach ($results as $key => $value) {

            $item = $value;

            $meta_id = $value->meta_id;

            $unserialized = unserialize($value->meta_value);

            //If meta title and link matches with unserialized data
			
            if (trim($unserialized['qcopd_item_title']) == wp_unslash(trim($meta_title)) && trim($unserialized['qcopd_item_link']) == trim($meta_link)) {

                $metaId = $meta_id;

                //Defaults for current iteration
                $upvote_count = 0;
                $new_array = array();
                $flag = 0;

                //Check if there already a set value (previous)
                if (array_key_exists('qcopd_upvote_count', $unserialized)) {
                    $upvote_count = (int)$unserialized['qcopd_upvote_count'];
					
					$expire = sld_get_option('sld_upvote_expire_after');
					if($expire!='' && (int)$expire>0){
						$expire_date = date('Y-m-d H:i:s',strtotime("-$expire days"));
						$item_id = $post_id.'_'.$unserialized['qcopd_timelaps'];
						$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $utable WHERE item_id = '$item_id' and time > '$expire_date'");
						$upvote_count = $rowcount;
					}
					
                    $flag = 1;
                }

                foreach ($unserialized as $key => $value) {
                    if ($flag) {
                        if ($key == 'qcopd_upvote_count') {
                            $new_array[$key] = $upvote_count + 1;
                        } else {
                            $new_array[$key] = $value;
                        }
                    } else {
                        $new_array[$key] = $value;
                    }
                }

                if (!$flag) {
                    $new_array['qcopd_upvote_count'] = $upvote_count + 1;
                }

                $votes = (int)$new_array['qcopd_upvote_count'];

                $updated_value = serialize($new_array);

                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_value' => $updated_value,
                    ),
                    array('meta_id' => $metaId)
                );

                $voted_li = array("$uniqueid");

                $total = 0;
                $total = count($_COOKIE['voted_li']);
                $total = $total + 1;

				
				$wpdb->insert(
					$utable,
					array(
						'item_id'=> $uniqueid,
						'ip'	=> $userip,
						'time'  => date('Y-m-d H:i:s')
					)
				);
				
				if(sld_get_option('sld_upvote_restrict_by_ip')!='on'){
					setcookie("voted_li[$total]", $uniqueid, time() + (86400 * 7), "/");
				}

                $data['vote_status'] = 'success';
                $data['votes'] = $votes;

            }

        }
		
    }

    $data['cookies'] = $_COOKIE['voted_li'];

    echo json_encode($data);


    die(); // stop executing script
}

//Implementing the ajax action for frontend users
add_action('wp_ajax_qcopd_upvote_action', 'upvote_ajax_action_stuff'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_upvote_action', 'upvote_ajax_action_stuff'); // ajax for not logged in users

//captcha image change script
function qcld_sld_change_captcha(){
	session_start();
	if(isset($_SESSION['captcha'])){
		unset($_SESSION['captcha']);
	}
	$_SESSION['captcha'] = sld_simple_php_captcha();
	echo $_SESSION['captcha']['image_src'];
	die();
}

add_action('wp_ajax_qcld_sld_change_captcha', 'qcld_sld_change_captcha'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_change_captcha', 'qcld_sld_change_captcha'); // ajax for not logged in users


function qcld_sld_loadmore_function(){

 if(sld_get_option('sld_use_global_thumbs_up')!=''){
     $sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
 }else{
     $sld_thumbs_up = 'fa-thumbs-up';
 }

	
	
	$paged = $_POST['page'];
	$column = $_POST['column'];
	$upvote = $_POST['upvote'];
	$itemperpage = $_POST['itemperpage'];
	$item_count = $_POST['itemcount'];
	

	$list_args = array(
		'post_type' => 'sld',
		'posts_per_page' => $itemperpage,
		'paged'	=> $paged
		
	);
	
	$list_query = new WP_Query( $list_args );
	
	
	$listId = 1;

	while ( $list_query->have_posts() )
	{
		$list_query->the_post();

		$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );

		$conf = get_post_meta( get_the_ID(), 'qcopd_list_conf', true );

		$addvertise = get_post_meta( get_the_ID(), 'sld_add_block', true );

		$addvertiseContent = isset($addvertise['add_block_text']) ? $addvertise['add_block_text'] : '';

		//adding extra variable in config
		$conf['item_title_font_size'] = $title_font_size;
		$conf['item_subtitle_font_size'] = $subtitle_font_size;
		$conf['item_title_line_height'] = $title_line_height;
		$conf['item_subtitle_line_height'] = $subtitle_line_height;

		?>

		

		<!-- Override Set Style Elements -->
		<style>
			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple ul{
				border-top-color: <?php echo $conf['list_border_color']; ?>;
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple ul li a{
				background-color: <?php echo $conf['list_bg_color']; ?>;
				color: <?php echo $conf['list_txt_color']; ?>;

				<?php if($conf['item_title_font_size']!=''): ?>
				font-size:<?php echo $conf['item_title_font_size']; ?>;
				<?php endif; ?>

				<?php if($conf['item_title_line_height']!=''): ?>
				line-height:<?php echo $conf['item_title_line_height']; ?>;
				<?php endif; ?>

				<?php if( $conf['item_bdr_color'] != "" ) : ?>
				border-bottom-color: <?php echo $conf['item_bdr_color']; ?> !important;
				<?php endif; ?>
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple ul li a:hover{
				background-color: <?php echo $conf['list_bg_color_hov']; ?>;
				color: <?php echo $conf['list_txt_color_hov']; ?>;

				<?php if( $conf['item_bdr_color_hov'] != "" ) : ?>
				border-bottom-color: <?php echo $conf['item_bdr_color_hov']; ?> !important;
				<?php endif; ?>
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple .upvote-section .upvote-btn, #list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple .upvote-section .upvote-count {
				color: <?php echo $conf['list_txt_color']; ?>;
			}

			#list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple .upvote-section .upvote-btn:hover, #list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple li:hover .upvote-btn, #list-item-<?php echo $listId .'-'. get_the_ID(); ?>.simple li:hover .upvote-count{
				color: <?php echo $conf['list_txt_color_hov']; ?>;
			}

			#item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block .advertise-block.tpl-default{
				border-top: 5px solid #f86960;
				border-top-color: <?php echo $conf['list_border_color']; ?>;
				box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2);
			}

			#item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block .advertise-block.tpl-default ul{
				border: none;
				box-shadow: none !important;
				margin-bottom: 0 !important;
			}

		</style>


		<!-- Individual List Item -->
		<div id="list-item-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " simple";?> <?php echo "opd-list-id-" . get_the_ID(); ?>">
			<div class="qcopd-single-list">
				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
					}
				?>
				<h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>>
					<?php 
					if(isset($multipage) && $multipage=='true'):
						echo '<a href="'.$current_url.'/'.get_post(get_the_ID())->post_name.'">';
					endif;
					?>
					<?php echo get_the_title(); ?>
					<?php
						if($item_count == 'on'){
							echo '<span class="opd-item-count">('.$item_count_disp.')</span>';
						}
					?>
					<?php 
					if(isset($multipage) && $multipage=='true'):
						echo '</a>';
					endif;
					?>
				</h2>
				<?php 
				
				?>
				<ul id="jp-list-<?php echo get_the_ID(); ?>">
					<?php

						if( $item_orderby == 'upvotes' )
						{
    						usort($lists, "custom_sort_by_tpl_upvotes");
						}

						if( $item_orderby == 'title' )
						{
    						usort($lists, "custom_sort_by_tpl_title");
						}

						if( $item_orderby == 'timestamp' )
						{
							usort($lists, "custom_sort_by_tpl_timestamp");
						}

						if( $item_orderby == 'random' )
						{
							shuffle( $lists );
						}

						$count = 1;

						foreach( $lists as $list ) :
						
						$tooltip_content = '';

						if( $tooltip === 'true' ){
							$tooltip_content = ' data-tooltip="'.$list['qcopd_item_subtitle'].'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
						}
						//print_r($list);exit;
					?>
					<li id="item-<?php echo get_the_ID() ."-". $count; ?>" <?php echo $tooltip_content; ?>>

						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
						?>
						<!-- List Anchor -->
						<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> href="<?php echo $masked_url; ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?>  >

							<?php
								$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

								$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";

								$faviconImgUrl = "";
								$faviconFetchable = false;
								$filteredUrl = "";

								$directImgLink = (isset($list['qcopd_item_img_link']) && trim($list['qcopd_item_img_link']) != "") ? $list['qcopd_item_img_link'] : "";

								if( $showFavicon == 1 )
								{
									$filteredUrl = qcsld_remove_http( $item_url );

									if( $item_url != '' )
									{

										$faviconImgUrl = 'https://www.google.com/s2/favicons?domain=' . $filteredUrl;
									}

									if( $directImgLink != '' )
									{

										$faviconImgUrl = trim($directImgLink);
									}

									$faviconFetchable = true;

									if( $item_url == '' && $directImgLink == '' ){
										$faviconFetchable = false;
									}
								}

							?>

							<!-- Image, If Present -->
							<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
								<span class="list-img">
									<?php
										$img = wp_get_attachment_image_src($list['qcopd_item_img']);
									?>
									<img src="<?php echo $img[0]; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="list-img">
									<i class="fa <?php echo $iconClass; ?>"></i>
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="list-img favicon-loaded">
									<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php else : ?>
								<span class="list-img">
									<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php endif; ?>

							<!-- Link Text -->
							<?php
								echo $list['qcopd_item_title'];
							?>

						</a>

						<?php if( $upvote == 'on' ) : ?>

							<!-- upvote section -->
							<div class="upvote-section upvote-section-simple">
								<span data-post-id="<?php echo get_the_ID(); ?>" data-unique="<?php echo get_the_ID().'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
									<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
								</span>
								<span class="upvote-count">
									<?php
									  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
									  	echo (int)$list['qcopd_upvote_count'];
									  }
									?>
								</span>
							</div>
							<!-- /upvote section -->

						<?php endif; ?>
						
							<?php if(sld_get_option('sld_enable_bookmark')=='on'): ?>
							<!-- upvote section -->
							<div class="bookmark-section bookmark-section-simple">
							
								<?php 
								$bookmark = 0;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv)){
										$bookmark = 1;
									}
								}
								?>
							
							
								<span data-post-id="<?php echo get_the_ID(); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa <?php echo ($bookmark==1?'fa-star':'fa-star-o'); ?>" aria-hidden="true"></i>
								</span>
								
							</div>
							<?php endif; ?>
							
							<?php if(isset($list['qcopd_new']) and $list['qcopd_new']==1):?>
							<!-- new icon section -->
							<div class="new-icon-section">
								<span>new</span>
							</div>
							<!-- /new icon section -->
							<?php endif; ?>
							
							
							<?php if(isset($list['qcopd_featured']) and $list['qcopd_featured']==1):?>
							<!-- featured section -->
							<div class="featured-section">
								
							</div>
							<!-- /featured section -->
							<?php endif; ?>

					</li>

					<?php $count++; endforeach; ?>

				</ul>

				

			</div>

		</div>
		<!-- /Individual List Item -->


		<?php

		$listId++;
	}

	
	
	die();
}


add_action('wp_ajax_qcld_sld_loadmore', 'qcld_sld_loadmore_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_loadmore', 'qcld_sld_loadmore_function'); // ajax for not logged in users


function qcld_sld_loadmore_filter_function(){
	
	$paged = $_POST['page'];
	$column = $_POST['column'];
	$item_count = $_POST['itemcount'];
	$itemperpage = $_POST['itemperpage'];
	

	$list_args = array(
		'post_type' => 'sld',
		'posts_per_page' => $itemperpage,
		'paged'	=> $paged
		
	);
	
	$listItems = get_posts( $list_args );
	
	
	foreach ($listItems as $item) :
		$config = get_post_meta( $item->ID, 'qcopd_list_conf' );
		$filter_background_color = '';
		$filter_text_color = '';
		if(isset($config[0]['filter_background_color']) and $config[0]['filter_background_color']!=''){
			$filter_background_color = $config[0]['filter_background_color'];
		}
		if(isset($config[0]['filter_text_color']) and $config[0]['filter_text_color']!=''){
			$filter_text_color = $config[0]['filter_text_color'];
		}
		?>

		<?php
		$item_count_disp = "";

		if( $item_count == "on" ){
			$item_count_disp = count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
		}
		?>

		<a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>">
			<?php echo $item->post_title; ?>
			<?php
			if($item_count == 'on'){
				echo '<span class="opd-item-count-fil">('.$item_count_disp.')</span>';
			}
			?>
		</a>

	<?php endforeach;
	die();
}


add_action('wp_ajax_qcld_sld_loadmore_filter', 'qcld_sld_loadmore_filter_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_loadmore_filter', 'qcld_sld_loadmore_filter_function'); // ajax for not logged in users

function qcopd_search_sld_page_function(){
	if(trim($_POST['shortcode'])!=''){
		$shortcode = trim($_POST['shortcode']);
		$list_args = array(
			'post_type' => 'page',
			'posts_per_page' => -1,
		);
		$listItems = get_posts( $list_args );
		$data = '';
		foreach($listItems as $item){
			
			if(strpos($item->post_content, $shortcode ) !== false ){
				if($shortcode=='sld_login'){
					$data = 'Login page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				if($shortcode=='sld_registration'){
					$data = 'Registration page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				if($shortcode=='sld_dashboard'){
					$data = 'Dashboard page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				if($shortcode=='sld_restore'){
					$data = 'Password Restore page found!<br><a href="'.get_permalink($item->ID).'">'.get_permalink($item->ID).'</a>';
				}
				break;
			}
		}
		
		if($data!=''){
			echo $data;
		}else{
			
				if($shortcode=='sld_login'){
					
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Login',
						'post_type' => 'page',
						'post_content'=> '[sld_login]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					echo 'Login page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
					
				}
				if($shortcode=='sld_registration'){
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Register',
						'post_type' => 'page',
						'post_content'=> '[sld_registration]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					echo 'Registration page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
				}
				if($shortcode=='sld_dashboard'){
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Dashboard',
						'post_type' => 'page',
						'post_content'=> '[sld_dashboard]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					echo 'Dashboard page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
				}
				if($shortcode=='sld_restore'){
					$post = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_author' => get_current_user_id(),
						'post_date' => date('Y-m-d H:i:s'),
						'post_status' => 'publish',
						'post_title' => 'SLD Restore Password',
						'post_type' => 'page',
						'post_content'=> '[sld_restore]'
					);
					//insert page and save the id
					$PostID = wp_insert_post($post, false);
					echo 'Restore page found!<br><a href="'.get_permalink($PostID).'">'.get_permalink($PostID).'</a>';
				}
			
		}
	}
	die();
}



add_action('wp_ajax_qcopd_search_sld_page', 'qcopd_search_sld_page_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_search_sld_page', 'qcopd_search_sld_page_function'); // ajax for not logged in users

function qcopd_flash_rewrite_rules_fnc(){
	flush_rewrite_rules();
	die();
}


add_action('wp_ajax_qcopd_flash_rewrite_rules', 'qcopd_flash_rewrite_rules_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_flash_rewrite_rules', 'qcopd_flash_rewrite_rules_fnc'); // ajax for not logged in users

function qcopd_reset_all_upvotes_fnc(){
	global $wpdb;
	
	$list = $_POST['list'];
	$item = $_POST['item'];
	
	if($list=='all'){
		$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE 1 and meta_key = 'qcopd_list_item01'");
	}else{
		$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE 1 and post_id = $list and meta_key = 'qcopd_list_item01'");
	}
	
	
	
	foreach($results as $key=>$value){
		
		$unserialized = unserialize($value->meta_value);
		foreach($unserialized as $k=>$v){
			
			if($item=='all' or $item==''){
				if($k=='qcopd_upvote_count'){
					$unserialized[$k]=0;

					$wpdb->delete(
						"{$wpdb->prefix}sld_ip_table",
						array( 'item_id' => $value->post_id.'_'.$unserialized['qcopd_timelaps'] ),
						array( '%s' )
					);
					
				}
			}else{
				if($k=='qcopd_upvote_count'){
					if($unserialized['qcopd_item_title']==$item){
						$unserialized[$k]=0;
						$wpdb->delete(
							"{$wpdb->prefix}sld_ip_table",
							array( 'item_id' => $value->post_id.'_'.$unserialized['qcopd_timelaps'] ),
							array( '%s' )
						);
					}
				}
			}
			
			
		}
		
		$updated_value = serialize($unserialized);
		
		$wpdb->update(
			$wpdb->postmeta,
			array(
				'meta_value' => $updated_value,
			),
			array('meta_id' => $value->meta_id)
		);
		
	}
	echo '<p style="color:green;font-weight:bold;">Upvote reset successfully!</p>';
	die();
}


add_action('wp_ajax_qcopd_reset_all_upvotes', 'qcopd_reset_all_upvotes_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_reset_all_upvotes', 'qcopd_reset_all_upvotes_fnc'); // ajax for not logged in users

//load list items
function qcopd_list_items_fnc(){
	global $wpdb;
	$listId = $_POST['listid'];
	$lists = get_post_meta( $listId, 'qcopd_list_item01' );
	echo '<div class="qcsld_single_field_shortcode"><label style="width: 200px;display: inline-block;">Select Item</label><select style="width: 225px;" id="sld_list_item"><option value="all">All Items</option>';
	foreach( $lists as $list ) :
		echo '<option value="'.$list['qcopd_item_title'].'">'.$list['qcopd_item_title'].'</option>';
	endforeach;
	echo '</select></div>';
	die();
}


add_action('wp_ajax_show_qcsld_list_items', 'qcopd_list_items_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_show_qcsld_list_items', 'qcopd_list_items_fnc'); // ajax for not logged in users

//load list items
function qcld_sld_show_list_item_fnc(){
	global $wpdb;
	$listId = $_POST['listid'];
	$lists = get_post_meta( $listId, 'qcopd_list_item01' );
	
	foreach( $lists as $list ) :
		echo '<option value="'.$list['qcopd_item_title'].'">'.$list['qcopd_item_title'].'</option>';
	endforeach;
	
	die();
}


add_action('wp_ajax_qcld_sld_show_list_item', 'qcld_sld_show_list_item_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcld_sld_show_list_item', 'qcld_sld_show_list_item_fnc'); // ajax for not logged in users

function qcopd_item_click_action_fnc(){
	global $wpdb;
	$itemid = trim($_POST['itemid']);
    $itemurl = trim($_POST['itemurl']);
    $itemsid = trim($_POST['itemsid']);
	$table             = $wpdb->prefix.'sld_click_table';
	$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE 1 and post_id = $itemid and meta_key = 'qcopd_list_item01'");
	
	foreach($results as $key=>$value){
		$unserialized = unserialize($value->meta_value);
		if (trim($unserialized['qcopd_item_link']) == trim($itemurl) && trim($unserialized['qcopd_timelaps'])==$itemsid){
			$click_count = 0;
			$new_array = array();
			$flag = 0;

			//Check if there already a set value (previous)
			if (array_key_exists('qcopd_click', $unserialized)) {
				$click_count = (int)$unserialized['qcopd_click'];
				$flag = 1;
			}

			foreach ($unserialized as $k => $v) {
				if ($flag) {
					if ($k == 'qcopd_click') {
						$new_array[$k] = $click_count + 1;
					} else {
						$new_array[$k] = $v;
					}
				} else {
					$new_array[$k] = $v;
				}
			}

			if (!$flag) {
				$new_array['qcopd_click'] = $click_count + 1;
			}
			$updated_value = serialize($new_array);
			$wpdb->update(
				$wpdb->postmeta,
				array(
					'meta_value' => $updated_value,
				),
				array('meta_id' => $value->meta_id)
			);
		}
	}
	
	$date = date('Y-m-d H:i:s');
	$userip = sld_get_the_user_ip();
	$wpdb->insert(
		$table,
		array(
			'time'  => $date,
			'itemurl'   => $itemurl,
			'itemid'   => $itemid,
			'ip'   => $userip
		)
	);
	
	die();
}


add_action('wp_ajax_qcopd_item_click_action', 'qcopd_item_click_action_fnc'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_item_click_action', 'qcopd_item_click_action_fnc'); // ajax for not logged in users


function qcopd_load_long_description_function(){
	
	$post_id = trim($_POST['post_id']);
    $meta_title = wp_unslash(trim($_POST['meta_title']));
    $meta_link = trim($_POST['meta_link']);
	global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key = 'qcopd_list_item01'");
	
	foreach ($results as $key => $value) {
		$unserialized = unserialize($value->meta_value);
		if (trim($unserialized['qcopd_item_title']) == trim($meta_title) && trim($unserialized['qcopd_item_link']) == trim($meta_link)) {
			echo '<div class="sld_single_content">';
?>

		<div class="feature-image" style="margin-top: 43px;">
		<?php
			$iconClass = (isset($unserialized['qcopd_fa_icon']) && trim($unserialized['qcopd_fa_icon']) != "") ? $unserialized['qcopd_fa_icon'] : "";

			$showFavicon = (isset($unserialized['qcopd_use_favicon']) && trim($unserialized['qcopd_use_favicon']) != "") ? $unserialized['qcopd_use_favicon'] : "";

			$faviconImgUrl = "";
			$faviconFetchable = false;
			$filteredUrl = "";

			$directImgLink = (isset($unserialized['qcopd_item_img_link']) && trim($unserialized['qcopd_item_img_link']) != "") ? $unserialized['qcopd_item_img_link'] : "";

			if( $showFavicon == 1 )
			{
				$filteredUrl = qcsld_remove_http( $item_url );

				if( $item_url != '' )
				{

					$faviconImgUrl = 'https://www.google.com/s2/favicons?domain=' . $filteredUrl;
				}

				if( $directImgLink != '' )
				{

					$faviconImgUrl = trim($directImgLink);
				}

				$faviconFetchable = true;

				if( $item_url == '' && $directImgLink == '' ){
					$faviconFetchable = false;
				}
			}
			?>
		<!-- Image, If Present -->
			<?php if( isset($unserialized['qcopd_item_img'])  && $unserialized['qcopd_item_img'] != "" ) : ?>


				<?php
					$img = wp_get_attachment_image_src($unserialized['qcopd_item_img'], 'medium');
					
				?>
				<img src="<?php echo $img[0]; ?>" alt="<?php echo $unserialized['qcopd_item_title']; ?>">


			<?php elseif( $iconClass != "" ) : ?>

				<span class="icon fa-icon">
					<i class="fa <?php echo $iconClass; ?>"></i>
				</span>

			<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>

				<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $unserialized['qcopd_item_title']; ?>">

			<?php else : ?>

				<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $unserialized['qcopd_item_title']; ?>">

			<?php endif; ?>
		</div>

<?php
			echo '<h2>'.$unserialized['qcopd_item_title'].'</h2>';
			echo apply_filters('the_content',$unserialized['qcopd_description']);
			echo '<div style="clear:both"></br></div><a href="'.$unserialized['qcopd_item_link'].'" target="_blank" class="sld_single_button">Visit This Link</a>';
			echo '</div>';
		}
	}
	die();
}



add_action('wp_ajax_qcopd_load_long_description', 'qcopd_load_long_description_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_load_long_description', 'qcopd_load_long_description_function'); // ajax for not logged in users

function qcopd_load_video_function(){
	
	$video_link = trim($_POST['videurl']);
	
	$video_link = str_replace('watch?v=','embed/',$video_link);
	
	$urls = parse_url($video_link);
	if(isset($urls['host']) && $urls['host']=='vimeo.com'){
		
		$videoId = explode('/',$video_link);
		
		$video_link = 'https://player.vimeo.com/video/'.end($videoId);
		echo '<iframe width="560" height="315" src="'.$video_link.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
	}else{
		echo '<iframe width="560" height="315" src="'.$video_link.'?rel=0&amp;showinfo=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
	}
	
	

	die();
}



add_action('wp_ajax_qcopd_load_video', 'qcopd_load_video_function'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_load_video', 'qcopd_load_video_function'); // ajax for not logged in users
