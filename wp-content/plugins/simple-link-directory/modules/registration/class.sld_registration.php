<?php 

//Custom Registration //
class sld_custom_registration
{
	private static $instance;

	public static function instance() {
		if ( !isset( self::$instance ) ) {
			self::$instance = new sld_custom_registration();
		}
		return self::$instance;
	}
	private function __construct()
	{

		add_shortcode( 'sld_registration', array($this,'custom_registration_shortcode') );
		add_role( 'slduser', __( 'SLD User' ), array( ) );
	}
	
	public function custom_registration_shortcode() {

		ob_start();
		$this->custom_registration_function();
		return ob_get_clean();
	}
	
	public function custom_registration_function() {
		if ( isset($_POST['submit'] ) ) {
			
			$this->registration_validation(
				$_POST['username'],
				$_POST['password'],
				$_POST['email'],
				$_POST['fname'],
				$_POST['lname']
			);
			 
			// sanitize user form input
			global $username, $password, $email, $first_name, $last_name;
			$username   =   sanitize_user( $_POST['username'] );
			$password   =   esc_attr( $_POST['password'] );
			$email      =   sanitize_email( $_POST['email'] );
			
			$first_name =   sanitize_text_field( $_POST['fname'] );
			$last_name  =   sanitize_text_field( $_POST['lname'] );
		  
	 
			// call @function complete_registration to create the user
			// only when no WP_error is found
			$this->complete_registration(
				$username,
				$password,
				$email,
				$first_name,
				$last_name
			);
		}
	 
		$this->registration_form(
			@$username,
			@$password,
			@$email,
			@$first_name,
			@$last_name
		  
			
			);
	}

	public function sld_new_user_notification( $user_id, $plaintext_pass = '' ) {
		$user = new WP_User($user_id);

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);

		$message  = sprintf(__('New user registration on your blog %s:','qc-opd'), get_option('blogname')) . "\r\n\r\n";
		$message .= sprintf(__('Username: %s','qc-opd'), $user_login) . "\r\n\r\n";
		$message .= sprintf(__('E-mail: %s','qc-opd'), $user_email) . "\r\n";
		if(sld_get_option('sld_admin_email')!=''){
			@wp_mail(sld_get_option('sld_admin_email'), sprintf(__('[%s] New User Registration','qc-opd'), get_option('blogname')), $message);
		}
		

		if ( empty($plaintext_pass) )
			return;

		$message  = __('Hi,') . "\r\n\r\n";
		$message .= sprintf(__("Welcome to %s! Here's how to log in:",'qc-opd'), get_option('blogname')) . "\r\n\r\n";
		$message .= qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_login_url','') . "\r\n";
		$message .= sprintf(__('Username: %s','qc-opd'), $user_login) . "\r\n";
		//$message .= sprintf(__('Password: %s','qc-opd'), $plaintext_pass) . "\r\n\r\n";
		/*if(sld_get_option('sld_admin_email')!=''){
			$message .= sprintf(__('If you have any stuck, please contact webmaster at %s.'), sld_get_option('sld_admin_email')) . "\r\n\r\n";
		}*/
		

