<?php
defined('ABSPATH') or die("No direct script access!");



/* URL Filtering Logic, to remove http:// or https:// */
function qcsld_remove_http($url) {
   $disallowed = array('http://', 'https://');
   foreach($disallowed as $d) {
      if(strpos($url, $d) === 0) {
         return str_replace($d, '', $url);
      }
   }
   return trim($url);
}

/*Custom Item Sort Logic*/
function custom_sort_by_tpl_click($a, $b) {
	
    return @($a['qcopd_click'] * 1 < $b['qcopd_click'] * 1);
	
}

function custom_sort_by_tpl_upvotes($a, $b) {
    return @($a['qcopd_upvote_count'] * 1 < $b['qcopd_upvote_count'] * 1);
}

function custom_sort_by_tpl_featured($a, $b) {
    return @($a['qcopd_featured'] * 1 < $b['qcopd_featured'] * 1);
}

function custom_sort_by_tpl_title($a, $b) {
    return strnatcasecmp($a['qcopd_item_title'], $b['qcopd_item_title']);
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
			'filterorderby' => 'menu_order',
			'order' => 'ASC',
			'filterorder' => 'ASC',
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
			'list_title_font_size' => '' ,
			'list_title_line_height' => '',
            'title_font_size' => '',
            'subtitle_font_size' => '',
            'title_line_height' => '',
            'subtitle_line_height' => '',
            'filter_area' => 'normal',
            'topspacing' => 0,
            'infinityscroll' => 0,
            'itemperpage' => 5,
			'favorite'	=> '',
			'multipage'	=>'false',
			'cattabid'	=>'',
			'removetop'	=>'no',
			'clink'		=> '',
			'statistics'=>'',
			'enable_left_filter'=>'false'

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
		'tooltip' => $tooltip,
        'list_title_font_size' => $list_title_font_size ,
        'list_title_line_height' => $list_title_line_height ,
        'title_font_size' => $title_font_size,
        'subtitle_font_size' => $subtitle_font_size,
        'title_line_height' => $title_line_height,
        'subtitle_line_height' => $subtitle_line_height,
        'filter_area' => $filter_area,
        'topspacing' => $topspacing,
	);

	$limit = -1;

	if( $mode == 'one' )
	{
		$limit = 1;
	}
	
	
	if($style=="simple" && $infinityscroll==1){
		$list_args_total = array(
			'post_type' => 'sld',
			'orderby' => $orderby,
			'order' => $order,
			'posts_per_page' => -1,
		);
		$total_list_query = new WP_Query( $list_args_total );
		$count = $total_list_query->post_count;
		$total_page_count = ceil($count/$per_page);
		
		//Query Parameters
		$list_args = array(
			'post_type' => 'sld',
			'posts_per_page' => $per_page,
			'paged'			=> 1
			
		);
	}else{
		//Query Parameters
		$list_args = array(
			'post_type' => 'sld',
			'orderby' => $orderby,
			'order' => $order,
			'posts_per_page' => $limit,
			
		);
	}
	$statistic = false;
	
	if(sld_get_option('sld_enable_statistics')=="on"){
		$statistic = true;
	}
	
	if(isset($statistics) && $statistics=="false"){
		$statistic = false;
	}
	
	if(isset($statistics) && $statistics=="true"){
		$statistic = true;
	}
	

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
	


	// The Query
	$list_query = new WP_Query( $list_args );

    if ( isset($atts["style"]) && $atts["style"] )
        $template_code = $atts["style"];

    if (!$template_code)
        $template_code = "simple";

    if( $mode == 'one' and $template_code!='style-13' and $template_code!='style-11' and $template_code!='style-10' and $template_code!='style-3' and $template_code!='style-4' and $template_code!='style-14' ){
    	$column = '1';
    }
	
	if(sld_get_option('sld_enable_bookmark')=='on'){
		$sldfavorite = 'on';
	}else{
		$sldfavorite = 'off';
	}
	
	if($favorite=='enable'){
		$sldfavorite = 'on';
	}elseif($favorite=='disable'){
		$sldfavorite = 'off';
	}
	
	
	
$customjs = sld_get_option( 'sld_custom_js' );

if($topspacing==''){
	$topspacing = 0;
}

?>
<script style="text/javascript">
var slduserMessage= '<?php echo sld_get_option('sld_bookmark_popup_content');?>';

<?php 
if(sld_get_option('sld_upvote_user_login')=='on'){
?>
var allowupvote = true;
<?php if(sld_get_option('sld_upvote_login_url')!=''): ?>
var upvoteloginurl = '<?php echo sld_get_option('sld_upvote_login_url'); ?>';
<?php else: ?>
var upvoteloginurl = '';
<?php endif; ?>

<?php 
}else{
?>
var allowupvote = false;
<?php
}
?>

</script>
<?php

if($customjs!=''):
?>
<script type="text/javascript">
jQuery(document).ready(function($)
{
<?php echo $customjs; ?>

})
</script>
<?php
endif;
?>
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
.qc-grid-item h2{
    <?php if($list_title_font_size!=''){ ?> font-size:<?php echo $list_title_font_size; ?>;<?php } ?>
<?php if($list_title_line_height!=''){ ?> line-height:<?php echo $list_title_line_height; ?>;<?php } ?>
}

</style>

<?php if(sld_get_option('sld_enable_scroll_to_top')=='on'): ?>
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

<?php if($filter_area=='fixed'): ?>
    if($("body").prop("clientWidth")>500){
        $(".filter-area").sticky({ topSpacing: <?php echo $topspacing; ?>, center:true });
    }
<?php endif; ?>
})
</script>
<?php endif; ?>


<?php
 if(sld_get_option('sld_use_global_thumbs_up')!=''){
     $sld_thumbs_up = sld_get_option('sld_use_global_thumbs_up');
 }else{
     $sld_thumbs_up = 'fa-thumbs-up';
 }
?>

<?php


    $tempath = QCOPD_DIR ."/templates/".$template_code."/template.php";
    require ( $tempath );
	wp_reset_query();
	
	if($statistic && $multipage!="true"){
	?>
	<script type="text/javascript">
		var statistic = true;
	</script>
	<?php
	}
	
}



/*TinyMCE button for Inserting Shortcode*/
/* Add Slider Shortcode Button on Post Visual Editor */
function qcopdsld_tinymce_button_function() {
	add_filter ("mce_external_plugins", "qcopd_sld_btn_js");
	add_filter ("mce_buttons", "qcopd_sld_btn");
}

function qcopd_sld_btn_js($plugin_array) {
	$plugin_array['qcopdsldbtn'] = plugins_url('assets/js/qcopd-tinymce-button.js', __FILE__);
	return $plugin_array;
}

function qcopd_sld_btn($buttons) {
	array_push ($buttons, 'qcopdsldbtn');
	return $buttons;
}

//add_action('init', 'qcopdsld_tinymce_button_function');


