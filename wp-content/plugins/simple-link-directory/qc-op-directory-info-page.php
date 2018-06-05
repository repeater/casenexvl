<?php

class Qcopd_info_page {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'qcopd_info_menu' ) );
	}

	function qcopd_info_menu() {
		add_submenu_page(
			'edit.php?post_type=sld',
			'Directory Settings',
			'Settings',
			'manage_options',
			'qcopd_info_page',
			array(
				$this,
				'qcopd_info_page_content'
			)
		);
	}

	function  qcopd_info_page_content() {
		?>
		<div class="wrap">
		
			<div id="poststuff">
			
				<div id="post-body" class="metabox-holder columns-2">
				
					<div id="post-body-content" style="position: relative;">
				
						<div>
							<img style="width: 200px;" src="<?php echo QCOPD_IMG_URL; ?>/simple-link-directory.png" alt="Simple Link Directory">
						</div>
						
						<div class="clear">
							<?php do_action('buypro_promotional_link'); ?>
						</div>
						
						<div class="clear">
						<u>
							<h1>Settings</h1>
						</u>
						</div>
						
						<div>
							<p>
								<strong style="color: red;">This version of the plugin does not have any specific global setting. For more options and flexibilities, consider purchasing the <a href="https://www.quantumcloud.com/simple-link-directory/" target="_blank">premium version</a>.</strong>
							</p>
						</div>
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
								Compatible order by values: 'ID', 'author', 'title', 'name', 'type', 'date', 'modified', 'rand' and 'menu_order'.
							</p>
							<p>
								<strong>5. order</strong>
								<br>
								Value for this option can be set as "ASC" for Ascending or "DESC" for Descending order.
							</p>
							<p>
								<strong>6. item_orderby</strong>
								<br>
								Value for this option is only "title" that will be set as "ASC" order.
							</p>
							<p>
								<strong>7. list_id</strong>
								<br>
								Only applicable if you want to display a single list [not all]. You can provide specific list id here as a value. You can also get ready shortcode for a single list under "Manage List Items" menu.
							</p>
							<p>
								<strong>8. category</strong>
								<br>
								Supply the category slug of your specific directory category.
								<br>
								Example: category="designs"
							</p>
							<p>
								<strong>9. enable_embedding</strong>
								<br>
								Allow visitors to embed list in other sites. Supported values - "true", "false".
								<br>
								Example: enable_embedding="true"
							</p>
						</div>

						<div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;">
							 Crafted By: <a href="http://www.quantumcloud.com" target="_blank">Web Design Company</a> - QuantumCloud 
						</div>
						
					  </div>
					  <!-- /post-body-content -->	
					  
					  <div id="postbox-container-1" id="postbox-container">
						<!-- Plugin Logo -->
						<div style="border: 1px solid #ccc; padding: 10px 0; text-align: center;">
							<img style="width: 130px; max-width: 100%;" src="<?php echo QCOPD_IMG_URL; ?>/simple-link-directory.png" alt="Simple Link Directory">
						</div>
						
						<!-- Promo Block 1 -->
						<div style="margin-top: 20px;">
							<?php
							$size = getimagesize('https://www.quantumcloud.com/wp/link-existency-checker.png');
							if( isset( $size[0] ) && $size[0] == 200 ) :
							?>
								<iframe style="min-height: 400px;" src="https://www.quantumcloud.com/wp/plugins/sidebar-rt/index.php" frameborder="0"></iframe>
							<?php endif; ?>
						</div>
						
					  </div>
						
					</div>
					<!-- /post-body-->	
				
				</div>
				<!-- /poststuff -->
			
			</div>
			<!-- /wrap -->
			
		<?php
	}
}

new Qcopd_info_page;