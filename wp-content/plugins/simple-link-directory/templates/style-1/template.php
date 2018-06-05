<link rel="stylesheet" type="text/css" href="<?php echo OCOPD_TPL_URL . "/$template_code/template.css"; ?>" />
<?php
	$customCss = ot_get_option( 'sld_custom_style' );

	if( trim($customCss) != "" ) :
?>
	<style>
		<?php echo trim($customCss); ?>
	</style>

<?php endif; ?>
<?php
global $wpdb;
// The Loop
if ( $list_query->have_posts() ) 
{
	
	if(ot_get_option('sld_enable_top_part')=='on') :
		
	 do_action('qcsld_attach_embed_btn', $shortcodeAtts);
	
	endif;

	//Directory Wrap or Container

	echo '<div class="qcopd-list-wrapper"><div id="opd-list-holder" class="qc-grid qcopd-list-holder">';

	$listId = 1;

	while ( $list_query->have_posts() ) 
	{
		$list_query->the_post();

		//$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );
		
		$lists = array();
		$results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = ".get_the_ID()." AND meta_key = 'qcopd_list_item01'");
		if(!empty($results)){
			foreach($results as $result){
				$unserialize = unserialize($result->meta_value);
				$lists[] = $unserialize;
			}
		}

		$conf = get_post_meta( get_the_ID(), 'qcopd_list_conf', true );

		

		if( $item_orderby == 'title' )
		{
			usort($lists, "custom_sort_by_tpl_title");
		}
		if( $item_orderby == 'upvotes' )
		{
			usort($lists, "custom_sort_by_tpl_upvotes");
		}
		if( $item_orderby == 'timestamp' )
		{
			usort($lists, "custom_sort_by_tpl_timestamp");
		}

		?>

		<?php if( $style == "style-1" ) : ?>
        <style>

            #qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>.style-1 .ca-menu li .ca-main {

            <?php if($title_font_size!=''): ?>
                font-size:<?php echo $title_font_size; ?> !important;
            <?php endif; ?>

            <?php if($title_line_height!=''): ?>
                line-height:<?php echo $title_line_height; ?> !important;
            <?php endif; ?>
            }

            #qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>.style-1 .ca-menu li .ca-sub {
            <?php if($subtitle_font_size!=''): ?>
                font-size:<?php echo $subtitle_font_size; ?> !important;
            <?php endif; ?>

            <?php if($subtitle_line_height!=''): ?>
                line-height:<?php echo $subtitle_line_height; ?>!important;
            <?php endif; ?>
            }

        </style>

		<div id="qcopd-list-<?php echo $listId .'-'. get_the_ID(); ?>" class="qc-grid-item qcopd-list-column opd-column-<?php echo $column; echo " " . $style;?> <?php echo "opd-list-id-" . get_the_ID(); ?>">

			<div class="qcopd-single-list-1">
				
				<h2>
					<?php echo get_the_title(); ?>
				</h2>
				<ul class="ca-menu">
					<?php $count = 1; 
						
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
						?>
						<!-- List Anchor -->
						<a <?php echo (isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] == 1) ? 'rel="nofollow"' : ''; ?> href="<?php echo $masked_url; ?>" <?php echo (isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] == 1) ? 'target="_blank"' : ''; ?>>

							<!-- Image, If Present -->
							<?php if( ($list_img == "true") && isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) : ?>
								<span class="ca-icon list-img-1">
									<?php 
										$img = wp_get_attachment_image_src($list['qcopd_item_img']);
									?>
									<img src="<?php echo $img[0]; ?>" alt="">
								</span>
							<?php else : ?>
								<span class="ca-icon list-img-1">
									<img src="<?php echo QCOPD_IMG_URL; ?>/list-image-placeholder.png" alt="">
								</span>
							<?php endif; ?>

							<!-- Link Text -->
							<div class="ca-content">
                                <h3 class="ca-main <?php echo $canContentClass; ?>">
								<?php 
									echo trim($list['qcopd_item_title']); 
								?>
                                </h3>
                                <?php if( isset($list['qcopd_item_subtitle']) ) : ?>
	                                <p class="ca-sub">
	                                <?php 
										echo trim($list['qcopd_item_subtitle']); 
									?>
	                                </p>
	                            <?php endif; ?>
                            </div>

						</a>
						<?php if( $upvote == 'on' ) : ?>

							<!-- upvote section -->
							<div class="upvote-section">
								<span data-post-id="<?php echo get_the_ID(); ?>" data-item-title="<?php echo trim($list['qcopd_item_title']); ?>" data-item-link="<?php echo $list['qcopd_item_link']; ?>" class="upvote-btn upvote-on">
									<i class="fa fa-thumbs-up"></i>
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
						
						<?php if(isset($list['qcopd_featured']) and $list['qcopd_featured']==1):?>
							<!-- featured section -->
							<div class="featured-section">
								<i class="fa fa-bolt"></i>
							</div>
							<!-- /featured section -->
						<?php endif; ?>
						
					</li>
					<?php $count++; endforeach; ?>
				</ul>

			</div>
		</div>

		<?php endif; ?>

		<?php

		$listId++;
	}

	echo '<div class="sld-clearfix"></div>
			</div>
		<div class="sld-clearfix"></div>
	</div>';

}
