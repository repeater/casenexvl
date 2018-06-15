<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class Sld_package {
	// class instance
	static $instance;

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'sld_custom_plugin_admin_menu' ) );

	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	public function sld_custom_plugin_admin_menu() {

		$hook = add_submenu_page(
			'edit.php?post_type=sld',
			'Manage Paid Package',
			'Manage Paid Package',
			'manage_options',
			'qcsld_package',
			array(
				$this,
				'qc_sld_plugin_settings_page'
			)
		);




	}
	public function qc_sld_plugin_settings_page(){
		global $wpdb;
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$table             = $wpdb->prefix.'sld_package';
		$current_user = wp_get_current_user();
		$msg = '';
		//echo $table;exit;

		//get form data
		if(isset($_POST['qc_sld_item_duration']) and $_POST['qc_sld_item_duration']!='' and isset($_POST['qc_sld_save'])){

			$title = sanitize_text_field($_POST['qc_sld_package_title']);
			$description = sanitize_text_field($_POST['qc_sld_package_desc']);
			$duration = sanitize_text_field($_POST['qc_sld_item_duration']);
			$sandbox = isset($_POST['qc_sld_test_mode'])?$_POST['qc_sld_test_mode']:0;
			$enable = isset($_POST['qc_sld_package_enable'])?$_POST['qc_sld_package_enable']:0;
			$currency = sanitize_text_field($_POST['qc_sld_currency']);
			$item = sanitize_text_field($_POST['qc_sld_item']);
			$amount = ($_POST['qc_sld_amount']);
			$email = sanitize_email($_POST['qc_sld_paypal']);
			$date = date('Y-m-d H:i:s');
			$recurring = $_POST['qc_sld_recurring'];
			if($duration=='lifetime')
				$recurring = 0;

			if(isset($_POST['qc_sld_update']) and $_POST['qc_sld_update']!=''){
				$uid = $_POST['qc_sld_update'];
				$wpdb->update(
					$table,
					array(
						'date'  => $date,
						'title'   => $title,
						'description'   => $description,
						'duration'   => $duration,
						'currency'   => $currency,
						'item'   => $item,
						'Amount' => $amount,
						
						'enable'   => $enable,
						
					),

					array( 'id' => $uid),
					array(
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						

					),
					array( '%d')
				);
				$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Package Updated. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}else{

				$wpdb->insert(
					$table,
					array(
						'date'  => $date,
						'title'   => $title,
						'description'   => $description,
						'duration'   => $duration,
						'currency'   => $currency,
						'item'   => $item,
						'Amount' => $amount,
						'enable'   => $enable,
						
					)
				);
				
				

				$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Package Created. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
			}


		}
		//reset option
		if(isset($_POST['qc_sld_reset'])){
			$rows     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE %d", 1 ) );
			foreach($rows as $row){
				$wpdb->delete(
					$table,
					array( 'id' => $row->id ),
					array( '%d' )
				);
			}
			$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Package Reset has been successfull. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}

		$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE %d", 1 ) );


?>
		<div class="wrap">
			

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;">

						<?php
						if($msg!=''){
							echo $msg;
						}
						?>
						<h1><?php echo __('Manage Your Package', 'qc-opd') ?></h1>
						<hr>
						<?php
						if(empty($row)){
						?>
							<?php echo __('<p>You have no package created! Please submit the following information and press save to Create a Package and charge your client to submit there list item.</p>', 'qc-opd') ?>
							

						<?php } ?>

						<form method="post" action="">
							<table class="form-table">

                                <tr>
                                    <th><label for="qc_sld_package_title"><?php _e( 'Enable Package', 'qc-opd' ); ?></label>
                                    </th>

                                    <td>
                                        <input type="checkbox" id="qc_sld_package_enable" name="qc_sld_package_enable" <?php echo (isset($row->enable) && $row->enable==1)?'checked="checked"':''; ?> value="1"/>

                                    </td>
                                </tr>
								<tr>
                                    <th><label for="qc_sld_package_title"><?php _e( 'Package Title', 'qc-opd' ); ?></label>
                                    </th>

                                    <td>
                                        <input type="text" id="qc_sld_package_title" name="qc_sld_package_title" value="<?php echo (isset($row->title)&&$row->title!=''?$row->title:''); ?>" required/>

                                    </td>
                                </tr>
                                <tr>
                                    <th><label for="qc_sld_package_desc"><?php _e( 'Package Description', 'qc-opd' ); ?></label>
                                    </th>

                                    <td>
                                        <textarea id="qc_sld_package_desc" name="qc_sld_package_desc" rows="5" cols="50"><?php echo (isset($row->description)&&$row->description!=''?$row->description:''); ?></textarea>

                                    </td>
                                </tr>

								<tr>
									<th><label for="qc_sld_item_duration"><?php _e( 'Duration', 'qc-opd' ); ?></label>
									</th>

									<td>
										<select id="qc_sld_item_duration" name="qc_sld_item_duration" required>
											<option value="">None</option>
											<?php
											for($i=1;$i<25;$i++){
												if(isset($row->duration) and $row->duration==$i){
													echo '<option value="'.$i.'" selected="selected">'.$i.' Month</option>';
												}else{
													echo '<option value="'.$i.'">'.$i.' Month</option>';
												}

											}
											?>
											<option value="lifetime" <?php echo ($row->duration=='lifetime'?'selected="selected"':''); ?>><?php _e( 'Lifetime', 'qc-opd' ); ?></option>
										</select>
										<span class="description"><?php _e( 'Select duration for how long the items will remain visible and also it will be use for recurring payment if selected.', 'qc-opd' ); ?></span>
									</td>
								</tr>

                                <tr>
                                    <th><label for="qc_sld_currency"><?php _e( 'Currency', 'qc-opd' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="qc_sld_currency" id="qc_sld_currency" required>

                                            <option value="USD" <?php echo (isset($row->currency)&&$row->currency=='USD'?'selected="selected"':''); ?>>US Dollars ($)</option>
                                            <option value="EUR" <?php echo (isset($row->currency)&&$row->currency=='EUR'?'selected="selected"':''); ?>>Euros (€)</option>
                                            <option value="GBP" <?php echo (isset($row->currency)&&$row->currency=='GBP'?'selected="selected"':''); ?>>Pounds Sterling (£)</option>
                                            <option value="ARS" <?php echo (isset($row->currency)&&$row->currency=='ARS'?'selected="selected"':''); ?>>Argentine Peso ($)</option>
                                            <option value="AUD" <?php echo (isset($row->currency)&&$row->currency=='AUD'?'selected="selected"':''); ?>>Australian Dollars ($)</option>
                                            <option value="BRL" <?php echo (isset($row->currency)&&$row->currency=='BRL'?'selected="selected"':''); ?>>Brazilian Real (R$)</option>
                                            <option value="CAD" <?php echo (isset($row->currency)&&$row->currency=='CAD'?'selected="selected"':''); ?>>Canadian Dollars ($)</option>
                                            <option value="CNY" <?php echo (isset($row->currency)&&$row->currency=='CNY'?'selected="selected"':''); ?>>Chinese Yuan</option>
                                            <option value="CZK" <?php echo (isset($row->currency)&&$row->currency=='CZK'?'selected="selected"':''); ?>>Czech Koruna</option>
                                            <option value="DKK" <?php echo (isset($row->currency)&&$row->currency=='DKK'?'selected="selected"':''); ?>>Danish Krone</option>
                                            <option value="HKD" <?php echo (isset($row->currency)&&$row->currency=='HKD'?'selected="selected"':''); ?>>Hong Kong Dollar ($)</option>
                                            <option value="HUF" <?php echo (isset($row->currency)&&$row->currency=='HUF'?'selected="selected"':''); ?>>Hungarian Forint</option>
                                            <option value="INR" <?php echo (isset($row->currency)&&$row->currency=='INR'?'selected="selected"':''); ?>>Indian Rupee</option>
                                            <option value="IDR" <?php echo (isset($row->currency)&&$row->currency=='IDR'?'selected="selected"':''); ?>>Indonesia Rupiah</option>
                                            <option value="ILS" <?php echo (isset($row->currency)&&$row->currency=='ILS'?'selected="selected"':''); ?>>Israeli Shekel</option>
                                            <option value="JPY" <?php echo (isset($row->currency)&&$row->currency=='JPY'?'selected="selected"':''); ?>>Japanese Yen (¥)</option>
                                            <option value="MYR" <?php echo (isset($row->currency)&&$row->currency=='MYR'?'selected="selected"':''); ?>>Malaysian Ringgits</option>
                                            <option value="MXN" <?php echo (isset($row->currency)&&$row->currency=='MXN'?'selected="selected"':''); ?>>Mexican Peso ($)</option>
                                            <option value="NGN" <?php echo (isset($row->currency)&&$row->currency=='NGN'?'selected="selected"':''); ?>>Nigerian Naira (₦)</option>
                                            <option value="NZD" <?php echo (isset($row->currency)&&$row->currency=='NZD'?'selected="selected"':''); ?>>New Zealand Dollar ($)</option>
                                            <option value="NOK" <?php echo (isset($row->currency)&&$row->currency=='NOK'?'selected="selected"':''); ?>>Norwegian Krone</option>
                                            <option value="PHP" <?php echo (isset($row->currency)&&$row->currency=='PHP'?'selected="selected"':''); ?>>Philippine Pesos</option>
                                            <option value="PLN" <?php echo (isset($row->currency)&&$row->currency=='PLN'?'selected="selected"':''); ?>>Polish Zloty</option>
                                            <option value="SGD" <?php echo (isset($row->currency)&&$row->currency=='SGD'?'selected="selected"':''); ?>>Singapore Dollar ($)</option>
                                            <option value="ZAR" <?php echo (isset($row->currency)&&$row->currency=='ZAR'?'selected="selected"':''); ?>>South African Rand (R)</option>
                                            <option value="KRW" <?php echo (isset($row->currency)&&$row->currency=='KRW'?'selected="selected"':''); ?>>South Korean Won</option>
                                            <option value="SEK" <?php echo (isset($row->currency)&&$row->currency=='SEK'?'selected="selected"':''); ?>>Swedish Krona</option>
                                            <option value="CHF" <?php echo (isset($row->currency)&&$row->currency=='CHF'?'selected="selected"':''); ?>>Swiss Franc</option>
                                            <option value="TWD" <?php echo (isset($row->currency)&&$row->currency=='TWD'?'selected="selected"':''); ?>>Taiwan New Dollars</option>
                                            <option value="THB" <?php echo (isset($row->currency)&&$row->currency=='THB'?'selected="selected"':''); ?>>Thai Baht</option>
                                            <option value="TRY" <?php echo (isset($row->currency)&&$row->currency=='TRY'?'selected="selected"':''); ?>>Turkish Lira</option>
                                            <option value="VND" <?php echo (isset($row->currency)&&$row->currency=='VND'?'selected="selected"':''); ?>>Vietnamese Dong</option>

                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <th><label for="qc_sld_item"><?php _e( 'Total Item', 'qc-opd' ); ?></label>
                                    </th>

                                    <td>
                                        <input type="text" id="qc_sld_item" name="qc_sld_item" value="<?php echo (isset($row->item)&&$row->item!=''?$row->item:'10'); ?>" required/>
                                        <span class="description"><?php _e( 'How many links user can add?', 'qc-opd' ); ?></span>
                                    </td>
                                </tr>

                                <tr>
									<th><label for="qc_sld_amount"><?php _e( 'Package Price', 'qc-opd' ); ?></label>
									</th>

									<td>
										<input type="text" id="qc_sld_amount" name="qc_sld_amount" value="<?php echo (isset($row->Amount)&&$row->Amount!=''?$row->Amount:''); ?>" required/>
										<span class="description"><?php _e( 'Enter price for the package.', 'qc-opd' ); ?></span>
									</td>
								</tr>

								
								

								<tr>
									<th><label for="qc_sld_save"><?php _e( '', 'qc-opd' ); ?></label>
									</th>

									<td>
										<?php
										if(isset($row->id) and $row->id!=''){?>
											<input type="hidden" name="qc_sld_update" id="qc_sld_update" value="<?php echo $row->id; ?>" />
										<?php } ?>
										<input type="submit" name="qc_sld_save" id="qc_sld_save" value="Save" />

									</td>
								</tr>

							</table>
						</form>
						<hr>
						<h3>Reset Package</h3>
						<form method="post" action="">
							<table class="form-table">

								<tr>
									<th><label for="qc_sld_save"><?php _e( 'Reset Package', 'qc-opd' ); ?></label>
									</th>

									<td>
										<input type="submit" name="qc_sld_reset" id="qc_sld_reset" value="Reset" />
										<span class="description"><?php _e( 'Reset package', 'qc-opd' ); ?></span>
									</td>
								</tr>

							</table>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="wrap">
			
<?php 
$table             = $wpdb->prefix.'sld_claim_configuration';
$current_user = wp_get_current_user();
$msg = '';
//echo $table;exit;

//get form data
if(isset($_POST['qc_sld_claim_save'])){

	
	$enable = isset($_POST['qc_sld_claim_listing_enable'])?$_POST['qc_sld_claim_listing_enable']:0;
	$currency = sanitize_text_field($_POST['qc_sld_currency']);
	
	$amount = ($_POST['qc_sld_amount']);
	
	$date = date('Y-m-d H:i:s');
	
	
	if(isset($_POST['qc_sld_update']) and $_POST['qc_sld_update']!=''){
		$uid = $_POST['qc_sld_update'];
		$wpdb->update(
			$table,
			array(
				'date'  => $date,
				'currency'   => $currency,
				'Amount' => $amount,
				
				'enable'   => $enable,
				
			),

			array( 'id' => $uid),
			array(
				'%s',
				'%s',
				'%s',

				'%d',
				

			),
			array( '%d')
		);
		$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Claim Configuration Updated. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}else{

		$wpdb->insert(
			$table,
			array(
				'date'  => $date,
				
				'currency'   => $currency,
				'Amount' => $amount,
				
				'enable'   => $enable,
				
			)
		);
		
		

		$msg = '<div id="message" class="updated notice notice-success is-dismissible"><p>Claim Configuration Created. </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}


}
$row     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE %d", 1 ) ); ?>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content" style="padding: 50px;box-sizing: border-box;box-shadow: 0 8px 25px 3px rgba(0,0,0,.2);background: #fff;">

						<?php
						if($msg!=''){
							echo $msg;
						}
						?>
						<h1><?php echo __('Manage Claim Listing Payment', 'qc-opd') ?></h1>
						<hr>
						

						<form method="post" action="">
							<table class="form-table">

                                <tr>
                                    <th><label for="qc_sld_package_title"><?php _e( 'Enable Payment for Claim Listing', 'qc-opd' ); ?></label>
                                    </th>

                                    <td>
                                        <input type="checkbox" id="qc_sld_claim_listing_enable" name="qc_sld_claim_listing_enable" <?php echo (isset($row->enable) && $row->enable==1)?'checked="checked"':''; ?> value="1"/>
										

                                    </td>
                                </tr>

								<tr>
									<th><label for="qc_sld_amount"><?php _e( 'Claim Listing Price', 'qc-opd' ); ?></label>
									</th>

									<td>
										<input type="text" id="qc_sld_amount" name="qc_sld_amount" value="<?php echo (isset($row->Amount)&&$row->Amount!=''?$row->Amount:''); ?>" required/>
										<span class="description"><?php _e( 'Enter price for the Claim Listing.', 'qc-opd' ); ?></span>
									</td>
								</tr>

                                <tr>
                                    <th><label for="qc_sld_currency"><?php _e( 'Currency', 'qc-opd' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="qc_sld_currency" id="qc_sld_currency" required>

                                            <option value="USD" <?php echo (isset($row->currency)&&$row->currency=='USD'?'selected="selected"':''); ?>>US Dollars ($)</option>
                                            <option value="EUR" <?php echo (isset($row->currency)&&$row->currency=='EUR'?'selected="selected"':''); ?>>Euros (€)</option>
                                            <option value="GBP" <?php echo (isset($row->currency)&&$row->currency=='GBP'?'selected="selected"':''); ?>>Pounds Sterling (£)</option>
                                            <option value="ARS" <?php echo (isset($row->currency)&&$row->currency=='ARS'?'selected="selected"':''); ?>>Argentine Peso ($)</option>
                                            <option value="AUD" <?php echo (isset($row->currency)&&$row->currency=='AUD'?'selected="selected"':''); ?>>Australian Dollars ($)</option>
                                            <option value="BRL" <?php echo (isset($row->currency)&&$row->currency=='BRL'?'selected="selected"':''); ?>>Brazilian Real (R$)</option>
                                            <option value="CAD" <?php echo (isset($row->currency)&&$row->currency=='CAD'?'selected="selected"':''); ?>>Canadian Dollars ($)</option>
                                            <option value="CNY" <?php echo (isset($row->currency)&&$row->currency=='CNY'?'selected="selected"':''); ?>>Chinese Yuan</option>
                                            <option value="CZK" <?php echo (isset($row->currency)&&$row->currency=='CZK'?'selected="selected"':''); ?>>Czech Koruna</option>
                                            <option value="DKK" <?php echo (isset($row->currency)&&$row->currency=='DKK'?'selected="selected"':''); ?>>Danish Krone</option>
                                            <option value="HKD" <?php echo (isset($row->currency)&&$row->currency=='HKD'?'selected="selected"':''); ?>>Hong Kong Dollar ($)</option>
                                            <option value="HUF" <?php echo (isset($row->currency)&&$row->currency=='HUF'?'selected="selected"':''); ?>>Hungarian Forint</option>
                                            <option value="INR" <?php echo (isset($row->currency)&&$row->currency=='INR'?'selected="selected"':''); ?>>Indian Rupee</option>
                                            <option value="IDR" <?php echo (isset($row->currency)&&$row->currency=='IDR'?'selected="selected"':''); ?>>Indonesia Rupiah</option>
                                            <option value="ILS" <?php echo (isset($row->currency)&&$row->currency=='ILS'?'selected="selected"':''); ?>>Israeli Shekel</option>
                                            <option value="JPY" <?php echo (isset($row->currency)&&$row->currency=='JPY'?'selected="selected"':''); ?>>Japanese Yen (¥)</option>
                                            <option value="MYR" <?php echo (isset($row->currency)&&$row->currency=='MYR'?'selected="selected"':''); ?>>Malaysian Ringgits</option>
                                            <option value="MXN" <?php echo (isset($row->currency)&&$row->currency=='MXN'?'selected="selected"':''); ?>>Mexican Peso ($)</option>
                                            <option value="NGN" <?php echo (isset($row->currency)&&$row->currency=='NGN'?'selected="selected"':''); ?>>Nigerian Naira (₦)</option>
                                            <option value="NZD" <?php echo (isset($row->currency)&&$row->currency=='NZD'?'selected="selected"':''); ?>>New Zealand Dollar ($)</option>
                                            <option value="NOK" <?php echo (isset($row->currency)&&$row->currency=='NOK'?'selected="selected"':''); ?>>Norwegian Krone</option>
                                            <option value="PHP" <?php echo (isset($row->currency)&&$row->currency=='PHP'?'selected="selected"':''); ?>>Philippine Pesos</option>
                                            <option value="PLN" <?php echo (isset($row->currency)&&$row->currency=='PLN'?'selected="selected"':''); ?>>Polish Zloty</option>
                                            <option value="SGD" <?php echo (isset($row->currency)&&$row->currency=='SGD'?'selected="selected"':''); ?>>Singapore Dollar ($)</option>
                                            <option value="ZAR" <?php echo (isset($row->currency)&&$row->currency=='ZAR'?'selected="selected"':''); ?>>South African Rand (R)</option>
                                            <option value="KRW" <?php echo (isset($row->currency)&&$row->currency=='KRW'?'selected="selected"':''); ?>>South Korean Won</option>
                                            <option value="SEK" <?php echo (isset($row->currency)&&$row->currency=='SEK'?'selected="selected"':''); ?>>Swedish Krona</option>
                                            <option value="CHF" <?php echo (isset($row->currency)&&$row->currency=='CHF'?'selected="selected"':''); ?>>Swiss Franc</option>
                                            <option value="TWD" <?php echo (isset($row->currency)&&$row->currency=='TWD'?'selected="selected"':''); ?>>Taiwan New Dollars</option>
                                            <option value="THB" <?php echo (isset($row->currency)&&$row->currency=='THB'?'selected="selected"':''); ?>>Thai Baht</option>
                                            <option value="TRY" <?php echo (isset($row->currency)&&$row->currency=='TRY'?'selected="selected"':''); ?>>Turkish Lira</option>
                                            <option value="VND" <?php echo (isset($row->currency)&&$row->currency=='VND'?'selected="selected"':''); ?>>Vietnamese Dong</option>

                                        </select>
                                    </td>
                                </tr>

								<tr>
									<th><label for="qc_sld_save"><?php _e( '', 'qc-opd' ); ?></label>
									</th>

									<td>
										<?php
										if(isset($row->id) and $row->id!=''){?>
											<input type="hidden" name="qc_sld_update" id="qc_sld_update" value="<?php echo $row->id; ?>" />
										<?php } ?>
										<input type="submit" name="qc_sld_claim_save" id="qc_sld_save" value="Save" />

									</td>
								</tr>

							</table>
						</form>
						<hr>
						
					</div>
				</div>
			</div>
		</div>
<?php
	}
}
function sld_package(){
	return Sld_package::get_instance();
}
sld_package();