<?php
/*
Plugin Name: Embed Google AdWords Codes on WooCommerce
Plugin URI: http://www.storeya.com/
Description: The ultimate Woocommerce plugin for Google AdWords advertising - embedding Conversion Tracking and Remarketing codes for you! 
Version: 1.4
Author: StoreYa
Author URI: http://www.storeya.com/

=== VERSION HISTORY ===
01.11.13 - v1.0 - The first version

=== LEGAL INFORMATION ===
Copyright © 2013 StoreYa Feed LTD - http://www.storeya.com/

License: GPLv2 
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

add_action('wp_footer', 'adw_rem_tag_insert');

add_action('wp_head', 'goog_search_console_ver_insert');

add_action( 'woocommerce_thankyou', 'adw_conv_tag_insert' );

	// get an array with all product categories

	function get_product_category( $product_id ) {

		$prod_cats        = get_the_terms( $product_id, 'product_cat' );
		$prod_cats_output = array();

        // only continue with the loop if one or more product categories have been set for the product
		if ( ! empty( $prod_cats ) ) {
			foreach ( (array) $prod_cats as $k1 ) {
				array_push( $prod_cats_output, $k1->name );
			}

			// apply filter to the $prod_cats_output array
			$prod_cats_output = apply_filters( 'wgdr_filter', $prod_cats_output, 'prod_cats_output' );
		}

		return $prod_cats_output;
	}
	
	
		// get an array with all cart product ids
	function get_cart_product_ids( $cartprods ) {

		// initiate product identifier array
		$cartprods_items = array();

		// go through the array and get all product identifiers
		foreach ( (array) $cartprods as $entry ) {

				// fill the array with all product SKUs
							
				$product = wc_get_product( $entry['product_id'] );
				
				if ($product->is_type( 'variable' )) 
				{
				
				    $variable_product = $entry['data'];
				    // Get the  SKU
				    $sku = $variable_product ->get_sku();
				    array_push( $cartprods_items,  $sku);
						
				}
				else
	            {
	               array_push( $cartprods_items, $product->get_sku() );
	            }		
		}


		// apply filter to the $cartprods_items array
		$cartprods_items = apply_filters( 'wgdr_filter', $cartprods_items, 'cartprods_items' );

		return $cartprods_items;
	}
	
	
			// get an array with all cart product ids
	function get_cart_original_product_ids( $cartprods ) {

		// initiate product identifier array
		$cartprods_items = array();

		// go through the array and get all product identifiers
		foreach ( (array) $cartprods as $entry ) {

				// fill the array with all product SKUs
				$product = wc_get_product( $entry['product_id'] );
				array_push( $cartprods_items, $product->get_id() );
			
		}


		// apply filter to the $cartprods_items array
		$cartprods_items = apply_filters( 'wgdr_filter', $cartprods_items, 'cartprods_items' );

		return $cartprods_items;
	}
	
	
	
		// get an array with all product ids in the order
	function get_content_ids( $order ) {

		$order_items       = $order->get_items();
		$order_items_array = array();

		foreach ( (array) $order_items as $item ) {

				// fill the array with all product SKUs
				$product = wc_get_product( $item['product_id'] );
				
				if ($product->is_type( 'variable' )) 
				{
				    //$variable_product = $item['data'];				   
				   // $sku = $variable_product ->get_sku();
				   $product = wc_get_product( $item['variation_id'] );				 
				
				  // Get SKU
				  $sku = $product->get_sku();				   
				  array_push( $order_items_array,  $sku);
						
				}
				else
			            {
			               array_push( $order_items_array, $product->get_sku() );
			            }			
		}

		// apply filter to the $order_items_array array
		$order_items_array = apply_filters( 'wgdr_filter', $order_items_array, 'order_items_array' );

		return $order_items_array;
	}
	
		// get an array with all product ids in the order
	function get_original_content_ids( $order ) {

		$order_items       = $order->get_items();
		$order_items_array = array();

		foreach ( (array) $order_items as $item ) {


				// fill the array with all product SKUs
				$product = wc_get_product( $item['product_id'] );
				array_push( $order_items_array, $product->get_id() );

			
		}

		// apply filter to the $order_items_array array
		$order_items_array = apply_filters( 'wgdr_filter', $order_items_array, 'order_items_array' );

		return $order_items_array;
	}
	
	

function adw_rem_tag_insert()
{
    global $current_user;
    if (get_option('adw_rem_tag')) {            
       // $adw_rem_tag_script = get_option('adw_rem_tag');
        //echo $adw_rem_tag_script; 
        
        
        
        	global $woocommerce;
        	
        	$adw_conv_id  = get_adw_conv_id();

	
		?>


        <!-- START Google Code for Dynamic Remarketing --><?php

		// Check if is homepage and set home paramters.
		// is_home() doesn't work in my setup. I don't know why. I'll use is_front_page() as workaround
		if ( is_front_page() ) {
			?>

            <script type="text/javascript">
                var google_tag_params = {
                    dynx_pagetype: 'home'
                };
            </script>
			<?php
		} // Check if it is a product category page and set the category parameters.
        elseif ( is_product_category() ) {
			$product_id = get_the_ID();
			?>

            <script type="text/javascript">
                var google_tag_params = {
                    dynx_pagetype: 'searchresults',
                    dynx_itemid2: <?php echo( json_encode( get_product_category( $product_id ) ) ); ?>
                };
            </script>
			<?php
		} // Check if it a search results page and set the searchresults parameters.
        elseif ( is_search() ) {
			?>

            <script type="text/javascript">
                var google_tag_params = {
                    dynx_pagetype: 'searchresults'
                };
            </script>
			<?php
		} // Check if it is a product page and set the product parameters.
        elseif ( is_product() ) {
			$product_id = get_the_ID();
			$product    = wc_get_product( $product_id );

			if( is_bool( $product ) ){
			    error_log( 'WooCommerce detects the page ID ' . $product_id . ' as product, but when invoked by wc_get_product( ' . $product_id . ' ) it returns no product object');
			    return;
            }
            
            
            if ($product->is_type( 'variable' )) 
				{
				
				    $available_variations = $product->get_available_variations();
				    $variation_id=$available_variations[0]['variation_id'];
				    $variable_product_1= new WC_Product_Variation( $variation_id );
				    $sku = $variable_product_1->get_sku();
				    $price = 	$variable_product_1->get_price();		   
						
				}
				else
			            {
			              $sku =$product->get_sku() ;
			              $price = 	$product->get_price();
			            }            
            
            

			$product_id_code = '
		<script type="text/javascript">
			var google_tag_params = {
				dynx_itemid: ' . json_encode(  $sku  ) . ',
				dynx_itemid2: ' . json_encode( get_product_category( $product_id ) ) . ',
				dynx_pagetype: "offerdetail",
				dynx_totalvalue: ' . $price  . '
			};
		</script>';

			// apply filter to product id
			$product_id_code = apply_filters( 'wgdr_filter', $product_id_code, 'product_id_code', $product_id );

			echo $product_id_code;


		} // Check if it is the cart page and set the cart parameters.
        elseif ( is_cart() ) {
			$cartprods = $woocommerce->cart->get_cart();
			
			$product_ids = get_cart_original_product_ids( $cartprods );
			
			$categories = array();
			foreach ( $product_ids  as $item ) {
		            $categorised_array=  get_product_category( $item );
		            
		            foreach ( $categorised_array  as $category_item ) {
		               if(isset($category_item) && !empty($category_item) && !in_array($category_item, $categories))
		               {
		                  array_push($categories, $category_item);
		               }
		            }	         
		        }
			
			?>

            <script type="text/javascript">
                var google_tag_params = {
                    dynx_itemid: <?php echo( json_encode( get_cart_product_ids( $cartprods ) ) );?>,
                    dynx_pagetype: 'conversionintent',
                    dynx_totalvalue: <?php echo $woocommerce->cart->cart_contents_total; ?>,
                    dynx_itemid2: <?php echo( json_encode( $categories ) ); ?>
                };
            </script>
			<?php
		} // Check if it the order received page and set the according parameters
        elseif ( is_order_received_page() ) {

	        $order_key      = $_GET['key'];
	        $order          = new WC_Order( wc_get_order_id_by_order_key( $order_key ) );
	        $order_subtotal = $order->get_subtotal();
	        
	        
	       $product_ids = get_original_content_ids($order);
	       $categories = array();
			foreach ( $product_ids  as $item ) {
		            $categorised_array=  get_product_category( $item );
		            
		            foreach ( $categorised_array  as $category_item ) {
		               if(isset($category_item) && !empty($category_item) && !in_array($category_item, $categories))
		               {
		                  array_push($categories, $category_item);
		               }
		            }	         
		        }
	        
			?>

            <script type="text/javascript">
                var google_tag_params = {
                    dynx_itemid: <?php echo( json_encode( get_content_ids( $order ) ) ); ?>,
                    dynx_pagetype: 'conversion',
                    dynx_totalvalue: <?php echo $order_subtotal; ?>,
                     dynx_itemid2: <?php echo( json_encode( $categories ) ); ?>
                };
            </script>
			<?php

			// Only run conversion script if the payment has not failed. (has_status('completed') is too restrictive)
			// And use the order meta to check if the conversion code has already run for this order ID. If yes, don't run it again.
			if ( ! $order->has_status( 'failed' ) && ( ( get_post_meta( $order->get_order_number(), '_WGDR_conversion_pixel_fired', true ) == "true" ) ) ) {
				?>

                <script type="text/javascript">
                    var google_tag_params = {
                        dynx_itemid: <?php echo( json_encode( $this->get_content_ids( $order ) ) ); ?>,
                        dynx_pagetype: 'conversion',
                        dynx_totalvalue: <?php echo $order_subtotal; ?>

                    };
                </script>
				<?php
				update_post_meta( $order->get_order_number(), '_WGDR_conversion_pixel_fired', 'true' );
			} // end if order status
		} // For all other pages set the parameters for other.
		else {
			?>

            <script type="text/javascript">
                var google_tag_params = {
                    dynx_pagetype: 'other'
                };
            </script>
			<?php
		}

		?>

        <script type="text/javascript">
            /* <![CDATA[ */
            var google_conversion_id = <?php echo json_encode( $adw_conv_id, JSON_NUMERIC_CHECK ); ?>;
            var google_custom_params = window.google_tag_params;
            var google_remarketing_only = true;
            /* ]]> */
        </script>
        <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
        </script>
        <noscript>
            <div style="display:inline;">
                <img height="1" width="1" style="border-style:none;" alt=""
                     src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/<?php echo $adw_conv_id; ?>/?value=0&guid=ON&script=0"/>
            </div>
        </noscript>
        <!-- END Google Code for Dynamic Remarketing -->

		<?php
   
        
        
    }
}

