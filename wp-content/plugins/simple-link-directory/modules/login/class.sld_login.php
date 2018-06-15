<?php
/*
* Author : QuantumCloud
* class Handle Login, Password restore.
*/

class qc_sld_login
{
	private static $instance;
	public static function instance(){
		if(!isset(self::$instance)){
			self::$instance = new qc_sld_login();
		}
		return self::$instance;
	}
	private function __construct(){
		$this->loadResources();
	}
	
	/*
	* Load Resources
	*/
	
	public function loadResources(){
		
		//add_action( 'wp_enqueue_scripts', array($this,'sldcustom_login_enqueue_style') );
		add_action('template_redirect', array($this,'sldcustom_login_load_before_headers'));
		add_action( 'save_post', array($this,'sldcustom_login_get_pages_with_shortcodes') );
		add_shortcode('sld_login', array($this,'sldcustom_login_show'));
		add_shortcode('sld_restore', array($this,'sldcustom_login_restore_show'));
	}
	
	/*
	*Wp enqueue Script
	* Load stylesheet.
	*/
	public function sldcustom_login_enqueue_style(){
		wp_register_style( 'sldcustom_login-css', QCOPD_ASSETS_URL.'/css/style.css', __FILE__ );
		wp_enqueue_style( 'sldcustom_login-css' );
	}
	
	/*
	* Shortcode sldcustom_login.
	*/
	
	public function sldcustom_login_show($atts){
		ob_start();
		
		if ( isset( $_GET['authentication'] ) ) {
			if ( $_GET['authentication'] == 'success' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'Successfully logged in!', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'failed' )
				echo "<div style='color: red;border: 1px solid #e38484;margin: 2px;padding: 2px;text-align: center;margin-bottom: 8px;'>". __( 'Wrong credentials or you are not allowed to log in.', 'qc-opd' ) ."</div>";
			else if ( $_GET['authentication'] == 'logout' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'Successfully logged out!', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'failed-activation' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'Something went wrong while activating your user', 'qc-opd' ) ."</p></div>";
					else if ( $_GET['authentication'] == 'disabled' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'You are not allowed to access this area.', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['authentication'] == 'success-activation' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'Successfully activated', 'qc-opd' ) ."</p></div>";
		}

		if ( is_user_logged_in() ) {

		$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
		$current_user = wp_get_current_user();
		$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
		$show_user_information = get_option( 'cl_hideuser' ) == 'on' ? false : true;
?>
		<div class="cleanlogin-container" >
			<div class="cleanlogin-preview">
				<div class="cleanlogin-preview-top">
					<h2><?php echo __('Login', 'qc-opd') ?></h2>
				</div>
				<p style="    font-size: 14px;
			font-weight: bold;
			margin-bottom: 6px;"><?php echo __('User already logged in', 'qc-opd') ?></p>

				<a class="sld_logout_button" href="<?php echo esc_url( add_query_arg( 'action', 'logout', $login_url) ); ?>" class="cleanlogin-preview-logout-link"><?php echo __( 'Log out', 'qc-opd' ); ?></a>	
			</div>		
		</div>
<?php
			
		} else {
			
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
<?php		
		}

		return ob_get_clean();
	}
	
