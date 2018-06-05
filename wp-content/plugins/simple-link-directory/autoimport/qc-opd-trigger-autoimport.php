<?php

//Sample Dummy Contents

//delete_option( 'qcopd_dummy_stat' );

if( function_exists('get_option') ){
  add_action( 'init', 'qcld_check_current_flag_then_insert_opt' );
  add_action( 'init', 'qcopd_insert_dummy_post' );
}

function qcld_check_current_flag_then_insert_opt()
{
  
  $currentDummyOption = 0;

  $currentDummyOption = get_option('qcopd_dummy_stat');

  if( $currentDummyOption != 1 )
  {

    global $wpdb;

    $query = "INSERT INTO $wpdb->options (option_name, option_value)
    SELECT * FROM (SELECT 'qcopd_dummy_stat', '1') AS tmp
    WHERE NOT EXISTS (
      SELECT option_name, option_value FROM $wpdb->options WHERE option_name = 'qcopd_dummy_stat' AND option_value = '1'
    ) LIMIT 1";

    $inserted = $wpdb->get_var( $query );

  }
  
}

function qcopd_insert_dummy_post()
{
	
	$currentDummyOption = get_option('qcopd_dummy_stat');
	
	if( $currentDummyOption != 1 ){
	
		$required = array(
			'post_exists' => ABSPATH . 'wp-admin/includes/post.php',
		);

		foreach ( $required as $func => $req_file ) {
			if ( ! function_exists( $func ) )
				require_once $req_file;
		}
		
		if( function_exists('is_user_logged_in') && is_user_logged_in() )
		{
		
			$post_arr = array(
				'post_title'   => 'Design Directory',
				'post_status'  => 'publish',
				'post_author'  => get_current_user_id(),
				'post_type' => 'sld',
				'meta_input'   => array(
					'qcopd_list_item01' => array(
						'qcopd_item_title' => 'dna88',
						'qcopd_item_link' => 'http://www.dna88.com/ultimate-list-of-free-web-design-resources/',
						'qcopd_item_nofollow' => 1,
						'qcopd_item_newtab' => 1,
						'qcopd_item_subtitle' => 'Ultimate List of Free Web Design Resources',
					)
				),
			);

			wp_insert_post( $post_arr );
		
		}
	
	}
	
}