function goog_search_console_ver_insert()
{
    global $current_user;
    if (get_option('goog_search_console_ver')) {            
        $goog_search_console_ver_script = get_option('goog_search_console_ver');
        echo $goog_search_console_ver_script; 
    }
}

function get_adw_conv_id(){
	    global $current_user;
		if (get_option('adw_conv_id')) {            
			$adw_conv_id = get_option('adw_conv_id');
			return $adw_conv_id ; 
		}	
       return null;		
	}
	
	function get_adw_conv_label(){
	    global $current_user;
		if (get_option('adw_conv_label')) {            
			$adw_conv_label = get_option('adw_conv_label');
			return $adw_conv_label ; 
		}	
       return null;		
	}

function adw_conv_tag_insert($order_id) {
	
		$adw_conv_id  = get_adw_conv_id();	
	    $adw_conv_label  = get_adw_conv_label();
		
		$order = new WC_Order( $order_id );
		$order_total = $order->get_total();			
		
    if ( !$order->has_status( 'failed' ) && isset($adw_conv_id) && isset($adw_conv_label)){    
		
		$currency = $order->get_order_currency();
		
		
?>
	<!-- Start Google AdWords Conversion Code -->
	<script type="text/javascript">	
	var google_conversion_id = <?php echo $adw_conv_id; ?>;
	var google_conversion_language = 'en';
	var google_conversion_format = '3';
	var google_conversion_color = 'ffffff';
	var google_conversion_label = '<?php echo $adw_conv_label; ?>';
	var google_conversion_value = <?php echo $order_total; ?>;
	var google_conversion_currency = '<?php echo $currency; ?>';
	var google_conversion_order_id = <?php echo $order_id; ?>;
	var google_remarketing_only = false;	
	</script>
	<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
	</script>
	<noscript>
	<div style="display:inline;">
	<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/<?php echo $adw_conv_id; ?>/?value=<?php echo $order_total; ?>&currency_code=<?php echo $currency; ?>&label=<?php echo $adw_conv_label; ?>&guid=ON&script=0&oid=<?php echo $order_id; ?>"/>
	</div>
	</noscript>
	</script>
	<!-- End Google AdWords Conversion Code -->

<?php	
	} 
}


