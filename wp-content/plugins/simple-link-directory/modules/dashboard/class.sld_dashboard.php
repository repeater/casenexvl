<?php
/*
* Author: QuantumCloud.
* Dashboard class.
*/

if ( ! defined( 'ABSPATH' ) ) exit;
class qc_sld_dashboard
{
	private static $instance;

	protected $allow_item_submit; //Allow item submission
	protected $show_package; //Allow package
    protected $total_item;
    protected $submited_item;
    protected $remain_item;



	public static function instance(){
		if(!isset(self::$instance)){
			self::$instance = new qc_sld_dashboard();
		}
		return self::$instance;
	}
	private function __construct(){
		
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		} 
		require(QCOPD_DIR_MOD.'/dashboard/qc-subscriber-entry-approve.php');
		require(QCOPD_DIR_MOD.'/dashboard/qc-subscriber_entry_list.php');

		//add_action('pre_get_posts',array($this,'sld_users_own_attachments'));
		
        add_action('template_redirect', array($this,'sldcustom_redirect_load_before_headers'));

		add_action('init', array($this, 'sld_plugin_init'));

		//add_action( 'wp_enqueue_scripts', array($this,'sldcustom_dashboard_enqueue_style') );
		add_shortcode('sld_dashboard', array($this,'sld_dashboard_show'));
		//add_action('wp_loaded', array($this,'sldcustom_user_permission_add'));
		
