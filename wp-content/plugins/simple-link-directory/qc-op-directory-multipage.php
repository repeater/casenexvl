<?php
defined('ABSPATH') or die("No direct script access!");

function sld_show_category(){
	
	global $wp,$post;
	$current_url = home_url( $wp->request );
	$cterms = get_terms( 'sld_cat', array(
		'hide_empty' => true,
		'orderby' => 'name',
		'order' => 'ASC' 
	) );
	
	if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
		$optionPage = get_page(sld_get_option_page('sld_directory_page'));
		
		if (strpos($current_url, $optionPage->post_name) === false) {
			$current_url = $current_url.'/'.$optionPage->post_name;
		}
		
	}
	
	if(!empty($cterms)){
		
?>
		<link rel="stylesheet" type="text/css" href="<?php echo OCOPD_TPL_URL . "/style-multipage/style.css"; ?>" />
		<link rel="stylesheet" type="text/css" href="<?php echo OCOPD_TPL_URL . "/style-multipage/responsive.css"; ?>" />
		<div class="qcld_sld_category_list">
			<ul>
			<?php
				$ci = 0;
				foreach ($cterms as $cterm){
					?>
						<li>
						
							<div class="column-grid3">
								<div class="sld-main-content-area bg-color-0<?php echo (($ci%5)+1); ?>">
									<div class="sld-main-panel">
										<div class="panel-title">
											<h3><?php echo $cterm->name; ?></h3>
										</div>
										<?php $image_id = get_term_meta ( $cterm -> term_id, 'category-image-id', true );
										if($image_id){
										?>
										<div class="feature-image">					
											<?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
										</div>
										<?php } ?>
									</div>
									<div class="sld-hover-content">
										<p><?php echo $cterm->description; ?></p>
										<?php 
											if(sld_get_option('sld_lan_visit_page')!=''){
												$visit_page = sld_get_option('sld_lan_visit_page');
											}else{
												$visit_page = __('Visit Page','qc-opd');
											}
										?>
										<a href="<?php echo $current_url; ?>/<?php echo $cterm->slug; ?>" ><?php echo $visit_page; ?></a>
									</div>															
								</div>
							</div>
						
						</li>
					<?php
					$ci++;					
				}
			?>
			</ul>
		</div>
<?php

	}
}

