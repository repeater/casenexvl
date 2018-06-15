<?php wp_enqueue_style('sld-css-style-multi', OCOPD_TPL_URL . "/$template_code/style.css" ); ?>
<?php wp_enqueue_style('sld-css-style-multi-responsive', OCOPD_TPL_URL . "/$template_code/responsive.css" ); ?>

<?php if( $paginate_items == true ) : ?>
	<?php
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-jpages', OCOPD_TPL_URL . "/simple/jPages.min.js", array('jquery'));
		wp_enqueue_style('sld-jpages-css', OCOPD_TPL_URL . "/simple/jPages.css" );
		wp_enqueue_style('sld-animate-css', OCOPD_TPL_URL . "/simple/animate.css" );
	?>
<?php endif; ?>

<?php
	$customCss = sld_get_option( 'sld_custom_style' );

	if( trim($customCss) != "" ) :
?>
	<style>
		<?php echo trim($customCss); ?>
	</style>

<?php endif; ?>

<?php

// The Loop
if ( $list_query->have_posts() )
{

	//Getting Settings Values
	if($search=='true'){
		$searchSettings = 'on';
	}else{
		if($search=='false'){
			$searchSettings = 'off';
		}else{
			$searchSettings = sld_get_option( 'sld_enable_search' );
		}
	}
	$itemAddSettings = sld_get_option( 'sld_enable_add_new_item' );
	$itemAddLink = sld_get_option( 'sld_add_item_link' );
	$enableTopArea = sld_get_option( 'sld_enable_top_part' );
	$enableFiltering = sld_get_option( 'sld_enable_filtering' );

	//Check if border should be set
	$borderClass = "";

	if( $searchSettings == 'on' || $itemAddSettings == 'on' )
	{
		$borderClass = "sld-border-bottom";
	}

	//Hook - Before Search Template
	do_action( 'qcsld_before_search_tpl', $shortcodeAtts);

	//If the top area is not disabled (both serch and add item)
	if( $enableTopArea == 'on' && $top_area != 'off' ) :

		//Load Search Template
		require ( dirname(__FILE__) . "/search-template.php" );

	endif;

	//Hook - Before Filter Template
	do_action( 'qcsld_before_filter_tpl', $shortcodeAtts);

	//Enable Filtering
	if( $enableFiltering == 'on' && $mode == 'all' ) :

		//Load Search Template
		require ( dirname(__FILE__) . "/filter-template.php" );

	endif;

    if(sld_get_option('sld_enable_filtering_left')=='on') {
	    $args = array(
		    'numberposts' => - 1,
		    'post_type'   => 'sld',
		    'orderby'     => $filterorderby,
			'order'       => $filterorder,
	    );

	    if ( $category != "" ) {
		    $taxArray = array(
			    array(
				    'taxonomy' => 'sld_cat',
				    'field'    => 'slug',
				    'terms'    => $category,
			    ),
		    );

		    $args = array_merge( $args, array( 'tax_query' => $taxArray ) );

	    }

	    $listItems = get_posts( $args );
	    ?>
        <style>
            .filter-area {

                position: relative;
            }

            .slick-prev::before, .slick-next::before {
                color: #489fdf;
            }

            .slick-prev, .slick-next {
                transform: translate(0px, -80%);
            }
        </style>
        <div class="filter-area-main sld_filter_mobile_view">
            <div class="filter-area" style="width: 100%;">

                <div class="filter-carousel">
                    <div class="item">
					<?php 
						$item_count_disp_all = '';
						foreach ($listItems as $item){
							if( $item_count == "on" ){
								$item_count_disp_all += count(get_post_meta( $item->ID, 'qcopd_list_item01' ));
							}
						}
					?>
					<a href="#" class="filter-btn" data-filter="all">
						<?php _e('Show All', 'qc-opd'); ?>
						<?php
							if($item_count == 'on'){
								echo '<span class="opd-item-count-fil">('.$item_count_disp_all.')</span>';
							}
						?>
					</a>
                    </div>

				    <?php foreach ( $listItems as $item ) :
					    $config = get_post_meta( $item->ID, 'qcopd_list_conf' );
					    $filter_background_color = '';
					    $filter_text_color = '';
					    if ( isset( $config[0]['filter_background_color'] ) and $config[0]['filter_background_color'] != '' ) {
						    $filter_background_color = $config[0]['filter_background_color'];
					    }
					    if ( isset( $config[0]['filter_text_color'] ) and $config[0]['filter_text_color'] != '' ) {
						    $filter_text_color = $config[0]['filter_text_color'];
					    }
						
					    ?>

					    <?php
					    $item_count_disp = "";

					    if ( $item_count == "on" ) {
						    $item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
					    }
					    ?>

                        <div class="item">
                            <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>"
                               style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>">
							    <?php echo $item->post_title; ?>
							    <?php
							    if ( $item_count == 'on' ) {
								    echo '<span class="opd-item-count-fil">(' . $item_count_disp . ')</span>';
							    }
							    ?>
                            </a>
                        </div>

				    <?php endforeach; ?>

                </div>

                <script>
                    jQuery(document).ready(function ($) {

                        var fullwidth = window.innerWidth;
                        if (fullwidth < 479) {
                            $('.filter-carousel').slick({


                                infinite: false,
                                speed: 500,
                                slidesToShow: 1,


                            });
                        } else {
                            $('.filter-carousel').slick({

                                dots: false,
                                infinite: false,
                                speed: 500,
                                slidesToShow: 1,
                                centerMode: false,
                                variableWidth: true,
                                slidesToScroll: 3,

                            });
                        }

                    });
                </script>

            </div>
        </div>
	    <?php
    }
	//If RTL is Enabled
	$rtlSettings = sld_get_option( 'sld_enable_rtl' );
	$rtlClass = "";

	if( $rtlSettings == 'on' )
	{
	   $rtlClass = "direction-rtl";
	}

	//Hook - Before Main List
	do_action( 'qcsld_before_main_list', $shortcodeAtts);

	//Directory Wrap or Container

	echo '<div class="qcopd-list-wrapper qc-full-wrapper">';

	echo '<div id="opd-list-holder" class="qc-grid qcopd-list-hoder '.$rtlClass.'">';
	global $wpdb;
	$outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

	$listId = 1;
	global $wp;
	$current_url = home_url( $wp->request );
	while ( $list_query->have_posts() )
	{
		$list_query->the_post();

		//$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
		$lists = array();
		$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = ".get_the_ID()." AND meta_key = 'qcopd_list_item01' order by `meta_id` ASC");
		if(!empty($results)){
			foreach($results as $result){
				$unserialize = @unserialize($result->meta_value);
				$lists[] = $unserialize;
			}
		}
		
		$lists = sldmodifyupvotes(get_the_ID(), $lists);
		
		$conf = get_post_meta( get_the_ID(), 'qcopd_list_conf', true );

		$addvertise = get_post_meta( get_the_ID(), 'sld_add_block', true );

		$addvertiseContent = isset($addvertise['add_block_text']) ? $addvertise['add_block_text'] : '';


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
		if(sld_get_option('sld_featured_item_top')=='on'){
			$lists = sld_featured_at_top($lists);
		}
		//adding extra variable in config
		@$conf['item_title_font_size'] = $title_font_size;
		@$conf['item_subtitle_font_size'] = $subtitle_font_size;
		@$conf['item_title_line_height'] = $title_line_height;
		@$conf['item_subtitle_line_height'] = $subtitle_line_height;
		?>



		<div id="qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-grid-item qcopd-list-column opd-column-<?php echo 1; echo " style-14";?> <?php echo "opd-list-id-" . get_the_ID(); ?>">

			<div class="opd-list-style-12 sld-container title_style_sld" style="<?php echo ($listId==1)?'margin-top:10px':''; ?>">

				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
					}
				?>
				
				<div class="main-title">
						<?php 
						if($multipage=='true'):
							echo '<a href="'.$current_url.'/'.get_post(get_the_ID())->post_name.'">';
						endif;
						?>
                            <h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>><?php echo get_the_title(); ?>
								<?php
									if($item_count == 'on'){
										echo '<span class="list-item-count">('.$item_count_disp.')</span>';
									}
								?>
								
							</h2>
						<?php 
						if($multipage=='true'):
							echo '</a>';
						endif;
						?>
                </div>
				
				<ul class="tooltip_tpl12-tpl sld-list" id="jp-list-<?php echo get_the_ID(); ?>">
					<?php $count = 1; ?>
					<?php foreach( $lists as $list ) : ?>
					<?php
						$canContentClass = "subtitle-present";

						if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
						{
							$canContentClass = "subtitle-absent";
						}
					?>
					
					<li id="item-<?php echo get_the_ID() ."-". $count; ?>" class="sld-26">
						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}
							
						?>
                            	<div class="column-grid<?php echo 3; ?>">
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
													<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>


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
													<?php if($sldfavorite=='on'): ?>
													<!-- upvote section -->
													<div class="bookmark-section bookmark-section-style-14">
													
														<?php 
														$bookmark = 0;
														if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
															$unv = explode(',',$list['qcopd_is_bookmarked']);
															if(in_array(get_current_user_id(),$unv) && get_current_user_id()!=0){
																$bookmark = 1;
															}
														}
														?>
													
													
														<span data-post-id="<?php echo get_the_ID(); ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" class="bookmark-btn bookmark-on">
															
															<i class="fa <?php echo ($bookmark==1?'fa-star':'fa-star-o'); ?>" aria-hidden="true"></i>
														</span>
														
													</div>
													<?php endif; ?>
													<?php if( $upvote == 'on' ) : ?>

													<!-- upvote section -->
													<div class="upvote-section upvote-section-style-14 upvote upvote-icon">
														<span data-post-id="<?php echo get_the_ID(); ?>" data-unique="<?php echo get_the_ID().'_'.$list['qcopd_timelaps']; ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
															<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
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

												<?php endif; ?>
												
												</div>
													
							
                                        	
											
                                            <p><?php echo trim($list['qcopd_item_subtitle']); ?></p>
											
											<?php 
												global $wp_query;
												//constructing new url for multipage//
												$newurl = home_url();
												if(isset($wp_query->query_vars['pagename']) and $wp_query->query_vars['pagename']!=''){
													$newurl = $newurl.'/'.$wp_query->query_vars['pagename'];
												}
												if(isset($wp_query->query_vars['sldcat']) and $wp_query->query_vars['sldcat']!=''){
													$newurl = $newurl.'/'.$wp_query->query_vars['sldcat'];
												}
												if(isset($wp_query->query_vars['sldlist']) and $wp_query->query_vars['sldlist']!=''){
													$newurl = $newurl.'/'.$wp_query->query_vars['sldlist'];
												}else{
													$newurl = $newurl.'/'.get_post(get_the_ID())->post_name;
												}
												
												
												if(isset($list['qcopd_item_title']) && $list['qcopd_item_title']!=''){
													$newurl = $newurl.'/'.urlencode(str_replace(' ','-',strtolower($list['qcopd_item_title'])));
												}
												
												if(isset($list['qcopd_timelaps']) && $list['qcopd_timelaps']!=''){
													$newurl = $newurl.'/'.trim($list['qcopd_timelaps']);
												}
												$item_url = $masked_url = $newurl;
												$othersetting = '';
												if(sld_get_option('sld_multi_same_window')=='off'){
													if(isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1){
														$othersetting .= ' rel="nofollow"';
													}
													if(isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1){
														$othersetting .=' target="_blank"';
													}
												}
												
												if(sld_get_option('sld_lan_visit_page')!=''){
													$visit_page = sld_get_option('sld_lan_visit_page');
												}else{
													$visit_page = __('Visit Page','qc-opd');
												}
											?>
											
												<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> href="<?php echo $masked_url; ?>" <?php echo $othersetting; ?> ><?php echo $visit_page; ?></a>
											

                                        </div>
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
                                    </div>
                                </div>
                    </li>

					<?php $count++; endforeach; ?>
					<?php if( $addvertiseContent != '' ) : ?>
                        
						<li class="sld-26">
                            	<div class="column-grid<?php echo $column; ?>">
                                	<div class="sld-main-content-area bg-color-01">
                                    	<div class="sld-main-panel">
                                        	<div class="panel-title">
                                            	<p><?php echo apply_filters('the_content',$addvertiseContent); ?></p>
                                            </div>
                                            
                                        </div>
                                        
                                    </div>
                                </div>
						</li>
					<?php endif; ?>
				</ul>
				<div style="clear:both;"></div>
				

			</div>
		</div>


		<?php

		$listId++;
	}

	echo '<div class="sld-clearfix"></div>
			</div>
		<div class="sld-clearfix"></div>
	</div>';

	//Hook - After Main List
	do_action( 'qcsld_after_main_list', $shortcodeAtts);

}

?>

