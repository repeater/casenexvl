<?php

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
       
    }

    return $posts;
}

//perform the check when the_posts() function is called
add_action('the_posts', 'qcopd_check_for_shortcode');

add_action('wp_enqueue_scripts', 'qcopd_load_all_scripts');

function qcopd_load_all_scripts(){

	//Scripts
	wp_enqueue_script( 'jquery', 'jquery');
   // wp_enqueue_script( 'qcopd-grid-packery', QCOPD_ASSETS_URL . '/js/packery.pkgd.js', array('jquery'),true,true);
	wp_enqueue_script( 'qcopd-custom-script', QCOPD_ASSETS_URL . '/js/directory-script.js', array('jquery'));
	wp_enqueue_style( 'qcsld-fa-css', QCOPD_ASSETS_URL . '/css/font-awesome.min.css' );
	//StyleSheets
	wp_enqueue_style( 'qcopd-custom-css', QCOPD_ASSETS_URL . '/css/directory-style.css');
	wp_enqueue_style( 'qcopd-custom-rwd-css', QCOPD_ASSETS_URL . '/css/directory-style-rwd.css');
	
}
add_action( 'admin_enqueue_scripts', 'qcsld_admin_enqueue' );

function qcsld_admin_enqueue(){
	wp_enqueue_style( 'qcopd-custom-admin-css', QCOPD_ASSETS_URL . '/css/admin-style.css');
}


function sld_packery_adding_scripts() {
	
wp_register_script('sld-packery-script', QCOPD_ASSETS_URL . '/js/packery.pkgd.js','','1.1', true);
wp_enqueue_script('sld-packery-script');

}
add_action( 'wp_enqueue_scripts', 'sld_packery_adding_scripts', 100 ); 


