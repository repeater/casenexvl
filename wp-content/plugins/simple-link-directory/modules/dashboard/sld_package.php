<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$package = $wpdb->get_row("select * from $package_table where 1");
if(empty($package)){
	echo __('<p>No Package Available</p>','qc-opd');
	return;
}
$pkginfo = $wpdb->get_row("select * from $package_purchased_table where 1 and user_id = ".$current_user->ID." order by date DESC");


?>
<style type="text/css">

	strong {
		font-weight: bold;
	}

	em {
		font-style: italic;
	}

	table {
		background: #f5f5f5;
		border: 1px solid #fff !important;
		box-shadow: inset 0 1px 0 #fff;
		font-size: 12px;
		line-height: 24px;
		margin: 30px auto;
		text-align: left;
		width: 800px;
	}


	td {
		border-right: 1px solid #fff;
		border-left: 1px solid #e8e8e8;
		border-top: 1px solid #fff;
		border-bottom: 1px solid #e8e8e8;
		padding: 10px 15px;
		position: relative;
		transition: all 300ms;
	}



	td:last-child {
		border-right: 1px solid #e8e8e8;
		box-shadow: inset -1px 0 0 #fff;
	}

	tr:last-of-type td {
		box-shadow: inset 0 -1px 0 #fff;
	}

	tr:last-of-type td:first-child {
		box-shadow: inset 1px -1px 0 #fff;
	}

	tr:last-of-type td:last-child {
		box-shadow: inset -1px -1px 0 #fff;
	}
.sld_table_package_head{
	background: #474343;
	color: #fff;
	text-align: center;
}
	.sld_table_package_content{
		font-size:16px;
 }


</style>
<h2><?php echo __('Package Details', 'qc-opd') ?></h2>



<table id="sld_package_table">
	<tbody>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong><?php echo __('Title', 'qc-opd') ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->title)&&$package->title!=''?$package->title:''); ?></td>
	</tr>
    <?php
    if($package->description!=''){
    ?>
        <tr>
            <td width="30%" class="sld_table_package_head"><strong><?php echo __('Description', 'qc-opd') ?></strong></td>
            <td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->description)&&$package->description!=''?$package->description:''); ?></td>
        </tr>
    <?php
    }
    ?>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong><?php echo __('Duration', 'qc-opd') ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content">
		<?php
			echo (isset($package->duration)&&$package->duration!='lifetime'?$package->duration.' Month':ucwords($package->duration));
		?>
		
		</td>
	</tr>
	

    <tr>
        <td width="30%" class="sld_table_package_head"><strong><?php echo __('Link', 'qc-opd') ?></strong></td>
        <td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->item)&&$package->item!=''?$package->item:'0'); ?></td>
    </tr>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong><?php echo __('Price', 'qc-opd') ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content"><?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?></td>
	</tr>

	<tr>
		<td width="30%" class="sld_table_package_head"><strong><?php echo __('Currency', 'qc-opd') ?></strong></td>
		<td width="70%" align="center" class="sld_table_package_content"><?php echo $package->currency; ?></td>
	</tr>
	<tr>
		<td width="30%" class="sld_table_package_head"></td>
		<td width="70%" align="center" class="sld_table_package_content">
			<?php if(sld_get_option('sld_enable_paypal_recurring')=='on'): ?>
				<div class="">
					<span>Enable Recurring</span><input type="checkbox" name="sld_enable_recurring" id="sld_enable_recurring" value="1" />
				</div>
			<?php endif; ?>
		</td>
	</tr>
<?php

if(sld_get_option('sld_enable_paypal_test_mode')=='on'){
    $mainurl = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
}else{
    $mainurl = 'https://www.paypal.com/cgi-bin/webscr';
}



