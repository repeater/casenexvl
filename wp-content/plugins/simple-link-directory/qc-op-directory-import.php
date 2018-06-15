<?php

class Qcopd_BulkImport
{

    function __construct()
    {
        //Add a menu in admin panel to link Import Export
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    public $post_id;

    //Callback function for Import Export Menu
    function qcopd_info_menu()
    {
        add_submenu_page(
            'edit.php?post_type=sld',
            'Bulk Import',
            'Import/Export',
            'manage_options',
            'qcopd_bimport_page',
            array(
                $this,
                'qcopd_bimport_page_content'
            )
        );
    }

    function qcopd_bimport_page_content()
    {
        ?>
        <div class="wrap">

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-3">

                    <div id="post-body-content" style="padding: 50px;
    box-sizing: border-box;
    box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);
    background: #fff;">

                        <u>
                            <h1>Bulk Export/Import</h1>
                        </u>

                        <div>
                            
                            <p style="color: red; padding: 15px;">
								<strong>Please Note:</strong> The Export Import Feature is still in Beta. We have been testing the feature extensively and it works great. However, before performing any sort of Imports, it is strongly recommended that you take a full backup of your website database first. So that if something went wrong during the import, you can revert and no data is lost.
							</p>
							<hr>
							<p>
                                <strong>Sample CSV File:</strong>
                                <a href="<?php echo QCOPD_ASSETS_URL . '/file/sample-csv-file.csv'; ?>" target="_blank">
                                    Download
                                </a>
                            </p>

                            <p><strong>NOTES:</strong></p>

                            <p>
                                <ol>
                                    <li>Attached file should be a plain CSV file.</li>
                                    <li>File must be prepared as per provided sample CSV file or as per the exported CSV file.</li>
                                </ol>
                            </p>
                            
                        </div>
						<hr>
						<div style="padding: 15px; margin: 20px 0;" id="sld-export-container">

							<h3><u>Export to a CSV File</u></h3>

	                        <p>
	                        	<strong><u>Option Details:</u></strong>
	                        </p>
	                        <p>
	                        	Export button will create a downloadable CSV file with all of your existing SLD lists and its elements.
	                        </p>

							<a class="button-primary" href="<?php echo admin_url( 'admin-post.php?action=sldprint.csv' ); ?>">Export SLD Data</a>

                        </div>
						<hr>

                        <div style="padding: 15px; margin: 10px 0;">

                        <h3><u>Import from a CSV File</u></h3>

                        <p><strong><u>Importing in Another Website:</u></strong> Please note that uploaded images for list items &amp; categories will not be copied if you import the full CSV to another WordPress installation.</p>

                        <p>
                        	<strong><u>Option Details:</u></strong>
                        </p>
                        <p>
                        	In both of the below cases, attached CSV file must be identical as per the provided format or as per the exported format.
                        </p>
                        <p>
                        	<strong><u>Add New Items: </u></strong>
                        	This option will add new lists and its elements from the CSV file. No lists or its elements get's deleted or updated by this option. If there exist any lists with the same title as CSV lines, then duplicate lists will get created during import.
                        </p>
                        <p>
                        	<strong><u>Delete Existing Items then Add New Items: </u></strong>
                        	This option will first delete ALL the existing SLD lists and its elements [without attached images] from the database, then it attempts to import lists and elements from the attached CSV file. This option is suitable for editing list elements. If you follow this option for a single site, then all previously attached images will get relinked.
                        </p>

                        <!-- Handle CSV Upload -->

                        <?php

                        //Generate a 5 digit random number based on microtime
                        $randomNum = substr(sha1(mt_rand() . microtime()), mt_rand(0,35), 5);


                        /*******************************
                         * If Add New or Delete then Add New button was pressed
                         * then proceed for further processing
                         *******************************/
                        if( !empty($_POST) && isset($_POST['upload_csv']) || !empty($_POST) && isset($_POST['delete_upload_csv']) ) 
                        {

                        	//First check if the uploaded file is valid
                        	$valid = true;
                        	
                        	$allowedTypes = array(
                        			'application/vnd.ms-excel',
                        			'text/comma-separated-values', 
                        			'text/csv', 
                        			'application/csv', 
                        			'application/excel', 
                        			'application/vnd.msexcel', 
                        			'text/anytext',
                        			'application/octet-stream',
                        		);
							//echo $_FILES['csv_upload']['type'];exit;
                        	if( !in_array($_FILES['csv_upload']['type'], $allowedTypes) ){
                        		$valid = false;
                        	}

                        	if( ! $valid ){
                        		echo "Status: Invalid file type.";
                        	}
                            
                            //If the file is valid and delete button was pressed
                            if( $valid && !empty($_POST) && isset($_POST['delete_upload_csv']) )
                            {
                            	
                            	$allposts = get_posts( 'numberposts=-1&post_type=sld&post_status=any' );

								foreach( $allposts as $postinfo ) 
								{
								    delete_post_meta( $postinfo->ID, 'qcopd_list_conf' );
								    delete_post_meta( $postinfo->ID, 'qcopd_list_item01' );
								    delete_post_meta( $postinfo->ID, 'sld_add_block' );

								    wp_delete_post( $postinfo->ID, true );
								}

                            }

                            //If the file is valid and client is logged in
                            if ( $valid && function_exists('is_user_logged_in') && is_user_logged_in() ) 
							{

                                $tmpName = $_FILES['csv_upload']['tmp_name'];
								
								if( $tmpName != "" )
								{
								
									$file = fopen($tmpName, "r");
                                    $flag = true;
									
									//Reading file and building our array
									
									$baseData = array();

									$count = 0;

									$laps = 1;

									//Read fields from CSV file and dump in $baseData
									while(($data = fgetcsv($file)) !== FALSE) 
									{
										
										if ($flag) {
											$flag = false;
											continue;
										}
										
										$baseData[$data[0]][] = array(
											'list_id' => trim($data[0]),
											'list_title' => sanitize_text_field((trim($data[1]))),
											'qcopd_item_title' => sanitize_text_field((trim($data[2]))),
											'qcopd_item_link' => trim($data[3]),
											'qcopd_item_nofollow' => trim($data[4]),
											'qcopd_item_newtab' => trim($data[5]),
											'qcopd_item_subtitle' => sanitize_text_field((trim($data[6]))),
											'qcopd_fa_icon' => sanitize_text_field((trim($data[7]))),
											'qcopd_use_favicon' => trim($data[8]),
											'qcopd_item_img' => trim($data[9]),
											'qcopd_item_img_title' => trim($data[10]),
											'qcopd_item_img_link' => trim($data[11]),
											'qcopd_upvote_count' => trim($data[12]),
											'attached_terms' => trim($data[13]),
											'qcopd_entry_time' => date("Y-m-d H:i:s"),
											'qcopd_timelaps' => $laps,
											'list_border_color' => trim($data[14]),
											'list_bg_color' => trim($data[15]),
											'list_bg_color_hov' => trim($data[16]),
											'list_txt_color' => trim($data[17]),
											'list_txt_color_hov' => trim($data[18]),
											'list_subtxt_color' => trim($data[19]),
											'list_subtxt_color_hov' => trim($data[20]),
											'item_bdr_color' => trim($data[21]),
											'item_bdr_color_hov' => trim($data[22]),
											'list_title_color'	=> trim($data[23]),
											'filter_background_color'	=> trim($data[24]),
											'filter_text_color'	=> trim($data[25]),
											'add_block_text' => sanitize_text_field((trim($data[26]))),
											'menu_order' => trim($data[27]),
											'post_status' => trim($data[28]),
										);

										$count++;
										$laps++;

									}
									
									fclose($file);
									
									//Inserting Data from our built array
									
									$keyCounter = 0;
									$metaCounter = 0;
									
									global $wpdb;

									//Sort $baseData numerically
									ksort($baseData, SORT_NUMERIC);
									
									//Parse $baseData and insert in the database
									foreach( $baseData as $key => $data ){
									
										
										//Check menu order for current SLD post, set 0 if empty
										$menu_order_val = isset($data[0]['menu_order']) ? $data[0]['menu_order'] : 0;

										//Grab current LIST title
										$post_title = (isset($data[0]['list_title']) && $data[0]['list_title'] != "" ) ? $data[0]['list_title'] : '';

										//Grab current LIST status, set 'publish' if empty
										$post_status = (isset($data[0]['post_status']) && $data[0]['post_status'] != "" ) ? $data[0]['post_status'] : 'publish';

										//If $post_title is empty, then go for next iteration
										if( $post_title == '' ){
											continue;
										}

										//Build post array and insert as new POST
										$post_arr = array(
											'post_title' => trim($post_title),
											'post_status' => $post_status,
											'post_author' => get_current_user_id(),
											'post_type' => 'sld',
											'menu_order' => $menu_order_val,
										);

										wp_insert_post($post_arr);

										//Get the newest post ID, that we just inserted
										$newest_post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'sld' ORDER BY ID DESC LIMIT 1");

										$attachedTerms = '';

										$innerListCounter = 0;

										$configArray = array();
										$addBlockArray = array();

										//Add list meta fields. i.e. list items and configs
										foreach( $data as $k => $item ){

											if( $innerListCounter == 0 )
											{
												$attachedTerms = $item['attached_terms'];

												$configArray['list_border_color'] = $item['list_border_color'];
												$configArray['list_bg_color'] = $item['list_bg_color'];
												$configArray['list_bg_color_hov'] = $item['list_bg_color_hov'];
												$configArray['list_txt_color'] = $item['list_txt_color'];
												$configArray['list_txt_color_hov'] = $item['list_txt_color_hov'];
												$configArray['list_subtxt_color'] = $item['list_subtxt_color'];
												$configArray['list_subtxt_color_hov'] = $item['list_subtxt_color_hov'];
												$configArray['item_bdr_color'] = $item['item_bdr_color'];
												$configArray['item_bdr_color_hov'] = $item['item_bdr_color_hov'];

												$addBlockArray['add_block_text'] = $item['add_block_text'];

												add_post_meta(
													$newest_post_id, 
													'qcopd_list_conf', array(
														'list_border_color' => $item['list_border_color'],
														'list_bg_color' => $item['list_bg_color'],
														'list_bg_color_hov' => $item['list_bg_color_hov'],
														'list_txt_color' => $item['list_txt_color'],
														'list_txt_color_hov' => $item['list_txt_color_hov'],
														'list_subtxt_color' => $item['list_subtxt_color'],
														'list_subtxt_color_hov' => $item['list_subtxt_color_hov'],
														'item_bdr_color' => $item['item_bdr_color'],
														'item_bdr_color_hov' =>  $item['item_bdr_color_hov'],
														'list_title_color' =>  $item['list_title_color'],
														'filter_background_color' =>  $item['filter_background_color'],
														'filter_text_color' =>  $item['filter_text_color'],
													)
												);

												add_post_meta(
													$newest_post_id, 
													'sld_add_block', array(
														'add_block_text' =>  $item['add_block_text'],
													)
												);

												$innerListCounter++;
											}

											$attachment_id = "";
											
											$attachmentId = intval($item['qcopd_item_img']);
											
											if( $attachmentId != "" && wp_get_attachment_url( $attachmentId ) ){
											  $attachment_id = $attachmentId;
											}

											if( $attachmentId != '' ){
												$image = wp_get_attachment_metadata( $attachmentId );

												$imageTitle = isset( $image['file'] ) ? $image['file'] : '';

												if( $imageTitle != trim($item['qcopd_item_img_title']) ){
													$attachment_id = "";
												}
											}
											
											add_post_meta(
												$newest_post_id, 
												'qcopd_list_item01', array(
													'qcopd_item_title' => $item['qcopd_item_title'],
													'qcopd_item_link' => $item['qcopd_item_link'],
													'qcopd_item_subtitle' => $item['qcopd_item_subtitle'],
													'qcopd_item_nofollow' => $item['qcopd_item_nofollow'],
													'qcopd_item_newtab' => $item['qcopd_item_newtab'],
													'qcopd_fa_icon' => $item['qcopd_fa_icon'],
													'qcopd_use_favicon' => $item['qcopd_use_favicon'],
													'qcopd_item_img' => $attachment_id,
													'qcopd_upvote_count' => $item['qcopd_upvote_count'],
													'qcopd_entry_time' =>  $item['qcopd_entry_time'],
													'qcopd_timelaps' =>  $item['qcopd_timelaps'],
													'qcopd_item_img_link' =>  $item['qcopd_item_img_link'],
												)
											);
											
											$metaCounter++;
											
										} //end of inner-foreach
										
										$keyCounter++;

										//Relate terms, if exists
										if( $attachedTerms !== '' )
										{
											
											$termIds = array();

											$postTerms = explode(',', $attachedTerms);

											foreach ($postTerms as $term ) {

												$termId = intval(trim($term));

												if( term_exists($termId, 'sld_cat') )
												{
													array_push($termIds, $termId);
												}
											}

											wp_set_post_terms( $newest_post_id, $termIds, 'sld_cat' );

										}
									
									} //end of outer-foreach

									//Display iteration result
									if( $keyCounter > 0 && $metaCounter > 0 )
									{
										echo  '<div><span style="color: red; font-weight: bold;">RESULT:</span> <strong>'.$keyCounter.'</strong> entry with <strong>'.$metaCounter.'</strong> element(s) was made successfully.</div>';
									}
								
							    }
								else
								{
								   echo "Status: Please upload a valid CSV file.";
								}

                            }

                        } 
                        else 
                        {
							//echo "Attached file is invalid!";
                        }

                        ?>
                            
                            <p>
                                <strong>
                                    <?php echo __('Upload a CSV file here to Import: '); ?>
                                </strong>
                            </p>

                            <form name="uploadfile" id="uploadfile_form" method="POST" enctype="multipart/form-data" action="" accept-charset="utf-8">
                                
                                <?php wp_nonce_field('qcsld_import_nonce', 'qc-opd'); ?>

                                <p>
                                    <?php echo __('Select file to upload') ?>
                                    <input type="file" name="csv_upload" id="csv_upload" size="35" class="uploadfiles"/>
                                </p>
								<p style="color:red;">**CSV File & Characters must be saved with UTF-8 encoding**</p>
                                <p>
                                    <input class="button-primary sld-add-as-new" type="submit" name="upload_csv" id="" value="<?php echo __('Add New Items') ?>"/>

                                    <input class="button-primary delete-old" type="submit" name="delete_upload_csv" id="" value="<?php echo __('Delete Existing Items then Add New Items') ?>"/>
                                </p>
								

                            </form>

                        </div>

                        <div style="padding: 15px 10px; border: 1px solid #ccc; text-align: center; margin-top: 20px;">
                            Crafted By: <a href="http://www.quantumcloud.com" target="_blank">Web Design Company</a> -
                            QuantumCloud
                        </div>

                    </div>
                    <!-- /post-body-content -->

                </div>
                <!-- /post-body-->

            </div>
            <!-- /poststuff -->


        </div>
        <!-- /wrap -->

        <?php
    }
}

