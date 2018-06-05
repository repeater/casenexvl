<?php

class Mo_OAuth_Hanlder {
	
	function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url){
		
		$ch = curl_init($tokenendpoint);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Basic '.base64_encode($clientid.":".$clientsecret),
			'Accept: application/json'
		));
		
		curl_setopt( $ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.$clientid.'&client_secret='.$clientsecret.'&code='.$code);
		$content = curl_exec($ch);
		
		
		if(curl_error($ch)){
			echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
			exit( curl_error($ch) );
		}

		if(!is_array(json_decode($content, true))){
			echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
			exit("Invalid response received.");
		}
		
		$content = json_decode($content,true);
		if(isset($content["error_description"])){
			exit($content["error_description"]);
		} else if(isset($content["error"])){
			exit($content["error"]);
		} else if(isset($content["access_token"])) {
			$access_token = $content["access_token"];
		} else {
			echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
			exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
		}
		
		return $access_token;
	}
	
	function getResourceOwner($resourceownerdetailsurl, $access_token){

		$ch = curl_init($resourceownerdetailsurl);
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Bearer '.$access_token
				));
				
		$content = curl_exec($ch);
		
		if(curl_error($ch)){
			echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
			exit( curl_error($ch) );
		}
		
		if(!is_array(json_decode($content, true))) {
			echo "<b>Response : </b><br>";print_r($content);echo "<br><br>";
			exit("Invalid response received.");
		}
		
		$content = json_decode($content,true);
		if(isset($content["error_description"])){
			if(is_array($content["error_description"]))
				print_r($content["error_description"]);
			else
				echo $content["error_description"];
			exit;
		} else if(isset($content["error"])){
			if(is_array($content["error"]))
				print_r($content["error"]);
			else
				echo $content["error"];
			exit;
		} 
		
		return $content;
	}
	
		
}

?>