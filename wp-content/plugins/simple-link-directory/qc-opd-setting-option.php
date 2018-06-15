<?php



add_filter( 'ot_show_pages', '__return_false' );
add_filter( 'ot_show_new_layout', '__return_false' );

function qcld_sld_remove_ot_menu () {
    remove_submenu_page( 'themes.php', 'ot-theme-options' );
	
}
//add_action( 'admin_init', 'qcld_sld_remove_ot_menu' );

add_filter( 'ot_header_version_text', 'sld_ot_version_text_custom' );

function sld_ot_version_text_custom()
{
	$text = 'Developed by <a href="http://www.quantumcloud.com" target="_blank">Web Design Company - QuantumCloud</a>';
	
	return $text;
}

/**
 * Hook to register admin pages 
 */
add_action( 'init', 'sld_register_options_pages' );

/**
 * Registers all the required admin pages.
 */

function sld_register_options_pages() {

  // Only execute in admin & if OT is installed
  if ( is_admin() && function_exists( 'ot_register_settings' ) ) {

    // Register the pages
    ot_register_settings( 
      array(
        array( 
          'id'              => 'sld_option_tree',
          'pages'           => array(
            array(
              'id'              => 'sld_options',
              'parent_slug'     => 'edit.php?post_type=sld',
              'page_title'      => 'Settings',
              'menu_title'      => 'Settings',
              'capability'      => 'edit_theme_options',
              'menu_slug'       => 'sld-options-page',
              'icon_url'        => null,
              'position'        => null,
              'updated_message' => 'SLD Options Updated.',
              'reset_message'   => 'SLD Options Reset.',
              'button_text'     => 'Save Changes',
              'show_buttons'    => true,
              'screen_icon'     => 'options-general',
              'contextual_help' => null,
			  
    'sections'        => array( 
      array(
        'id'          => 'general',
        'title'       => __( 'Settings', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'frontend',
        'title'       => __( 'Frontend Submission', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'claim_listing',
        'title'       => __( 'Claim Listing', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'payment',
        'title'       => __( 'Payment Method', 'theme-text-domain' )
      ),
      array(
        'id'          => 'bookmark',
        'title'       => __( 'Favorite Settings', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'multipage',
        'title'       => __( 'Multipage Settings', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'upvote',
        'title'       => __( 'Upvote Settings', 'theme-text-domain' )
      ),
	  
	  array(
        'id'          => 'language',
        'title'       => __( 'Language Settings', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'custom_css',
        'title'       => __( 'Custom CSS', 'theme-text-domain' )
      ),
	  array(
        'id'          => 'custom_js',
        'title'       => __( 'Custom Javascript', 'theme-text-domain' )
      ),
      array(
        'id'          => 'help',
        'title'       => __( 'Help', 'theme-text-domain' )
      )
    ),
			  
    'settings'        => array(
	    array(
		    'label'       => __('SLD Admin Email'),
		    'id'          => 'sld_admin_email',
		    'type'        => 'text',
		    'desc'        => __('Please provide a valid email address.'),
		    'std'         => '',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'general'
	    ),
		array(
			'id'          => 'sld_use_global_font',
			'label'       => __( 'Select Font Setting', 'text-domain' ),
			'desc'        => __( '', 'text-domain' ),
			'type'        => 'select',
			'section'     => 'general',
			'choices'     => array( 
					array(
						'value'       => 'no',
						'label'       => __( 'Theme Font', 'text-domain' ),
					),
					array(
						'value'       => 'yes',
						'label'       => __( 'Custom Font', 'text-domain' ),
					),
				),
			'default'	=> 'yes'
			
		),

	array(
		'label'       => __('Custom Font'),
		'id'          => 'sld_global_font',
		'type'        => 'google-fonts',
		'desc'        => __('You have to select Custom Font from Font setting, Otherwise change will not take place.'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general',
		'condition' => 'sld_use_global_font:is(yes)'
	),
	array(
		'label'       => __('Enable Top Area'),
		'id'          => 'sld_enable_top_part',
		'type'        => 'on-off',
		'desc'        => __('Top Area includes Live Search, Add a Item, Share Button.'),
		'std'         => 'on',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	array(
		'label'       => __('Enable Live Search'),
		'id'          => 'sld_enable_search',
		'type'        => 'on-off',
		'desc'        => __('Live search through directory items.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),

	array(
			'id'          => 'sld_filter_ptype',
			'label'       => __( 'Filter Button Presentation Style', 'text-domain' ),
			'desc'        => __( '', 'text-domain' ),
			'type'        => 'select',
			'section'     => 'general',
			'choices'     => array( 
					array(
						'value'       => 'normal',
						'label'       => __( 'Normal Buttons', 'text-domain' ),
					),
					array(
						'value'       => 'carousel',
						'label'       => __( 'Carousel', 'text-domain' ),
					),
				),
			'default'	=> 'yes'
			
		),

	array(
		'label'       => __('Enable List Filtering at Top Position'),
		'id'          => 'sld_enable_filtering',
		'type'        => 'on-off',
		'desc'        => __(''),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),

	    array(
		    'label'       => __('Enable List Filtering at Left Position'),
		    'id'          => 'sld_enable_filtering_left',
		    'type'        => 'on-off',
		    'desc'        => __(''),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'general'
	    ),

	array(
		'label'       => __('Enable RTL Direction'),
		'id'          => 'sld_enable_rtl',
		'type'        => 'on-off',
		'desc'        => __('If you make this option ON, then list heading and list items will be arranged in Right-to-Left direction.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	array(
		'label'       => __('Enable Embed List Button on Listing Pages'),
		'id'          => 'sld_enable_embed_list',
		'type'        => 'on-off',
		'desc'        => __('Enable embed link button to generate iFrame embed code for particular list.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	
	
	array(
		'label'       => __('Embed Credit Title'),
		'id'          => 'sld_embed_credit_title',
		'type'        => 'text',
		'desc'        => __('This text will be displayed below embedded list in other sites.'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	array(
		'label'       => __('Embed Credit Link'),
		'id'          => 'sld_embed_credit_link',
		'type'        => 'text',
		'desc'        => __('This text will be displayed below embedded list in other sites.'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	array(
		'label'       => __('Track Outbound Clicks'),
		'id'          => 'sld_enable_click_tracking',
		'type'        => 'on-off',
		'desc'        => __('You need to have the analytics.js [<a href="https://support.google.com/analytics/answer/1008080#GA" target="_blank">Analytics tracking code in every page of your site</a>].'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	array(
		'label'       => __('Enable Upvote Option in Widgets'),
		'id'          => 'sld_enable_widget_upvote',
		'type'        => 'on-off',
		'desc'        => __('Allow users to upvote items using widgets.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'upvote'
	),
	    array(
		    'id'          => 'sld_use_global_thumbs_up',
		    'label'       => __( 'Select Upvote Icon', 'text-domain' ),
		    'desc'        => __( '', 'text-domain' ),
		    'type'        => 'select',
		    'section'     => 'upvote',
		    'choices'     => array(
			    array(
				    'value'       => 'fa-thumbs-up',
				    'label'       => __( 'Thumbs Up', 'text-domain' ),
			    ),
			    array(
				    'value'       => 'fa-heart',
				    'label'       => __( 'Heart', 'text-domain' ),
			    ),
			    array(
				    'value'       => 'fa-smile-o',
				    'label'       => __( 'Smile Face', 'text-domain' ),
			    ),
			    array(
				    'value'       => 'fa-fire',
				    'label'       => __( 'Fire', 'text-domain' ),
			    ),
			    array(
				    'value'       => 'fa-star',
				    'label'       => __( 'Star', 'text-domain' ),
			    ),

		    ),
		    'default'	=> 'yes'

	    ),
		
		array(
			'label'       => __('Upvote restrict by IP'),
			'id'          => 'sld_upvote_restrict_by_ip',
			'type'        => 'on-off',
			'desc'        => __('Daily one upvote per IP.'),
			'std'         => 'off',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'section'     => 'upvote'
		),
		
		array(
			'label'       => __('Allow only logged in user to upvote'),
			'id'          => 'sld_upvote_user_login',
			'type'        => 'on-off',
			'desc'        => __('Allow only logged in user to upvote.'),
			'std'         => 'off',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'section'     => 'upvote'
		),
		array(
			'label'       => __('Login Url'),
			'id'          => 'sld_upvote_login_url',
			'type'        => 'text',
			'desc'        => __(''),
			'std'         => '',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'section'     => 'upvote',
			'condition' => 'sld_upvote_user_login:is(on)',
		),
		
		
		array(
			'label'       => __('Reset Upvotes'),
			'id'          => 'sld_upvote_reset',
			'type'        => 'Textblock',
			'desc'        => __('<button id="sld_reset_all_upvotes" class="option-tree-ui-button button button-primary">Reset Upvotes</button>'),
			'section'     => 'upvote'
			
		),
		array(
			'label'       => __('Upvote Expire after (Days). Ex: 30'),
			'id'          => 'sld_upvote_expire_after',
			'type'        => 'text',
			'desc'        => __('Upvote will automatic expire after choosen days. This field only accept integer as day. EX: 30. If you do not want any expiration of upvote just leave this field empty.'),
			'section'     => 'upvote'
			
		),
		array(
			'label'       => __('Enable Statistics'),
			'id'          => 'sld_enable_statistics',
			'type'        => 'on-off',
			'desc'        => __('Enable Statistics.'),
			'std'         => 'off',
			'rows'        => '',
			'post_type'   => '',
			'taxonomy'    => '',
			'class'       => '',
			'section'     => 'general'
		),
		
	array(
		'label'       => __('Enable Scroll to Top Button'),
		'id'          => 'sld_enable_scroll_to_top',
		'type'        => 'on-off',
		'desc'        => __('Show Scroll to Top.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),

	array(
		'label'       => __('Enable Claim Listing'),
		'id'          => 'sld_enable_claim_listing',
		'type'        => 'on-off',
		'desc'        => __('Shortcode: [sld_claim_listing] <br><span style="color:red;font-weight:bold"> Before enabling Claim Listing you must enable and configure Frontend Submission. <br>You have to create a page by using this Shortcode [sld_claim_listing] where user can register & claim list item.</span><br><br> <div id="sld_page_check"></div> '),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'claim_listing'
	),
	array(
		'label'       => __('Show Claim Listing Button at Top'),
		'id'          => 'sld_show_claim_listing_button',
		'type'        => 'on-off',
		'desc'        => __(''),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'claim_listing'
	),
	


	//Code for frontend section//
	array(
		'label'       => __('Enable Frontend Submission'),
		'id'          => 'sld_enable_add_new_item',
		'type'        => 'on-off',
		'desc'        => __('<span style="color:red;font-weight:bold">For front end submission to work you need to create 4 different pages and paste the following short code in each page: [sld_login], [sld_registration], [sld_dashboard] & [sld_restore] </span><br><br> <div id="sld_page_check"></div> '),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend'
	),
	    array(
		    'label'       => __('Enable Free Frontend Submission'),
		    'id'          => 'sld_enable_free_submission',
		    'type'        => 'on-off',
		    'desc'        => __('Allow user to submit their items free of cost.'),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_add_new_item:is(on)',
	    ),
	    array(
		    'label'       => __('Free Submission Limit'),
		    'id'          => 'sld_free_item_limit',
		    'type'        => 'text',
		    'desc'        => __('Enter your limit amount. EX:10.'),
		    'std'         => '10',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_free_submission:is(on),sld_enable_add_new_item:is(on)',

	    ),
	    /*array(
		    'label'       => __('Enable Package for Frontend Submission'),
		    'id'          => 'sld_enable_package',
		    'type'        => 'on-off',
		    'desc'        => __('To enable package please make sure that you have setup a package from <a href="'.get_admin_url().'edit.php?post_type=sld&page=qcsld_package">Simple Link Directory/Package</a>.'),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_add_new_item:is(on)',
	    ),*/
	    array(
		    'label'       => __('Enable Email Notification for New Item Submission'),
		    'id'          => 'sld_email_notification',
		    'type'        => 'on-off',
		    'desc'        => __(''),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_add_new_item:is(on)',
	    ),
	array(
		'label'       => __('Mark Paid Item as Featured'),
		'id'          => 'sld_paid_item_featured',
		'type'        => 'on-off',
		'desc'        => __('Enable to mark paid item as featured.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),
	array(
		'label'       => __('Show Featured Item at Top'),
		'id'          => 'sld_featured_item_top',
		'type'        => 'on-off',
		'desc'        => __('Enable to show featured item at top.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),
	array(
		'label'       => __('Registration Captcha'),
		'id'          => 'sld_enable_captcha',
		'type'        => 'on-off',
		'desc'        => __('Captcha for frontend registration form.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),
	
	array(
		'label'       => __('Allow any logged in user to add link'),
		'id'          => 'sld_enable_anyusers',
		'type'        => 'on-off',
		'desc'        => __('Allow any logged in user to add link'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),

	    array(
		    'label'       => __('Allow User to Update Profile'),
		    'id'          => 'sld_profile_update',
		    'type'        => 'on-off',
		    'desc'        => __('Allow User to Update their Profile from dashboard.'),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_add_new_item:is(on)',
	    ),

	    array(
		    'label'       => __('Allow User to Upload Image'),
		    'id'          => 'sld_image_upload',
		    'type'        => 'on-off',
		    'desc'        => __('Allow user to upload item image.'),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_add_new_item:is(on)',
	    ),
		
	    array(
		    'label'       => __('Do Not Allow do-follow Links '),
		    'id'          => 'sld_disable_no_follow',
		    'type'        => 'on-off',
		    'desc'        => __(''),
		    'std'         => 'off',
		    'rows'        => '',
		    'post_type'   => '',
		    'taxonomy'    => '',
		    'class'       => '',
		    'section'     => 'frontend',
		    'condition' => 'sld_enable_add_new_item:is(on)',
	    ),

	array(
		'label'       => __('Auto Approve Registered Users'),
		'id'          => 'sld_enable_user_approval',
		'type'        => 'on-off',
		'desc'        => __('When turned off admin needs to Approve users manually from main WordPress Users.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),
	array(
		'label'       => __('Auto Approve Item Submission'),
		'id'          => 'sld_enable_auto_approval',
		'type'        => 'on-off',
		'desc'        => __('Auto approval for frontend list submission.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),
	
	array(
		'label'       => __('Enable Paypal payment'),
		'id'          => 'sld_enable_paypal_payment',
		'type'        => 'on-off',
		'desc'        => __('Enable Paypal as payment gateway'),
		'std'         => 'on',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		
	),
	array(
		'label'       => __('Paypal Email Address'),
		'id'          => 'sld_paypal_email',
		'type'        => 'text',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		'condition' => 'sld_enable_paypal_payment:is(on)',
	),
	array(
		'label'       => __('Enable Paypal Test Mode'),
		'id'          => 'sld_enable_paypal_test_mode',
		'type'        => 'on-off',
		'desc'        => __('Enable Paypal Test Mode'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		'condition' => 'sld_enable_paypal_payment:is(on)',
	),
	array(
		'label'       => __('Enable Paypal Recurring Payment'),
		'id'          => 'sld_enable_paypal_recurring',
		'type'        => 'on-off',
		'desc'        => __('Enable Paypal Recurring Payment'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		'condition' => 'sld_enable_paypal_payment:is(on)',
	),
	
	array(
		'label'       => __('Enable Stripe payment'),
		'id'          => 'sld_enable_stripe_payment',
		'type'        => 'on-off',
		'desc'        => __('Enable stripe as payment gateway'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		
	),
	
	array(
		'label'       => __('Stripe Secret Key'),
		'id'          => 'sld_stripe_sectet_key',
		'type'        => 'text',
		'desc'        => __('You can find the Secret key from <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">https://dashboard.stripe.com/account/apikeys</a> after logging in to your stripe dashboard'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		'condition' => 'sld_enable_stripe_payment:is(on)',
	),
	array(
		'label'       => __('Stripe Public Key'),
		'id'          => 'sld_stripe_public_key',
		'type'        => 'text',
		'desc'        => __('You can find the Public key from <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">https://dashboard.stripe.com/account/apikeys</a> after logging in to your stripe dashboard'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'payment',
		'condition' => 'sld_enable_stripe_payment:is(on)',
	),
	
	array(
		'label'       => __('Exclude List By ID'),
		'id'          => 'sld_exclude_list',
		'type'        => 'text',
		'desc'        => __('Exclude list from frontend submission by id. Use comma(,) to seperate multiple ids. EX:102,105'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(on)',
	),
	
	array(
		'label'       => __('Link Add New Button to a Page Instead'),
		'id'          => 'sld_add_new_behave',
		'type'        => 'on-off',
		'desc'        => __(''),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_enable_add_new_item:is(off)',
	),
	array(
		'label'       => __('Add Button Link'),
		'id'          => 'sld_add_item_link',
		'type'        => 'text',
		'desc'        => __('Example: http://www.yourdomain.com'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'frontend',
		'condition' => 'sld_add_new_behave:is(on),sld_enable_add_new_item:is(off)'
	),
	
	array(
		'label'       => 'Custom CSS',
		'id'          => 'sld_custom_style',
		'type'        => 'css',
		'desc'        => __('Write your custom CSS here.'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'custom_css'
	),
	array(
		'label'       => 'Custom Javascript',
		'id'          => 'sld_custom_js',
		'type'        => 'javascript',
		'desc'        => __('Write your custom javascript here. No need any script tag.'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'custom_js'
	),

//Bookmark section

array(
		'label'       => __('Enable Favorite'),
		'id'          => 'sld_enable_bookmark',
		'type'        => 'on-off',
		'desc'        => __(''),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
array(
		'label'       => __('User Login url'),
		'id'          => 'sld_bookmark_user_login_url',
		'type'        => 'text',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
array(
		'label'       => __('Favorite List Title'),
		'id'          => 'sld_bookmark_title',
		'type'        => 'text',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
array(
		'label'       => __('Popup Message'),
		'id'          => 'sld_bookmark_popup_content',
		'type'        => 'text',
		'desc'        => __('This message will show when login required to add favorite.'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('List Holder Color'),
		'id'          => 'sld_bookmark_list_holder_color',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('Item Background Color'),
		'id'          => 'sld_bookmark_item_background_color',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('Item Background Color Hover'),
		'id'          => 'sld_bookmark_item_background_color_hover',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('Item Text Color'),
		'id'          => 'sld_bookmark_item_text_color',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('Item Text Color Hover'),
		'id'          => 'sld_bookmark_item_text_color_hover',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),

	array(
		'label'       => __('Item Sub Text Color'),
		'id'          => 'sld_bookmark_item_sub_text_color',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('Item Sub Text Color Hover'),
		'id'          => 'sld_bookmark_item_sub_text_color_hover',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	
	array(
		'label'       => __('Item Border Color'),
		'id'          => 'sld_bookmark_item_border_color',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),
	array(
		'label'       => __('Item Border Color Hover'),
		'id'          => 'sld_bookmark_item_border_color_hover',
		'type'        => 'colorpicker',
		'desc'        => __(''),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'bookmark'
	),

	array(
		'label'       => __('Add Link'),
		'id'          => 'sld_lan_add_link',
		'type'        => 'text',
		'desc'        => __('Change the language for Add Link'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Share List'),
		'id'          => 'sld_lan_share_list',
		'type'        => 'text',
		'desc'        => __('Change the language for Share List'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	
	array(
		'label'       => __('Live Search Items'),
		'id'          => 'sld_lan_live_search',
		'type'        => 'text',
		'desc'        => __('Change the language for Live Search Items'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Show All'),
		'id'          => 'sld_lan_show_all',
		'type'        => 'text',
		'desc'        => __('Change the language for Show All'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Visit Page'),
		'id'          => 'sld_lan_visit_page',
		'type'        => 'text',
		'desc'        => __('Change the language for Visit Page'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Related Items'),
		'id'          => 'sld_lan_related_items',
		'type'        => 'text',
		'desc'        => __('Change the language for Related Items'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Already Have an Account?'),
		'id'          => 'sld_lan_already_account',
		'type'        => 'text',
		'desc'        => __('Change the language for Already Have an Account?'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Visit This Link'),
		'id'          => 'sld_lan_visit_link',
		'type'        => 'text',
		'desc'        => __('Change the language for Visit This Link'),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'language'
	),
	array(
		'label'       => __('Enable SLD Multipage'),
		'id'          => 'sld_enable_multipage',
		'type'        => 'on-off',
		'desc'        => __(''),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'multipage'
		
	),
	array(
		'label'       => __('SLD Multipage'),
		'id'          => 'sld_text_block_multipage',
		'type'        => 'Textblock',
		'desc'        => __('Multipage Shortcode: [qcopd-directory-multipage]<br><br>Please use [qcopd-directory-multipage] shortcode for sld multipage.<br><br>After adding this shortcode on a page you have to flush WordPress URL rewrite rules. Please go to <b>Settings > Permalinks</b> then click the <b>Save Changes</b> button Or you can click the button below to flush rewrite rules quickly.<br>For multi page directory we suggest uploading custom images. Recommended size 300px by 300px. Multi page mode supports one default template only.<div id="sld_flash_msg"></div><br><button id="sld_flash_button" class="option-tree-ui-button button button-primary">Flush Rewrite Rules</button> '),
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'multipage',
		'condition' => 'sld_enable_multipage:is(on)',
	),
	array(
		'label'       => __('Open All Link Details Pages in the Same Window'),
		'id'          => 'sld_multi_same_window',
		'type'        => 'on-off',
		'desc'        => __('Turn on to link open in same window Or it will take the settings from individual list item.'),
		'std'         => 'on',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'multipage'
	),
	array(
		'label'       => __('Show Alexa Ranking'),
		'id'          => 'sld_show_alexa_rank',
		'type'        => 'on-off',
		'desc'        => __('Show alexa rank in item landing page'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'multipage'
	),
	
	
	array(
		'label'       => __('Help'),
		'id'          => 'aid',
		'type'        => 'Textblock',
		'desc'        => '<div>
								
								<h3>Shortcode Generator</h3>
							<p>
We encourage you to use the ShortCode generator found in the toolbar of your page/post editor in visual mode. See sample below for where to find it</p><img src="'.QCOPD_IMG_URL.'/shortcode-generator.jpg" alt="shortcode generator" />
		
								<h3>Shortcode for frontend submission</3>
								<p>
									This feature will allow your users to submit their links to your lists from website front end. To achieve this you have to create 4 different pages and paste the following short code in each page.
								</p>
								<h3>Shortcode for frontend submission</3>
								<p>
									Please make sure that you have installed and activated sld plugin before adding these shortcodes.
									<br>
									<strong><u>For Login Page:</u></strong>
									<br>
									[sld_login]
									<br>
									Login From will appear when you add this shortcode on a page.
									<br>
									<br>
									<strong><u>For Registration Page:</u></strong>
									<br>
									[sld_registration]
									<br>
									Registration From will appear when you add this shortcode on a page.
									<br>
									<br>
									<strong><u>For Dashboard:</u></strong>
									<br>
									[sld_dashboard]
									<br>
									Dashboard (where people can manage there list items) will appear when you add this shortcode.
									<br>
									<br>
									<strong><u>For Restore SLD User Password:</u></strong>
									<br>
									[sld_restore]
									<br>
									User will get password reset option when you add this shortcode on a page. 
									<br>
								</p>
								<h3>Shortcode for Multipage</3>
									<p>Please make sure that you have installed and activated sld plugin before adding these shortcodes.<br><br>
										<strong><u>For Multipage:</u></strong>
										<br>
										[qcopd-directory-multipage]
										<br>
									</p>
									<strong>Available Parameters</strong>
									<p>
										<strong>1. orderby</strong>
										<br>
										Compatible order by values: "ID", "author", "title", "name", "type", "date", "modified", "rand" and "menu_order".
									</p>
									
									<p>
										<strong>2. order</strong>
										<br>
										Value for this option can be set as "ASC" for Ascending or "DESC" for Descending order.
										<br>
										
										<strong>For List Ordering to work, either specify orderby="menu_order" order="ASC in the short code or leave these empty.</strong>
										
									</p>
									<p>
										<strong>3. item_orderby</strong>
										<br>
										Values: "upvotes", "title", "random". You can order/sort list items by upvote counts or by their titles.
										<br>
										Example: item_orderby="upvotes"
									</p>
								<h3>Shortcode Example</h3>
								<p>
									<strong><u>For all the lists:</u></strong>
									<br>
									[qcopd-directory mode="all" style="simple" column="2" search="true" category="" upvote="on" item_count="on" orderby="date" order="DESC" item_orderby="title"]
									<br>
									<br>
									<strong><u>For only a single list:</u></strong>
									<br>
									[qcopd-directory mode="one" list_id="75"]
									<br>
									<br>
									<strong><u>For Category Tab</u></strong>
									<br>
									[sld-tab mode="categorytab" style="simple" column="2" search="true" category="" upvote="on" item_count="on" orderby="date" order="DESC" item_orderby="title"]
									<br>
									<br>
									<strong><u>Available Parameters:</u></strong>
									<br>
									
								</p>
								
								<p>
									<strong>1. mode</strong>
									<br>
									Value for this option can be set as "one" or "all".
									<br>
									<p>
									If you set mode="one", then filter option will not appear.
									</p>
								</p>
								<p>
									<strong>2. column</strong>
									<br>
									Avaialble values: "1", "2", "3" or "4".
								</p>
								<p>
									<strong>3. style</strong>
									<br>
									Avaialble values: "simple", "style-1", "style-2", "style-3", "style-4", "style-5", "style-6", "style-7", "style-8", "style-9", "style-10", "style-11", "style-12", "style-13", "style-14".
									<br>
									<br>
									To get details idea about how different style templates will look, please see the [Demo Images] tab from the left side.
								</p>
								<p>
									<strong>4. orderby</strong>
									<br>
									Compatible order by values: "ID", "author", "title", "name", "type", "date", "modified", "rand" and "menu_order".
								</p>
								<p>
									<strong>5. order</strong>
									<br>
									Value for this option can be set as "ASC" for Ascending or "DESC" for Descending order.
									<br>
									<br>
									<strong>For List Ordering to work, either specify orderby="menu_order" order="ASC in the short code or leave these empty.</strong>
									<br>
									<br>
								</p>
								<p>
									<strong>6. list_id</strong>
									<br>
									Only applicable if you want to display a single list [not all]. You can provide specific list id here as a value. You can also get ready shortcode for a single list under "Manage List Items" menu.
								</p>
								<p>
									<strong>7. category</strong>
									<br>
									Supply the category slug of your specific directory category.
									<br>
									Example: category="designs"
								</p>
								<p>
									<strong>8. search</strong>
									<br>
									Values: true or false. If you want to display on-page search for items, then you can set this parameter to - true.
									<br>
									Example: search="true"
								</p>
								<p>
									<strong>9. upvote</strong>
									<br>
									Values: on or off. This options allows upvoting of your list items.
									<br>
									Example: upvote="on"
								</p>
								<p>
									<strong>10. item_count</strong>
									<br>
									Values: on or off. This options allows to display list items count just beside your list heading.
									<br>
									Example: item_count="on"
								</p>
								<p>
									<strong>11. top_area</strong>
									<br>
									Values: on or off. You can hide top area (search and link submit) from any individual templates if you require. This option is handy if you want to display multiple template in the same page.
									<br>
									Example: top_area="off"
								</p>
								<p>
									<strong>12. item_orderby</strong>
									<br>
									Values: "upvotes", "title". You can order/sort list items by upvote counts or by their titles.
									<br>
									Example: item_orderby="upvotes"
								</p>
								<p>
									<strong>13. mask_url</strong>
									<br>
									Values: "on", "off". This option will allow you to hide promotional/affliate links from the visitors. Visitors will not be able to see these type of links when they mouseover on the links, but upon clicking on these links - they will be redirected to the original/set affliate links.
									<br>
									Example: mask_url="on"
									<br>
									<strong><i>Please note that URL masking may hurt your SEO.</i></strong>
								</p>
								<p>
									<strong>14. paginate_items</strong>
									<br>
									Values: "true", "false". This option will allow you to paginate list items. It will break the list page wise.
									<br>
									Example: paginate_items="true"
									<br>
									[Only applicable for certain templates.]
								</p>
								<p>
									<strong>15. per_page</strong>
									<br>
									This option indicates the number of items per page. Default is "5". paginate_items="true" is required to find this parameter in action.
									<br>
									Example: per_page="5"
									<br>
									[Only applicable for certain templates.]
								</p>
								<p>
									<strong>16. tooltip</strong>
									<br>
									You can enable or disable tooltip by using this parameter. Accepted values are "true" and "false".
									<br>
									Example: tooltip="true"
									<br>
									[Only applicable for certain templates.]
								</p>
								<p>
									<strong>17. Filter Area</strong>
									<br>
									You can set the filter area fixed position using this below parameter.
									<br>
									Example: filter_area="fixed"
									<br>
									Available values: fixed, normal.
								</p>
								<p>
									<strong>18. Filter Area Top spacing</strong>
									<br>
									You can set Top Spacing for filter area using this below parameter.
									<br>
									Example: topspacing="50"
									<br>
									Available values: It could be any integer.
								</p>
								<p>
									<strong>19. Remove specific category from Category Tab</strong>
									<br>
									You can remove specific category from Category Tab using this below parameter.
									<br>
									Example: category_remove="50,51,52"
									<br>
									You can add multiple Category ID as coma(,) seperated value.
									
								</p>
							</div>',
		'std'         => '',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'help'
	),
    )
            )
          )
        )
      )
    );

  }

}

?>