new Qcopd_BulkImport;

function qcsld_admin_importexport_enqueue($hook) 
{
	
	if ( 'sld_page_qcopd_bimport_page' != $hook ) {
        return;
    }

    wp_enqueue_script( 'sld-import-script', QCOPD_ASSETS_URL . '/admin/js/sld-admin-js.js' );
    wp_enqueue_style( 'sld-import-style', QCOPD_ASSETS_URL . '/admin/css/sld-admin-css.css' );
	
}

add_action( 'admin_enqueue_scripts', 'qcsld_admin_importexport_enqueue' );


function download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    /*header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");*/

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

function array2csv(array &$array)
{
   if (count($array) == 0) {
     return null;
   }

   ob_start();

   $df = fopen("php://output", 'w');

   $titles = array('List ID', 'List Title', 'Item Title', 'Link', 'No Follow', 'New Tab', 'Sub Title', 'FA Icon Class', 'Use Favicon', 'Attachment ID', 'Attachment Title', 'Direct/External Image Link', 'Upvotes', 'Terms', 'List Holder Color', 'Item Background Color', 'Item Background Color (Hover)', 'Item Text Color', 'Item Text Color (Hover)', 'Item Sub Text Color', 'Item Sub Text Color (Hover)', 'Item Border Color', 'Item Border Color (Hover)','List Title Color','Filter Button Background Color','Filter Button Text Color', 'Ad Content', 'List Order', 'Post Status');

   fputcsv($df, $titles);

   foreach ($array as $row) {
      fputcsv($df, $row);
   }

   fclose($df);

   return ob_get_clean();
}