		wp_mail($user_email, sprintf(__('[%s] Your username and password','qc-opd'), get_option('blogname')), $message);

	}




	public function registration_validation( $username, $password, $email, $first_name, $last_name)  {
		global $reg_errors;
		$reg_errors = new WP_Error;

		if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
			$reg_errors->add('field', __('Required form field is missing','qc-opd'));
		}elseif( 4 > strlen( $username ) ){
			$reg_errors->add( 'username_length', __('Username too short. At least 4 characters is required','qc-opd') );
		}elseif( 5 > strlen( $password ) ){
			$reg_errors->add( 'password', __('Password length must be greater than 5','qc-opd') );
		}elseif( !is_email( $email ) ){
			$reg_errors->add( 'email_invalid', __('Email is not valid','qc-opd') );
		}elseif( email_exists( $email ) ){
			$reg_errors->add( 'email', __('Email Already in use','qc-opd') );
		}elseif( ! validate_username( $username ) ){
			 $reg_errors->add( 'username_invalid', __('Sorry, the username you entered is not valid','qc-opd') );
		}elseif(isset($_POST['ccode']) && strtolower($_POST['ccode'])!==strtolower($_SESSION['captcha']['code'])){
		    $reg_errors->add('captcha_invalid', __('Captcha does not match!','qc-opd'));
        }

		if ( username_exists( $username ) )
			$reg_errors->add('user_name', __('Sorry, that username already exists!','qc-opd') );



		if ( is_wp_error( $reg_errors ) ) {
		 
			foreach ( $reg_errors->get_error_messages() as $error ) {
			 
				echo '<div style="color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">';
				echo '';
				echo $error . '<br/>';
				echo '</div>';
				 
			}
		 
		}
	}
	public function complete_registration() {
		global $reg_errors, $username, $password, $email, $first_name, $last_name, $bio;
		if ( 1 > count( $reg_errors->get_error_messages() ) ) {
			$userdata = array(
				'user_login'    =>   $username,
				'user_email'    =>   $email,
				'user_pass'     =>   $password,
				'first_name'    =>   $first_name,
				'last_name'     =>   $last_name,
				
			);
			$user = wp_insert_user( $userdata );
			wp_update_user( array ('ID' => $user, 'role' => 'slduser') ) ;
            $this->sld_new_user_notification($user, $password);



	?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
		$('#sldfname').val('');
		$('#sldlname').val('');
		$('#sldemail').val('');
		$('#sldusername').val('');
		$('#sldpassword').val('');

		})
		</script>
		<?php
            if(sld_get_option('sld_enable_user_approval')=='off'){
	            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">'.__('User Information submitted! Waiting for approval.','qc-opd').'</div>.';
            }else{
	            echo '<div style="color: green;border: 1px solid green;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;">'.__('Registration Successful!','qc-opd').' <a href="' . qc_sld_login_page()->sldcustom_login_get_translated_option_page( 'sld_login_url','') . '">'.__('Go to login page','qc-opd').'</a></div>.';
            }

        }
	}
	
	public function registration_form( $username, $password, $email, $first_name, $last_name ) {

        $_SESSION['captcha'] = sld_simple_php_captcha();

		echo '
		<style>
		div {
			margin-bottom:2px;
		}
		 
		input{
			margin-bottom:4px;
		}
		</style>
		';
	 
		echo '
		<div class="cleanlogin-container">	<form autocomplete="off" class="cleanlogin-form" action="' . $_SERVER['REQUEST_URI'] . '" method="post" id="registration_form_sld">
		<h2>'. __( 'Create account', 'qc-opd' ).'</h2>
		<fieldset><div class="cleanlogin-field">
		<input class="cleanlogin-field-username" type="text" name="fname" id="sldfname" placeholder="'. __( 'First Name', 'qc-opd' ).' *" value="' . ( isset( $_POST['fname']) ? $first_name : null ) . '" required>
		<i class="fa fa-user"></i>
		</div></fieldset>
		 
		<fieldset><div class="cleanlogin-field">
	   
		<input class="cleanlogin-field-username" placeholder="'. __( 'Last Name', 'qc-opd' ).' *" type="text" name="lname" id="sldlname" value="' . ( isset( $_POST['lname']) ? $last_name : null ) . '" required>
		<i class="fa fa-user"></i>
		</div></fieldset>


		<fieldset><div class="cleanlogin-field">
		<input class="cleanlogin-field-username" placeholder="'. __( 'Username', 'qc-opd' ).' *" type="text" name="username" id="sldusername" value="' . ( isset( $_POST['username'] ) ? $username : null ) . '" required>
		<i class="fa fa-user"></i>
		</div></fieldset>
		
		<fieldset><div class="cleanlogin-field"><input type="password" style="display: none;" />
		<input class="cleanlogin-field-username" placeholder="'. __( 'Password', 'qc-opd' ).' *" type="password" name="password" id="sldpassword" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '" required>
		<i class="fa fa-lock"></i>
		</div></fieldset>
		
		<fieldset><div class="cleanlogin-field">
		<input class="cleanlogin-field-username" placeholder="'. __( 'Email', 'qc-opd' ).' *" type="email" name="email" id="sldemail" value="' . ( isset( $_POST['email']) ? $email : null ) . '" required><input type="text" style="display: none;" />
		<i class="fa fa-envelope"></i>
		</div></fieldset>
		';

		if(sld_get_option( 'sld_enable_captcha')=='on'){
            echo '<fieldset><div class="cleanlogin-field">
            <img src="'.($_SESSION['captcha']['image_src']).'" alt="Captcha Code" id="sld_captcha_image" />
            <img style="width: 24px;cursor:pointer;" id="captcha_reload" src="'.QCOPD_IMG_URL.'/captcha_reload.png" />
            <input class="cleanlogin-field-username" placeholder="'. __( 'Code', 'qc-opd' ).'" type="text" name="ccode" id="sldcode" value="" required>
            </div></fieldset>
             ';
        }

		
		echo '<fieldset style="    text-align: center; padding: 0px !important;"><div style="margin-top: 16px;margin-bottom: 0px;" class="cleanlogin-field">
		<input type="hidden" name="sldregistration" value="sld"/>
		<input type="submit" class="submit_registration" name="submit" value="'. __( 'Register', 'qc-opd' ).'"/>
		</div></fieldset>
		
		</form></div>
		';
	}
	
}

function sld_registration_page() {
	return sld_custom_registration::instance();
}
sld_registration_page();

 