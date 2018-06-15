<!-- Style-1 Template -->
<!--Adding Template Specific Style -->
<?php wp_enqueue_style('sld-css-style-9', OCOPD_TPL_URL . "/$template_code/template.css" ); ?>

<?php if( $tooltip == true ) : ?>
<?php wp_enqueue_style('sld-css-style-9-tooltip', OCOPD_TPL_URL . "/$template_code/html5tooltips.css" ); ?>
<?php wp_enqueue_style('sld-css-style-9-tooltip-animate', OCOPD_TPL_URL . "/$template_code/html5tooltips.animation.css" ); ?>
<?php wp_enqueue_script('jquery-style-9-jpages', OCOPD_TPL_URL . "/$template_code/html5tooltips.js"); ?>

<?php endif; ?>

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

<!--<script>
	jQuery(document).ready(function($) {
		jQuery('.tooltip_tpl9').tooltipster({
			theme: 'tooltipster-punk'
		});
	});
</script>-->

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
	if( $enableFiltering == 'on' && $mode == 'all' && $enable_left_filter!='true') :

		//Load Search Template
		require ( dirname(__FILE__) . "/filter-template.php" );

	endif;

	if(sld_get_option('sld_enable_filtering_left')=='on' || $enable_left_filter=='true') {
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

                <?php if($cattabid==''): ?>
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
				<?php endif; ?>

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
	?>
	<?php
	if(sld_get_option('sld_enable_filtering_left')=='on' || $enable_left_filter=='true') {
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

		$filterType = sld_get_option( 'sld_filter_ptype' ); //normal, carousel

//If FILTER TYPE is NORMAL



			?>

            <div class="filter-area left-side-filter">

                <a href="#" class="filter-btn" data-filter="all">
					<?php _e( 'Show All', 'qc-opd' ); ?>
                </a>

	            <?php foreach ( $listItems as $item ) :
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

		            if ( $item_count == "on" ) {
			            $item_count_disp = count( get_post_meta( $item->ID, 'qcopd_list_item01' ) );
		            }
		            ?>

                    <a href="#" class="filter-btn" data-filter="opd-list-id-<?php echo $item->ID; ?>" style="background:<?php echo $filter_background_color ?>;color:<?php echo $filter_text_color ?>">
			            <?php echo $item->post_title; ?>
			            <?php
			            if ( $item_count == 'on' ) {
				            echo '<span class="opd-item-count-fil">(' . $item_count_disp . ')</span>';
			            }
			            ?>
                    </a>

	            <?php endforeach; ?>

            </div>




		<?php
	}
	?>
	<?php
	echo '<div id="opd-list-holder" class="qc-grid qcopd-list-hoder '.$rtlClass.'">';
	global $wpdb;
	if(is_user_logged_in() and $sldfavorite=='on'){
		$b_title = sld_get_option('sld_bookmark_title');
		
		$userid = get_current_user_id();
		$user_meta_data = get_user_meta($userid, 'sld_bookmark_user_meta');
		
		
		$conf['list_bg_color'] = sld_get_option('sld_bookmark_item_background_color');
		
		$conf['list_bg_color_hov'] = sld_get_option('sld_bookmark_item_background_color_hover');
		
		$conf['list_txt_color_hov'] = sld_get_option('sld_bookmark_item_text_color_hover');
		$conf['list_txt_color'] = sld_get_option('sld_bookmark_item_text_color');
		
		$conf['list_border_color'] = sld_get_option('sld_bookmark_item_border_color');
		
		$conf['item_bdr_color'] = sld_get_option('sld_bookmark_item_border_color');
		$conf['item_bdr_color_hov'] = sld_get_option('sld_bookmark_item_border_color_hover');
		
		$conf['list_subtxt_color'] = sld_get_option('sld_bookmark_item_sub_text_color');
		$conf['list_subtxt_color_hov'] = sld_get_option('sld_bookmark_item_sub_text_color_hover');
			
?>

<style>

			#bookmark_list.style-9 ul{
				border-top: 5px solid <?php echo $conf['item_bdr_color']; ?>;
				padding-top: 10px !important;
			}
			#bookmark_list.style-9 li a{
				color: <?php echo $conf['list_txt_color']; ?>;
				<?php if($conf['item_title_font_size']!=''): ?>
				font-size:<?php echo $conf['item_title_font_size']; ?> !important;
				<?php endif; ?>

				<?php if($conf['item_title_line_height']!=''): ?>
				line-height:<?php echo $conf['item_title_line_height']; ?> !important;
				<?php endif; ?>
			}
			#bookmark_list.style-9 li a:hover {
				color: <?php echo $conf['list_txt_color_hov']; ?>;

			}

			#bookmark_list.style-9 .tooltip_tpl9:before {
				color: <?php echo $conf['list_txt_color_hov']; ?>;
				<?php if($conf['item_subtitle_font_size']!=''): ?>
				font-size:<?php echo $conf['item_subtitle_font_size']; ?> !important;
				<?php endif; ?>

				<?php if($conf['item_subtitle_line_height']!=''): ?>
				line-height:<?php echo $conf['item_subtitle_line_height']; ?>!important;
				<?php endif; ?>
			  background-color: <?php echo $conf['list_subtxt_color_hov']; ?>;
			  border: 1px solid;
			  border-color: <?php echo $conf['list_txt_color_hov']; ?>;
			}



		</style>
		<div id="bookmark_list" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " " . $style;?> <?php echo "opd-list-id-" . get_the_ID(); ?>">

			<div class="opd-list-style-9">
				<h2><?php echo ($b_title!=''?$b_title:'Quick Links'); ?></h2>
				<ul class="tooltip_tpl9-tpl" id="sld_bookmark_ul">
					
					<?php
					$lists = array();
			if(!empty($user_meta_data)){
				foreach($user_meta_data[0] as $postid=>$metaids){
					
					if(!empty($metaids)){
						foreach($metaids as $metaid){
							
							$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = $postid AND meta_key = 'qcopd_list_item01'");
							if(!empty($results)){
								foreach ($results as $key => $value) {
									$unserialized = unserialize($value->meta_value);
									if (trim($unserialized['qcopd_timelaps']) == trim($metaid)) {
										$customdata = $unserialized;
										$customdata['postid'] = $postid;
										$lists[] = $customdata;
									}
								}
							}
							
						}
					}
				}
			}
					usort($lists, "custom_sort_by_tpl_title");
					$b = 1;			
					foreach($lists as $list){
					?>
					<?php
						$canContentClass = "subtitle-present";

						if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
						{
							$canContentClass = "subtitle-absent";
						}
					?>
					<li id="sld_bookmark_li_<?php echo $b; ?>">

						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}

						if(isset($list['qcopd_item_subtitle']) and $list['qcopd_item_subtitle']!=''){
							$toolipsetup = ' data-tooltip="'.$list['qcopd_item_subtitle'].'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
						}else{
							$toolipsetup = '';
						}

						?>

						<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo $toolipsetup; ?> <?php echo (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> href="<?php echo $masked_url; ?>" <?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> >

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
								<span class="logo-icon">
									<?php
										$img = wp_get_attachment_image_src($list['qcopd_item_img']);
									?>
									<img src="<?php echo $img[0]; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="logo-icon">
									<i class="fa <?php echo $iconClass; ?>"></i>
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="logo-icon favicon-loaded">
									<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php else : ?>
								<span class="logo-icon">
									<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php endif; ?>

							<span class="li-txt">
								<?php
									echo trim($list['qcopd_item_title']);
								?>
							</span>
						</a>

						<div class="bookmark-section bookmark-section-style-9">
							
								<?php 
								$bookmark = 1;
								if(isset($list['qcopd_is_bookmarked']) and $list['qcopd_is_bookmarked']!=''){
									$unv = explode(',',$list['qcopd_is_bookmarked']);
									if(in_array(get_current_user_id(),$unv)){
										$bookmark = 1;
									}
								}
								?>
							
							
								<span data-post-id="<?php echo $list['postid']; ?>" data-item-code="<?php echo trim($list['qcopd_timelaps']); ?>" data-is-bookmarked="<?php echo ($bookmark); ?>" data-li-id="sld_bookmark_li_<?php echo $b; ?>" class="bookmark-btn bookmark-on">
									
									<i class="fa fa-times-circle" aria-hidden="true"></i>
								</span>
								
							</div>

						

					</li>

				<?php $b++; }; ?>
				</ul>

				

			</div>
		</div>