add_action( 'admin_post_sldprint.csv', 'sld_export_print_csv' );

function sld_export_print_csv()
{
    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) )
        return;

    $args = array(
		'post_type' => 'sld',
		'posts_per_page' => -1,
		'orderby' => 'id',
		'order' => 'ASC',
	);

    //Build the array first
    $export_query = new WP_Query( $args );

	if ( $export_query->have_posts() ) 
	{

		$childArray = array();

		while ( $export_query->have_posts() ) 
		{
			$export_query->the_post();

			$post_title = get_the_title();

			$list_id = get_the_ID();

			$menu_order = get_post_field( 'menu_order', get_the_ID() );

			$post_status = get_post_status( get_the_ID() );

			$lists = get_post_meta( get_the_ID(), 'qcopd_list_item01' );

			$config = get_post_meta( get_the_ID(), 'qcopd_list_conf' );

			$addBlock = get_post_meta( get_the_ID(), 'sld_add_block' );

			$add_content = "";

			if( count($addBlock) > 0 )
			{
				$add_content = $addBlock[0]['add_block_text'];
			}
			
			$config_exists = false;

			if( count($config) > 0 )
			{
				$config_exists = true;
			}

			$terms = array();

			$terms = get_the_terms( get_the_ID(), 'sld_cat' );

			$termArray = array();
			$attachedTerms = '';

			if( $terms && count($terms) > 0 )
			{

				foreach ( $terms as $term ) 
				{
			        $termArray[] = $term->term_id;
			    }
			                         
			    $attachedTerms = join( ", ", $termArray );
			}

			if( count($lists) > 0 )
			{

				$innerListNumber = 1;

				foreach( $lists as $list )
				{
					$innerArray = array();

					$title = $list['qcopd_item_title'];

					$subtitle = $list['qcopd_item_subtitle'];

					$link = $list['qcopd_item_link'];

					$nofollow = ( isset($list['qcopd_item_nofollow']) && $list['qcopd_item_nofollow'] != '0' ) ? 1 : 0;
					$newtab = ( isset($list['qcopd_item_newtab']) && $list['qcopd_item_newtab'] != '0' ) ? 1 : 0;

					$faIconClass = (isset($list['qcopd_fa_icon']) && trim($list['qcopd_fa_icon']) != "") ? $list['qcopd_fa_icon'] : "";

					$useFavicon = (isset($list['qcopd_use_favicon']) && trim($list['qcopd_use_favicon']) != "0") ? 1 : 0;

					$setImageId = ( isset($list['qcopd_item_img'])  && $list['qcopd_item_img'] != "" ) ? trim($list['qcopd_item_img']) : '';

					$externalImageLink = ( isset($list['qcopd_item_img_link'])  && $list['qcopd_item_img_link'] != "" ) ? trim($list['qcopd_item_img_link']) : '';

					$upvotes = ( isset($list['qcopd_upvote_count'])  && $list['qcopd_upvote_count'] != "" ) ? trim($list['qcopd_upvote_count']) : 0;

					$image = wp_get_attachment_metadata( $setImageId );

					$imageTitle = isset( $image['file'] ) ? $image['file'] : '';

					$innerArray[0] = trim($list_id);
					$innerArray[1] = trim($post_title);
					$innerArray[2] = $title;
					$innerArray[3] = $link;
					$innerArray[4] = $nofollow;
					$innerArray[5] = $newtab;
					$innerArray[6] = $subtitle;
					$innerArray[7] = $faIconClass;
					$innerArray[8] = $useFavicon;
					$innerArray[9] = $setImageId;
					$innerArray[10] = $imageTitle;
					$innerArray[11] = $externalImageLink;
					$innerArray[12] = $upvotes;
					$innerArray[13] = $attachedTerms;

					$innerArray[14] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_border_color'] : "";
					$innerArray[15] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_bg_color'] : "";
					$innerArray[16] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_bg_color_hov'] : "";
					$innerArray[17] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_txt_color'] : "";
					$innerArray[18] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_txt_color_hov'] : "";
					$innerArray[19] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_subtxt_color'] : "";
					$innerArray[20] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_subtxt_color_hov'] : "";
					$innerArray[21] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['item_bdr_color'] : "";
					$innerArray[22] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['item_bdr_color_hov'] : "";
					$innerArray[23] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['list_title_color'] : "";
					$innerArray[24] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['filter_background_color'] : "";
					$innerArray[25] = ( $config_exists && $innerListNumber == 1 ) ? $config[0]['filter_text_color'] : "";

					$innerArray[26] = ( $innerListNumber == 1 ) ? $add_content : "";

					$innerArray[27] = ( isset($menu_order) && $menu_order != '' ) ? $menu_order : 0;

					$final_post_status = ( isset($post_status) && $post_status != '' ) ? $post_status : 'publish';

					$innerArray[28] = ( $innerListNumber == 1 ) ? $final_post_status : '';

					array_push($childArray, $innerArray);

					$innerListNumber++;
				}
			}

		}


		wp_reset_postdata();
	}

	/*echo '<pre>';
		print_r( $childArray );
	echo '</pre>';*/

	download_send_headers("sld_lists_" . date("Y-m-d") . ".csv");

	$result = array2csv($childArray);

	print $result;

}
