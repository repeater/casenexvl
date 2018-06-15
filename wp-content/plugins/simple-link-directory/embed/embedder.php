<?php
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


    

add_action('qcsld_after_add_btn', 'qcld_custom_embedder');
function qcld_custom_embedder($shortcodeAtts)
{
	$embed_link_button = sld_get_option('sld_enable_embed_list');
	if ($embed_link_button == 'on') {
    global $post;
	
	$site_title = get_bloginfo('title');
	$site_link = get_bloginfo('url');

	if( sld_get_option( 'sld_embed_credit_title' ) != "" ){
		$site_title = sld_get_option( 'sld_embed_credit_title' );
	}

	if( sld_get_option( 'sld_embed_credit_link' ) != "" ){
		$site_link = sld_get_option( 'sld_embed_credit_link' );
	}

    $pagename = $post->post_name;

    if ($pagename != 'embed-link') {
        ?>

		<!-- Generate Embed Code -->

        <a class="button-link js-open-modal cls-embed-btn" href="#" data-modal-id="popup"
           data-url="<?php echo rtrim(get_page_by_title('Embed Link')->guid,'/'); ?>"
           data-orderby="<?php echo $shortcodeAtts['orderby']; ?>"
           data-order="<?php echo $shortcodeAtts['order']; ?>"
           data-mode="<?php echo $shortcodeAtts['mode']; ?>"
           data-list-id="<?php echo $shortcodeAtts['list_id']; ?>"
           data-column="<?php echo $shortcodeAtts['column']; ?>"
           data-style="<?php echo $shortcodeAtts['style']; ?>"
           data-search="<?php echo $shortcodeAtts['search']; ?>"
           data-category="<?php echo $shortcodeAtts['category']; ?>"
           data-upvote="<?php echo $shortcodeAtts['upvote']; ?>"
           data-tooltipp="<?php echo $shortcodeAtts['tooltip']; ?>"
           data-credittitle="<?php echo $site_title; ?>"
           data-creditlink="<?php echo $site_link; ?>" title="Embed this List on your website!">
		     <?php 
				if(sld_get_option('sld_lan_share_list')!=''){
					echo sld_get_option('sld_lan_share_list');
				}else{
					echo __('Share List', 'qc-opd') ;
				}
			 ?>
			<i class="fa fa-share-alt"></i>
		   </a>
            <?php
                add_action( 'wp_footer', 'sld_share_modal' );
            ?>
    <?php }}
}

function sld_share_modal() {
    ?>
    <div id="popup" class="modal-box">
            <header>
                <a href="#" class="js-modal-close close">×</a>
                <h3><?php echo __('Generate Embed Code For This List', 'qc-opd') ?></h3>
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
                            <div class="ifram-sm">
                                <span>&nbsp;</span>
                                <a class="btn icon icon-code" id="generate-igcode"
                                   onclick=""><?php echo __('Generate & Copy', 'qc-opd') ?></a>
                                </select>
                            </div>
                        </div>

                        <div class="ifram-row">
                            <div class="ifram-lg">
                                <span class="qcld-span-label"><?php echo __('Generated Code', 'qc-opd') ?></span>
                                <br>
                                <textarea id="igcode_textarea" class="igcode_textarea" name="igcode" style="width:100%; height:120px;"
                                          readonly="readonly"></textarea>
                                <p class="guideline">Hit "Generate & Copy" button to generate embed code. It will be copied
                                    to your Clipboard. You can now paste this embed code inside your website's HTML where
                                    you want to show the List.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
}