?>
    <tr>
        <td width="30%" bgcolor="#fff" style="border-left:none;" ></td>
        <td width="70%" align="center" class="sld_table_package_content">
			<?php if(sld_get_option('sld_enable_paypal_payment')!='off'): ?>
            <form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor">
                <input type="hidden" name="cmd" value="_xclick" />

                <input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                <input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
                <input type="hidden" name="no_note" value="1"/>
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="charset" value="utf-8" />

                <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array('payment'=> 'save', 'userid'=>$current_user->ID), $url ) ) ?>" />

                <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

                <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
                <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
				<input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
				
				
                <input type="hidden" name="amount" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?>">

                <input type="hidden" name="quantity" value="1">
                <input type="hidden" name="custom" value="normal">
				
                <input type="image" name="submit" border="0"  src="<?php echo QCOPD_IMG_URL.'/btn_buynow_LG.gif'; ?>" alt="PayPal - The safer, easier way to pay online">
				<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;margin-top: -6px;">Paypal</p>
            </form>
			
			<?php if(sld_get_option('sld_enable_paypal_recurring')=='on'): ?>
			<form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor_recurring" style="display:none">
                <input type="hidden" name="cmd" value="_xclick-subscriptions" />

                <input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                <input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
                <input type="hidden" name="no_note" value="1"/>
                <input type="hidden" name="no_shipping" value="1" />
                <input type="hidden" name="charset" value="utf-8" />

                <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array('payment'=> 'save', 'userid'=>$current_user->ID), $url ) ) ?>" />

                <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

                <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
                <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
				<input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
				
				
				<input type="hidden" name="a3" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?>">
				<input type="hidden" name="p3" value="<?php echo (isset($package->duration)&&$package->duration!=''?$package->duration:'0'); ?>">
				<input type="hidden" name="t3" value="M">
				<input type="hidden" name="custom" value="recurring">

				<!-- Set recurring payments until canceled. -->
				<input type="hidden" name="src" value="1">
				
                <input type="image" name="submit" border="0"  src="<?php echo QCOPD_IMG_URL.'/btn_buynow_LG.gif'; ?>" alt="PayPal - The safer, easier way to pay online">
				<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;margin-top: -6px;">Paypal</p>
            </form>
			<?php endif; ?>
			<?php endif; ?>
			
			<?php if(sld_get_option('sld_enable_stripe_payment')=="on"): ?>
				<form action="<?php echo esc_url( add_query_arg( array('payment'=> 'stripe-save', 'userid'=>$current_user->ID), $url ) ) ?>" method="post">
					<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
						  data-key="<?php echo sld_get_option('sld_stripe_public_key'); ?>"
						  data-description="<?php echo $package->title; ?>"
						  data-amount="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount*100):'0'); ?>"
						  data-locale="auto"
						  data-currency="<?php echo $package->currency; ?>"
						  ></script>
					<p style="margin: 0px 0px;padding: 0px;color: #000;font-size: 14px;">Stripe</p>
				</form>
			<?php endif; ?>
			
        </td>
    </tr>

	</tbody>
</table>







<?php
$pkglist = $wpdb->get_results("select ppt.id as pid, ppt.package_id as id, ppt.`date` as purchase_date, ppt.renew as renew_date, ppt.expire_date as expiredate, ppt.recurring,ppt.status, pt.title, pt.Amount as cost, pt.currency as currency, pt.item as total_item from $package_purchased_table as ppt, $package_table as pt where 1 and ppt.user_id = ".$current_user->ID." and ppt.package_id = pt.id order by ppt.date DESC");