<?php
		
	
}
	$outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

	$listId = 1;

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

		<style>

			#qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>.style-9 ul{
				border-top: 5px solid <?php echo $conf['item_bdr_color']; ?>;
				padding-top: 10px !important;
			}
			#qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>.style-9 li a{
				color: <?php echo $conf['list_txt_color']; ?>;
				<?php if($conf['item_title_font_size']!=''): ?>
				font-size:<?php echo $conf['item_title_font_size']; ?> !important;
				<?php endif; ?>

				<?php if($conf['item_title_line_height']!=''): ?>
				line-height:<?php echo $conf['item_title_line_height']; ?> !important;
				<?php endif; ?>
			}
			#qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>.style-9 li a:hover {
				color: <?php echo $conf['list_txt_color_hov']; ?>;

			}

			#qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>.style-9 .tooltip_tpl9:before {
				color: <?php echo $conf['list_txt_color_hov']; ?>;
				<?php if($conf['item_subtitle_font_size']!=''): ?>
				font-size:<?php echo $conf['item_subtitle_font_size']; ?> !important;
				<?php endif; ?>

				<?php if($conf['item_subtitle_line_height']!=''): ?>
				line-height:<?php echo $conf['item_subtitle_line_height']; ?>!important;
				<?php endif; ?>
			  background-color: <?php echo $conf['list_subtxt_color_hov']; ?>;
			  border: 1px solid;
			  border-color: <?php echo $conf['list_txt_color_hov']; ?>;
			}

			#item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block .advertise-block.tpl-default{
				border: 1px solid #dedede;
				border-top: 5px solid <?php echo $conf['item_bdr_color']; ?>;
				margin-bottom: 25px;
			}

			#item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block .advertise-block.tpl-default ul{
				border: none;
				box-shadow: none !important;
				margin-bottom: 0 !important;
			}

		</style>

		<?php if( $paginate_items === 'true' ) : ?>

			<script>
				jQuery(document).ready(function($){
					$("#jp-holder-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>").jPages({
		    			containerID : "jp-list-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>",
		    			perPage : <?php echo $per_page; ?>,
		  			});
					
					
					$(".sld_search_filter").keyup(function(){

						setTimeout(function(){
							$("#jp-holder-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>").jPages({
								containerID : "jp-list-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>",
								perPage : <?php echo $per_page; ?>,
							});
							$('.qc-grid').packery({
							  itemSelector: '.qc-grid-item',
							  gutter: 10
							});
						}, 900);

					})
					
					

				});
				
			</script>

		<?php endif; ?>

		<div id="qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " " . $style;?> <?php echo "opd-list-id-" . get_the_ID(); ?>">

			<div class="opd-list-style-9 qcopd-single-list">
				<?php
					$item_count_disp = "";

					if( $item_count == "on" ){
						$item_count_disp = count(get_post_meta( get_the_ID(), 'qcopd_list_item01' ));
					}
				?>
				
				<?php if($removetop!='yes'): ?>
					<h2 <?php echo (isset($conf['list_title_color'])&&$conf['list_title_color']!=''?'style="color:'.$conf['list_title_color'].';"':''); ?>>
						<?php 
					if($multipage=='true'):
						echo '<a href="'.$current_url.'/'.get_post(get_the_ID())->post_name.'">';
					elseif(isset($conf['title_link']) && $conf['title_link']!=''):
						echo '<a href="'.$conf['title_link'].'" '.(isset($conf['title_link_new_tab'])&&$conf['title_link_new_tab']==1?'target="_blank"':'').'>';
					endif;
					?>
					<?php echo get_the_title(); ?>
					<?php
						if($item_count == 'on'){
							echo '<span class="opd-item-count">('.$item_count_disp.')</span>';
						}
					?>
					<?php 
					if($multipage=='true' or @$conf['title_link']!=''):
						echo '</a>';
					endif;
					?>
					</h2>
				<?php endif; ?>
				
				<ul class="tooltip_tpl9-tpl" id="jp-list-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>" <?php echo ($removetop=='yes'?'style="border-top:unset !important;"':''); ?>>
					<?php $count = 1; $total = count( $lists ); 
					if( $item_orderby == 'upvotes' )
						{
    						usort($lists, "custom_sort_by_tpl_upvotes");
						}
						
						if( $item_orderby == 'clicks' )
						{
    						usort($lists, "custom_sort_by_tpl_click");
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
					?>
					<?php foreach( $lists as $list ) : ?>
					<?php
						$canContentClass = "subtitle-present";

						if( !isset($list['qcopd_item_subtitle']) || $list['qcopd_item_subtitle'] == "" )
						{
							$canContentClass = "subtitle-absent";
						}
						
					?>
					<li id="item-<?php echo get_the_ID() ."-". $count; ?>">

						<?php
							$item_url = $list['qcopd_item_link'];
							$masked_url = $list['qcopd_item_link'];

							if( $mask_url == 'on' ){
								$masked_url = 'http://' . qcsld_get_domain($list['qcopd_item_link']);
							}

						if(isset($list['qcopd_item_subtitle']) and $list['qcopd_item_subtitle']!=''){
							$toolipsetup = ' data-tooltip="'.$list['qcopd_item_subtitle'].'" data-tooltip-stickto="top" data-tooltip-color="#000" data-tooltip-animate-function="scalein"';
						}else{
							$toolipsetup = '';
						}

						?>

						<?php if(sld_is_youtube_video($item_url)): ?>
							<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
								<div class="sld_video">
									Loading..
								</div>
							</div>
							<a class="open-mpf-sld-video sld_load_video" href="#" data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo get_the_ID(); ?>" data-videourl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" >
							
						<?php elseif(sld_is_vimeo_video($item_url)): ?>
							<div id="sldvideo-<?php echo $count; ?>" class="white-popup mfp-hide">
								<div class="sld_video">
									Loading..
								</div>
							</div>
							<a class="open-mpf-sld-video sld_load_video" href="#" data-mfp-src="#sldvideo-<?php echo $count; ?>" data-itemid="<?php echo get_the_ID(); ?>" data-videourl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>" >
							
						<?php else: ?>
							
							<a <?php if( $mask_url == 'on') { echo 'onclick="document.location.href = \''.$item_url.'\'; return false;"'; } ?> <?php echo (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> href="<?php echo $masked_url; ?>"
							<?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?> data-itemid="<?php echo get_the_ID(); ?>" data-itemurl="<?php echo $list['qcopd_item_link']; ?>" data-itemsid="<?php echo $list['qcopd_timelaps']; ?>">
							
						<?php endif; ?>

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
								<span class="logo-icon">
									<?php
										$img = wp_get_attachment_image_src($list['qcopd_item_img']);
									?>
									<img src="<?php echo $img[0]; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php elseif( $iconClass != "" ) : ?>
								<span class="logo-icon">
									<i class="fa <?php echo $iconClass; ?>"></i>
								</span>
							<?php elseif( $showFavicon == 1 && $faviconFetchable == true ) : ?>
								<span class="logo-icon favicon-loaded">
									<img src="<?php echo $faviconImgUrl; ?>" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php else : ?>
								<span class="logo-icon">
									<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="<?php echo $list['qcopd_item_title']; ?>">
								</span>
							<?php endif; ?>

							<span class="li-txt">
								<?php
									echo trim($list['qcopd_item_title']);
								?>
							</span>
						</a>

							<!-- upvote section -->
							<div class="bookmark-section bookmark-section-style-9">
								<?php 
									if(isset($list['qcopd_description']) and $list['qcopd_description']!=''){
								?>
								<span class="open-mpf-sld-more sld_load_more" data-post-id="<?php echo get_the_ID(); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" style="cursor:pointer" data-mfp-src="#sldinfo-<?php echo get_the_ID() ."-". $count; ?>">
									<i class="fa fa-info-circle"></i>
								</span>
								<?php		
									}
								?>
								<?php if($sldfavorite=='on'): ?>
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
								<?php endif; ?>
							</div>
							<?php 
								if(isset($list['qcopd_description']) and $list['qcopd_description']!=''){
							?>
								<div id="sldinfo-<?php echo get_the_ID() ."-". $count; ?>" class="white-popup mfp-hide">
									<div class="sld_more_text">
										Loading..
									</div>
								</div>								
							<?php		
								}
							?>
							
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
								<i class="fa fa-bolt"></i>
							</div>
							<!-- /featured section -->
							<?php endif; ?>
							
						<?php if( $upvote == 'on' ) : ?>

							<!-- upvote section -->
							<div class="upvote-section upvote-section-style-9">
								(
								<span data-post-id="<?php echo get_the_ID(); ?>" data-unique="<?php echo get_the_ID().'_'.($list['qcopd_timelaps']!=''?$list['qcopd_timelaps']:$count); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
									<i class="fa <?php echo $sld_thumbs_up; ?>"></i>
								</span>
								<span class="upvote-count">
									<?php
									  if( isset($list['qcopd_upvote_count']) && (int)$list['qcopd_upvote_count'] > 0 ){
									  	echo (int)$list['qcopd_upvote_count'];
									  }
									?>
								</span>
								)
							</div>
							<!-- /upvote section -->

						<?php endif; ?>
						

						

					</li>

					<?php $count++; endforeach; ?>
				</ul>

				<?php if( $paginate_items === 'true' ) : ?>

				<!-- navigation panel -->
				<div id="jp-holder-<?php echo get_the_ID(); ?><?php echo (isset($cattabid)&&$cattabid!=''?'-'.$cattabid:''); ?>" class="sldp-holder"></div>

				<?php endif; ?>

			</div>
		</div>

		<?php if( $addvertiseContent != '' ) : ?>
		<!-- Add Block -->
		<div class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; ?> <?php echo "opd-list-id-" . get_the_ID(); ?>" id="item-<?php echo $listId .'-'. get_the_ID(); ?>-add-block">
			<div class="advertise-block tpl-default">
				<?php echo apply_filters('the_content',$addvertiseContent); ?>
			</div>
		</div>
		<!-- /Add Block -->
		<?php endif; ?>

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

<script>
var login_url_sld = '<?php echo sld_get_option('sld_bookmark_user_login_url'); ?>';
var template = '<?php echo $style; ?>';
var bookmark = {
	<?php 
	if ( is_user_logged_in() ) {
	?>
	is_user_logged_in:true,
	<?php
	} else {
	?>
	is_user_logged_in:false,
	<?php
	}
	?>
	userid: <?php echo get_current_user_id(); ?>

};
	jQuery(document).ready(function($){

		$( '.filter-btn[data-filter="all"]' ).on( "click", function() {
	  		//Masonary Grid
		    $('.qc-grid').packery({
		      itemSelector: '.qc-grid-item',
		      gutter: 10
		    });
		});

		$( '.filter-btn[data-filter="all"]' ).trigger( "click" );

	});
	jQuery(window).load(function(e){
		jQuery('.qc-grid').packery({
		  itemSelector: '.qc-grid-item',
		  gutter: 10
		});
	})
</script>