add_shortcode('qcopd-directory-multipage', 'qcopd_directory_multipage_full_shortcode');
function qcopd_directory_multipage_full_shortcode( $atts = array() ){
	global $wp_query, $wp;
	ob_start();
	
	extract( shortcode_atts(
		array(
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'item_orderby' => "",
		), $atts
	));
	
	
	$current_url = home_url( $wp->request );
	if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
		$optionPage = get_page(sld_get_option_page('sld_directory_page'));
		
		if (strpos($current_url, $optionPage->post_name) === false) {
			$current_url = $current_url.'/'.$optionPage->post_name;
		}
		
	}
	

	$customCss = sld_get_option( 'sld_custom_style' );

	if( trim($customCss) != "" ) :
	?>
	<style>
		<?php echo trim($customCss); ?>
	</style>

	<?php endif;
	
	
    if((isset($wp_query->query_vars['slditem']) && $wp_query->query_vars['slditem']!='') or (isset($wp_query->query_vars['slditemname']) && $wp_query->query_vars['slditemname']!='')){
		
		$slditem = $wp_query->query_vars['slditem'];
		
		if($post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' )){
			
			$lists = get_post_meta( $post->ID, 'qcopd_list_item01' );
			$citem = '';
			foreach($lists as $k=>$list){
				
				if(!isset($wp_query->query_vars['slditem'])):
					$slditem = $wp_query->query_vars['slditemname'];
					$list['qcopd_timelaps'] = str_replace(' ','-',strtolower($list['qcopd_item_title']));
				endif;
				
				if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']==$slditem){
					
					$citem = $k;
				?>
					<div class="sld_single_item_container">
						<div class="sld_single_breadcrumb">
							<ul class="sld_breadcrumb">
							
							<?php 
							$bcurl = get_page_link();
							if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
								$optionPage = get_page(sld_get_option_page('sld_directory_page'));
								
								if (strpos($bcurl, $optionPage->post_name) === false) {
									$bcurl = $bcurl.$optionPage->post_name.'/';
								}
							}
							?>
							
								<li><a href="<?php echo $bcurl; ?>"><?php echo get_the_title(); ?></a></li>
							  <?php if(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''): ?>
								<li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat']; ?>"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])); ?></a></li>
							 <?php endif; ?>
							 
							 <?php if(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''): ?>
								<li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat'].'/'.$wp_query->query_vars['sldlist']; ?>"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist'])); ?></a></li>
								<li class="sld_breadcrumb_last_child"><a href="#" disabled><?php echo $list['qcopd_item_title'] ?></a></li>
							<?php endif; ?>
							</ul>
							
							<div class="upvote-section upvote-section-style-single">
								<span data-post-id="<?php echo $post->ID; ?>" data-unique="<?php echo $post->ID.'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="sld-upvote-btn-single upvote-on">
									<i class="fa fa-thumbs-up"></i>
								</span>
								<span class="upvote-count count">
									<?php
									  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
										echo (int)$list['qcopd_upvote_count'];
									  }
									?>
								</span>
							</div>

						</div>
						
						<div class="sld_single_content">
						
								<div class="feature-image">
								<?php
									$iconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

									$showFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "") ? $list['qcopd_use_favicon'] : "";

									$faviconImgUrl = "";
									$faviconFetchable = false;
									$filteredUrl = "";
									$item_url = $list['qcopd_item_link'];
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
									<?php if( isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>


										<?php
											$img = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
											
										?>
										<img src="<?php echo $img[0]; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">


									<?php elseif( $iconClass != "" ) : ?>

										<span class="icon fa-icon">
											<i class="fa <?php echo $iconClass; ?>"></i>
										</span>

									<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>

										<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">

									<?php else : ?>

										<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $list['qcopd_item_title']; ?>">

									<?php endif; ?>
								</div>
							
							
								<h2><?php echo $list['qcopd_item_title']; ?></h2>
								<p><?php echo $list['qcopd_item_subtitle'] ?></p>
								
								<?php echo (isset($list['qcopd_description'])?apply_filters('the_content', $list['qcopd_description']):''); ?>
								
								<div class="sld_resource_action">
									<?php 
										if(sld_get_option('sld_lan_visit_link')!=''){
											$visitlink = sld_get_option('sld_lan_visit_link');
										}else{
											$visitlink = __('Visit This Link','qc-opd');
										}
									?>
									<a href="<?php echo $list['qcopd_item_link']; ?>" target="_blank" class="sld_single_button"><?php echo $visitlink; ?></a>
									
									<nav class="sld-nav-socials">
										<h5 class="sld-social-title">Share</h5>
										<ul>
											<li class="nav-socials__item">
												
												<a href="https://twitter.com/share?url=<?php echo $current_url; ?>/&amp;text=<?php echo urlencode($list['qcopd_item_title']); ?>" title="Twitter" target="_blank">
													<i class="fa fa-twitter"></i>
													
												</a>
											</li>
											<li class="nav-socials__item">
												
												<a href="https://facebook.com/sharer.php?u=<?php echo $current_url; ?>/&amp;t=<?php echo urlencode($list['qcopd_item_title']); ?>+<?php echo $current_url; ?>/" title="Facebook" target="_blank">
													<i class="fa fa-facebook-f"></i>
												</a>
											</li>
											<li class="nav-socials__item">
												
												<?php
													$param = '';
													if(isset($list['qcopd_item_img']) && $list['qcopd_item_img']!=''){
														$imgurlm = wp_get_attachment_image_src($list['qcopd_item_img'], 'medium');
														if(isset($imgurlm[0]) && $imgurlm[0]!=''){
															$param = '&amp;media='.$imgurlm[0];
														}
													}
												?>
												
												<a href="https://pinterest.com/pin/create/button/?url=<?php echo $current_url; ?>/<?php echo $param; ?>&amp;description=<?php echo urlencode($list['qcopd_item_title']); ?>" title="Pinterest" target="_blank">
													<i class="fa fa-pinterest-p"></i>
												</a>
											</li>
										</ul>
									</nav>									
									
								</div>
								
								<?php
									if(sld_get_option('sld_show_alexa_rank')=='on'):
									$rankdata = sld_alexaRank(parse_url($list['qcopd_item_link'])['scheme'].'://'.parse_url($list['qcopd_item_link'])['host']);
									if(!empty($rankdata)):
								?>
								<div class="sld_alexa_rank">
									<span>Alexa Global Rank - <?php echo $rankdata['globalRank'][0]; ?></span> <br> <span> <?php echo 'Alexa Country '.$rankdata['CountryRank']['@attributes']['NAME']; ?> - <?php echo $rankdata['CountryRank']['@attributes']['RANK']; ?></span>
								</div>
								<?php 
									endif;
									endif;
								?>

							
						</div>
						
					</div>
				<?php	
				}
			}
			?>
			<?php if(count($lists)>3): ?>
				<link rel="stylesheet" type="text/css" href="<?php echo OCOPD_TPL_URL . "/style-multipage/style.css"; ?>" />
				<link rel="stylesheet" type="text/css" href="<?php echo OCOPD_TPL_URL . "/style-multipage/responsive.css"; ?>" />
				<div class="sld_single_related_content">
					<?php
						$it=0;
						$relatedArray = array();
						foreach($lists as $f=>$list){
							if($f>$citem && $it<3){
								$relatedArray[] = $list;
								$it++;
							}
						}
						if(count($relatedArray) < 3 and count($lists) > 3){
							for($rr=0;count($relatedArray)<3;$rr++){
								
								$relatedArray[] = $lists[$rr];
							}
						}
						if(sld_get_option('sld_lan_related_items')!=''){
							$relateditems = sld_get_option('sld_lan_related_items');
						}else{
							$relateditems = __('Related Items','qc-opd');
						}
						
					?>
					<h2><?php echo $relateditems; ?></h2>	
					<div class="qcld_sld_category_list">
					
						<ul class="sld_single_related tooltip_tpl12-tpl sld-list" id="jp-list-<?php echo $post->ID; ?>">
							<?php $count = 1; ?>
							<?php foreach( $relatedArray as $list ) : ?>
							<?php
								$canContentClass = "subtitle-present";

								if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
								{
									$canContentClass = "subtitle-absent";
								}
							?>
							
							<li id="item-<?php echo $post->ID ."-". $count; ?>" class="sld-26">
								<?php
									$item_url = $list['qcopd_item_link'];
									$masked_url = $list['qcopd_item_link'];
									$mask_url = 'off';
									if( $mask_url == 'on' ){
										$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
									}
								?>
								<div class="column-grid3">
									<div class="sld-main-content-area bg-color-0<?php echo (($count%5)+1); ?>">
										<div class="sld-main-panel">
											<div class="panel-title">
												<h3><?php
													echo trim($list['qcopd_item_title']);
												?></h3>
											</div>
											<div class="feature-image">
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
													<?php if(isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>


														<?php
																$img = wp_get_attachment_image_src($list['qcopd_item_img']);
															?>
															<img src="<?php echo $img[0]; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">


													<?php elseif( $iconClass != "" ) : ?>

													<span class="icon fa-icon">
														<i class="fa <?php echo $iconClass; ?>"></i>
													</span>

													<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>


														<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">


													<?php else : ?>

														<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $list['qcopd_item_title']; ?>">

													<?php endif; ?>
											</div>
											
										</div>
										<div class="sld-hover-content">
											<div class="style-14-upvote-section">
												

												<!-- upvote section -->
												<div class="upvote-section upvote-section-style-14 upvote upvote-icon">
													<span data-post-id="<?php echo $post->ID; ?>" data-unique="<?php echo $post->ID.'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
														<i class="fa fa-thumbs-up"></i>
													</span>
													<span class="upvote-count count">
														<?php
														  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
															echo (int)$list['qcopd_upvote_count'];
														  }
														?>
													</span>
												</div>
												<!-- /upvote section -->

											
											
											</div>
											<p><?php echo trim($list['qcopd_item_subtitle']); ?></p>
											<?php 
												if(sld_get_option('sld_lan_visit_page')!=''){
													$visit_page = sld_get_option('sld_lan_visit_page');
												}else{
													$visit_page = __('Visit Page','qc-opd');
												}
											?>
											<?php 
											$bcurl = get_page_link();
											if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
												$optionPage = get_page(sld_get_option_page('sld_directory_page'));
												
												if (strpos($bcurl, $optionPage->post_name) === false) {
													$bcurl = $bcurl.$optionPage->post_name.'/';
												}
											}
											?>
											<a href="<?php echo $bcurl.$wp_query->query_vars['sldcat'].'/'.$wp_query->query_vars['sldlist'].'/'.urlencode(str_replace(' ','-',strtolower(trim($list['qcopd_item_title'])))).'/'.$list['qcopd_timelaps']; ?>" ><?php echo $visit_page; ?></a>

										</div>
									</div>
								</div>
							</li>

							<?php $count++; endforeach; ?>

						</ul>
					</div>					
				</div>
			<?php endif; ?>
			<?php
		}
		
		
	}
	elseif(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''){
		
		if ( $post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' ) )
			$id = $post->ID;
		else
			$id = 0;
		
		if($id>0){
			?>
				<div class="sld_single_breadcrumb">
					<ul class="sld_breadcrumb">
					
					<?php 
							$bcurl = get_page_link();
							if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
								$optionPage = get_page(sld_get_option_page('sld_directory_page'));
								
								if (strpos($bcurl, $optionPage->post_name) === false) {
									$bcurl = $bcurl.$optionPage->post_name.'/';
								}
							}
							?>
					
					
						<li><a href="<?php echo $bcurl; ?>"><?php echo get_the_title(); ?></a></li>
					  <?php if(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''): ?>
						<li><a href="<?php echo $bcurl.$wp_query->query_vars['sldcat']; ?>"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])); ?></a></li>
					 <?php endif; ?>
					 
					 <?php if(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''): ?>
						<li class="sld_breadcrumb_last_child"><a href="#"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist'])); ?></a></li>
					<?php endif; ?>
					</ul>
				</div>
			<?php
			echo do_shortcode('[qcopd-directory mode="one" list_id="'.$id.'" style="style-multipage" column="3" upvote="on" search="true" item_count="on" orderby="'.$orderby.'" filterorderby="date" order="'.$order.'" filterorder="ASC" paginate_items="false" favorite="disable" tooltip="true" list_title_font_size="" item_orderby="'.$item_orderby.'" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing="" multipage="true"]');
		}
		
	}elseif(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''){
		?>
		<div class="sld_single_breadcrumb">
			<ul class="sld_breadcrumb">
			<?php 
				$bcurl = get_page_link();
				if(sld_get_option_page('sld_directory_page')==get_option( 'page_on_front' )){
					$optionPage = get_page(sld_get_option_page('sld_directory_page'));
					
					if (strpos($bcurl, $optionPage->post_name) === false) {
						$bcurl = $bcurl.$optionPage->post_name.'/';
					}
				}
				?>
				<li><a href="<?php echo $bcurl; ?>"><?php echo get_the_title(); ?></a></li>
			  <?php if(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''): ?>
				<li class="sld_breadcrumb_last_child"><a href="#"><?php echo str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])); ?></a></li>
			 <?php endif; ?>
			 
			 
			</ul>
		</div>
		<?php
		echo do_shortcode('[qcopd-directory category="'.$wp_query->query_vars['sldcat'].'" style="style-multipage" column="3" upvote="on" search="true" item_count="on" orderby="'.$orderby.'" filterorderby="date" order="'.$order.'" filterorder="ASC" paginate_items="false" favorite="disable" tooltip="false" list_title_font_size="" item_orderby="'.$item_orderby.'" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing="" multipage="true"]');

	}else{
		sld_show_category();
	}
	
    $content = ob_get_clean();
    return $content;
}


