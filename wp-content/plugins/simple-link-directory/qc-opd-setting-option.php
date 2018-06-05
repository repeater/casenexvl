<?php



add_filter( 'ot_show_pages', '__return_false' );
add_filter( 'ot_show_new_layout', '__return_false' );


function qcld_sld_remove_ot_menu () {
    remove_submenu_page( 'themes.php', 'ot-theme-options' );
}
add_action( 'admin_init', 'qcld_sld_remove_ot_menu' );

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
          'id'              => 'option_tree',
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
		'label'       => __('Enable Top Area'),
		'id'          => 'sld_enable_top_part',
		'type'        => 'on-off',
		'desc'        => __('Top area includes Embed button (more options coming soon)'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	
	
	
	array(
		'label'       => __('Enable Upvote'),
		'id'          => 'sld_enable_upvote',
		'type'        => 'on-off',
		'desc'        => __('Turn ON to visible Upvote feature for all templates.'),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general'
	),
	
	array(
		'label'       => __('Enable Add New Button'),
		'id'          => 'sld_add_new_button',
		'type'        => 'on-off',
		'desc'        => __(''),
		'std'         => 'off',
		'rows'        => '',
		'post_type'   => '',
		'taxonomy'    => '',
		'class'       => '',
		'section'     => 'general',
		
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
		'section'     => 'general',
		'condition' => 'sld_add_new_button:is(on)'
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
	
	array(
		'label'       => __('Add New'),
		'id'          => 'sld_lan_add_link',
		'type'        => 'text',
		'desc'        => __('Change the language for Add New'),
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
		'label'       => __('Help'),
		'id'          => 'aid',
		'type'        => 'Textblock',
		'desc'        => '<div class="wrap">
		
			<div id="poststuff">
			
				<div id="post-body" class="metabox-holder columns-2">
				
					<div id="post-body-content" style="position: relative;">
				
						<div>
							<img style="width: 200px;" src="'.QCOPD_IMG_URL.'/simple-link-directory.png" alt="Simple Link Directory">
						</div>
						
						<div class="clear">
							<?php do_action(\'buypro_promotional_link\'); ?>
						</div>
						
						<h3>Shortcode Generator</h3>
							<p>
We encourage you to use the ShortCode generator found in the toolbar of your page/post editor in visual mode. See sample below for where to find it</p><img src="'.QCOPD_IMG_URL.'/shortcode-generator.jpg" alt="shortcode generator" />
						<div>
							<h3>Shortcode Example</h3>
							
							<p>
								<strong>You can use our given SHORTCODE GENERATOR to generate and insert shortcode easily, titled as "SLD" with WordPress content editor.</strong>
							</p>

							<p>
								<strong><u>For all the lists:</u></strong>
								<br>
								[qcopd-directory mode="all" column="2" style="simple" orderby="date" order="DESC" enable_embedding="false"]
								<br>
								<br>
								<strong><u>For only a single list:</u></strong>
								<br>
								[qcopd-directory mode="one" list_id="75"]
								<br>
								<br>
								<strong><u>Available Parameters:</u></strong>
								<br>
							</p>
							<p>
								<strong>1. mode</strong>
								<br>
								Value for this option can be set as "one" or "all".
							</p>
							<p>
								<strong>2. column</strong>
								<br>
								Avaialble values: "1", "2", "3" or "4".
							</p>
							<p>
								<strong>3. style</strong>
								<br>
								Avaialble values: "simple", "style-1", "style-2", "style-3".
								<br>
								<strong style="color: red;">
									Only 2 templates are available in the free version. For more styles or templates, please purchase the <a href="https://www.quantumcloud.com/simple-link-directory/" target="_blank">premium version</a>.
								</strong>
							</p>
							<p>
								<strong>4. orderby</strong>
								<br>
								Compatible order by values: \'ID\', \'author\', \'title\', \'name\', \'type\', \'date\', \'modified\', \'rand\' and \'menu_order\'.
							</p>
							<p>
								<strong>5. order</strong>
								<br>
								Value for this option can be set as "ASC" for Ascending or "DESC" for Descending order.
							</p>
							<p>
								<strong>6. item_orderby</strong>
								<br>
								Value for this option are "title", "upvotes", "timestamp" that will be set as "ASC" & others will be "DESC" order.
							</p>
							<p>
								<strong>7. list_id</strong>
								<br>
								Only applicable if you want to display a single list [not all]. You can provide specific list id here as a value. You can also get ready shortcode for a single list under "Manage List Items" menu.
							</p>
							
							<p>
								<strong>8. enable_embedding</strong>
								<br>
								Allow visitors to embed list in other sites. Supported values - "true", "false".
								<br>
								Example: enable_embedding="true"
							</p>
							<p>
								<strong>8. upvote</strong>
								<br>
								Allow visitors to list item. Supported values - "on", "off".
								<br>
								Example: upvote="on"
							</p>
						</div>

						<div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;">
							 Crafted By: <a href="http://www.quantumcloud.com" target="_blank">Web Design Company</a> - QuantumCloud 
						</div>
						
					  </div>
					  <!-- /post-body-content -->	
					  
					  

					</div>
					<!-- /post-body-->

				</div>
				<!-- /poststuff -->

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