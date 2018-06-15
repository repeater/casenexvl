<?php
/**
 * Plugin Name: Simple Link Directory - Pro
 * Plugin URI: https://www.quantumcloud.com/
 * Description: Simple Link Directory is an advanced solution to all link page or partners page needs on your website. This innovative Link Directory PlugIn allows admin to create list of website links with website logo and a short description.
 * Version: 7.8.0
 * Author: QunatumCloud
 * Author URI: https://www.quantumcloud.com/
 * Requires at least: 4.0
 * Tested up to: 4.9
 * Text Domain: qc-opd
 * Domain Path: /lang/
 * License: GPL2
 */

defined('ABSPATH') or die("No direct script access!");

global $sld_plugin_version;

$sld_plugin_version = '3.4.1';

if( defined('ABSPATH') && !function_exists('wp_get_current_user') )
{
	//include_once(ABSPATH . 'wp-includes/pluggable.php');
}

//Custom Constants
define('QCOPD_URL', plugin_dir_url(__FILE__));
define('QCOPD_IMG_URL', QCOPD_URL . "/assets/images");
define('QCOPD_ASSETS_URL', QCOPD_URL . "assets");

define('QCOPD_DIR', dirname(__FILE__));
define('QCOPD_DIR_MOD', dirname(__FILE__)."/modules");
define('QCOPD_INC_DIR', QCOPD_DIR . "/inc");
define('OCOPD_TPL_DIR', QCOPD_DIR . "/templates");
define('OCOPD_UPLOAD_DIR', QCOPD_DIR . "/uploads");

define('OCOPD_TPL_URL', QCOPD_URL . "/templates");
define('OCOPD_UPLOAD_URL', QCOPD_URL . "uploads");


//load text domain
load_plugin_textdomain( 'qc-opd', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

//Helper function to extract domain
function qcsld_get_domain($url)
{
  $pieces = parse_url($url);
  $domain = isset($pieces['host']) ? $pieces['host'] : '';
  if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    return $regs['domain'];
  }
  return false;
}

//Include files and scripts

add_filter( 'ot_theme_mode', '__return_false', 999 );
require_once( 'option-tree/ot-loader.php' );
require_once( 'qc-opd-setting-option.php' );
require_once( 'qc-op-directory-post-type.php' );
require_once( 'qc-op-directory-assets.php' );

require_once( 'qc-op-directory-shortcodes.php' );
require_once( 'qc-opd-sorting-support.php' );
require_once( 'qc-opd-ajax-stuffs.php' );
require_once( 'qc-opd-ajax-bookmark.php' );
require_once( 'qcopd-helper-functions.php' );
require_once( 'qcopd-widgets.php' );
require_once( 'qcopd-custom-hooks.php' );

require_once( 'qc-op-directory-import.php' );
require_once( 'qcopd-shortcode-generator.php' );
require_once( 'qcopd-fa-modal.php' );
require_once( 'qcopd-all-category.php' );
require_once( 'qc-op-directory-multipage.php' );

//captcha module

require_once( QCOPD_DIR_MOD.'/captcha/simple-php-captcha.php' );
//Module for custom user registration sld.

require_once( QCOPD_DIR_MOD.'/registration/class.sld_registration.php' );
require_once( QCOPD_DIR_MOD.'/login/class.sld_login.php' );
require_once( QCOPD_DIR_MOD.'/dashboard/class.sld_dashboard.php' );

//Module for user approval
require_once( QCOPD_DIR_MOD.'/approval/sld-user-approve.php' );
//Package module integration
require_once( QCOPD_DIR_MOD.'/package/class.sld_package.php' );

require_once( QCOPD_DIR_MOD.'/claim_listing/class.sld_claim_listing_page.php' );
require_once( QCOPD_DIR_MOD.'/claim_listing/class.sld_claim_order_list.php' );
require_once( QCOPD_DIR_MOD.'/package/class.sld_order_list.php' );
require_once( QCOPD_DIR_MOD.'/click_report/class.sld_click_list.php' );
require_once( QCOPD_DIR_MOD.'/category/sld_category_image.php' );

