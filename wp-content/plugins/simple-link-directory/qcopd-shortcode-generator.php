<?php

/*TinyMCE Shortcode Generator Button - 25-01-2017*/

function qcsld_tinymce_shortcode_button_function() {
	add_filter ("mce_external_plugins", "qcsld_shortcode_generator_btn_js");
	add_filter ("mce_buttons", "qcsld_shortcode_generator_btn");
}

function qcsld_shortcode_generator_btn_js($plugin_array) {
	$plugin_array['qcsld_shortcode_btn'] = plugins_url('assets/js/qcsld-tinymce-button.js', __FILE__);
	return $plugin_array;
}

function qcsld_shortcode_generator_btn($buttons) {
	array_push ($buttons, 'qcsld_shortcode_btn');
	return $buttons;
}

add_action ('init', 'qcsld_tinymce_shortcode_button_function');

function qcsld_load_custom_wp_admin_style_free() {
        wp_register_style( 'sld_shortcode_gerator_css', QCOPD_ASSETS_URL . '/css/shortcode-modal.css', false, '1.0.0' );
        wp_enqueue_style( 'sld_shortcode_gerator_css' );
}
add_action( 'admin_enqueue_scripts', 'qcsld_load_custom_wp_admin_style_free' );

function qcsld_render_shortcode_modal_free() {

	?>

	<div id="sm-modal" class="modal">

		<!-- Modal content -->
		<div class="modal-content">
		
			<span class="close">
				<span class="dashicons dashicons-no"></span>
			</span>
			<h3> 
				<?php _e( 'SLD - Shortcode Generator' , 'qc-opd' ); ?></h3>
			<hr/>
			
			<div class="sm_shortcode_list">

				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Mode
					</label>
					<select style="width: 225px;" id="sld_mode">
						<option value="all">All List</option>
						<option value="one">One List</option>

					</select>
				</div>
				
				<div id="sld_list_div" class="qcsld_single_field_shortcode hidden-div">
					<label style="width: 200px;display: inline-block;">
						Select List 
					</label>
					<select style="width: 225px;" id="sld_list_id">
					
						<option value="">Please Select List</option>
						
						<?php
						
							$ilist = new WP_Query( array( 'post_type' => 'sld', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC') );
							if( $ilist->have_posts()){
								while( $ilist->have_posts() ){
									$ilist->the_post();
						?>
						
						<option value="<?php echo get_the_ID(); ?>"><?php echo get_the_title(); ?></option>
						
						<?php } } ?>
						
					</select>
				</div>
				
				<div id="sld_list_cat" class="qcsld_single_field_shortcode hidden-div">
					<label style="width: 200px;display: inline-block;">
						List Category
					</label>
					<select style="width: 225px;" id="sld_list_cat_id">
					
						<option value="">Please Select Category</option>
						
						<?php
						
							$terms = get_terms( 'sld_cat', array(
								'hide_empty' => true,
							) );
							if( $terms ){
								foreach( $terms as $term ){
						?>
						
						<option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
						
						<?php } } ?>
						
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Template Style
					</label>
					<select style="width: 225px;" id="sld_style">
						<option value="simple">Default Style</option>
						<option value="style-1">Style 01</option>
						<option value="style-2">Style 02</option>
						<option value="style-3">Style 03</option>
						<option value="style-4">Style 04</option>
						<option value="style-5">Style 05</option>
					</select>
					
					<div id="demo-preview-link">
						Demo URL: 
						<div id="demo-url">
							<a href="http://dev.quantumcloud.com/sld/" target="_blank">http://dev.quantumcloud.com/sld/</a>
						</div>
					</div>
					
				</div>
				
				<div id="sld_column_div" class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Column
					</label>
					<select style="width: 225px;" id="sld_column">
						<option value="1">Column 1</option>
						<option value="2">Column 2</option>
						<option value="3">Column 3</option>
						<option value="4">Column 4</option>
					</select>
				</div>
                <div class="qcsld_single_field_shortcode">
                    <label style="width: 200px;display: inline-block;">
                        Title Font Size
                    </label>
                    <select style="width: 225px;" id="sld_title_font_size">
                        <option value="">Default</option>
						<?php
						for($i=10;$i<50;$i++){
							echo '<option value="'.$i.'px">'.$i.'px</option>';
						}
						?>
                    </select>
                </div>

                <div class="qcsld_single_field_shortcode">
                    <label style="width: 200px;display: inline-block;">
                        Title Line Height
                    </label>
                    <select style="width: 225px;" id="sld_title_line_height">
                        <option value="">Default</option>
						<?php
						for($i=10;$i<50;$i++){
							echo '<option value="'.$i.'px">'.$i.'px</option>';
						}
						?>
                    </select>
                </div>

                <div class="qcsld_single_field_shortcode">
                    <label style="width: 200px;display: inline-block;">
                        Subtitle Font Size
                    </label>
                    <select style="width: 225px;" id="sld_subtitle_font_size">
                        <option value="">Default</option>
						<?php
						for($i=10;$i<50;$i++){
							echo '<option value="'.$i.'px">'.$i.'px</option>';
						}
						?>
                    </select>
                </div>



                <div class="qcsld_single_field_shortcode">
                    <label style="width: 200px;display: inline-block;">
                        Subtitle Line Height
                    </label>
                    <select style="width: 225px;" id="sld_subtitle_line_height">
                        <option value="">Default</option>
						<?php
						for($i=10;$i<50;$i++){
							echo '<option value="'.$i.'px">'.$i.'px</option>';
						}
						?>
                    </select>
                </div>
				<div id="sld_orderby_div" class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Order By
					</label>
					<select style="width: 225px;" id="sld_orderby">
						<option value="date">Date</option>
						<option value="ID">ID</option>
						<option value="title">Title</option>
						<option value="modified">Date Modified</option>
						<option value="rand">Random</option>
						<option value="menu_order">Menu Order</option>
					</select>
				</div>
				
				<div id="sld_order_div" class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Order
					</label>
					<select style="width: 225px;" id="sld_order">
						<option value="ASC">Ascending</option>
						<option value="DESC">Descending</option>
					</select>
				</div>
				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Item Orderby
					</label>
					<select style="width: 225px;" id="sld_itemorderby">
						<option value="">None</option>
						<option value="title">Title</option>
						<option value="upvotes">Upvotes</option>
						<option value="timestamp">Date Modified</option>
					</select>
				</div>
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_embeding" name="ckbox" value="true" type="checkbox">
						Enable Embeding
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
					</label>
					<input class="sld-sc-btn" type="button" id="qcsld_add_shortcode" value="Add Shortcode" />
				</div>
				
			</div>
		</div>

	</div>
	<?php
	exit;
}

add_action( 'wp_ajax_show_qcsld_shortcodes', 'qcsld_render_shortcode_modal_free');
