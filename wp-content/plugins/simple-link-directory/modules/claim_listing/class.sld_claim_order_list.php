<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Sld_claim_order_list
{
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

	public function deny_subscriber_profile($id){
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$id;
        $identifier = time();
        $pdata = $wpdb->get_row($sql);

        if( $pdata->approval==1 || $pdata->approval==3 ){

            $searchQuery = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = ".$pdata->sld_list." and `meta_key` = 'qcopd_list_item01' and meta_value LIKE '%".$pdata->custom."%'";
            $result = @$wpdb->get_row($searchQuery);

            $meta_id = @$result->meta_id;

            @$wpdb->delete(
                "{$wpdb->prefix}postmeta",
	            array( 'meta_id' => $meta_id ),
	            array( '%d' )
            );

            $wpdb->update(
                $wpdb->prefix.'sld_user_entry',
                array(
                    'custom'  => '',
                    'approval'=> 2
                ),
                array( 'id' => $id),
                array(
                    '%s',
                    '%d',
                ),
                array( '%d')
            );
        }

    }
	
	
	public function sld_custom_plugin_admin_menu() {

		$hook = add_submenu_page(
			'edit.php?post_type=sld',
			'Claimed Listing',
			'Claimed Listing',
			'manage_options',
			'qcsld_claim_order_list',
			array(
				$this,
				'qc_sld_plugin_order_list_page'
			)
		);

	}
	public function sld_notification_approval( $user_id, $item ) {
		
		$user = new WP_User($user_id);
		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		$message  = __('Hi,') . "\r\n\r\n";
		$message .= sprintf(__("Welcome to %s! Your claim listing item %s has been approved.",'qc-opd'), get_option('blogname'), $item) . "\r\n\r\n";
		
		$message .= sprintf(__("You can find the item in <a href='%s'>dashboard > your links</a> tab.",'qc-opd'), qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','')) . "\r\n";

		wp_mail($user_email, sprintf(__('[%s] Your claim listing item has been approved!','qc-opd'), get_option('blogname')), $message);

	}
	public function top_action(){
		global $wpdb;
		$table             = $wpdb->prefix.'sld_claim_purchase';
		
		if(isset($_GET['act']) and $_GET['act']=='delete' ){
			$id = $_GET['id'];
			$pdata = $wpdb->get_row("Select * from $table where 1 and `id`='$id'");
			$userid = $pdata->user_id;
			
			$wpdb->delete(
				$table,
				array( 'id' => $id ),
				array( '%d' )
			);
			
			echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.__('Claim Deleted','qc-opd').' </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','qc-opd').'</span></button></div>';
		}
		
		if(isset($_GET['act']) and $_GET['act']=='approve'){
			
			$id = $_GET['id'];
			$pdata = $wpdb->get_row("Select * from $table where 1 and `id`='$id'");
			$userid = $pdata->user_id;
			
			$listId = $pdata->listid;
			if($pdata->status=='pending'){
				$lists = get_post_meta( $listId, 'qcopd_list_item01' );
				
				$userentry = $wpdb->prefix.'sld_user_entry';
				
				$category = get_the_terms($listId,'sld_cat');
				if(!empty($category)){
					$catslug = $category[0]->slug;
				}else{
					$catslug = '';
				}
				
				
				
				$datetime = date('Y-m-d H:i:s');
				
				foreach($lists as $item){
					if($item['qcopd_item_title']==$pdata->item){
						
						$wpdb->insert(
							$userentry,
							array(
								'item_title'  => $item['qcopd_item_title'],
								'item_link'   => $item['qcopd_item_link'],
								'item_subtitle' => $item['qcopd_item_subtitle'],
								'category'   => $catslug,
								'sld_list'  => $listId,
								'user_id'=>  $userid,
								'image_url'=> $item['qcopd_item_img_link'],
								'time'=> $datetime,
								'nofollow'=> $item['qcopd_item_nofollow'],
								'custom'=>$item['qcopd_timelaps'],
								'approval'=>1
								
							)
						);
						break;
						
					}
				}
				
				
				
				$wpdb->update(
					$table,
					array(
						'status'  => 'approved'
					),
					array( 'id' => $id),
					array(
						'%s',
					),
					array( '%d')
				);
				$this->sld_notification_approval($userid, $pdata->item);
				echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.__('Claim has been Approved successfully.','qc-opd').' </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','qc-opd').'</span></button></div>';
			}else{
				echo '<div id="message" class="updated notice notice-success is-dismissible"><p>'.__('Claim has been Approved already.','qc-opd').' </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.','qc-opd').'</span></button></div>';
			}
		}
		
	}
	
	public function qc_sld_plugin_order_list_page(){
		
		global $wpdb;
		
		$ctable = $wpdb->prefix.'sld_claim_purchase';
		$cptable = $wpdb->prefix.'sld_claim_configuration';
		
		$current_user = wp_get_current_user();
		$this->top_action();
		$claimconfiguration     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $cptable WHERE %d", 1 ) );

	?>
		<div class="qchero_sliders_list_wrapper">
			<div class="sld_menu_title">
				<h2 style="font-size: 26px;text-align:center"><?php echo __('Claimed Listing', 'qc-opd') ?></h2>

			</div>
			<div class="qchero_slider_table_area">
				<div class="sld_payment_table">
					<div class="sld_payment_row header">
						<div class="sld_payment_cell">
							<?php _e( 'Date', 'qc-opd' ) ?>
						</div>
						
						<div class="sld_payment_cell">
							<?php _e( 'List Name', 'qc-opd' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'List Item Name', 'qc-opd' ) ?>
						</div>

						<div class="sld_payment_cell">
							<?php _e( 'Buyer Name', 'qc-opd' ) ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'User Name', 'qc-opd' ); ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'Payable Amount', 'qc-opd' ); ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'Paid Amount', 'qc-opd' ); ?>
						</div>
						<div class="sld_payment_cell">
							<?php _e( 'Payment Status', 'qc-opd' ); ?>
						</div>
						
						<div class="sld_payment_cell">
							<?php _e( 'Approval', 'qc-opd' ); ?>
						</div>
						
						<div class="sld_payment_cell">
							<?php _e( 'Action', 'qc-opd' ); ?>
						</div>
					</div>

			<?php
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $ctable WHERE %d order by `date` DESC ", 1 ) );
			foreach($rows as $row){
			?>
				<div class="sld_payment_row">
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Date', 'qc-opd') ?></div>
						<?php echo date('m/d/Y', strtotime($row->date)); ?>
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('List Name', 'qc-opd') ?></div>
						<?php echo get_the_title($row->listid); ?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('List Item Name', 'qc-opd') ?></div>
						<?php echo ($row->item); ?>
					</div>

					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Buyer Name', 'qc-opd') ?></div>
						<?php echo $row->payer_name; ?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('User Name', 'qc-opd') ?></div>
						<?php
							$userinfo = get_user_by( 'ID', $row->user_id );
							echo $userinfo->user_login;
						?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Payable Amount', 'qc-opd') ?></div>
						<?php echo $claimconfiguration->Amount.' '.$claimconfiguration->currency; ?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Paid Amount', 'qc-opd') ?></div>
						<?php echo $row->paid_amount.' '.$claimconfiguration->currency; ?>
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Payment Status', 'qc-opd') ?></div>
						<?php 
							if($claimconfiguration->enable==1){
								if($row->paid_amount==0){
									echo '<span style="color:red">Not Paid</span>';
								}else{
									echo '<span style="color:green">Paid</span>';
								}
							}else{
								echo '<span style="color:green">Free</span>';
							}
						?>
					</div>
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Approval', 'qc-opd') ?></div>
						<?php echo ucfirst($row->status); ?>
					</div>
					
					<div class="sld_payment_cell">
						<div class="sld_responsive_head"><?php echo __('Action', 'qc-opd') ?></div>
						
						<?php if($row->status!='approved'): ?>
						<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=sld&page=qcsld_claim_order_list&act=approve&id=' . $row->id ), 'sld' ) ?>">
							<button class="button button-primary"><?php echo __('Approve', 'qc-opd') ?></button>
							
						</a>
						<?php endif; ?>
						
						<?php if($row->status=='approved'):
						
							$userentry = $wpdb->prefix.'sld_user_entry';
							$get_user_item = $wpdb->get_row("Select * from $userentry where 1 and `sld_list` = '".$row->listid."' and `user_id` = '".$row->user_id."' order by id desc limit 1");
						?>
						<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=sld&page=qcsld_user_entry_list&action=edit&book='.$get_user_item->id ), 'sld' ) ?>">
							<button class="button button-primary"><?php echo __('Edit Item', 'qc-opd') ?></button>
						</a>
						<?php endif; ?>
						
						<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=sld&page=qcsld_claim_order_list&act=delete&id=' . $row->id ), 'sld' ) ?>">
							<button class="button button-danger"><?php echo __('Delete', 'qc-opd') ?></button>
							
						</a>
					</div>
				</div>
			<?php
			}
			?>

			</div>

		</div>
		</div>
	<?php
	}
}
function Sld_claim_order_list(){
	return Sld_claim_order_list::get_instance();
}
Sld_claim_order_list();