require_once( 'qc-support-promo-page/class-qc-support-promo-page.php' );
register_activation_hook( __FILE__, 'qcld_sld_activate');
//Remove Slug Edit Box
add_action('admin_head', 'qcopd_remove_post_slug_editing');

//adding session
add_action( 'init', function() {
	if(session_id() == '')
		session_start();
});

/* Inserting jquery */
function sld_insert_jquery(){
wp_enqueue_script('jquery', false, array(), false, false);
}
add_filter('wp_enqueue_scripts','sld_insert_jquery',1);

function qcopd_remove_post_slug_editing()
{
	$option = sld_get_option('sld_global_font');
	//print_r($option);exit;
    global $post_type;

    if ($post_type == 'sld') {
        echo "<style>#edit-slug-box {display:none;} #qcopd_upvote_count, #qcopd_entry_time, #qcopd_timelaps, #qcopd_is_bookmarked,#qcopd_click { display: none; }</style>";
    }
}

//Check if outbound click tracking is ON
add_action('wp_head', 'qcopd_add_outbound_click_tracking_script');

function qcopd_add_outbound_click_tracking_script()
{

  if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php");
  }
  if(is_user_logged_in()){
    $current_user = wp_get_current_user();
    if(in_array('administrator',$current_user->roles)){
      return;
    }
  }


    $outbound_conf = sld_get_option( 'sld_enable_click_tracking' );

    if ( isset($outbound_conf) && $outbound_conf == 'on' ) {

        ?>
        <script>


          
function _gaLt(event) {

        /* If GA is blocked or not loaded, or not main|middle|touch click then don't track */
        if (!ga.hasOwnProperty("loaded") || ga.loaded != true || (event.which != 1 && event.which != 2)) {
            return;
        }

        var el = event.srcElement || event.target;

        /* Loop up the DOM tree through parent elements if clicked element is not a link (eg: an image inside a link) */
        while (el && (typeof el.tagName == 'undefined' || el.tagName.toLowerCase() != 'a' || !el.href)) {
            el = el.parentNode;
        }

        /* if a link with valid href has been clicked */
        if (el && el.href) {

            var link = el.href;

            /* Only if it is an external link */
            if (link.indexOf(location.host) == -1 && !link.match(/^javascript\:/i)) {

                /* Is actual target set and not _(self|parent|top)? */
                var target = (el.target && !el.target.match(/^_(self|parent|top)$/i)) ? el.target : false;

                /* Assume a target if Ctrl|shift|meta-click */
                if (event.ctrlKey || event.shiftKey || event.metaKey || event.which == 2) {
                    target = "_blank";
                }

                var hbrun = false; // tracker has not yet run

                /* HitCallback to open link in same window after tracker */
                var hitBack = function() {
                    /* run once only */
                    if (hbrun) return;
                    hbrun = true;
                    window.location.href = link;
                };

                if (target) { /* If target opens a new window then just track */
                    ga(
                        "send", "event", "Outgoing Links", link,
                        document.location.pathname + document.location.search
                    );
                } else { /* Prevent standard click, track then open */
                    event.preventDefault ? event.preventDefault() : event.returnValue = !1;
                    /* send event with callback */
                    ga(
                        "send", "event", "Outgoing Links", link,
                        document.location.pathname + document.location.search, {
                            "hitCallback": hitBack
                        }
                    );

                    /* Run hitCallback again if GA takes longer than 1 second */
                    setTimeout(hitBack, 1000);
                }
            }
        }
    }

    var _w = window;
    /* Use "click" if touchscreen device, else "mousedown" */
    var _gaLtEvt = ("ontouchstart" in _w) ? "click" : "mousedown";
    /* Attach the event to all clicks in the document after page has loaded */
    _w.addEventListener ? _w.addEventListener("load", function() {document.body.addEventListener(_gaLtEvt, _gaLt, !1)}, !1)
        : _w.attachEvent && _w.attachEvent("onload", function() {document.body.attachEvent("on" + _gaLtEvt, _gaLt)});
        </script>
        <?php

    }
}

