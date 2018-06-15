<?php

/*Load Scripts only in the shortcode page*/
function qcopd_check_for_shortcode($posts) {
	if ( empty($posts) )
		return $posts;

	// false because we have to search through the posts first
	$found = false;

	// search through each post
	foreach ($posts as $post) {
		// check the post content for the short code
		if ( stripos($post->post_content, 'qcopd-directory') )
			// we have found a post with the short code
			$found = true;
		// stop the search
		break;
	}

	if ($found){
		//Load Script and Stylesheets
		//add_action('wp_enqueue_scripts', 'qcopd_load_all_scripts');
	}

	return $posts;
}

//perform the check when the_posts() function is called
add_action('the_posts', 'qcopd_check_for_shortcode');

add_action('template_redirect', 'qcopd_check_for_shorcode');
function qcopd_check_for_shorcode(){
	global $wp_query;
	if ( is_singular() ) {
		$post = $wp_query->get_queried_object();
		
		
		if ( $post && strpos($post->post_content, 'qcopd-directory' ) !== false ) {
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');
		}
		elseif($post && strpos($post->post_content, 'sld_dashboard' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}
        elseif($post && strpos($post->post_content, 'sld_login' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}elseif($post && strpos($post->post_content, 'sld_registration' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}elseif($post && strpos($post->post_content, 'sld_restore' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}elseif($post && strpos($post->post_content, 'qcopd-directory-multipage' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}
		elseif($post && strpos($post->post_content, 'sld-tab' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}elseif($post && strpos($post->post_content, 'sld_claim_listing' ) !== false ){
			add_action('wp_enqueue_scripts', 'qcopd_load_global_scripts');

		}
	}
}




/*Load Global Scripts*/



function qcopd_load_global_scripts()
{
	 if(sld_get_option('sld_image_upload')=='on'){
	    wp_enqueue_media();
	 }
	//FontAwesome
	
	wp_register_style( 'sldcustom_dashboard-css', QCOPD_ASSETS_URL.'/css/dashboardstyle.css', __FILE__ );
	wp_enqueue_style( 'sldcustom_dashboard-css' );
	    //FontAwesome
    wp_enqueue_style('qcopd-embed-form-css', QCOPD_URL . 'embed/css/embed-form.css');

    //Scripts
    wp_enqueue_script('qcopd-embed-form-script', QCOPD_URL . 'embed/js/embed-form.js', array('jquery'));
	
	wp_enqueue_style( 'qcfontawesome-css', QCOPD_ASSETS_URL . '/css/font-awesome.min.css');
	wp_enqueue_style( 'qcopd-custom-css', QCOPD_ASSETS_URL . '/css/directory-style.css');
	wp_enqueue_style( 'qcopd-custom-rwd-css', QCOPD_ASSETS_URL . '/css/directory-style-rwd.css');
	wp_enqueue_style( 'qcopd-custom-registration-css', QCOPD_ASSETS_URL . '/css/sld_registration.css');
	wp_enqueue_style( 'qcopd-sldcustom-common-css', QCOPD_ASSETS_URL . '/css/sldcustomize-common.css');
	wp_enqueue_style('qcopd-embed-form-css', QCOPD_URL . 'embed/css/embed-form.css');
	wp_enqueue_style( 'sld-tab-css', QCOPD_ASSETS_URL . '/css/tab_style.css');
	wp_enqueue_style( 'qcopd-magpopup-css', QCOPD_ASSETS_URL . '/css/magnific-popup.css');

	wp_register_style( 'sldcustom_login-css', QCOPD_ASSETS_URL.'/css/style.css', __FILE__ );
	wp_enqueue_style( 'sldcustom_login-css' );

	//Scripts
	//wp_enqueue_script( 'jquery', 'jquery');
	wp_enqueue_script("jquery");
	//wp_deregister_script('jquery');
	wp_enqueue_script( 'qcopd-grid-packery', QCOPD_ASSETS_URL . '/js/packery.pkgd.js', array('jquery'),'',true);
	wp_enqueue_script( 'qcopd-magpopup-js', QCOPD_ASSETS_URL . '/js/jquery.magnific-popup.min.js', array('jquery'));
	wp_enqueue_script( 'qcopd-tooltipster', QCOPD_ASSETS_URL . '/js/tooltipster.bundle.min.js', array('jquery'),'',true);
	
	wp_enqueue_script( 'qcopd-custom-script', QCOPD_ASSETS_URL . '/js/directory-script.js', array('jquery', 'qcopd-grid-packery'),'1.0',true);
	
	wp_enqueue_script('qcopd-embed-form-script', QCOPD_URL . 'embed/js/embed-form.js', array('jquery'),'',true);

	wp_enqueue_script( 'qcopd-custom-script-sticky', QCOPD_ASSETS_URL . '/js/jquery.sticky.js', array('jquery'),'',true);

	wp_enqueue_script( 'qcopd-sldcustom-common-script', QCOPD_ASSETS_URL . '/js/sldcustomization-common.js', array('jquery'),'',true);
	
	$params = array(
	  'ajaxurl' => admin_url('admin-ajax.php'),
	  'ajax_nonce' => wp_create_nonce('quantum_ajax_validation_18'),
	);
	wp_localize_script( 'qcopd-custom-script', 'ajax_object', $params );
	
	
	$filterType = sld_get_option( 'sld_filter_ptype' );

		wp_enqueue_style( 'jq-slick.css-css', QCOPD_ASSETS_URL . '/css/slick.css');
		wp_enqueue_style( 'jq-slick-theme-css', QCOPD_ASSETS_URL . '/css/slick-theme.css');
		wp_enqueue_script( 'jq-slick.min-js', QCOPD_ASSETS_URL . '/js/slick.min.js', array('jquery'));

}

/*******************************
 * Admin Script
 *******************************/
function qcsld_admin_enqueue($hook) {

	wp_enqueue_media();
	wp_enqueue_script( 'qcsld-fa-script', QCOPD_ASSETS_URL . '/js/admin-fa-script.js' );
	wp_enqueue_script( 'qcsld-admin-cmn-js', QCOPD_ASSETS_URL . '/js/admin-common.js' );
	wp_enqueue_script( 'qcopd-sldcustom-common-script-admin', QCOPD_ASSETS_URL . '/js/sldcustomization-common.js', array('jquery'));
	wp_enqueue_style( 'qcsld-fa-modal-css', QCOPD_ASSETS_URL . '/css/admin-fa-css.css' );
	wp_enqueue_style( 'qcsld-fa-css', QCOPD_ASSETS_URL . '/css/font-awesome.min.css' );
	wp_enqueue_style( 'qcsld-common-css', QCOPD_ASSETS_URL . '/css/admin-common.css' );
	wp_enqueue_style( 'qcopd-sldcustom-common-css-admin', QCOPD_ASSETS_URL . '/css/sldcustomize-common.css');
	
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script( 'ilist-admin-quicksearch-js', QCOPD_ASSETS_URL . '/js/jquery.quicksearch.js', array('jquery'), $ver = false, $in_footer = false );
	$screen = get_current_screen();
	if($screen->post_type=='sld'){
		wp_deregister_script('alpha-color-picker');
		wp_deregister_style('alpha-color-picker');
		wp_deregister_style('Total_Soft_Poll');
		
	}

}

add_action( 'admin_enqueue_scripts', 'qcsld_admin_enqueue' );

/*Global Font Configs*/


add_action('wp_head', 'sld_global_font_configurations_func');

function sld_global_font_configurations_func()
{

	$sld_use_global_font = sld_get_option('sld_use_global_font');
	if(isset($sld_use_global_font) and $sld_use_global_font=='yes'){
		$sldFontConfig = sld_get_option( 'sld_global_font' );
		
		if( isset($sldFontConfig) && count($sldFontConfig) > 0 ){
			$fontFamily = (trim($sldFontConfig[0]['family']));
			
			$json  = wp_remote_fopen( QCOPD_ASSETS_URL . '/fonts/webfont.json' );
			$json = json_decode($json);
			
			foreach($json->items as $fonts){
				
				if($fontFamily==str_replace(' ','',strtolower($fonts->family))){
					$fontFamily = $fonts->family;
					break;
				}
			}
			
			
			?>
            <!-- Global Font Linking -->
            <link href="https://fonts.googleapis.com/css?family=<?php echo str_replace(" ", "+", $fontFamily); ?>" rel="stylesheet">
			<?php
			if( $fontFamily != '' ){
				?>
                <!-- Global Font Settings -->
                <style>
                    .qc-grid-item h3, .qc-grid-item h2, .qc-grid-item h3 span, .qc-grid-item .upvote-count, .qc-grid-item ul li, .qc-grid-item ul li a, .sldp-holder a, .html5tooltip-top .html5tooltip-text, .html5tooltip-top a, .tooltipster-base{
						<?php 
						if($fontFamily=='Indieflower'){
						?>
							    font-family: 'Indie Flower',cursive;
						<?php
						}else{
						?>
							font-family: <?php echo $fontFamily; ?>, sans-serif !important;
						<?php
						}
						?>
                        
                    }
                </style>
				<?php
			}

		}
	}

}
