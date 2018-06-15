<?php
	if ( ! defined( 'ABSPATH' ) ) exit; 
	$new_password = sanitize_text_field( $_GET['pass'] );
	$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
?>

<div class="cleanlogin-container">
	<form class="cleanlogin-form">
		
		<fieldset>
			<div class="cleanlogin-field">
				<label><?php echo __( 'Your new password is', 'qc-opd' ); ?></label>
				<input type="text" name="pass" value="<?php echo $new_password; ?>">
			</div>
		
		</fieldset>
		
		<div class="cleanlogin-form-bottom" style="background: none;">
				
			<?php if ( $login_url != '' )
				echo "<a href='$login_url' class='cleanlogin-form-login-link sld_logout_button'>". __( 'Log in', 'qc-opd') ."</a>";
			?>
						
		</div>
	</form>
</div>