/**
 * Submenu filter function. Tested with Wordpress 4.1.1
 * Sort and order submenu positions to match your custom order.
 *
 * @author Hendrik Schuster <contact@deviantdev.com>
 */
function qcopd_order_index_catalog_menu_page( $menu_ord )
{

  global $submenu;

  // Enable the next line to see a specific menu and it's order positions
  //echo '<pre>'; print_r( $submenu['edit.php?post_type=sld'] ); echo '</pre>'; exit();

  // Sort the menu according to your preferences
  //Original order was 5,11,12,13,14,15

  $arr = array();

  $arr[] = $submenu['edit.php?post_type=sld'][5];
  $arr[] = $submenu['edit.php?post_type=sld'][10];
  $arr[] = $submenu['edit.php?post_type=sld'][15];
  $arr[] = $submenu['edit.php?post_type=sld'][16];
  $arr[] = $submenu['edit.php?post_type=sld'][17];
  $arr[] = $submenu['edit.php?post_type=sld'][19];
  $arr[] = $submenu['edit.php?post_type=sld'][18];

  $submenu['edit.php?post_type=sld'] = $arr;

  return $menu_ord;

}

// add the filter to wordpress
//add_filter( 'custom_menu_order', 'qcopd_order_index_catalog_menu_page' );


	/*
	* Register Activation hook for multi & single site
	*
	*/
	 function qcld_sld_activate($network_wide){
		global $wpdb;
		if ( is_multisite() && $network_wide ) {
			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				sld_create_table();
				restore_current_blog();
			}
		} else {
			sld_create_table();
		}
		 
	}