if ( is_admin() ) {	


$plugurldir = get_option('siteurl') . '/' . PLUGINDIR . '/embed_google_adwords_codes/';
$igac_domain = 'embedGoogleAdWordsCodes';
load_plugin_textdomain($igac_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/embed_google_adwords_codes/');
add_action('init', 'igac_init');

add_action('admin_notices', 'igac_admin_notice');
add_filter('plugin_action_links', 'igac_plugin_actions', 10, 2);



function igac_init()
{
    if (function_exists('current_user_can') && current_user_can('manage_options'))
        add_action('admin_menu', 'igac_add_settings_page');
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $options = get_option('igacDisable');
}
function igac_settings()
{
    register_setting('embed_google_adwords_codes-group', 'adw_rem_tag');
    
     register_setting('embed_google_adwords_codes-group', 'goog_search_console_ver');
    
    register_setting('embed_google_adwords_codes-group', 'adw_conv_tag');
    
    register_setting('embed_google_adwords_codes-group', 'adw_conv_id');
    register_setting('embed_google_adwords_codes-group', 'adw_conv_label');
    
    register_setting('embed_google_adwords_codes-group', 'igacDisable');
    add_settings_section('embed_google_adwords_codes', "Embed Google AdWords Codes", "", 'embed_google_adwords_codes-group');

}
function igac_plugin_get_version()
{
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__)));
    $plugin_file   = basename((__FILE__));
    return $plugin_folder[$plugin_file]['Version'];
}


