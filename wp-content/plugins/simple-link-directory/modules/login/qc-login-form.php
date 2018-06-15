<?php
	if ( ! defined( 'ABSPATH' ) ) exit;
	
	$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
	$register_url = $this->sldcustom_login_get_translated_option_page( 'sld_register_url', '');
	$restore_url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');
?>

<div class="cleanlogin-container">		

	<form class="cleanlogin-form" method="post" action="<?php echo $login_url;?>">
		<h2> <?php echo __('Login', 'qc-opd') ?> </h2>
		
		<fieldset>
			<div class="cleanlogin-field">
				<input class="cleanlogin-field-username" type="text" name="log" placeholder="<?php echo __( 'Username', 'qc-opd' ); ?>">
			</div>
			
			<div class="cleanlogin-field">
				<input class="cleanlogin-field-password" type="password" name="pwd" placeholder="<?php echo __( 'Password', 'qc-opd' ); ?>">
			</div>
		</fieldset>
		
		<fieldset style="text-align:center; padding:0 !important;">
			<input class="cleanlogin-field submit_registration" type="submit" value="<?php echo __( 'Log in', 'qc-opd' ); ?>" name="submit">
			<input type="hidden" name="action" value="login">
			
		</fieldset>
		
		<div class="cleanlogin-form-bottom">
			
			<div class="cleanlogin-field-remember">
				<?php 
					echo "<a style='float: right;color: #18191f ;font-weight: bold; padding-left:15px;' href='$register_url' class='cleanlogin-form-pwd-link'>". __( 'Create Account', 'qc-opd' ) ."</a>";
				?>
			</div>

			<?php 
				echo "<a style='float: right;color: #666;font-weight: bold; padding-right:15px;' href='$restore_url' class='cleanlogin-form-pwd-link'>". __( 'Forgot your password?', 'qc-opd' ) ."</a>";
			?>
						
		</div>
		

	</form>

</div>
