<?php
	if ( ! defined( 'ABSPATH' ) ) exit; 
?>

<div class="cleanlogin-container">
	<form class="cleanlogin-form" method="post" action="">
		<h2><?php echo __('Reset password', 'qc-opd') ?></h2>
		<fieldset>
		
			<div class="cleanlogin-field">
				<input class="cleanlogin-field-username" type="text" name="username" value="" placeholder="<?php echo __( 'Username (or E-mail)', 'qc-opd' ) ; ?>">
			</div>

			<div class="cleanlogin-field-website">
				<label for='website'><?php echo __('Website', 'qc-opd') ?></label>
	    		<input type='text' name='website' value=".">
	    	</div>
		
		</fieldset>
		
		<div style="text-align:center">	
			<input type="submit" value="<?php echo __( 'Restore password', 'qc-opd' ); ?>" name="submit">
			<input type="hidden" name="action" value="restore">		
		</div>

	</form>
</div>