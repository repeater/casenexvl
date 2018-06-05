<?php
/**
 * Plugin Name: Simple Link Directory - Lite
 * Plugin URI: https://wordpress.org/plugins/simple-link-directory
 * Description: Directory WordPress plugin to curate topic based link collections. Curate gorgeous Link Directory, Local Business Directory, Partners or Vendors Directory
 * Version: 4.3.0
 * Author: QuantumCloud
 * Author URI: https://www.quantumcloud.com/
 * Requires at least: 4.0
 * Tested up to: 4.9
 * Text Domain: qc-opd
 * Domain Path: /lang/
 * License: GPL2
 */

defined('ABSPATH') or die("No direct script access!");

//Custom Constants
define('QCOPD_URL', plugin_dir_url(__FILE__));
define('QCOPD_IMG_URL', QCOPD_URL . "/assets/images");
define('QCOPD_ASSETS_URL', QCOPD_URL . "/assets");

define('QCOPD_DIR', dirname(__FILE__));
define('QCOPD_INC_DIR', QCOPD_DIR . "/inc");

//Include files and scripts
require_once( 'qc-op-directory-post-type.php' );
require_once( 'option-tree/ot-loader.php' );

require_once( 'qc-op-directory-assets.php' );
require_once( 'qc-op-directory-shortcodes.php' );

require_once( 'embed/embedder.php' );

require_once( 'qcopd-shortcode-generator.php' );
require_once( 'qc-op-directory-import.php' );
require_once( 'qc-opd-ajax-stuffs.php' );

/*05-31-2017*/
require_once('qc-support-promo-page/class-qc-support-promo-page.php');

require_once('class-qc-free-plugin-upgrade-notice.php');
/*05-31-2017 - Ends*/
/* Option page */
require_once('qc-opd-setting-option.php');
require_once('qc-rating-feature/qc-rating-class.php');


//Remove Slug Edit Box
add_action('admin_head', 'qcopd_remove_post_slug_editing');

function qcopd_remove_post_slug_editing() 
{
    global $post_type;

    if ($post_type == 'sld') {
        echo "<style>#edit-slug-box {display:none;}#qcopd_upvote_count, #qcopd_entry_time, #qcopd_timelaps { display: none; }</style>";
    }
	
	echo '<style>
	.button.qcsld-promo-link {
	  color: #ff0000;
	  font-weight: normal;
	  margin-left: 0;
	  margin-top: 1px !important;
	}
	.clear{ clear: both; }
	</style>';
}


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


    $outbound_conf = ot_get_option( 'sld_enable_click_tracking' );

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

/*Add Promotional Link - Bue Pro - 12-30-2016*/
add_action( 'manage_posts_extra_tablenav', 'promo_link_in_cpt_table' );

function promo_link_in_cpt_table()
{
	$screen = get_current_screen();
	
	$current_screen = $screen->id;
	
	$link = "";
	
	if( $current_screen == 'edit-sld' )
	{	
		$link = '<div class="alignleft actions"><a href="https://www.quantumcloud.com/simple-link-directory/" target="_blank" class="button qcsld-promo-link">Upgrade to Pro (Now with Monetize Option)</a></div>';
	}
	
	echo $link;
	
}

add_action( 'buypro_promotional_link', 'promo_link_in_settings_page' );

function promo_link_in_settings_page()
{
	$screen = get_current_screen();
	
	$current_screen = $screen->id;
	
	$link = "";
	
	$link = '<div class="alignleft actions"><a href="https://www.quantumcloud.com/simple-link-directory/" target="_blank" class="button qcsld-promo-link">Upgrade to Pro (Now with Monetize Option)</a></div>';
	
	echo $link;
	
}

/**
 * Submenu filter function. Tested with Wordpress 4.1.1
 * Sort and order submenu positions to match your custom order.
 *
 * @author Hendrik Schuster <contact@deviantdev.com>
 */
function qclsldf_order_index_catalog_menu_page( $menu_ord ) 
{

  global $submenu;

  // Enable the next line to see a specific menu and it's order positions
  //echo '<pre>'; print_r( $submenu['edit.php?post_type=sld'] ); echo '</pre>'; exit();

  $arr = array();

  $arr[] = $submenu['edit.php?post_type=sld'][5];
  $arr[] = $submenu['edit.php?post_type=sld'][10];
  $arr[] = $submenu['edit.php?post_type=sld'][15];
  $arr[] = $submenu['edit.php?post_type=sld'][16];
  $arr[] = $submenu['edit.php?post_type=sld'][17];
  $arr[] = $submenu['edit.php?post_type=sld'][18];
  
  if( isset($submenu['edit.php?post_type=sld'][300]) ){
    $arr[] = $submenu['edit.php?post_type=sld'][300];
  }

  $submenu['edit.php?post_type=sld'] = $arr;

  return $menu_ord;

}





// add the filter to wordpress
//add_filter( 'custom_menu_order', 'qclsldf_order_index_catalog_menu_page' );