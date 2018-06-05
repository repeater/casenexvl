<?php


define('OCOPD_TPL_DIR', QCOPD_DIR . "/templates");
define('OCOPD_TPL_URL', QCOPD_URL . "/templates");

/*Custom Item Sort Logic*/


function custom_sort_by_tpl_title($a, $b) {
    //return $a['qcopd_item_title'] > $b['qcopd_item_title'];
	return strnatcasecmp($a['qcopd_item_title'], $b['qcopd_item_title']);
}

function custom_sort_by_tpl_upvotes($a, $b) {
    return @($a['qcopd_upvote_count'] * 1 < $b['qcopd_upvote_count'] * 1);
}


function custom_sort_by_tpl_timestamp($a, $b) {
	if( isset($a['qcopd_timelaps']) && isset($b['qcopd_timelaps']) )
	{
		$aTime = (int)$a['qcopd_timelaps'];
		$bTime = (int)$b['qcopd_timelaps'];
		return $aTime < $bTime;
	}
}

//For all list elements
add_shortcode('qcopd-directory', 'qcopd_directory_full_shortcode');

function qcopd_directory_full_shortcode( $atts = array() )
{
	ob_start();
    show_qcopd_full_list( $atts );
    $content = ob_get_clean();
    return $content;
}

function show_qcopd_full_list( $atts = array() )
{
	$template_code = "";

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
			'upvote' => "off",
			'item_count' => "on",
			'top_area' => "on",
			'item_orderby' => "",
			'item_order' => "",
			'mask_url' => "off",
			'enable_embedding' => 'false',
			'title_font_size' => '',
			'subtitle_font_size' => '',
			'title_line_height' => '',
			'subtitle_line_height' => '',
		), $atts
	));

	//ShortCode Atts
	$shortcodeAtts = array(
		'orderby' => $orderby,
		'order' => $order,
		'mode' => $mode,
		'list_id' => $list_id,
		'column' => $column,
		'style' => $style,
		'list_img' => $list_img,
		'search' => $search,
		'category' => $category,
		'upvote' => $upvote,
		'item_count' => $item_count,
		'top_area' => $top_area,
		'item_orderby' => $item_orderby,
		'item_order' => $item_order,
		'mask_url' => $mask_url,
		'enable_embedding' => $enable_embedding,
		'title_font_size' => $title_font_size,
		'subtitle_font_size' => $subtitle_font_size,
		'title_line_height' => $title_line_height,
		'subtitle_line_height' => $subtitle_line_height,
	);
	
	$limit = -1;

	if( $mode == 'one' )
	{
		$limit = 1;	
	}

	if($orderby=='menu_order'){
		$orderby = $orderby.' title';
	}
	
	//Query Parameters
	$list_args = array(
		'post_type' => 'sld',
		'orderby' => $orderby,
		'order' => $order,
		'posts_per_page' => $limit,
	);

	if( $list_id != "" && $mode == 'one' )
	{
		$list_args = array_merge($list_args, array( 'p' => $list_id ));
	}
	
	if( $category != "" )
	{
		$taxArray = array(
			array(
				'taxonomy' => 'sld_cat',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);
		
		$list_args = array_merge($list_args, array( 'tax_query' => $taxArray ));
		
	}
	
	if(ot_get_option('sld_enable_upvote')=='on'){
		$upvote = 'on';
	}
	// The Query
	$list_query = new WP_Query( $list_args );
	
    if ( isset($atts["style"]) && $atts["style"] )
        $template_code = $atts["style"];

    if (!$template_code)
        $template_code = "simple";

    if( $mode == 'one' ){
    	$column = '1';
    }

?>

<?php if(ot_get_option('sld_enable_scroll_to_top')=='on'): ?>
<style type="text/css">
.sld_scrollToTop{
	width: 30px;
    height: 30px;
    padding: 10px !important;
    text-align: center;
    font-weight: bold;
    color: #444;
    text-decoration: none;
    position: fixed;
    top: 88%;
    right: 29px;
    display: none;
    background: url('<?php echo QCOPD_IMG_URL;?>/up-arrow.ico') no-repeat 5px 5px;
    background-size: 20px 20px;
    text-indent: -99999999px;
    background-color: #ddd;
    border-radius: 3px;
	z-index:9999999999;
	box-sizing: border-box;

}
.sld_scrollToTop:hover{
text-decoration:none;
}
.filter-area{z-index: 99 !important;
    padding: 10px 0px;
    
}

</style>
<a href="#"class="sld_scrollToTop">Scroll To Top</a>
<script type="text/javascript">
jQuery(document).ready(function($){
  $(window).scroll(function(){
		if ($(this).scrollTop() > 100) {
			$('.sld_scrollToTop').fadeIn();
		} else {
			$('.sld_scrollToTop').fadeOut();
		}
	});

	//Click event to scroll to top
	$('.sld_scrollToTop').click(function(){
		$('html, body').animate({scrollTop : 0},800);
		return false;
	});


})
</script>
<?php endif; ?>

<?php
	
	$customjs = ot_get_option( 'sld_custom_js' );
	if(trim($customjs)!=''){
		?>
			<script type="text/javascript">
				<?php echo $customjs; ?>
			</script>
		<?php
	}
	
    //require ( OCOPD_TPL_DIR . "/$template_code/template.php" );
	echo '<!--  Starting Simple Link Directory Plugin Output -->';
	$tempath = QCOPD_DIR ."/templates/".$template_code."/template.php";
    require ( $tempath );
	wp_reset_query();

?>
<script type="text/javascript">
jQuery(window).load(function()
{
	jQuery('.qc-grid').packery({
      itemSelector: '.qc-grid-item',
      gutter: 10
    });
});
</script>
<?php
}