add_filter('pre_get_document_title', 'sld_wp_title_for_multipage', 20);
function sld_wp_title_for_multipage( $title )
{
	global $wp_query, $wp;
	
	if((isset($wp_query->query_vars['slditem']) && $wp_query->query_vars['slditem']!='') or (isset($wp_query->query_vars['slditemname']) && $wp_query->query_vars['slditemname']!='')){
		
		if($post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' )){
			$lists = get_post_meta( $post->ID, 'qcopd_list_item01' );
			$slditem = $wp_query->query_vars['slditem'];
			foreach($lists as $list){
				if(!isset($wp_query->query_vars['slditem'])):
					$slditem = $wp_query->query_vars['slditemname'];
					$list['qcopd_timelaps'] = str_replace(' ','-',strtolower($list['qcopd_item_title']));
				endif;
				if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']==$slditem){
					$title = trim($list['qcopd_item_title']).' | '.get_bloginfo( 'name' ); 
				}
			}
		}
	}
	elseif(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''){
		
		$title = str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist'])).' | '.get_bloginfo( 'name' );
		
	}elseif(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''){
		$title = str_replace('-',' ',ucfirst($wp_query->query_vars['sldcat'])).' | '.get_bloginfo( 'name' ); 
	}

   /*  
    $title['page'] = '2'; // optional
    $title['tagline'] = 'Home Of Genesis Themes'; // optional
    $title['site'] = 'DevelopersQ'; //optional
    */
	
    return $title; 
}


