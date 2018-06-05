<?php


$embed_link_button = 1;

/*Load Embed Scripts*/
add_action('wp_enqueue_scripts', 'qcopd_load_embed_scripts');

function qcopd_load_embed_scripts()
{
	
	wp_enqueue_style('qcopd-embed-form-css', QCOPD_URL . '/embed/css/embed-form.css');

    wp_enqueue_script('qcopd-embed-form-script', QCOPD_URL . '/embed/js/embed-form.js', array('jquery'));

}


// Load template for embed link page url
function qcopd_load_embed_link_template($template)
{
    if (is_page('embed-link')) {
        return dirname(__FILE__) . '/qcopd-embed-link.php';
    }
    return $template;
}

add_filter('template_include', 'qcopd_load_embed_link_template', 99);


// Create embed page when plugin install or activate

//register_activation_hook(__FILE__, 'qcopd_create_embed_page');
add_action('init', 'qcopd_create_embed_page');

function qcopd_create_embed_page()
{

    if (get_page_by_title('Embed Link') == NULL) {
        //post status and options
        $post = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_author' => get_current_user_id(),
            'post_date' => date('Y-m-d H:i:s'),
            'post_status' => 'publish',
            'post_title' => 'Embed Link',
            'post_type' => 'page',
        );
        //insert page and save the id
        $embedPost = wp_insert_post($post, false);
        //save the id in the database
        update_option('hclpage', $embedPost);
    }
}

if ($embed_link_button == 1) {
    add_action('qcsld_attach_embed_btn', 'qcld_custom_embedder');
}

function qcld_custom_embedder($shortcodeAtts)
{
    global $post;
	
	$site_title = get_bloginfo('title');
	$site_link = get_bloginfo('url');

	if( ot_get_option( 'sld_embed_credit_title' ) != "" ){
		$site_title = ot_get_option( 'sld_embed_credit_title' );
	}

	if( ot_get_option( 'sld_embed_credit_link' ) != "" ){
		$site_link = ot_get_option( 'sld_embed_credit_link' );
	}
	
    $pagename = $post->post_name;

    if ($pagename != 'embed-link') {
	
        ?>
<div style="text-align: right;border-bottom: 1px solid #ddd;padding-bottom: 10px;margin-bottom: 10px;">




<?php if(ot_get_option( 'sld_add_new_button' )=='on' && ot_get_option( 'sld_add_item_link' )!=''): ?>
<a style="" href="<?php echo ot_get_option( 'sld_add_item_link' ); ?>" class="button-link cls-embed-btn">
<?php 
	if(ot_get_option('sld_lan_add_link')!=''){
		echo ot_get_option('sld_lan_add_link');
	}else{
		_e( 'Add New', 'qc-pd' ); 
	}
?>
</a>
<?php endif; ?>

<?php if($shortcodeAtts['enable_embedding'] == 'true'): ?>
<a class="button-link js-open-modal cls-embed-btn" href="#" data-modal-id="popup"
           data-url="<?php bloginfo('url'); ?>/embed-link"
           data-order="<?php echo $shortcodeAtts['order']; ?>"
           data-mode="<?php echo $shortcodeAtts['mode']; ?>"
           data-list-id="<?php echo $shortcodeAtts['list_id']; ?>"
           data-column="<?php echo $shortcodeAtts['column']; ?>"
           data-style="<?php echo $shortcodeAtts['style']; ?>"
           data-category="<?php echo $shortcodeAtts['category']; ?>" 
		   data-credittitle="<?php echo $site_title; ?>"
           data-creditlink="<?php echo $site_link; ?>"> 
			<?php 
				if(ot_get_option('sld_lan_share_list')!=''){
					echo ot_get_option('sld_lan_share_list');
				}else{
					echo __('Share List', 'qc-opd') ;
				}
			 ?>
		   <i class="fa fa-share-alt"></i> </a>
<?php endif; ?>

<div id="popup" class="modal-box">
  <header> <a href="#" class="js-modal-close close">×</a>
    <h3>Generate Embed Code For This List</h3>
  </header>
  <div class="modal-body">
    <div class="iframe-css">
      <div class="iframe-main">
        <div class="ifram-row">
          <div class="ifram-sm">
			<span>Width: (in '%' or 'px')</span>
			<input id="igwidth" name="igwidth" type="text" value="100">
		</div>
		<div class="ifram-sm" style="width: 70px;">
			<span>&nbsp;</span>
			<select name="igsizetype" class="iframe-main-select">
				<option value="%">%</option>
				<option value="px">px</option>
			</select>
		</div>
		<div class="ifram-sm">
			<span>Height: (in 'px')</span>
			<input id="igheight" name="igheight" type="text" value="400">
		</div>
          <div class="ifram-sm"> <span>&nbsp;</span> <a class="btn icon icon-code" id="generate-igcode" onclick="">Generate & Copy</a>
            </select>
          </div>
        </div>
        <div class="ifram-row">
          <div class="ifram-lg"> <span class="qcld-span-label">Generated Code</span> <br>
            <textarea id="igcode_textarea" class="igcode_textarea" name="igcode" style="width:100%; height:120px;" readonly="readonly"></textarea>
            <p class="guideline">Hit "Generate & Copy" button to generate embed code. It will be copied
              to your Clipboard. You can now paste this embed code inside your website's HTML where
              you want to show the List.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<?php }
}