		add_action( 'wp_ajax_qcld_sld_category_filter', array($this,'qcld_sld_category_filter_fnc') ); // ajax for logged in users
		add_action( 'wp_ajax_nopriv_qcld_sld_category_filter', array($this,'qcld_sld_category_filter_fnc') ); // ajax for not logged in users
		add_action('plugins_loaded',array($this,'qc_sld_admin_area'));

	}
    function sld_plugin_init(){
	    global $wpdb;
		
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		
	    $current_user = wp_get_current_user();
	    $table             = $wpdb->prefix.'sld_package_purchased';
	    $table1             = $wpdb->prefix.'sld_package';

	    if(isset($_GET['payment']) and $_GET['payment']=='save'){
		    $package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE %d", 1 ) );

		    if($package->duration=='lifetime'){
			    $package->duration = 120;
		    }

		    $userid = $_GET['userid'];
		    if(!empty($_POST)){
		        $txn_id = $_POST['txn_id'];
		        $name = $_POST['first_name'].' '.$_POST['last_name'];
		        $payer_email = $_POST['payer_email'];
		        $amount = $_POST['mc_gross'];
		        $status = $_POST['payment_status'];
			    $date = date('Y-m-d H:i:s');
				if(isset($_REQUEST['custom']) && $_REQUEST['custom']=='recurring'){
					$custom = 1;
					$package->duration = 120;
				}else{
					$custom = 0;
				}
			    $expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

			    $wpdb->insert(
				    $table,
				    array(
					    'date'  => $date,
					    'package_id'   => $package->id,
					    'user_id'   => $userid,
					    'paid_amount'   => $amount,
					    'transaction_id' => $txn_id,
					    'payer_name'   => $name,
					    'payer_email'   => $payer_email,
					    'status'   => $status,
                        'expire_date' => $expire_date,
						'recurring' => $custom
				    )
			    );
				wp_reset_query();
            }
        }



        if(isset($_GET['payment']) and $_GET['payment']=='renew'){

	        $pkg = $_GET['pkg'];
	        $package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE %d", 1 ) );
	        $package1     = $wpdb->get_row( "SELECT * FROM $table WHERE 1 and id = ".$pkg);


	        if($package->duration=='lifetime'){
		        $package->duration = 120;
            }

	        $expire_date = date("Y-m-d H:i:s", strtotime("+$package->duration month", strtotime($package1->expire_date)));



	        if(!empty($_POST)){
		        $txn_id = $_POST['txn_id'];
		        $name = $_POST['first_name'].' '.$_POST['last_name'];
		        $payer_email = $_POST['payer_email'];
		        $amount = $_POST['mc_gross'];
		        $status = $_POST['payment_status'];
		        $date = date('Y-m-d H:i:s');


		        $wpdb->update(
			        $table,
			        array(
				        'renew'  => $date,
				        'expire_date'=>$expire_date,
				        'transaction_id' => $txn_id,
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();
	        }
        }
		
		//Stripe payment
		if(isset($_GET['payment']) and $_GET['payment']=='stripe-save'){
			
			require_once(QCOPD_INC_DIR.'/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey(sld_get_option('sld_stripe_sectet_key'));

			$table             = $wpdb->prefix.'sld_package_purchased';
			$table1             = $wpdb->prefix.'sld_package';
			$package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE %d", 1 ) );

		    if($package->duration=='lifetime'){
			    $package->duration = 120;
		    }
			
			$amount = ($package->Amount*100);
			$currency = $package->currency;
		    $userid = $_GET['userid'];
			$token  = $_POST['stripeToken'];
			$email  = $_POST['stripeEmail'];
			$customer = \Stripe\Customer::create(array(
				  'email' => $email,
				  'source'  => $token
			 ));
			
			$charge = \Stripe\Charge::create(array(
			  'customer' => $customer->id,
			  'amount'   => $amount,
			  'currency' => $currency
			));
			
			
			
			
		    if(!empty($_POST)){
		        $txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $package->Amount;
		        $status = 'success';
			    $date = date('Y-m-d H:i:s');
				
			    $expire_date = date("Y-m-d", strtotime("+$package->duration month", strtotime($date)));

			    $wpdb->insert(
				    $table,
				    array(
					    'date'  => $date,
					    'package_id'   => $package->id,
					    'user_id'   => $userid,
					    'paid_amount'   => $amount,
					    'transaction_id' => $txn_id,
					    'payer_name'   => $name,
					    'payer_email'   => $payer_email,
					    'status'   => $status,
                        'expire_date' => $expire_date,
						
				    )
			    );
				wp_reset_query();
            }
			
		}
		
		if(isset($_GET['payment']) and $_GET['payment']=='stripe-renew'){
			require_once(QCOPD_INC_DIR.'/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey(sld_get_option('sld_stripe_sectet_key'));
			
	        $pkg = $_GET['pkg'];
	        $package     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table1 WHERE %d", 1 ) );
	        $package1     = $wpdb->get_row( "SELECT * FROM $table WHERE 1 and id = ".$pkg);


	        if($package->duration=='lifetime'){
		        $package->duration = 120;
            }

	        $expire_date = date("Y-m-d H:i:s", strtotime("+$package->duration month", strtotime($package1->expire_date)));

			$amount = ($package->Amount*100);
			$currency = $package->currency;
		    $userid = $_GET['userid'];
			$token  = $_POST['stripeToken'];
			$email  = $_POST['stripeEmail'];
			$customer = \Stripe\Customer::create(array(
				  'email' => $email,
				  'source'  => $token
			 ));
			
			$charge = \Stripe\Charge::create(array(
			  'customer' => $customer->id,
			  'amount'   => $amount,
			  'currency' => $currency
			));

	        if(!empty($_POST)){
		        $txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $package->Amount;
		        $status = 'success';
		        $date = date('Y-m-d H:i:s');


		        $wpdb->update(
			        $table,
			        array(
				        'renew'  => $date,
				        'expire_date'=>$expire_date,
				        'transaction_id' => $txn_id,
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();
	        }
        }
		
		//Paypal Claim Payment Handle
		
		if(isset($_GET['payment']) and $_GET['payment']=='claim-paypal'){
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			
	        $pkg = $_GET['pkg'];

	        if(!empty($_POST)){
		        $txn_id = $_POST['txn_id'];
		        $name = $_POST['first_name'].' '.$_POST['last_name'];
		        $payer_email = $_POST['payer_email'];
		        $amount = $_POST['mc_gross'];
		        $date = date('Y-m-d H:i:s');

		        $wpdb->update(
			        $ctable,
			        array(
				        'transaction_id'=>$txn_id,
						'paid_amount'	=>$amount,
						'payer_name'	=>$name,
						'payer_email'	=>$payer_email
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();
	        }
        }
		
		// Stripe claim payment handle
		
		if(isset($_GET['payment']) and $_GET['payment']=='claim-stripe'){
			
			$ctable = $wpdb->prefix.'sld_claim_purchase';
			$cptable = $wpdb->prefix.'sld_claim_configuration';
			
			require_once(QCOPD_INC_DIR.'/stripe-php-master/init.php');
			\Stripe\Stripe::setApiKey(sld_get_option('sld_stripe_sectet_key'));
			
	        $pkg = $_GET['pkg'];
			
	        $claimpayment = $wpdb->get_row("select * from $cptable where 1");

			$amount = ($claimpayment->Amount*100);
			$currency = $claimpayment->currency;
			$token  = $_POST['stripeToken'];
			$email  = $_POST['stripeEmail'];
			$customer = \Stripe\Customer::create(array(
				  'email' => $email,
				  'source'  => $token
			 ));
			$charge = \Stripe\Charge::create(array(
			  'customer' => $customer->id,
			  'amount'   => $amount,
			  'currency' => $currency
			));
	        if(!empty($_POST)){
		        $txn_id = $token;
		        $name = $email;
		        $payer_email = $email;
		        $amount = $claimpayment->Amount;
		        $wpdb->update(
			        $ctable,
			        array(
				        'transaction_id'=>$txn_id,
						'paid_amount'	=>$amount,
						'payer_name'	=>$name,
						'payer_email'	=>$payer_email
			        ),
                    array('id'=>$pkg),
                    array(
                        '%s',
                        '%d',
                        '%s',
                        '%s',
                    ),
                    array('%d')
		        );
				wp_reset_query();
	        }
        }
		
    }

	public function sld_new_item_notification( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $message  = sprintf(__('A New link has been submitted to your list %s. Please go to Simple Link Directory > Manage User Items to view this link.'), get_option('blogname')) . "\r\n\r\n";
		$message .= sprintf(__('Item Name : %s'), $item) . "\r\n\r\n";
        $message .= sprintf(__('Item Submitted By : %s'), $user_login) . "\r\n\r\n";


        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] A New link has been submitted to your list!'), get_option('blogname')), $message);

    }
	
	public function sld_edit_item_notification( $user_id, $item) {
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $message  = sprintf(__('A link has been edited to your list %s. Please go to Simple Link Directory > Manage User Items to view this link.'), get_option('blogname')) . "\r\n\r\n";
		$message .= sprintf(__('Item Edited : %s'), $item) . "\r\n\r\n";
        $message .= sprintf(__('Item Edited By : %s'), $user_login) . "\r\n\r\n";


        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] A link has been edited to your list!'), get_option('blogname')), $message);

    }

	public function sld_claim_notification($user_id, $item) {
		
        $user = new WP_User($user_id);

        $user_login = ($user->user_login);

        $message  = sprintf(__('A item has been claimed from your list %s. Please go to Simple Link Directory > Claimed Listing to view this claimed item.'), get_option('blogname')) . "\r\n\r\n";
        $message .= sprintf(__('Item Claimed : %s'), $item) . "\r\n\r\n";
        $message .= sprintf(__('Item Claimed By : %s'), $user_login) . "\r\n\r\n";


        @wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] A item has been claimed from your list!'), get_option('blogname')), $message);

    }
	
	
	/*
	*
	* Admin Area integration
	*/
	public function qc_sld_admin_area(){
		return Sld_user_entry::get_instance();
	}
	
	public function sldcustom_user_permission_add(){
		$current_user = wp_get_current_user();

		if(is_user_logged_in() && in_array('slduser',$current_user->roles)){
			$current_user->add_cap('upload_files');
		}
		if(is_user_logged_in() && in_array('subscriber',$current_user->roles)){
			
			if(sld_get_option('sld_subscriber_image_upload')=='on'){
				$current_user->add_cap('upload_files');
			}else{
				$current_user->remove_cap('upload_files');
			}
			
		}
		
	}

    /**
     * Approve Subscriber profile.
     *
     * @return null
     */

    public function approve_subscriber_profile($id){
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$id;
        $identifier = time();
        $pdata = $wpdb->get_row($sql);
		
		$featured = 0;
		if($pdata->package_id > 0){
			$featured = 1;
		}
		
		if(sld_get_option('sld_paid_item_featured')!='on'){
			$featured = 0;
		}
		
        if( $pdata->approval==0 || $pdata->approval==2){
            $prepare = array( //preparing Meta
                'qcopd_item_title' 			=> sanitize_text_field($pdata->item_title),
                'qcopd_item_link' 			=> trim($pdata->item_link),
                'qcopd_item_subtitle' 		=> sanitize_text_field($pdata->item_subtitle),
				'qcopd_description' 		=> sanitize_text_field($pdata->description),
                'qcopd_item_img_link' 		=> trim($pdata->image_url),
                'qcopd_fa_icon' 			=> '',
                'qcopd_item_img' 			=> '',
                'qcopd_item_nofollow' 		=> ($pdata->nofollow==1?1:0),
                'qcopd_item_newtab' 		=> 1,
                'qcopd_use_favicon' 		=> 1,
                'qcopd_upvote_count' 		=> 0,
                'qcopd_entry_time' 			=> date('Y-m-d H:i:s'),
                'qcopd_timelaps' 			=> $identifier,
				'qcopd_featured'			=> $featured

            );

            add_post_meta( trim($pdata->sld_list), 'qcopd_list_item01', $prepare );

            $wpdb->update(
                $wpdb->prefix.'sld_user_entry',
                array(
                    'custom'  => $identifier,
                    'approval'=> 1
                ),
                array( 'id' => $id),
                array(
                    '%s',
                    '%d',
                ),
                array( '%d')
            );
			wp_reset_query();
        }elseif($pdata->approval==3){

            $sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$id;
            $pdata = $wpdb->get_row($sql);
            $identifier = time();
            if($pdata->custom!=''){
                $this->deny_subscriber_profile($id);
            }

            $prepare = array( //preparing Meta
                'qcopd_item_title' 			=> sanitize_text_field($pdata->item_title),
                'qcopd_item_link' 			=> trim($pdata->item_link),
                'qcopd_item_subtitle' 		=> sanitize_text_field($pdata->item_subtitle),
				'qcopd_description' 		=> sanitize_text_field($pdata->description),
                'qcopd_item_img_link' 		=> trim($pdata->image_url),
                'qcopd_fa_icon' 			=> '',
                'qcopd_item_img' 			=> '',
                'qcopd_item_nofollow' 		=> ($pdata->nofollow==1?1:0),
                'qcopd_item_newtab' 		=> 1,
                'qcopd_use_favicon' 		=> 1,
                'qcopd_upvote_count' 		=> 0,
                'qcopd_entry_time' 			=> date('Y-m-d H:i:s'),
                'qcopd_timelaps' 			=> $identifier,
				'qcopd_featured'			=> $featured

            );

            add_post_meta( trim($pdata->sld_list), 'qcopd_list_item01', $prepare );

            $wpdb->update(
                $wpdb->prefix.'sld_user_entry',
                array(
                    'custom'  => $identifier,
                    'approval'=> 1
                ),
                array( 'id' => $id),
                array(
                    '%s',
                    '%d',
                ),
                array( '%d')
            );
			wp_reset_query();
        }

    }

    /**
     * Delete User Entry.
     *
     * @return null
     */

    public function delete_subscriber_profile( $id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$id;
        $pdata = $wpdb->get_row($sql);

        if(@$pdata->approval==1){
            $this->deny_subscriber_profile($id);
        }

        $wpdb->delete(
            "{$wpdb->prefix}sld_user_entry",
            array( 'id' => $id ),
            array( '%d' )
        );


    }

    /**
     * Deny User Entry.
     *
     * @return null
     */

    public function deny_subscriber_profile($id){
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}sld_user_entry where 1 and id = ".$id;
        $identifier = time();
        $pdata = $wpdb->get_row($sql);

        if( $pdata->approval==1 || $pdata->approval==3 ){

			if(strlen($pdata->custom)<3){
				$searchQuery = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = ".$pdata->sld_list." and `meta_key` = 'qcopd_list_item01' and meta_value LIKE '%".$pdata->item_title."%'";
			}else{
				$searchQuery = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE 1 and `post_id` = ".$pdata->sld_list." and `meta_key` = 'qcopd_list_item01' and meta_value LIKE '%".$pdata->custom."%'";
			}
			

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

	/*
	*Wp enqueue Script
	* Load stylesheet.
	*/
	public function sldcustom_dashboard_enqueue_style(){
		wp_register_style( 'sldcustom_dashboard-css', QCOPD_ASSETS_URL.'/css/dashboardstyle.css', __FILE__ );
		wp_enqueue_style( 'sldcustom_dashboard-css' );
	}

	/*
	*Wp ajax function
	*Category filter
	*/
	public function qcld_sld_category_filter_fnc(){
		
		$cateogy = sanitize_text_field($_POST['cat']);
		$sld = new WP_Query( array( 
			'post_type' => 'sld',
			'tax_query' => array(
				array (
					'taxonomy' => 'sld_cat',
					'field' => 'term_id',
					'terms' => $cateogy,
				)
			),
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'menu_order'
			) 
		);
		
		$excludel = sld_get_option('sld_exclude_list');
		
		while( $sld->have_posts() ) : $sld->the_post();
		?>
			<?php 
			if($excludel!=''){
				$exclude = explode(',',$excludel);
				if(!in_array(get_the_ID(),$exclude)){
					?>
					<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
					<?php
				}
			}else{
			?>
			<option value="<?php echo get_the_ID(); ?>"><?php the_title(); ?></option>
			<?php
			}
			?>
			
		<?php
		endwhile;
				
		die();
		
	}

    /*
    *
    * Load before header
    */
    function sldcustom_redirect_load_before_headers()
    {
        if (isset($_GET['sldact']) and $_GET['sldact'] == 'logout') {
            wp_logout();
            $url = get_home_url();
            wp_safe_redirect($url);
        }
    }

	/*
	*
	* GET Lists Status
	*/
	public function getStatus($args){
		if($args==0){
			return '<span style="color:#f4b042">'.__('Pending', 'qc-opd').'</span>';
		}elseif($args==1){
			return '<span style="color:green">'.__('Approved', 'qc-opd').'</span>';
		}elseif($args==2){
			return '<span style="color:red">'.__('Deny', 'qc-opd').'</span>';
		}else{
			return '<span style="color:#f4b042">'.__('Edited', 'qc-opd').'</span>';
		}
	}

	/*
	*
	* GET Image
	*/
	public function getImage($args){
		if($args!=''){
			echo '<img src="'.$args.'" width="50"/>';
		}else{
			echo '<img src="'.QCOPD_IMG_URL.'/no-image.png'.'" width="50"/>';
		}
	}	
	
	public function sld_dashboard_show(){
		ob_start();
		global $wpdb;

		$current_user = wp_get_current_user();
		$table             = $wpdb->prefix.'sld_user_entry';
		$package_purchased_table = $wpdb->prefix.'sld_package_purchased';
        $package_table = $wpdb->prefix.'sld_package';
		
		if(is_user_logged_in() && (in_array('slduser',$current_user->roles) or in_array('administrator',$current_user->roles) or sld_get_option('sld_enable_anyusers')=='on')){
			
			$url = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_dashboard_url');

			//check whether package enable or not
			
			$get_package = $wpdb->get_row("select * from $package_table where 1 limit 1");
			
            $itempurchase = $wpdb->get_row("select sum(p.item)as cnt from $package_table as p, $package_purchased_table as pd where pd.package_id = p.id and pd.user_id = ".$current_user->ID);

            if(!empty($get_package) and $get_package->enable==1){
	            if($itempurchase->cnt!='' and $itempurchase->cnt > 0){
		            $this->show_package = true;
		            $this->total_item = $itempurchase->cnt;
	            }else{
		            $this->show_package = true;
	            }
            }else{
                $this->show_package = false;
            }

			if(sld_get_option('sld_enable_free_submission')=='on'){
				if(sld_get_option('sld_free_item_limit')!=''){
					$this->total_item += sld_get_option('sld_free_item_limit');
                }
            }

            //find total submited item
            $submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and user_id =".$current_user->ID);
			if($submited_item->cnt==''){
				$this->submited_item = 0;
            }else{
			    $this->submited_item = $submited_item->cnt;
            }

			if($this->total_item > 0){
			    if($this->total_item > $submited_item->cnt){
				    $this->remain_item = ($this->total_item - $submited_item->cnt);
				    $this->allow_item_submit = true;
                }else{
			        $this->remain_item = 0;
				    $this->allow_item_submit = false;
                }
            }else{
			    $this->remain_item = 0;
				$this->allow_item_submit = false;
            }

			if(is_user_logged_in() && in_array('administrator',$current_user->roles)){
				$this->allow_item_submit = true;
			}


?>
		<div class="sld_dashboard_main_area">
			<nav class="sldnav sldnav--red">
				<ul class="sldnav__list">
				
					<li class="sldnav__list__item <?php echo (!isset($_GET['action'])?'sldactive':''); ?>"><a href="<?php echo $url; ?>"><?php echo __('Dashboard', 'qc-opd') ?></a></li>
                    

					<li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='entry'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'entry', $url ) ) ?>"><?php echo __('Add Link', 'qc-opd') ?></a></li>
					
					<li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='entrylist'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'entrylist', $url ) ) ?>"><?php echo __('Your Links', 'qc-opd') ?></a></li>
				<?php if(sld_get_option('sld_enable_claim_listing')=='on'): ?>
                   <li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='claim'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'claim', $url ) ) ?>"><?php echo __('Claim Listing', 'qc-opd') ?></a></li>
				<?php endif; ?>
                <?php if($this->show_package==true): ?>
                <li class="sldnav__list__item <?php echo (isset($_GET['action'])&&$_GET['action']=='package'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'action', 'package', $url ) ) ?>"><?php echo __('Package', 'qc-opd') ?></a></li>
                <?php endif; ?>

<!--					<li class="sldnav__list__item --><?php //echo (isset($_GET['action'])&&$_GET['action']=='payment'?'sldactive':''); ?><!--"><a href="--><?php //echo esc_url( add_query_arg( 'action', 'payment', $url ) ) ?><!--">Payment</a></li>-->
					
<!--					<li class="sldnav__list__item --><?php //echo (isset($_GET['action'])&&$_GET['action']=='help'?'sldactive':''); ?><!--"><a href="--><?php //echo esc_url( add_query_arg( 'action', 'help', $url ) ) ?><!--">Help</a></li>-->

                    <li class="sldnav__list__item <?php echo (isset($_GET['sldact'])&&$_GET['sldact']=='logout'?'sldactive':''); ?>"><a href="<?php echo esc_url( add_query_arg( 'sldact', 'logout', $url ) ) ?>"><?php echo __('Logout', 'qc-opd') ?></a></li>

				</ul>
			</nav>
			
			<?php if(!isset($_GET['action']) and !isset($_GET['payment']) )://Dashboard ?>
			
			
				<?php 
				$userpkgs = $wpdb->get_results("select * from $package_purchased_table where 1 and user_id = ".$current_user->ID." order by date DESC");
				if(!empty($userpkgs)):
					foreach($userpkgs as $userpkg):			
						if(strtotime(date('Y-m-d')) < strtotime($userpkg->expire_date)):
						?>
							<div class="sld_package_notification">Your package <b><?php echo $get_package->title; ?></b> will expire on <b><?php echo( date( "Y-m-d", strtotime( $userpkg->expire_date ) ) ) ?></b> </div>
						<?php
						else:
						?>
							<div class="sld_package_notification">Your package <b><?php echo $get_package->title; ?></b> is already expired on <b><?php echo( date( "Y-m-d", strtotime( $userpkg->expire_date ) ) ) ?></b> </div>
						<?php
						endif;

					endforeach;
				endif;
				?>

                <?php

                if(isset($_POST['first_name']) && $_POST['first_name']!=''){
	                $user_id = $current_user->ID;
	                $user_data = wp_update_user( array( 'ID' => $user_id, 'first_name' => $_POST['first_name'], 'last_name' =>$_POST['last_name'], 'user_email'=> $_POST['user_email'], 'user_login'=> $_POST['user_login'] ) );
	                if ( is_wp_error( $user_data ) ) {
		                echo '<p style="color:red;font-size: 20px;">'.__('Something Went Wrong.','qc-opd').'</p>';
	                } else {
		                // Success!
		                echo '<p style="color:green;font-size: 20px;">'.__('User profile updated.','qc-opd').'</p>';
	                }
                }
                ?>
                <style type="text/css">
                    .sld_total_count {
                        font-family: arial, sans-serif;
                        border-collapse: collapse;
                        width: 100%;
                    }

                    .sld_total_count td {
                        text-align: left;
                        padding: 8px;
                    }
                </style>
				<h2>
					<?php 
					$fullName = $current_user->user_firstname.' '.$current_user->user_lastname;
					echo sprintf(__( 'Hi %s, Welcome to your Dashboard.', 'qc-opd' ),$fullName);
					?>
				</h2>
				
				
				
                <table class="sld_total_count">
                    <?php
                    if(in_array('administrator',$current_user->roles)){
                        ?>
                        <tr>
                            <td><?php echo __('Total Link', 'qc-opd') ?> : <?php echo __('Unlimited', 'qc-opd') ?></td>
                            <td><?php echo __('Submited Link', 'qc-opd') ?> : <?php echo $this->submited_item; ?></td>
                            <td><?php echo __('Remaining Link', 'qc-opd') ?> : <?php echo __('Unlimited', 'qc-opd') ?></td>
                        </tr>
                        <?php
                    }else{
                        ?>
                        <tr>
                            <td><?php echo __('Total Link', 'qc-opd') ?> : <?php echo $this->total_item; ?></td>
                            <td><?php echo __('Submited Link', 'qc-opd') ?> : <?php echo $this->submited_item; ?></td>
                            <td><?php echo __('Remaining Link', 'qc-opd') ?> : <?php echo $this->remain_item; ?></td>
                        </tr>
                        <?php
                    }
                    ?>

                </table>


                <?php
				if(sld_get_option('sld_profile_update')=='on'){
                ?>
                    <hr>
				<p class="updateprofile"><?php echo __('Update Profile Info', 'qc-opd') ?></p>
				<form method="post">
				<ul class="sld_form-style-1">
					<li><label><?php echo __('Full Name', 'qc-opd') ?> <span class="sld_required">*</span></label><input type="text" name="first_name" class="sld_field-divided" placeholder="First" value="<?php echo $current_user->user_firstname; ?>" />&nbsp;<input type="text" name="last_name" class="sld_field-divided" placeholder="Last" value="<?php echo $current_user->user_lastname; ?>" /></li>
					<li>
						<label><?php echo __('Email', 'qc-opd') ?> <span class="sld_required">*</span></label>
						<input type="email" name="user_email" class="field-long" value="<?php echo $current_user->user_email; ?>" />
					</li>
					<li>
						<label><?php echo __('Username', 'qc-opd') ?> <span class="sld_required">*</span></label>
						<input type="text" name="user_login" class="field-long" value="<?php echo $current_user->user_login; ?>" />
					</li>

					<li>
						<input class="sld_submit_style" type="submit" value="Submit" />
					</li>
				</ul>
				</form>
                <?php } ?>

			<?php endif; ?>
			
			<?php if(isset($_GET['action']) && $_GET['action']=='entry' )://entry 
				require(QCOPD_DIR_MOD.'/dashboard/sld_entry.php');
			 endif; ?>
			
			<?php if(isset($_GET['action']) && $_GET['action']=='entrylist' )://entrylist ?>
				<?php require(QCOPD_DIR_MOD.'/dashboard/sld_entrylist.php'); ?>
			<?php endif; ?>
			
			<?php if(sld_get_option('sld_enable_claim_listing')=='on'): ?>
				<?php if(isset($_GET['action']) && $_GET['action']=='claim' )://entrylist ?>
					<?php require(QCOPD_DIR_MOD.'/dashboard/sld_claim.php'); ?>
				<?php endif; ?>
			<?php endif; ?>
			
			<?php if(isset($_GET['action']) && $_GET['action']=='package' )://Payment ?>
				<?php require(QCOPD_DIR_MOD.'/dashboard/sld_package.php'); ?>
			<?php endif; ?>

			<?php if(isset($_GET['action']) && $_GET['action']=='help' )://help ?>
				<h2>This is SLD Help page.</h2>
			<?php endif; ?>

			<?php if(isset($_GET['payment']) && $_GET['payment']=='success' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully. Thank you!
                    Now you can add links to our Lists.','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='stripe-save' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully. Thank you!
                    Now you can add links to our Lists.','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='stripe-renew' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully for package renewal. Thank you! ','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='claim-stripe' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully for Claim Listing. Thank you! ','qc-opd') ?></p>
			<?php endif; ?>
			
			<?php if(isset($_GET['payment']) && $_GET['payment']=='claim-paypal' )://help ?>
                <p style="color: #000;font-size: 16px;margin: 11px 0px;text-align:center;"><?php echo __('Payment has been done successfully for Claim Listing. Thank you! ','qc-opd') ?></p>
			<?php endif; ?>


			<?php if(isset($_GET['action']) && $_GET['action']=='entryedit' )://help ?>
				<?php require(QCOPD_DIR_MOD.'/dashboard/sld_entryedit.php') ?>
			<?php endif; ?>
			
		</div>
<?php		
		}elseif(!is_user_logged_in()){
            $url = qc_sld_login_page()->sldcustom_login_get_translated_option_page('sld_login_url');
            echo sprintf(__('You have to login in to view this content. <a href="%s">Click Here</a> to log in.','qc-opd'),$url);
        }else{
			echo __('Sorry, You are not allowed to view the content of this page.','qc-opd');
		}
		return ob_get_clean();
	}
}

function qc_sld_dashboard(){
	return qc_sld_dashboard::instance();
}
qc_sld_dashboard();