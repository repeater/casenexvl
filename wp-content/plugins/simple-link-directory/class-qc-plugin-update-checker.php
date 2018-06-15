<?php

/**************************************************************
 * QuantumCloud Plugin [Pro] Update Checker
 * Last Updated On: 06-12-2017 02:12 AM
 * Copyright: QuantumCloud [https://www.quantumcloud.com]
 **************************************************************/

//First, performing a "class_exists" check, to be in the safe side

if (!class_exists('QcPluginUpdateNotice')) {
    class QcPluginUpdateNotice
    {

        /* Public variables, these can be overrides using instance callback
         * Please do not EDIT these variables here, overrides - if require, using
         * appropriate instance of this class
         */

        public $remote_url = "https://www.quantumcloud.com/wp/plugins/sld.json";
        public $plugin_slug = "qc-simple-link-directory";
        public $remote_version = 0;
        public $current_version = 0;
        public $changelog = "";
        public $plugin_name = "Simple Link Directory - Premium";
        public $extra_message = "<br><br><em>Automatic update is unavailable for this plugin.</em>";
        public $plugin_primary_file = "";

        public $show_changelog = true;
        public $show_extra_message = true;

        //Contructor - Set defaults
        function __construct($current_version, $remote_url)
        {
            $this->current_version = $current_version;
            $this->remote_url = $remote_url;

            $this->check_remote_version();
        }

        /*******************************
         * Check if the current screen
         * is the plugins.php page or not
         *******************************/
        function check_if_plugin_page()
        {

            //Check if current page is plugins.php, otherwise return false
            global $pagenow;

            if (is_admin() && ($pagenow == 'plugins.php' || $pagenow == "update-core.php")) {
                return true;
            }

            return false;

        } //End of check_if_plugin_page

        /*******************************
         * Check for available remove version
         * and for changelog parameter in json
         *******************************/
        public function check_remote_version()
        {

            $url = $this->remote_url;

            //if code == 404, then stop. If code == 200, then ok
            //Replacing "file_get_contents" with "cURL" here

            $remote_response = $this->qc_file_get_contents_curl($url);

            if ($remote_response === false) {
                return $this->remote_version = 0;
            }

            $string = $remote_response;

            if ($string) {
                $json_a = json_decode($string, true);

                foreach ($json_a as $key => $value) {
                    //Set remove version
                    if ($key == 'version' && $value != "") {
                        $this->remote_version = $value;
                    }

                    //Grab remote changelog texts
                    if ($key == 'changelog' && $value != "") {
                        $this->changelog = $value;
                    }
                }

                return $this->remote_version;
            } else {
                return $this->remote_version = 0;
            }
        }

        /******************************
         * For fetching remote .json file contents
         * using cURL
         ******************************/
        public function qc_file_get_contents_curl($remote_url)
        {
            $url = $remote_url;

            //Check if cURL is enabled
            if (!function_exists('curl_version')) {
                return false;
            }

            //If cURL is enabled, then proceed
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $contents = curl_exec($ch);

            //Check return HTTP response code
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpcode == 200) {
                return $contents;
            } else {
                return false;
            }

            curl_close($ch);

        } //End of function qc_file_get_contents_curl


        /*******************************
         * Show update notice below the
         * plugin row.
         *******************************/
        function show_update_notice($ver = null)
        {

            add_filter('site_transient_update_plugins', array(&$this, 'qcupdater_plugin_transient_response'), 99);
            add_filter('transient_update_plugins', array(&$this, 'qcupdater_plugin_transient_response'), 99);

            add_action("after_plugin_row_" . $this->plugin_primary_file, array(&$this, 'get_the_update_message'), 10);

        } //End of function show_update_notice

        function qcupdater_plugin_transient_response($update_plugins)
        {

            if (!is_object($update_plugins))
                return $update_plugins;

            if (!isset($update_plugins->response) || !is_array($update_plugins->response))
                $update_plugins->response = array();


            if ($this->compare_version()) {
                $update_plugins->response[$this->plugin_primary_file] = (object)array(
                    'slug' => $this->plugin_slug,
                    'new_version' => $this->remote_version
                );
            }

            return $update_plugins;
        }

        function get_current_version()
        {

            return $this->current_version;

        } //End of functon get_current_version

        /*******************************
         * Compare remote and installed version
         *******************************/
        function compare_version()
        {
            $remote = str_ireplace(".", "", $this->remote_version);
            $current = str_ireplace(".", "", $this->current_version);

            if ((int)$remote > (int)$current) {
                return true;
            }

            return false;

        } // End of function compare_version

        /*******************************
         * Generate message to show
         *******************************/
        function get_the_update_message()
        {

            global $pagenow;

            if (!$this->check_if_plugin_page()) {
                return;
            }

            ?>
            <style>
                #<?php echo $this->plugin_slug . '-update'; ?>{
						display: none;
					}
            </style>
            <?php

            $message = "<p>A new version of the plugin <strong>" . $this->plugin_name . "</strong> is available to download. Your installed version is <strong>" . $this->current_version . "</strong>, but the most recent updated version is <strong>" . $this->remote_version . "</strong></p>";

            if ($this->changelog != "" && $this->show_changelog) {
                $message .= "<hr><strong><u>Changelog:</u></strong><br>" . $this->changelog;
            }

            if ($this->extra_message != "" && $this->show_extra_message) {
                $message .= $this->extra_message;
            }

            ?>
            <tr class="plugin-update-tr">
                <td colspan="3" class="plugin-update colspanchange">
                    <div class="update-message notice inline notice-warning notice-alt">
                        <?php _e($message, 'qc-sld'); ?>
                    </div>
                </td>
            </tr>
            <?php

        } //End of function get_the_update_message

    } //End of class QcPluginUpdateNotice

} // End of class_exists


/********************************************************************************
 * Create instance [please make sure your instance variable name is unique]
 * and call the appropriate worker/callback function
 ********************************************************************************/

/*

Example $remote_file_url [JSON File]: https://dev.quantumcloud.com/sld/updater/sld.json

Json File Format [Version and Changelog parameters are must requried]:
-----------------------------------------------------------------------

{"slug": "qc-simple-link-directory","name": "Simple Link Directory - Premium","version": "3.4.5","changelog": "*Updated templates, <br>*Updated entry system"}

*/

//Set remote JSON file URL, it is required



/* Constructor Parameter 1 : current_plugin_version, it should set as a global variable in the main file of the plugin

Constructor Parameter 2 : remote_json_file_url
*/

add_action( 'admin_init', 'qcsld_check_updateplugin' );
function qcsld_check_updateplugin(){
    global $sld_plugin_version;
	$remote_file_url = "https://www.quantumcloud.com/wp/plugins/sld.json";


	if (strpos($_SERVER['PHP_SELF'], 'plugins.php') !== false) {

		$instance_sld_pro = new QcPluginUpdateNotice($sld_plugin_version, $remote_file_url);

		if (is_admin() && $instance_sld_pro->check_if_plugin_page() && $instance_sld_pro->compare_version()) {

			$instance_sld_pro->plugin_primary_file = 'qc-simple-link-directory/qc-op-directory-main.php';

			$instance_sld_pro->plugin_slug = 'qc-simple-link-directory'; //exact folder name of the plugin
			$instance_sld_pro->plugin_name = 'Simple Link Directory - Premium'; //Exacxt plugin name

			//$instance_sld_pro->extra_message = ''; //Show extra texts, if any

			$instance_sld_pro->show_changelog = true; //set false to turn off
			$instance_sld_pro->show_extra_message = true; //set false to turn off

			$instance_sld_pro->show_update_notice();

		}
	}


}