	/**
	 * Custom code to be loaded before headers
	 */
	public function sldcustom_login_load_before_headers() {
		global $wp_query; 
		if ( is_singular() ) { 
			$post = $wp_query->get_queried_object();
			
			// If contains any shortcode of our ones
			if ( $post && strpos($post->post_content, 'sld' ) !== false ) {

				// Sets the redirect url to the current page 
				$url = $this->sldcustom_login_url_cleaner( wp_get_referer() );
				

				// LOGIN
				if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'login' ) {
					
					$url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');

					$user = wp_signon();
					if ( is_wp_error( $user ) )
						$url = esc_url( add_query_arg( 'authentication', 'failed', $url ) );
					else {
						// if the user is not slduser.
						
						if(sld_get_option('sld_enable_anyusers')=='on'){
							$url = $this->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
						}else{
							if(!in_array('slduser',$user->roles)){
								wp_logout();
								$url = esc_url( add_query_arg( 'authentication', 'disabled', $url ) );
							}else{
								$url = $this->sldcustom_login_get_translated_option_page( 'sld_dashboard_url','');
							}
						}
						
					}
					
					
					wp_safe_redirect( $url );

				// LOGOUT
				} else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'logout' ) {
					wp_logout();
					$url = esc_url( add_query_arg( 'authentication', 'logout', $url ) );
					wp_safe_redirect( $url );
				}// RESTORE a password by sending an email with the activation link
				 else if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'restore' ) {
					$url = esc_url( add_query_arg( 'sent', 'success', $url ) );
					
					$username = isset( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
					$website = isset( $_POST['website'] ) ? sanitize_text_field( $_POST['website'] ) : '';
					// Since 1.1 (get username from email if so)
					if ( is_email( $username ) ) {
						$userFromMail = get_user_by( 'email', $username );
						if ( $userFromMail == false )
							$username = '';
						else
							$username = $userFromMail->user_login;
					}

					// honeypot detection
					if( $website != '.' )
						$url = esc_url( add_query_arg( 'sent', 'sent', $url ) );
					else if( $username == '' || !username_exists( $username ) )
						$url = esc_url( add_query_arg( 'sent', 'wronguser', $url ) );
					else {
						$user = get_user_by( 'login', $username );

						$url_msg = get_permalink();
						$url_msg = esc_url( add_query_arg( 'restore', $user->ID, $url_msg ) );
						$url_msg = wp_nonce_url( $url_msg, $user->ID );

						$email = $user->user_email;
						$blog_title = get_bloginfo();
						$message = sprintf( __( "Use the following link to restore your password: <a href='%s'>restore your password</a> <br/><br/>%s<br/>", 'qc-opd' ), $url_msg, $blog_title );
						$subject = "[$blog_title] " . __( 'Restore your password', 'qc-opd' );
						add_filter( 'wp_mail_content_type', array($this,'sldcustom_login_set_html_content_type') );
						if( !wp_mail( $email, $subject , $message ) )
							$url = esc_url( add_query_arg( 'sent', 'failed', $url ) );
						remove_filter( 'wp_mail_content_type', array($this,'sldcustom_login_set_html_content_type') );
					}
					wp_safe_redirect( $url );

				// When a user click the activation link goes here to RESTORE his/her password
				} else if ( isset( $_REQUEST['restore'] ) ) {
					$user_id = $_REQUEST['restore'];
					$retrieved_nonce = $_REQUEST['_wpnonce'];
					if ( !wp_verify_nonce($retrieved_nonce, $user_id ) )
						die( 'Failed security check, expired Activation Link due to duplication or date.' );

					$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
					
					// If edit profile page exists the user will be redirected there
					if( $edit_url != '') {
						wp_clear_auth_cookie();
						wp_set_current_user ( $user_id );
						wp_set_auth_cookie  ( $user_id );
						$url = $edit_url;

					// If not, a new password will be generated and notified
					} else {
						$url = $this->sldcustom_login_get_translated_option_page( 'sld_restore_url', '');
						// check if password complexity is checked
						$enable_passcomplex = get_option( 'sld_passcomplex' ) == 'on' ? true : false;
						
						if($enable_passcomplex)
							$new_password = wp_generate_password(12, true);
						else
							$new_password = wp_generate_password(8, false);

						$user_id = wp_update_user( array( 'ID' => $user_id, 'user_pass' => $new_password ) );

						if ( is_wp_error( $user_id ) ) {
							$url = esc_url( add_query_arg( 'sent', 'wronguser', $url ) );
						} else {
							$url = esc_url( add_query_arg( 'pass', $new_password, $url ) );
						}
					}

					wp_safe_redirect( $url );
				}
			} 
		}
	}
	

	/**
	 * [sldcustom_restore] shortcode
	 */
	function sldcustom_login_restore_show($atts) {

		ob_start();

		if ( isset( $_GET['sent'] ) ) {
			if ( $_GET['sent'] == 'success' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'You will receive an email with the activation link', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['sent'] == 'sent' )
				echo "<div class='cleanlogin-notification success'><p>". __( 'You may receive an email with the activation link', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['sent'] == 'failed' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'An error has ocurred sending the email', 'qc-opd' ) ."</p></div>";
			else if ( $_GET['sent'] == 'wronguser' )
				echo "<div class='cleanlogin-notification error'><p>". __( 'Username is not valid', 'qc-opd' ) ."</p></div>";
		}

		if ( !is_user_logged_in() ) {
			if ( isset( $_GET['pass'] ) ) {
				
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
			<?php
				
			} else{
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
<?php
			}
		} else {
			echo "<div class='cleanlogin-notification error'><p>". __( 'You are now logged in. It makes no sense to restore your account', 'qc-opd' ) ."</p></div>";
			
			$login_url = $this->sldcustom_login_get_translated_option_page( 'sld_login_url','');
			$current_user = wp_get_current_user();
			$edit_url = $this->sldcustom_login_get_translated_option_page( 'sld_edit_url', '');
			$show_user_information = get_option( 'cl_hideuser' ) == 'on' ? false : true;
?>
			<div class="cleanlogin-container" >
				<div class="cleanlogin-preview">
					<div class="cleanlogin-preview-top">
						<h2><?php echo __('Login', 'qc-opd') ?></h2>
					</div>
					<p style="    font-size: 14px;
				font-weight: bold;
				margin-bottom: 6px;"><?php echo __('User already logged in', 'qc-opd') ?></p>

					<a class="sld_logout_button" href="<?php echo esc_url( add_query_arg( 'action', 'logout', $login_url) ); ?>" class="cleanlogin-preview-logout-link"><?php echo __( 'Log out', 'qc-opd' ); ?></a>	
				</div>		
			</div>
<?php
		}

		return ob_get_clean();

	}
	
	/**
	 * Cleans an url
	 * @param url to be cleaned
	 */
	public function sldcustom_login_url_cleaner( $url ) {
		$query_args = array(
			'authentication',
			'updated',
			'created',
			'sent',
			'restore'
		);
		return esc_url( remove_query_arg( $query_args, $url ) );
	}
	
	/**
	 * SLD redirection support
	 */
	public function sldcustom_login_get_translated_option_page($page, $param = false) {
		$url = get_option($page, $param);
		//if SLD is installed get the page translation
		if (!function_exists('icl_object_id')) {
			return $url;
		} else {
			//get the page ID
			$pid = url_to_postid( $url ); 
			//set the translated urls
			return get_permalink( icl_object_id( $pid, 'page', false, ICL_LANGUAGE_CODE ) );
		}
	}
	
	
	
	/**
	 * Set email format to html
	 */
	public function sldcustom_login_set_html_content_type()
	{
		return 'text/html';
	}
	
	/**
	 * Detect shortcodes and update the plugin options
	 * @param post_id of an updated post
	 */
	function sldcustom_login_get_pages_with_shortcodes( $post_id ) {

		$revision = wp_is_post_revision( $post_id );

		if ( $revision ) $post_id = $revision;
		
		$post = get_post( $post_id );

		if ( has_shortcode( $post->post_content, 'sld_login' ) ) {
			update_option( 'sld_login_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_registration' ) ) {
			update_option( 'sld_register_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_dashboard' ) ) {
			update_option( 'sld_dashboard_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_restore' ) ) {
			update_option( 'sld_restore_url', get_permalink( $post->ID ) );
		}
		
		if ( has_shortcode( $post->post_content, 'sld_claim_listing' ) ) {
			update_option( 'sld_claim_url', get_permalink( $post->ID ) );
		}

	}
	
	
}

function qc_sld_login_page() {
	return qc_sld_login::instance();
}
qc_sld_login_page();