add_action('wp_head', 'sldmyCallbackToAddMeta', 1);
function sldmyCallbackToAddMeta(){
	
	global $wp_query, $wp;
	
	if((isset($wp_query->query_vars['slditem']) && $wp_query->query_vars['slditem']!='') or (isset($wp_query->query_vars['slditemname']) && $wp_query->query_vars['slditemname']!='')){
		
		if($post = get_page_by_path( $wp_query->query_vars['sldlist'], OBJECT, 'sld' )){
			$lists = get_post_meta( $post->ID, 'qcopd_list_item01' );
			$slditem = $wp_query->query_vars['slditem'];
			foreach($lists as $list){
				if(!isset($wp_query->query_vars['slditem'])):
					$slditem = $wp_query->query_vars['slditemname'];
					$list['qcopd_timelaps'] = str_replace(' ','-',strtolower($list['qcopd_item_title']));
				endif;
				if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']==$slditem){
					
					$title = trim($list['qcopd_item_title']).' | '.get_bloginfo( 'name' ); 
					$description = trim($list['qcopd_item_subtitle']);
					echo "<meta name='description' content='".$description."'>\n";
				}
			}
		}
		
	}elseif(isset($wp_query->query_vars['sldlist']) && $wp_query->query_vars['sldlist']!=''){
		
		$post = get_term_by('slug', $wp_query->query_vars['sldcat'], 'sld_cat');
		if(!empty($post)){
			echo "<meta name='description' content='".str_replace('-',' ',ucfirst($wp_query->query_vars['sldlist']))." - ".$post->description."'>\n";
		}
		
	}elseif(isset($wp_query->query_vars['sldcat']) && $wp_query->query_vars['sldcat']!=''){
		
		$post = get_term_by('slug', $wp_query->query_vars['sldcat'], 'sld_cat');
		if(!empty($post)){
			echo "<meta name='description' content='".$post->description."'>\n";
		}
		
	}
  
}