?>
<h2>Package List</h2>
<?php
if(!empty($pkglist)) {
	?>
    <div class="qc_sld_table_area">
        <div class="qc_sld_table">

            <div class="qc_sld_row header">

                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Package name', 'qc-opd' ) ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Purchase Date', 'qc-opd' ); ?>
                </div>
                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Expire Date', 'qc-opd' ); ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Total Link', 'qc-opd' ); ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Remaining Link', 'qc-opd' ); ?>
                </div>
                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Cost', 'qc-opd' ); ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Status', 'qc-opd' ); ?>
                </div>

                <div class="qc_sld_cell qc_sld_table_head">
					<?php _e( 'Renew', 'qc-opd' ); ?>
                </div>
            </div>
			<?php
			$c = 0;
			foreach ( $pkglist as $row ):
				$c ++;
				?>

                <div class="qc_sld_row">



                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Package name', 'qc-opd') ?></div>
						<?php echo $row->title; ?>
                    </div>


                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Purchase Date', 'qc-opd') ?></div>
						<?php echo( date( "Y-m-d", strtotime( $row->purchase_date ) ) ); ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Expire Date', 'qc-opd') ?></div>
						
						<?php 
							if(sld_get_option('sld_enable_stripe_payment')=="on"){
								echo 'Recurring';
							}else{
								echo( date( "Y-m-d", strtotime( $row->expiredate ) ) ); 
							}
							
						
						?>
						
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Total Link', 'qc-opd') ?></div>
						<?php echo $row->total_item; ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Remain Link', 'qc-opd') ?></div>
						<?php
						$submited_item = $wpdb->get_row("select count(*)as cnt from $table where 1 and package_id= ".$row->pid." and user_id =".$current_user->ID);
						echo ($row->total_item-$submited_item->cnt);
                        ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Cost', 'qc-opd') ?></div>
						<?php echo $row->cost.' '.$row->currency; ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Status', 'qc-opd') ?></div>
	                    <?php
						if(sld_get_option('sld_enable_stripe_payment')=="on"){
							if(trim($row->status)!='cancel'){
								echo 'Active';
							}else{
								echo 'Cancel';
							}
						}else{
							if(strtotime(date('Y-m-d')) < strtotime($row->expiredate)){
								echo 'Active';
							}else{
								echo 'Expired';
							}
						}
	                    

	                    ?>
                    </div>

                    <div class="qc_sld_cell">
                        <div class="sld_responsive_head"><?php echo __('Renew', 'qc-opd') ?></div>
						<?php if(sld_get_option('sld_enable_paypal_payment')!='off'): ?>
						<?php if(sld_get_option('sld_enable_paypal_recurring')!='on'): ?>
                        <form action="<?php echo $mainurl; ?>" method="post" id="paypalProcessor">
                            <input type="hidden" name="cmd" value="_xclick" />

                            <input type="hidden" name="business" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                            <input type="hidden" name="currency_code" value="<?php echo $package->currency; ?>" />
                            <input type="hidden" name="no_note" value="1"/>
                            <input type="hidden" name="no_shipping" value="1" />
                            <input type="hidden" name="charset" value="utf-8" />

                            <input type="hidden" name="notify_url" value="<?php echo esc_url( add_query_arg( array('payment'=> 'renew', 'pkg'=> $row->pid), $url ) ) ?>" />

                            <input type="hidden" name="return" value="<?php echo esc_url( add_query_arg( 'payment', 'success', $url ) ) ?>" />

                            <input type="hidden" name="cancel_return" value="<?php echo esc_url( add_query_arg( 'payment', 'cancel', $url ) ) ?>">
                            <input type="hidden" name="item_name" value="<?php echo $package->title; ?>">
                            <input type="hidden" name="amount" value="<?php echo (isset($package->Amount)&&$package->Amount!=''?$package->Amount:'0'); ?>">

                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="receiver_email" value="<?php echo sld_get_option('sld_paypal_email'); ?>">
                            <input type="image" name="submit" border="0"  src="<?php echo QCOPD_IMG_URL.'/btn_buynow_LG.gif'; ?>" alt="PayPal - The safer, easier way to pay online">
						
                        </form>
						<?php endif; ?>
						<?php endif; ?>
						
						<?php if(sld_get_option('sld_enable_stripe_payment')=="on"): ?>
							
							<form action="<?php echo esc_url( add_query_arg( array('payment'=> 'stripe-renew', 'pkg'=> $row->pid), $url ) ) ?>" method="post">
								<script src="https://checkout.stripe.com/checkout.js" class="stripe-button"
									  data-key="<?php echo sld_get_option('sld_stripe_public_key'); ?>"
									  data-description="<?php echo $package->title; ?>"
									  data-amount="<?php echo (isset($package->Amount)&&$package->Amount!=''?($package->Amount*100):'0'); ?>"
									  data-locale="auto"	
									  data-currency="<?php echo $package->currency; ?>"									  
									  ></script>
									 
							</form>
						
						<?php endif; ?>



                    </div>

                </div>
				<?php
			endforeach;
			?>

        </div>

    </div>
	<?php
}else{
?>
    <p><?php echo __('You have no package purchased!', 'qc-opd') ?></p>
<?php
}
?>