function igac_admin_notice()
{
    if (!get_option('adw_conv_tag') && !get_option('adw_rem_tag'))
        echo ('<div class="error"><p><strong>' . sprintf(__('Embed Google AdWords Codes plugin is not set. Please go to the <a href="%s">plugin page</a> and save a valid data to enable it.'), admin_url('options-general.php?page=embed_google_adwords_codes')) . '</strong></p></div>');
}
function igac_plugin_actions($links, $file)
{
    $igac_domain = 'embedGoogleAdWordsCodes';
    static $this_plugin;
    if (!$this_plugin)
        $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin && function_exists('admin_url')) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=embed_google_adwords_codes') . '">' . __('Settings', $igac_domain) . '</a>';
        array_unshift($links, $settings_link);
    }
    return ($links);
}

        
    function igac_add_settings_page()
    {
        $igac_domain = 'EmbedGoogleAdWordsCodes';
		function igac_settings_page()
        {
            global $plugurldir, $storeya_options;
			$igac_domain = 'EmbedGoogleAdWordsCodes';
?>
      <div class="wrap">
        <?php
            screen_icon();
?>
        <h2><?php
            _e('StoreYa - Embed Google AdWords Codes ', $igac_domain);
?> <small><?
            echo igac_plugin_get_version();
?></small></h2>
        <div class="metabox-holder meta-box-sortables ui-sortable pointer">
          <div class="postbox" style="float:left;width: 81.5em;margin-right:20px">

            <div class="inside" style="padding: 0 10px">
              <p style="text-align:center">
		      </p>
              <form onSubmit="FillInfo();" method="post" action="options.php">
                <?php
            settings_fields('embed_google_adwords_codes-group');
?>
                <h3>How remarketing works</h3>
		<p>Implementing Google AdWords Remarketing Tag, so that you can mark your site's visitors and show them more ads in the future until they are convinced to purchase your products / services.</p>
                <table class="form-table"><tbody><tr><th scope="row">Remarketing Code</th><td><textarea rows="10" cols="20" style="width:100%;" name="adw_rem_tag" ><?php echo get_option('adw_rem_tag');?></textarea></td></tr></tbody></table>
                
                  <h3>Google Search Console Verification</h3>
           	<p>Implementing Google Search Console Verification Code helps you monitor and maintain your site's presence in Google Search results. Connecting and verifying your website can help you understand how Google views your site and optimize its performance in search results.</p>
                <table class="form-table"><tbody><tr><th scope="row">Verification Code</th><td><textarea rows="2" cols="20" style="width:100%;" name="goog_search_console_ver" ><?php echo get_option('goog_search_console_ver');?></textarea>                             
                </td></tr></tbody></table>
                
                 <h3>Adwords conversion tracking code</h3>
           	<p>Implementing Google AdWords conversion tracking so you would know how effective your ads are; how many of the clicks you are paying for are actually gaining for you sales, installs or whatever you are trying to to reach.</p>
                <table class="form-table"><tbody><tr><th scope="row">Conversion Tracking Code</th><td><textarea rows="10" cols="20" style="width:100%;" name="adw_conv_tag" ><?php echo get_option('adw_conv_tag');?></textarea>                
         
                 
                 <input type="hidden" name="adw_conv_id" value="<?php echo get_option('adw_conv_id');?>">
                 <input type="hidden" name="adw_conv_label" value="<?php echo get_option('adw_conv_label');?>">
                
                </td></tr></tbody></table>
                
                    <p class="submit">
                      <input type="submit" class="button-primary" value="<?php
            _e('Save Changes');
?>" />
                    </p>
                  </form>
</p>   				  <a href="http://www.storeya.com/public/trafficbooster?utm_source=WP&utm_medium=TBPlugin&utm_campaign=TBReg" target="_blank"><img src="<?php echo (plugins_url( 'TB.jpg', __FILE__ )); ?>"  /></a>
</div>
                </div>

                </div>
              </div>
			  <img src="http://www.storeya.com/widgets/admin?p=WpEmbedGoogleAdWordsCodes"/>
	<script type="text/javascript">
	
	function FillInfo()
	{
	    <?php 
	
	    if (get_option('adw_conv_tag')) { 
	               
                $adw_conv_tag_script = get_option('adw_conv_tag');              
                     
                if (preg_match("/var google_conversion_id = (.*);/", $adw_conv_tag_script)) {
                
		   preg_match("/var google_conversion_id = (.*);/", $adw_conv_tag_script, $id_matches);
                   $conversion_id = $id_matches[1];                 
                   update_option( 'adw_conv_id', $conversion_id );
                   	  
		}              
                        
                if (preg_match("/var google_conversion_label = \"(.*)\";/", $adw_conv_tag_script )) {
                
		   preg_match("/var google_conversion_label = \"(.*)\";/", $adw_conv_tag_script , $label_matches); 
                   $conversion_label = $label_matches[1];                   
                   update_option( 'adw_conv_label', $conversion_label  );
                   	  
		}      
            } 
            else if(get_option('adw_rem_tag'))
            {
              $adw_rem_tag_script = get_option('adw_rem_tag');              
                     
                if (preg_match("/var google_conversion_id = (.*);/", $adw_rem_tag_script )) {
                
		   preg_match("/var google_conversion_id = (.*);/", $adw_rem_tag_script , $id_matches);
                   $conversion_id = $id_matches[1];                 
                   update_option( 'adw_conv_id', $conversion_id );
                   	  
		} 
            }
            
            
            else
            {
               update_option( 'adw_conv_id', null);
               update_option( 'adw_conv_label', null);
            }   
	   
	    ?>	   

	}

        </script>
        <?php
        }
        add_action('admin_init', 'igac_settings');
        add_submenu_page('options-general.php', __('StoreYa - Embed Google AdWords Codes', $igac_domain), __('StoreYa - Embed Google AdWords Codes', $igac_domain), 'manage_options', 'embed_google_adwords_codes', 'igac_settings_page');
    }
    
    
}



?>