function sld_custom_rewrite_tag() {
  add_rewrite_tag('%sldcat%', '([^&]+)');
  add_rewrite_tag('%sldlist%', '([^&]+)');
  add_rewrite_tag('%slditemname%', '([^&]+)');
  add_rewrite_tag('%slditem%', '([^&]+)');
}
add_action('init', 'sld_custom_rewrite_tag', 10, 0);



function sld_custom_rewrite_rule4() {
	if(sld_get_option('sld_enable_multipage')=='on'){
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);
			
			add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]&sldlist=$matches[2]&slditemname=$matches[3]&slditem=$matches[4]','top');

		}
	}

}
add_action('init', 'sld_custom_rewrite_rule4', 10, 0);

function sld_custom_rewrite_rule3() {
	if(sld_get_option('sld_enable_multipage')=='on'){
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);
			
			add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/([^/]*)/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]&sldlist=$matches[2]&slditemname=$matches[3]','top');

		}
	}

}
add_action('init', 'sld_custom_rewrite_rule3', 10, 0);

function sld_custom_rewrite_rule() {
	if(sld_get_option('sld_enable_multipage')=='on'){
		
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);
			
			add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]&sldlist=$matches[2]','top');

		}
		
	}

}
add_action('init', 'sld_custom_rewrite_rule', 10, 0);


function sld_custom_rewrite_rule1() {
	
	if(sld_get_option('sld_enable_multipage')=='on'){
	
		$optionPageId = sld_get_option_page('sld_directory_page');
		
		if($optionPageId==''){
			$findid = qc_get_id_by_shortcode('qcopd-directory-multipage');
			if($findid!=''){
				update_option( 'sld_directory_page', $findid );
				$optionPageId = $findid;
			}
			
		}
		
		if($optionPageId!=''){
			$optionPage = get_page($optionPageId);
			
			add_rewrite_rule('^'.$optionPage->post_name.'/([^/]*)/?','index.php?pagename='.$optionPage->post_name.'&sldcat=$matches[1]','top');
			
			
		}
	}

}
add_action('init', 'sld_custom_rewrite_rule1', 10, 0);
function sld_184163_disable_canonical_front_page( $redirect ) {
    if ( is_page() && $front_page = get_option( 'page_on_front' ) ) {
        if ( is_page( $front_page ) )
            $redirect = false;
    }

    return $redirect;
}

add_filter( 'redirect_canonical', 'sld_184163_disable_canonical_front_page' );

/**
 * Detect shortcodes and update the plugin options
 * @param post_id of an updated post
 */
function sld_multipage_get_pages_with_shortcodes($post_ID){
	
	$post = get_post( $post_ID );
	if ( has_shortcode( $post->post_content, 'qcopd-directory-multipage' ) ) {
		update_option( 'sld_directory_page', $post->ID );
	}
}
add_action( 'wp_insert_post', 'sld_multipage_get_pages_with_shortcodes', 1);

function sld_get_option_page($page, $param = false) {
	return get_option($page, $param);
}
