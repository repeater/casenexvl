<?php

include_once dirname( __FILE__ ) . '/eveonline/vendor/autoload.php';
use Pheal\Pheal;
use Pheal\Core\Config;

class Mo_Oauth_Widget extends WP_Widget {

	public function __construct() {
		update_option( 'host_name', 'https://auth.miniorange.com' );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles' ) );
		add_action( 'init', array( $this, 'mo_oauth_start_session' ) );
		add_action( 'wp_logout', array( $this, 'mo_oauth_end_session' ) );
		parent::__construct( 'mo_oauth_widget', 'miniOrange OAuth', array( 'description' => __( 'Login to Apps with OAuth', 'flw' ), ) );

	 }

	function mo_oauth_start_session() {
		if( ! session_id() ) {
			session_start();
		}

		if(isset($_REQUEST['option']) and $_REQUEST['option'] == 'testattrmappingconfig'){
			$mo_oauth_app_name = $_REQUEST['app'];
			wp_redirect(site_url().'?option=oauthredirect&app_name='. urlencode($mo_oauth_app_name)."&test=true");
			exit();
		}

	}

	function mo_oauth_end_session() {
		if( ! session_id() )
		{ 	session_start();
        }
		session_destroy();
	}

	public function widget( $args, $instance ) {
		extract( $args );

		echo $args['before_widget'];
		if ( ! empty( $wid_title ) ) {
			echo $args['before_title'] . $wid_title . $args['after_title'];
		}
		$this->mo_oauth_login_form();
		echo $args['after_widget'];
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['wid_title'] = strip_tags( $new_instance['wid_title'] );
		return $instance;
	}

	public function mo_oauth_login_form() {
		global $post;
		$this->error_message();
		$appsConfigured = get_option('mo_oauth_google_enable') | get_option('mo_oauth_eveonline_enable') | get_option('mo_oauth_facebook_enable');

		$appslist = get_option('mo_oauth_apps_list');
		if($appslist && sizeof($appslist)>0)
			$appsConfigured = true;

		if( ! is_user_logged_in() ) {
			
			if( $appsConfigured ) {

				$this->mo_oauth_load_login_script();

				$style = get_option('mo_oauth_icon_width') ? "width:".get_option('mo_oauth_icon_width').";" : "";
				$style .= get_option('mo_oauth_icon_height') ? "height:".get_option('mo_oauth_icon_height').";" : "";
				$style .= get_option('mo_oauth_icon_margin') ? "margin:".get_option('mo_oauth_icon_margin').";" : "";
				$custom_css = get_option('mo_oauth_icon_configure_css');
				if(empty($custom_css))
					echo '<style>.oauthloginbutton{background: #7272dc;height:40px;padding:8px;text-align:center;color:#fff;}</style>';
				else
					echo '<style>'.$custom_css.'</style>';

				if( get_option('mo_oauth_google_enable') ) {
				?>

				<a href="javascript:void(0)" onClick="moOAuthLogin('google');"><img src="<?php echo plugins_url( 'images/icons/google.jpg', __FILE__ )?>"></a>

				<?php
				}
				if( get_option('mo_oauth_eveonline_enable') ) { ?>
					<a href="javascript:void(0)" onClick="moOAuthLogin('eveonline');"><img style="<?php echo $style;?>" src="<?php echo plugins_url( 'images/icons/eveonline.png', __FILE__ )?>"></a>
				<?php }
				if( get_option('mo_oauth_facebook_enable') ) { ?>
					<a href="javascript:void(0)" onClick="moOAuthLogin('facebook');"><img src="<?php echo plugins_url( 'images/icons/facebook.png', __FILE__ )?>"></a> <?php
				}
				
				if (is_array($appslist)) {
					foreach($appslist as $key=>$app){
						if($key=="eveonline")
							continue;
							$imageurl = "";
						if($key=='facebook')
							$imageurl = plugins_url( 'images/fblogin.png', __FILE__ );
						else if($key=='google')
							$imageurl = plugins_url( 'images/googlelogin.png', __FILE__ );
						else if($key=='windows')
							$imageurl = plugins_url( 'images/windowslogin.png', __FILE__ );

						if(!empty($imageurl) && empty($custom_css)){
						?><br/><div><a href="javascript:void(0)" onClick="moOAuthLoginNew('<?php echo $key;?>');"><img style="<?php echo $style;?>" src="<?php echo $imageurl; ?>"></a></div><?php
						} else { 
							$appclass = "oauth_app_".str_replace(" ","-",$key);
							echo '<br/><a href="javascript:void(0)" onClick="moOAuthLoginNew(\''.$key.'\');"><div  style="'.$style.'" class="oauthloginbutton '.$appclass.'">';
							if (array_key_exists('displayappname', $app) && !empty($app['displayappname']) ) {
								echo $app['displayappname'];
							} else {
								echo 'Login with '.ucwords($key);
							}
							echo '</div></a>';
						}

					}
				}


			} else {
				?>
				<div>No apps configured.</div>
				<?php
			}
			?>

			<?php
		} else {
			$current_user = wp_get_current_user();
			$link_with_username = __('Howdy, ', 'flw') . $current_user->display_name;
			?>
			<div id="logged_in_user" class="login_wid">
				<li><?php echo $link_with_username;?> | <a href="<?php echo wp_logout_url( site_url() ); ?>" title="<?php _e('Logout','flw');?>"><?php _e('Logout','flw');?></a></li>
			</div>
			<?php
		}
	}

