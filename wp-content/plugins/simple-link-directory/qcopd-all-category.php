<?php
/**
 * Created by QuantunCloud.
 * Date: 9/14/2017
 * Time: 3:16 PM
 */

defined('ABSPATH') or die("No direct script access!");

add_shortcode('sld-tab', 'qcopd_directory_all_category');
function qcopd_directory_all_category($atts = array()){


	//Defaults & Set Parameters
	extract( shortcode_atts(
		array(
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'mode' => 'all',
			'list_id' => '',
			'column' => '1',
			'style' => 'simple',
			'list_img' => 'true',
			'search' => 'true',
			'category' => "",
			'upvote' => "on",
			'item_count' => "on",
			'top_area' => "on",
			'item_orderby' => "",
			'item_order' => "",
			'mask_url' => "off",
			'tooltip' => 'false',
			'paginate_items' => 'false',
			'per_page' => 5,
			'category_orderby'=>'date',
			'category_order'=>'ASC',
			'category_remove'=>'',
			'list_title_font_size' => '' ,
			'list_title_line_height' => '' ,

			'title_font_size' => '',
			'subtitle_font_size' => '',
			'title_line_height' => '',
			'subtitle_line_height' => '',
			'filter_area' => 'normal',
			'topspacing' => 0,

		), $atts
	));


    //Category remove array
    if($category_remove != ''){
	    $category_remove = explode(',',$category_remove);
	    $categoryremove = $category_remove;
    }else{
	    $categoryremove = array();
    }

	$cterms = get_terms( 'sld_cat', array(
		'hide_empty' => true,
		'orderby' => $category_orderby,
		'order' => $category_order
	) );



	ob_start();

    if(!empty($cterms)){
?>
		
        <div class="qcld_sld_tab_main"><!--start qcld_sld_tab_main-->
            <div class="qcld_sld_tab">
                <?php
                $ci = 0;
                foreach ($cterms as $cterm){
                    if(!in_array($cterm->term_id,$categoryremove)){
						$image_id = get_term_meta ( $cterm -> term_id, 'category-image-id', true );
                        ?>
                            <button style="<?php echo (!$image_id?'padding-left:22px!important':''); ?>" class="qcld_sld_tablinks <?php echo ($ci==0?'qcld_sld_active':''); ?>" data-cterm="<?php echo $cterm->slug; ?>" ><?php echo $cterm->name; ?>
							<span class="cat_img_top">
							<?php if($image_id) echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?></span>
							</button>
                        <?php
                        $ci++;
                    }
                }
                ?>
            </div>

	        <?php
	        $ci = 0;
	        foreach ($cterms as $cterm){
		        if(!in_array($cterm->term_id,$categoryremove)){
					//if($ci==1)continue;
			        ?>

                    <div id="<?php echo $cterm->slug; ?>" class="qcld_sld_tabcontent" <?php echo ($ci==0?'style="display:block"':''); ?>>
				        <?php
                            $shortcodeText = '[qcopd-directory category="'.$cterm->slug.'" search="'.$search.'" upvote="'.$upvote.'" item_count="'.$item_count.'" top_area="'.$top_area.'" mask_url="'.$mask_url.'" tooltip="'.$tooltip.'" paginate_items="'.$paginate_items.'" per_page="'.$per_page.'" style="'.$style.'" column="'.$column.'" orderby="'.$orderby.'" order="'.$order.'" list_title_font_size="'.$list_title_font_size.'" item_orderby="'.$item_orderby.'" list_title_line_height="'.$list_title_line_height.'" title_font_size="'.$title_font_size.'" subtitle_font_size="'.$subtitle_font_size.'" title_line_height="'.$title_line_height.'" subtitle_line_height="'.$subtitle_line_height.'" filter_area="'.$filter_area.'" topspacing="'.$topspacing.'" cattabid="'.$ci.'" multipage="true"]';
				        echo do_shortcode($shortcodeText);
				        ?>
                    </div>

			        <?php
			        $ci++;
		        }
	        }
	        ?>



        </div><!--end qcld_sld_tab_main-->
        <script>

            jQuery(document).ready(function($){
			
			
			$('.qcld_sld_tablinks').on('click', function(evt){
				
				var qcld_sld_event = $(this).attr('data-cterm')
				var i, qcld_sld_tabcontent, qcld_sld_tablinks;
                qcld_sld_tabcontent = document.getElementsByClassName("qcld_sld_tabcontent");
                for (i = 0; i < qcld_sld_tabcontent.length; i++) {
                    qcld_sld_tabcontent[i].style.display = "none";
                }
                qcld_sld_tablinks = document.getElementsByClassName("qcld_sld_tablinks");
                for (i = 0; i < qcld_sld_tablinks.length; i++) {
                    qcld_sld_tablinks[i].className = qcld_sld_tablinks[i].className.replace(" qcld_sld_active", "");
                }
                document.getElementById(qcld_sld_event).style.display = "block";
                evt.currentTarget.className += " qcld_sld_active";
				
				jQuery('#'+qcld_sld_event +' .qcopd-single-list').each(function(e){
					
					if($(this).find('.sldp-holder').length > 0 && $(this).find('.sldp-holder > .jp-current').length==0){

						var containerId = $(this).find('.sldp-holder').attr('id');
						var containerList = $(this).find('ul').attr('id');
						console.log(containerList);
						$("#"+$(this).find('.sldp-holder').attr('id')).jPages({
							containerID : containerList,
							perPage : <?php echo $per_page; ?>,
						});
						
					}
					
				})
				$('#'+qcld_sld_event).find(".filter-btn[data-filter='all']").click();
                /*jQuery('.qc-grid').packery({
                    itemSelector: '.qc-grid-item',
                    gutter: 10
                });
                jQuery( '.filter-btn[data-filter="all"]' ).trigger( "click" );*/
				
			})
			
		})
		
        </script>
		
		<?php if(sld_get_option('sld_enable_filtering_left')=='on'): ?>
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
		
<?php
    }

	$content = ob_get_clean();
	return $content;

}