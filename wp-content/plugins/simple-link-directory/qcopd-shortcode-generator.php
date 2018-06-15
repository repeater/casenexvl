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

function qcsld_load_custom_wp_admin_style() {
        wp_register_style( 'sld_shortcode_gerator_css', QCOPD_ASSETS_URL . '/css/shortcode-modal.css', false, '1.0.0' );
        wp_enqueue_style( 'sld_shortcode_gerator_css' );
}
add_action( 'admin_enqueue_scripts', 'qcsld_load_custom_wp_admin_style' );

function qcsld_render_shortcode_modal() {

	?>

	<div id="sm-modal" class="sld_modal">

		<!-- Modal content -->
		<div class="modal-content">
		
			<span class="close">
				<span class="dashicons dashicons-no"></span>
			</span>
			<h3> 
				<?php _e( 'SLD - Shortcode Generator' , 'qc-opd' ); ?></h3>
			<hr/>
<style type="text/css">
    .hero_tab {
        overflow: hidden;
        border: 1px solid #ccc;
        background-color: #f1f1f1;
    }

    /* Style the buttons inside the tab */
    .hero_tab button {
        background-color: inherit;
        float: left;
        border: none;
        outline: none;
        cursor: pointer;
        padding: 14px 16px;
        transition: 0.3s;
        font-size: 17px;
    }
    /* Change background color of buttons on hover */
    .hero_tab button:hover {
        background-color: #ddd;
    }

    /* Create an active/current tablink class */
    .hero_tab button.hero_active {
        background-color: #ccc;
    }
    /* Style the tab content */
    .hero_tabcontent {
        display: none;
        padding: 6px 12px;

        border-top: none;
        width: 704px;
    }
    #hero_general{display:block}
</style>

<div class="hero_tab">
    <button class="hero_tablinks hero_active" onclick="openCity(event, 'hero_general')">General</button>
    <button class="hero_tablinks" onclick="openCity(event, 'hero_settings')">Display Settings</button>

</div>
        <div id="hero_general" class="hero_tabcontent" style="padding: 6px 12px;">
			<div class="sm_shortcode_list">

				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Mode
					</label>
					<select style="width: 225px;" id="sld_mode">
						<option value="all">All List</option>
						<option value="one">One List</option>
						<option value="category">List Category</option>
                        <option value="categorytab">Category Tab</option>
					</select>
				</div>

                <div class="qcsld_single_field_shortcode hidden-div" id="sld_cat_orderby">
                    <label style="width: 200px;display: inline-block;">
                        Category Order By
                    </label>
                    <select style="width: 225px;" id="sld_category_orderby">
                        <option value="date">Date</option>
                        <option value="ID">ID</option>
                        <option value="title">Title</option>
                        <option value="modified">Date Modified</option>
                        <option value="rand">Random</option>
                        <option value="menu_order">Menu Order</option>
                    </select>
                </div>

                <div class="qcsld_single_field_shortcode hidden-div" id="sld_cat_order">
                    <label style="width: 200px;display: inline-block;">
                        Category Order
                    </label>
                    <select style="width: 225px;" id="sld_category_order">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
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
						<option value="">Select Style</option>
						<option value="simple">Default Style</option>
						<option value="style-1">Style 01</option>
						<option value="style-2">Style 02</option>
						<option value="style-3">Style 03</option>
						<option value="style-4">Style 04</option>
						<option value="style-5">Style 05</option>
						<option value="style-6">Style 06</option>
						<option value="style-7">Style 07</option>
						<option value="style-8">Style 08</option>
						<option value="style-9">Style 09</option>
						<option value="style-10">Style 10</option>
						<option value="style-11">Style 11</option>
						<option value="style-12">Style 12</option>
						<option value="style-13">Style 13</option>
						<option value="style-14">Style 14</option>
					</select>
					
					<div id="demo-preview-link">
						<div id="demo-url">
						</div>
					</div>
					
				</div>
				
				<div id="sld_infinity_scroll" class="qcsld_single_field_shortcode" style="display:none;">
					<label style="width: 200px;display: inline-block;">
						Infinity Scroll
					</label>
					<input id="infinityscroll" name="ckbox" value="1" type="checkbox">
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
                        Item Order By
                    </label>
                    <select style="width: 225px;" id="sld_item_orderby">

                        <option value="">None</option>
                        <option value="upvotes">Upvotes</option>
                        <option value="clicks">Clicks</option>
                        <option value="title">Title</option>
                        <option value="timestamp">Date Modified</option>
                        <option value="random">Random</option>

                    </select>
                </div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_orderby">
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
				
				<div class="qcsld_single_field_shortcode" id="sld_con_order">
					<label style="width: 200px;display: inline-block;">
						Order
					</label>
					<select style="width: 225px;" id="sld_order">
						<option value="ASC">Ascending</option>
						<option value="DESC">Descending</option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_orderby">
					<label style="width: 200px;display: inline-block;">
						Filter Button Order By
					</label>
					<select style="width: 225px;" id="sld_filter_orderby">
						<option value="date">Date</option>
						<option value="ID">ID</option>
						<option value="title">Title</option>
						<option value="modified">Date Modified</option>
						<option value="rand">Random</option>
						<option value="menu_order">Menu Order</option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_order">
					<label style="width: 200px;display: inline-block;">
						Filter Button Order
					</label>
					<select style="width: 225px;" id="sld_filter_order">
						<option value="ASC">Ascending</option>
						<option value="DESC">Descending</option>
					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode" id="sld_con_favorite">
					<label style="width: 200px;display: inline-block;">
						Favorite
					</label>
					<select style="width: 225px;" id="sld_favorite">
						<option value="disable">Disable</option>
						<option value="enable">Enable</option>

					</select>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_left_filter" name="ckbox" value="true" type="checkbox">
						Enable Left Filter
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_search" name="ckbox" value="true" type="checkbox">
						Search
					</label>
				</div>
				
				<!--<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_statistics" name="ckbox" value="true" type="checkbox">
						Statistics
					</label>
				</div>-->
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_upvote" name="ckbox" value="on" type="checkbox">
						Upvote
					</label>
				</div>
				
				<div class="qcsld_single_field_shortcode checkbox-sld">
					<label>
						<input class="sld_item_count" name="ckbox" value="on" type="checkbox">
						Item Count
					</label>
				</div>

				<div class="qcsld_single_field_shortcode checkbox-sld sld-off-field pg-template">
					<label>
						<input class="sld_enable_pagination" name="ckbox" value="on" type="checkbox">
						Enable Pagination
					</label>
				</div>

				<div id="sld_column_div" class="qcsld_single_field_shortcode sld-off-field pg-enabled">
					<label style="width: 200px;display: inline-block;">
						Items Per Page
					</label>
					<input style="width: 225px;" id="sld_items_per_page" type="text" name="sld_items_per_page" class="sld_items_per_page" value="10">
				</div>

				<div class="qcsld_single_field_shortcode checkbox-sld tt-template">
					<label>
						<input class="sld_enable_tooltip" name="ckbox" value="on" type="checkbox">
						Enable Tooltip / Popup Texts
					</label>
				</div>
				

				
			</div>
		</div>

        <div id="hero_settings" class="hero_tabcontent" style="padding: 6px 12px;">
            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    Filter Area
                </label>
                <select style="width: 225px;" id="sld_filter_area">
                    <option value="normal">Normal</option>
                    <option value="fixed">Fixed</option>

                </select>
            </div>
            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    Filter Area Top Spacing
                </label>
                <input type="text" style="width: 225px;" id="sld_topspacing" placeholder="Ex: 50" />
            </div>

            <div class="qcsld_single_field_shortcode">
                <label style="width: 200px;display: inline-block;">
                    List Title Font Size
                </label>
                <select style="width: 225px;" id="sld_list_title_font_size">
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
                    List Title Line Height
                </label>
                <select style="width: 225px;" id="sld_list_title_line_height">
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
                    Item Title Font Size
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
                    Item Subtitle Font Size
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
                    Item Title Line Height
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
                    Item Subtitle Line Height
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
        </div>

        <div class="qcsld_single_field_shortcode">
            <label style="width: 200px;display: inline-block;">
            </label>
            <input class="sld-sc-btn" type="button" id="qcsld_add_shortcode" value="Add Shortcode" />
        </div>

        <script type="text/javascript">
            function openCity(evt, cityName) {
                var i, tabcontent, tablinks;
                tabcontent = document.getElementsByClassName("hero_tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }
                tablinks = document.getElementsByClassName("hero_tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" hero_active", "");
                }
                document.getElementById(cityName).style.display = "block";
                evt.currentTarget.className += " hero_active";
            }

        </script>

		</div>

	</div>
	<?php
	exit;
}

add_action( 'wp_ajax_show_qcsld_shortcodes', 'qcsld_render_shortcode_modal');



function qcsld_render_upvote_reset_modal() {

	?>

	<div id="sm-modal" class="sld_modal">

		<!-- Modal content -->
		<div class="modal-content" style="top: 25%;">
		
			<span class="close">
				<span class="dashicons dashicons-no"></span>
			</span>
			<h3> 
				<?php _e( 'SLD Reset Upvotes' , 'qc-opd' ); ?></h3>
			<hr/>



			<div class="sm_shortcode_list">

				<div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
						Select List
					</label>
					<select style="width: 225px;" id="sld_list">
						<option value="all">All List</option>
						<?php 
						$list_args_total = array(
							'post_type' => 'sld',
							'posts_per_page' => -1,
						);
						$list_query = new WP_Query( $list_args_total );
						while ( $list_query->have_posts() )
						{
							$list_query->the_post();
							echo '<option value="'.get_the_ID().'">'.get_the_title().'</option>';
						}
						?>
					</select>
				</div>
				<div class="sld_reset_child_item">

				</div>
                <div class="qcsld_single_field_shortcode">
					<label style="width: 200px;display: inline-block;">
					</label>
					<input class="sld-sc-btn" type="button" id="sld_reset_votes" value="Reset Upvotes" />
				</div>
				
			</div>
			
		</div>

	</div>
	<?php
	exit;
}

add_action( 'wp_ajax_show_qcsld_upvote_reset', 'qcsld_render_upvote_reset_modal');