//create table function
function sld_create_table(){
		global $wpdb;
		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {

			if ( ! empty( $wpdb->charset ) ) {

				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {

				$collate .= " COLLATE $wpdb->collate";

			}
		}

		$table             = $wpdb->prefix.'sld_user_entry';
		$table1             = $wpdb->prefix.'sld_package';
		$table3             = $wpdb->prefix.'sld_claim_configuration';
		$table4             = $wpdb->prefix.'sld_claim_purchase';
		$table2             = $wpdb->prefix.'sld_package_purchased';

		$sql_sliders_Table = "
		CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `item_title` varchar(150) NOT NULL,
		  `item_link` varchar(150) NOT NULL,
		  `item_subtitle` text NOT NULL,
		  `category` varchar(50) NOT NULL,
		  `sld_list` varchar(100) NOT NULL,
		  `nofollow` varchar(10) NOT NULL,
		  `opennewtab` varchar(10) NOT NULL,
		  `approval` int(11) NOT NULL,
		  `package_id` int(11) NOT NULL,
		  `time` datetime NOT NULL,
		  `image_url` text NOT NULL,
		  `user_id` varchar(50) NOT NULL,
		  `description` text NOT NULL,
		  `custom` text NOT NULL,
		  PRIMARY KEY (`id`)
		)  $collate AUTO_INCREMENT=1 ";


     $sql_sld_package = "
      CREATE TABLE IF NOT EXISTS `$table1` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(200) NOT NULL,
      `description` text NOT NULL,
      `date` datetime NOT NULL,
      `duration` varchar(10) NOT NULL,
      `Amount` float NOT NULL,
      `currency` varchar(10) NOT NULL,
      `item` varchar(10) NOT NULL,
      `paypal` varchar(100) NOT NULL,
      `sandbox` int(11) NOT NULL,
      `recurring` int(11) NOT NULL,
      `enable` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";
	
	$sql_sld_claim_configuration = "
      CREATE TABLE IF NOT EXISTS `$table3` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` datetime NOT NULL,
      `Amount` float NOT NULL,
      `currency` varchar(10) NOT NULL,
      `enable` int(11) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";

     $sql_sld_package_purchased = "
      CREATE TABLE IF NOT EXISTS `$table2` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` datetime NOT NULL,
      `renew_date` datetime NOT NULL,
      `expire_date` datetime NOT NULL,
      `package_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `recurring` int(11) NOT NULL,
      `paid_amount` float NOT NULL,
      `transaction_id` varchar(150) NOT NULL,
      `payer_name` varchar(100) NOT NULL,
      `payer_email` varchar(100) NOT NULL,
      `status` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";
	
	$sql_sld_claim_purchased = "
      CREATE TABLE IF NOT EXISTS `$table4` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `date` datetime NOT NULL,
      `user_id` int(11) NOT NULL,
      `listid` int(11) NOT NULL,
	   `item` varchar(255) NOT NULL,
      `paid_amount` float NOT NULL,
      `transaction_id` varchar(150) NOT NULL,
      `payer_name` varchar(100) NOT NULL,
      `payer_email` varchar(100) NOT NULL,
      `status` varchar(50) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate AUTO_INCREMENT=1";


		 require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		 dbDelta( $sql_sliders_Table );
		 dbDelta( $sql_sld_package );
		 dbDelta( $sql_sld_package_purchased );
		 dbDelta( $sql_sld_claim_configuration );
		 dbDelta( $sql_sld_claim_purchased );

		 if(!function_exists('qc_sld_isset_table_column')) {
			 function qc_sld_isset_table_column($table_name, $column_name)
			 {
				 global $wpdb;
				 $columns = $wpdb->get_results("SHOW COLUMNS FROM  " . $table_name, ARRAY_A);
				 foreach ($columns as $column) {
					 if ($column['Field'] == $column_name) {
						 return true;
					 }
				 }
			 }
		 }


		 if ( ! @qc_sld_isset_table_column( $table, 'package_id' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `package_id` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table, 'description' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table` ADD `description` text NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }


		 if ( ! @qc_sld_isset_table_column( $table1, 'sandbox' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `sandbox` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table1, 'recurring' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `recurring` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 
		 if ( ! @qc_sld_isset_table_column( $table1, 'enable' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `enable` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table1, 'item' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table1` ADD `item` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }

		 if ( ! @qc_sld_isset_table_column( $table2, 'renew' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `renew` datetime NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table2, 'expire_date' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `expire_date` datetime NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
		 if ( ! @qc_sld_isset_table_column( $table2, 'recurring' ) ) {

			 $sql_slides_Table_update_1 = "ALTER TABLE `$table2` ADD `recurring` int(11) NOT NULL;";
			 @$wpdb->query( $sql_slides_Table_update_1 );
		 }
}
	
	
//Remove top admin bar for slduser only

//add_action('init', 'sld_remove_admin_bar_slduser');

function sld_remove_admin_bar_slduser(){
	if(!function_exists('wp_get_current_user')) {
		include(ABSPATH . "wp-includes/pluggable.php");
	}
	if(is_user_logged_in()){
		$current_user = wp_get_current_user();
		if(in_array('slduser',$current_user->roles)){
			add_filter('show_admin_bar', '__return_false');
		}
	}
}



//add_action( 'init', 'qcld_sld_wpdocs_load_textdomain' );

/**
 * Load plugin textdomain.
 */
function qcld_sld_wpdocs_load_textdomain() {
    load_plugin_textdomain( 'qc-opd', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
}

/*Menu Order*/
add_action( 'admin_init', 'qcsld_posts_order_wpse' );

function qcsld_posts_order_wpse()
{
    add_post_type_support( 'sld', 'page-attributes' );
}

//Plugin loaded
add_action( 'plugins_loaded', 'sld_plugin_loaded_fnc' );
function sld_plugin_loaded_fnc(){


	$prev = get_option('option_tree');
	
	if(!empty($prev) && isset($prev['sld_enable_top_part'])){
		if(!get_option('sld_option_restore')){
			if(get_option('sld_option_tree')){
				update_option('sld_option_tree', $prev);
				add_option( 'sld_option_restore', 'yes', '', 'yes' );
			}else{
				add_option( 'sld_option_tree', $prev, '', 'yes' );
				add_option( 'sld_option_restore', 'yes', '', 'yes' );
			}
		}
	}

	global $wpdb;
	$table             = $wpdb->prefix.'sld_package';
	$pkg = $wpdb->get_row("select * from $table");
	$getoption1 = get_option('sld_option_tree');
	if($pkg->paypal!=''){
		if($getoption1['sld_paypal_email']==''){
			$getoption1['sld_paypal_email'] = $pkg->paypal;
			update_option( 'sld_option_tree', $getoption1, 'yes' );
		}

	}
	
	if($prev['sld_custom_style']!='' && !get_option('sld_option_restore_css')){
		$getoption1['sld_custom_style'] = $prev['sld_custom_style'];
		update_option( 'sld_option_tree', $getoption1, 'yes' );
		add_option( 'sld_option_restore_css', 'yes', '', 'yes' );
	}
	
}




function sld_click_table_fnc(){
	
	global $wpdb;
	$collate = '';

	if ( $wpdb->has_cap( 'collation' ) ) {

		if ( ! empty( $wpdb->charset ) ) {

			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {

			$collate .= " COLLATE $wpdb->collate";

		}
	}

	$table             = $wpdb->prefix.'sld_click_table';




	if(get_option('sld_click_table') !='added'){
		
		$sql_sliders_Table = "
		CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `ip` varchar(255) NOT NULL,
		  `itemurl` varchar(255) NOT NULL,
		  `itemid` varchar(255) NOT NULL,
		  `time` datetime NOT NULL,
		  `optional` varchar(255) NOT NULL,
		  PRIMARY KEY (`id`)
		)  $collate AUTO_INCREMENT=1 ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_sliders_Table );

		add_option( 'sld_click_table', 'added', '', 'yes' );
	}
}

add_action('init', 'sld_click_table_fnc');



function sld_upvote_restrict_by_ip(){
	
	global $wpdb;
	$collate = '';

	if ( $wpdb->has_cap( 'collation' ) ) {

		if ( ! empty( $wpdb->charset ) ) {

			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {

			$collate .= " COLLATE $wpdb->collate";

		}
	}

	$table             = $wpdb->prefix.'sld_ip_table';




	if(sld_get_option('sld_upvote_restrict_by_ip')=='on' and get_option('sld_ip_table') !='added'){
		
		$sql_sliders_Table = "
		CREATE TABLE IF NOT EXISTS `$table` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `item_id` varchar(255) NOT NULL,
		  `ip` varchar(255) NOT NULL,
		  `time` datetime NOT NULL,
		  `optional` varchar(15) NOT NULL,
		  PRIMARY KEY (`id`)
		)  $collate AUTO_INCREMENT=1 ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_sliders_Table );

		add_option( 'sld_ip_table', 'added', '', 'yes' );
	}
}

add_action('init', 'sld_upvote_restrict_by_ip');

//add_action('pre_get_posts','sld_users_own_attachments');
function sld_users_own_attachments( $wp_query_obj ) {

    global $current_user, $pagenow;

    $is_attachment_request = ($wp_query_obj->get('post_type')=='attachment');

    if( !$is_attachment_request )
        return;

	if(in_array('slduser',$current_user->roles) or in_array('subscriber',$current_user->roles))
		$wp_query_obj->set('author', $current_user->ID );

    return;
}

/*Include Update Checker - 06-12-2017, 01:58 AM, Kadir*/
require_once 'class-qc-plugin-update-checker.php';

// SLD Customization code//

add_action( 'add_meta_boxes', 'qcld_add_post_meta_boxes' );
function qcld_add_post_meta_boxes(){
	add_meta_box(
		'sld-post-class',
		esc_html__( 'Reset Upvotes', 'sld' ),
		'qcld_reset_post_class_meta_box',
		array('sld'),
		'side',
		'high'
	  );
}
function qcld_reset_post_class_meta_box(){
?>
  <p>
    <label for="linkbait-post-class"><?php _e( "Click the Reset Button to reset upvotes", 'sld' ); ?></label>
    <br />
	<div id="sld_show_msg"></div>
    <br />
	<input id="sld_reset_upvote" value="Reset Upvote" class="button" type="button">
  </p>
<?php
}

// To show the column header
function sld_cat_column_header( $columns ){
  $columns['header_name'] = 'ID'; 
 
  return $columns;
}

add_filter( "manage_edit-sld_cat_columns", 'sld_cat_column_header', 1);

// To show the column value
function sld_cat_column_content( $value, $column_name, $tax_id ){
   return $tax_id ;
}
add_action( "manage_sld_cat_custom_column", 'sld_cat_column_content', 1, 3);


add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
  
function my_custom_dashboard_widgets() {
global $wp_meta_boxes;
 
wp_add_dashboard_widget('sld_custom_help_widget', 'Simple Link Directory', 'sld_custom_dashboard_help');
}
function sld_custom_dashboard_help() {
	
global $wpdb;
		
		$getid = "select id from {$wpdb->prefix}sld_user_entry";
		$ids = $wpdb->get_results($getid);

		$total = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1" );
		
		$pending = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and (approval=0 or approval=3)" );
		$deny = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=2 " );
		$approved = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=1 " );
		
		$edited = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and approval=3 " );

		$paiditem = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and package_id!=0 " );
		$freeitem = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and package_id=0 " );
		echo '<p>Submitted Links summery</p>';
		echo '<p>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s', 'qcsld_user_entry_list' )).'">All '.($total==''||$total==0?0:$total).'</a> <br> 
			
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=pending', 'qcsld_user_entry_list' )).'">'.__('Pending', 'qc-opd').' '.($pending==''||$pending==0?0:$pending).' </a><br> 
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=approved', 'qcsld_user_entry_list' )).'">'.__('Approved', 'qc-opd').' '.($approved==''||$approved==0?0:$approved).'</a> <br>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=denied', 'qcsld_user_entry_list' )).'">'.__('Denied', 'qc-opd').'  '.($deny==''||$deny==0?0:$deny).'</a> </br>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=paid', 'qcsld_user_entry_list' )).'">'.__('Paid', 'qc-opd').'  '.($paiditem==''||$paiditem==0?0:$paiditem).'</a> <br>
			<a href="'.admin_url(sprintf( 'edit.php?post_type=sld&page=%s&stat=free', 'qcsld_user_entry_list' )).'">'.__('Free', 'qc-opd').'  '.($freeitem==''||$freeitem==0?0:$freeitem).'</a>

		</p>';

}

add_action( 'admin_menu', 'sld_pending_users_bubble', 999 );


 
function sld_pending_users_bubble() {
	global $menu, $wpdb;

		$pending = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}sld_user_entry where 1 and (approval=0 or approval=3)" );
		$pending_users = ($pending==''||$pending==0?0:$pending);
		// Locate the key of
		$key = sld_recursive_array_search( 'Simple Link Directory', $menu );
		// Not found, just in case
		if ( ! $key ) {
			return;
		}
		// Modify menu item
		$menu[$key][0] .= sprintf( '<span class="update-plugins count-%1$s" style="background-color:#de4848;color:white;margin-left:5px;"><span class="plugin-count">%1$s</span></span>', $pending_users );
	
}


function sld_recursive_array_search( $needle, $haystack ) {
	foreach ( $haystack as $key => $value ) {
		$current_key = $key;
		if ( $needle === $value || ( is_array( $value ) && sld_recursive_array_search( $needle, $value ) !== false ) ) {
			return $current_key;
		}
	}
	return false;
}