	private function mo_oauth_load_login_script() {
	?>
	<script type="text/javascript">

		function HandlePopupResult(result) {
			window.location.href = result;
		}

		function moOAuthLogin(app_name) {
			window.location.href = '<?php echo site_url() ?>' + '/?option=generateDynmicUrl&app_name=' + app_name;
		}
		function moOAuthLoginNew(app_name) {
			//window.location.href = '<?php echo site_url() ?>' + '/?option=oauthredirect&app_name=' + app_name;
			var myWindow = window.open('<?php echo site_url() ?>' + '/?option=oauthredirect&app_name=' + app_name, "", "width=500,height=500");
		}
	</script>
	<?php
	}



	public function error_message() {
		if( isset( $_SESSION['msg'] ) and $_SESSION['msg'] ) {
			echo '<div class="' . $_SESSION['msg_class'] . '">' . $_SESSION['msg'] . '</div>';
			unset( $_SESSION['msg'] );
			unset( $_SESSION['msg_class'] );
		}
	}

	public function register_plugin_styles() {
		wp_enqueue_style( 'style_login_widget', plugins_url( 'css/style_login_widget.css', __FILE__ ) );
	}


}
	function mo_oauth_login_validate(){

		/* Handle Eve Online old flow */
		if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oauthredirect' ) !== false ) {
			$appname = $_REQUEST['app_name'];
			$appslist = get_option('mo_oauth_apps_list');

			if(isset($_REQUEST['test']))
				setcookie("mo_oauth_test", true);
			else
				setcookie("mo_oauth_test", false);

			foreach($appslist as $key => $app){
				if($appname==$key){

					$state = base64_encode($appname);
					$authorizationUrl = $app['authorizeurl'];
					if(strpos($authorizationUrl, '?' ) !== false)
					$authorizationUrl = $authorizationUrl."&client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code&state=".$state;
				    else 
					$authorizationUrl = $authorizationUrl."?client_id=".$app['clientid']."&scope=".$app['scope']."&redirect_uri=".$app['redirecturi']."&response_type=code&state=".$state;

					if(session_id() == '' || !isset($_SESSION))
						session_start();
					$_SESSION['oauth2state'] = $state;
					$_SESSION['appname'] = $appname;

					header('Location: ' . $authorizationUrl);
					exit;
				}
			}
		}

		else if(strpos($_SERVER['REQUEST_URI'], "/oauthcallback") !== false) {  //if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'oauthcallback' ) !== false ) {

			if(session_id() == '' || !isset($_SESSION))
				session_start();

			// OAuth state security check
			/*
			if (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {
				if (isset($_SESSION['oauth2state'])) {
					unset($_SESSION['oauth2state']);
				}
				exit('Invalid state');
			} */

			if (!isset($_GET['code'])){
				if(isset($_GET['error_description']))
					exit($_GET['error_description']);
				else if(isset($_GET['error']))
					exit($_GET['error']);
				exit('Invalid response');
			} else {

				try {

					$currentappname = "";

					if (isset($_SESSION['appname']) && !empty($_SESSION['appname']))
						$currentappname = $_SESSION['appname'];
					else if (isset($_GET['state']) && !empty($_GET['state'])){
						$currentappname = base64_decode($_GET['state']);
					}

					if (empty($currentappname)) {
						exit('No request found for this application.');
					}

					$appslist = get_option('mo_oauth_apps_list');
					$name_attr = "";
					$email_attr = "";
					$currentapp = false;
					foreach($appslist as $key => $app){
						if($key == $currentappname){
							$currentapp = $app;
							if(isset($app['email_attr'])){
								$email_attr = $app['email_attr'];
							}
							if(isset($app['name_attr'])){
								$name_attr = $app['name_attr'];
							}
						}
					}

					if (!$currentapp)
						exit('Application not configured.');

					$mo_oauth_handler = new Mo_OAuth_Hanlder();
					$accessToken = $mo_oauth_handler->getAccessToken($currentapp['accesstokenurl'], 'authorization_code',
						$currentapp['clientid'], $currentapp['clientsecret'], $_GET['code'], $currentapp['redirecturi']);

					if(!$accessToken)
						exit('Invalid token received.');

					$resourceownerdetailsurl = $currentapp['resourceownerdetailsurl'];
					if (substr($resourceownerdetailsurl, -1) == "=") {
						$resourceownerdetailsurl .= $accessToken;
					}
					$resourceOwner = $mo_oauth_handler->getResourceOwner($resourceownerdetailsurl, $accessToken);

					$email = "";
					$name = "";
					if($currentappname == "google"){
						if(isset($resourceOwner['emails'])){
							foreach($resourceOwner['emails'] as $email){
								if(isset($email['value'])){
									$email = $email['value'];
									break;
								}
							}
						}
						if(isset($resourceOwner['displayName']))
							$name = $resourceOwner['displayName'];
					} else if($currentappname == "facebook"){
						if(isset($resourceOwner['email']))
							$email = $resourceOwner['email'];
						if(isset($resourceOwner['name']))
							$name = $resourceOwner['name'];
					}  else if($currentappname == "windows"){
						if(isset($resourceOwner['emails']['preferred']))
							$email = $resourceOwner['emails']['preferred'];
						if(isset($resourceOwner['name']))
							$name = $resourceOwner['name'];
					} else {

						//TEST Configuration
						if(isset($_COOKIE['mo_oauth_test']) && $_COOKIE['mo_oauth_test']){
							echo '<style>table{border-collapse: collapse;}table, td, th {border: 1px solid black;padding:4px}</style>';
							echo "<h2>Test Configuration</h2><table><tr><th>Attribute Name</th><th>Attribute Value</th></tr>";
							testattrmappingconfig("",$resourceOwner);
							echo "</table>";
							exit();
						}

						if(!empty($email_attr))
							$email = getnestedattribute($resourceOwner, $email_attr); //$resourceOwner[$email_attr];
						if(!empty($name_attr))
							$name = getnestedattribute($resourceOwner, $name_attr); //$resourceOwner[$name_attr];

					}

					if(empty($email))
						exit('Email address not received. Check your <b>Attribute Mapping</b> configuration.');

					$user = get_user_by("login",$email);
					if(!$user)
						$user = get_user_by( 'email', $email);

					if($user){
						$user_id = $user->ID;
					} else {
						$random_password = wp_generate_password( 10, false );
						
						if(is_email($email))
							$user_id = wp_create_user( $email, $random_password, $email );
						else
							$user_id = wp_create_user( $email, $random_password);
						
						$user = get_user_by( 'login', $email);
						
						wp_update_user( array( 'ID' => $user_id, 'first_name' => $name ) );
						wp_update_user( array( 'ID' => $user_id, 'last_name' => '' ) );
					}

					if($user_id){
						wp_set_current_user($user_id);
						wp_set_auth_cookie($user_id);
						$user  = get_user_by( 'ID',$user_id );
						do_action( 'wp_login', $user->user_login, $user );
						//wp_redirect(home_url());

						$relaystate = home_url();
						echo '<script>window.opener.HandlePopupResult("'.$relaystate.'");window.close();</script>';
						exit;

					}


				} catch (Exception $e) {

					// Failed to get the access token or user details.
					//print_r($e);
					exit($e->getMessage());

				}

			}

		} else if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'generateDynmicUrl' ) !== false ) {
			$client_id = get_option('mo_oauth_' . $_REQUEST['app_name'] . '_client_id');
			$timestamp = round( microtime(true) * 1000 );
			$api_key = get_option('mo_oauth_admin_api_key');
			$token = $client_id . ':' . number_format($timestamp, 0, '', '') . ':' . $api_key;

			$customer_token = get_option('customer_token');
			$method = 'AES-128-ECB';
			$ivSize = openssl_cipher_iv_length($method);
			$iv     = openssl_random_pseudo_bytes($ivSize);
			$token_params_encrypt = openssl_encrypt ($token, $method, $customer_token,OPENSSL_RAW_DATA||OPENSSL_ZERO_PADDING, $iv);
			$token_params_encode = base64_encode( $iv.$token_params_encrypt );
			$token_params = urlencode( $token_params_encode );

			$return_url = urlencode( site_url() . '/?option=mooauth' );
			$url = get_option('host_name') . '/moas/oauth/client/authorize?token=' . $token_params . '&id=' . get_option('mo_oauth_admin_customer_key') . '&encrypted=true&app=' . $_REQUEST['app_name'] . '_oauth&returnurl=' . $return_url;
			wp_redirect( $url );
			exit;
		} else if( isset( $_REQUEST['option'] ) and strpos( $_REQUEST['option'], 'mooauth' ) !== false ){

			//do stuff after returning from oAuth processing
			$access_token 	= $_POST['access_token'];
			$token_type	 	= $_POST['token_type'];
			$user_email = '';
			if(array_key_exists('email', $_POST))
				$user_email 	= $_POST['email'];


			if( $user_email ) {
				if( email_exists( $user_email ) ) { // user is a member
					  $user 	= get_user_by('email', $user_email );
					  $user_id 	= $user->ID;
					  wp_set_auth_cookie( $user_id, true );
				} else { // this user is a guest
					  $random_password 	= wp_generate_password( 10, false );
					  $user_id 			= wp_create_user( $user_email, $random_password, $user_email );
					  wp_set_auth_cookie( $user_id, true );
				}
			} else if( $_POST['CharacterID'] ) {		//the user is trying to login through eve online
				$_SESSION['character_id'] = $_POST['CharacterID'];
				$_SESSION['character_name'] = $_POST['CharacterName'];
				Config::getInstance()->access = new \Pheal\Access\StaticCheck();

				$keyID = get_option('mo_eve_api_key');
				$vCode = get_option('mo_eve_verification_code');
				if( $keyID && $vCode ) {

					$pheal = new Pheal( $keyID, $vCode, "eve" );

					try{
						$response = $pheal->CharacterInfo(array("characterID" => $_SESSION['character_id']));
						$_SESSION['corporation_name']	= $response->corporation;
						$_SESSION['alliance_name'] 		= $response->alliance;
					} catch (\Pheal\Exceptions\PhealException $e) {
						/*echo sprintf(
							"an exception was caught! Type: %s Message: %s",
							get_class($e),
							$e->getMessage()
						);*/
					}

					$corporations 	= get_option('mo_eve_allowed_corps') ? get_option('mo_eve_allowed_corps') : false;
					$alliances 		= get_option('mo_eve_allowed_alliances') ? get_option('mo_eve_allowed_alliances') : false;
					$characterNames = get_option('mo_eve_allowed_char_name') ? get_option('mo_eve_allowed_char_name') : false;
					$valid_char 	= false;

					if( ! $corporations && ! $alliances && ! $characterNames ) {
						$valid_char = true;
					} else {
						if(isset($_SESSION['corporation_name']))
							$valid_corp 			= mo_oauth_check_validity_of_entity(get_option('mo_eve_allowed_corps'), $_SESSION['corporation_name'], 'corporation_name');
						else
							$valid_corp = "";
						if(isset($_SESSION['alliance_name']))
							$valid_alliance = mo_oauth_check_validity_of_entity(get_option('mo_eve_allowed_alliances'), $_SESSION['alliance_name'], 'alliance_name');
						else
							$valid_alliance = "";
						if(isset($_SESSION['character_name']))
							$valid_character_name 	= mo_oauth_check_validity_of_entity(get_option('mo_eve_allowed_char_name'), $_SESSION['character_name'], 'character_name');
						else
							$character_name = "";

						$valid_char = $valid_corp || $valid_alliance || $valid_character_name;
					}
					if( $valid_char ) {			//if corporation or alliance or character name is valid
						$characterID = $_SESSION['character_id'];
						$eveonline_email = $characterID . '.eveonline@wordpress.com';
						if( username_exists( $characterID ) ) {
							$user = get_user_by( 'login', $characterID );
							$user_id = $user->ID;

							update_user_meta( $user_id, 'user_eveonline_corporation_name', $_SESSION['corporation_name'] );
							update_user_meta( $user_id, 'user_eveonline_alliance_name', $_SESSION['alliance_name'] );
							update_user_meta( $user_id, 'user_eveonline_character_name', $_SESSION['character_name'] );
							set_avatar( $user_id, $characterID );
							wp_set_auth_cookie( $user_id, true );
						} else {
							$random_password = wp_generate_password( 10, false );
							$userdata = array(
								'user_login'	=>	$characterID,
								'user_email'	=>	$eveonline_email,
								'user_pass'		=>	$random_password,
								'display_name'	=>	$_SESSION['character_name'],
								'last_name'		=>	$_SESSION['character_name']
							);

							$user_id = wp_insert_user( $userdata ) ;
							update_user_meta($user_id, 'user_eveonline_corporation_name', $_SESSION['corporation_name']);
							update_user_meta($user_id, 'user_eveonline_alliance_name', $_SESSION['alliance_name']);
							update_user_meta($user_id, 'user_eveonline_character_name', $_SESSION['character_name']);
							set_avatar( $user_id, $characterID );
							wp_set_auth_cookie( $user_id, true );
						}
					} else{
						error_reporting(0);
						?>
						<table>

								<div class="rectangle" style="width:700px; height:180px;  margin:5% auto;">
								<h1 style="text-align:center">Access Denied!</h1>
								<div style="font-size:22px; color:#222;padding:20px;text-align:center;background:#F1F1F1;border:1.5px solid grey;  box-shadow: 10px 10px 5px grey;">It seems that either of your Corporation, Alliance or Character Name is not allowed to access this site.<br><br>
								Please contact site Administrator to get access.<br></div>
								</div>

						</table>
						<?php
						exit();
					}
				} else {
					// If API and vCode is not setup - login the user using Character ID
					$characterID = $_SESSION['character_id'];
					$eveonline_email = $characterID . '.eveonline@wordpress.com';
					if( username_exists( $characterID ) ) {
						$user = get_user_by( 'login', $characterID );
						$user_id = $user->ID;
						update_user_meta( $user_id, 'user_eveonline_character_name', $_SESSION['character_name'] );
						set_avatar( $user_id, $characterID );
						wp_set_auth_cookie( $user_id, true );
					} else {
						$random_password = wp_generate_password( 10, false );
						$userdata = array(
							'user_login'	=>	$characterID,
							'user_email'	=>	$eveonline_email,
							'user_pass'		=>	$random_password,
							'display_name'	=>	$_SESSION['character_name'],
							'last_name'		=>	$_SESSION['character_name']
						);
						$user_id = wp_insert_user( $userdata ) ;
						update_user_meta( $user_id, 'user_eveonline_character_name', $_SESSION['character_name'] );
						set_avatar( $user_id, $characterID );
						wp_set_auth_cookie( $user_id, true );
					}
				}
			}
			wp_redirect( home_url() );
			exit;
		}
		/* End of old flow */
	}

	//here entity is corporation, alliance or character name. The administrator compares these when user logs in
	function mo_oauth_check_validity_of_entity($entityValue, $entitySessionValue, $entityName) {

		$entityString = $entityValue ? $entityValue : false;
		$valid_entity = false;
		if( $entityString ) {			//checks if entityString is defined
			if ( strpos( $entityString, ',' ) !== false ) {			//checks if there are more than 1 entity defined
				$entity_list = array_map( 'trim', explode( ",", $entityString ) );
				foreach( $entity_list as $entity ) {			//checks for each entity to exist
					if( $entity == $entitySessionValue ) {
						$valid_entity = true;
						break;
					}
				}
			} else {		//only one entity is defined
				if( $entityString == $entitySessionValue ) {
					$valid_entity = true;
				}
			}
		} else {			//entity is not defined
			$valid_entity = false;
		}
		return $valid_entity;
	}

	function testattrmappingconfig($nestedprefix, $resourceOwnerDetails){
		foreach($resourceOwnerDetails as $key => $resource){
			if(is_array($resource) || is_object($resource)){
				if(!empty($nestedprefix))
					$nestedprefix .= ".";
				testattrmappingconfig($nestedprefix.$key,$resource);
			} else {
				echo "<tr><td>";
				if(!empty($nestedprefix))
					echo $nestedprefix.".";
				echo $key."</td><td>".$resource."</td></tr>";
			}
		}
	}

	function getnestedattribute($resource, $key){
		//echo $key." : ";print_r($resource); echo "<br>";
		if(empty($key))
			return "";

		$keys = explode(".",$key);
		if(sizeof($keys)>1){
			$current_key = $keys[0];
			if(isset($resource[$current_key]))
				return getnestedattribute($resource[$current_key], str_replace($current_key.".","",$key));
		} else {
			$current_key = $keys[0];
			if(isset($resource[$current_key]))
				return $resource[$current_key];
		}
	}

	function register_mo_oauth_widget() {
		register_widget('mo_oauth_widget');
	}

	add_action('widgets_init', 'register_mo_oauth_widget');
	add_action( 'init', 'mo_oauth_login_validate' );
?>