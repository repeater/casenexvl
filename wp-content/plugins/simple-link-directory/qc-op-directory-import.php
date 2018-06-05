<?php

class Qcopd_BulkImportFree
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'qcopd_info_menu'));
    }

    public $post_id;

    function qcopd_info_menu()
    {
        add_submenu_page(
            'edit.php?post_type=sld',
            'Bulk Import',
            'Import',
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

                    <div id="post-body-content" style="position: relative;">

                        <u>
                            <h1>Bulk Import</h1>
                        </u>

                        <div>
                            
                            <p>
								<strong>Please Note:</strong> The import feature is still under development. Right now it only allows importing and creating new Lists. Existing Lists will not get updated. Also, export feature is not available in free version.
							</p>
							
							<p>
                                <strong>Sample CSV File:</strong>
                                <a href="<?php echo QCOPD_ASSETS_URL . '/file/sample-csv-file.csv'; ?>" target="_blank">
                                    Download
                                </a>
                            </p>

                            <p><strong>PROCESS:</strong></p>

                            <p>
                                <ol>
                                    <li>First download the above CSV file.</li>
                                    <li>Add/Edit rows on the top of it, by maintaing proper provided format/fields.</li>
                                    <li>Finally, upload file in the below form.</li>
                                </ol>
                            </p>



                            <p><strong>NOTES:</strong></p>

                            <p>
                                <ol>
                                    <li>It should be a simple CSV file.</li>
                                    <li>File encoding should be in UTF-8</li>
                                    <li>File must be prepared as per provided sample CSV file.</li>
                                </ol>
                            </p>
                            
                        </div>

                        <div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">

                        <!-- Handle CSV Upload -->

                        <?php

                        $randomNum = substr(sha1(mt_rand() . microtime()), mt_rand(0,35), 5);

                        if( !empty($_POST) && isset($_POST['upload_csv']) ) 
                        {

                            if ( function_exists('is_user_logged_in') && is_user_logged_in() ) 
							{

                                $tmpName = $_FILES['csv_upload']['tmp_name'];
                                $file = fopen($tmpName, "r");
                                $flag = true;
								
								//Reading file and building our array
								
								$baseData = array();

                                $count = 0;

                                while(($data = fgetcsv($file)) !== FALSE) 
                                {
                                    if ($flag) {
                                        $flag = false;
                                        continue;
                                    }
									
									$baseData[$data[0]][] = array(
                                        'list_title' => sanitize_text_field(utf8_encode(trim($data[0]))),
                                        'qcopd_item_title' => sanitize_text_field(utf8_encode(trim($data[1]))),
                                        'qcopd_item_link' => sanitize_text_field(utf8_encode(trim($data[2]))),
                                        'qcopd_item_img' => '',
                                        'qcopd_item_nofollow' => trim($data[3]),
                                        'qcopd_item_newtab' => trim($data[4]),
                                        'qcopd_item_subtitle' => trim($data[5])
                                    );

                                    $count++;

                                }
                                
                                fclose($file);
								
								//Inserting Data from our built array
								
								$keyCounter = 0;
								$metaCounter = 0;
								
								global $wpdb;
								
								foreach( $baseData as $key => $data ){
								
									$post_arr = array(
										'post_title' => trim($key),
										'post_status' => 'publish',
										'post_author' => get_current_user_id(),
										'post_type' => 'sld',
									);

									wp_insert_post($post_arr);

									$newest_post_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type = 'sld' ORDER BY ID DESC LIMIT 1");

									foreach( $data as $k => $item ){										
										add_post_meta(
											$newest_post_id, 
											'qcopd_list_item01', array(
												'qcopd_item_title' => $item['qcopd_item_title'],
												'qcopd_item_link' => $item['qcopd_item_link'],
												'qcopd_item_img' => '',
												'qcopd_item_nofollow' => $item['qcopd_item_nofollow'],
												'qcopd_item_newtab' => $item['qcopd_item_newtab'],
												'qcopd_item_subtitle' => $item['qcopd_item_subtitle']
											)
										);
										
										$metaCounter++;
										
									} //end of inner-foreach
									
									$keyCounter++;
								
								} //end of outer-foreach

                                if( $keyCounter > 0 && $metaCounter > 0 )
								{
                                    echo  '<div><span style="color: red; font-weight: bold;">RESULT:</span> <strong>'.$keyCounter.'</strong> entry with <strong>'.$metaCounter.'</strong> element(s) was made successfully.</div>';
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
                                    <?php echo __('Upload csv file to import'); ?>
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
                                    <input class="button-primary" type="submit" name="upload_csv" id="" value="<?php echo __('Upload & Process') ?>"/>
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

new Qcopd_BulkImportFree;
