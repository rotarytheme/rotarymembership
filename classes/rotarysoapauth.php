<?php
/*
	Soap Authorizations
*/
require_once(ROTARY_MEMBERSHIP_PLUGIN_PATH . '/classes/rotaryauth.php');
require_once(ROTARY_MEMBERSHIP_PLUGIN_PATH . '/classes/rotarydacdbmemberdata.php');
/**
 * Soap_Auth
 *
 * A class to authenticate wordpress users using an external SOAP service
 *  based on the SOAP Authentication plug by Matthew Kellett
 */
class RotarySoapAuth extends RotaryAuth{
	/**
 * Constructor
 * Register the functions form the Soap_Auth class within the
 * relevant sections of the system
 */
 	private $client;
	private $token;
	private $superadmin;
	private static $instance;
	function __construct() {
		$this->client = new SoapClient('http://www.directory-online.com/xWeb/DaCdb.cfc?wsdl', array('trace' => true));
		$this->token = 0;
		$this->superadmin = false;
		//add_action('wp_authenticate', array($this, 'auth_check_login'), 1, 2);
		
		add_filter( 'authenticate', array($this, 'auth_check_login'), 10, 3 );
		//add_filter( 'authenticate', array($this, 'rotary_email_login_authenticate'), 20, 3 );
		//add_filter('show_password_fields', array($this, 'soap_show_password_fields')); 
	}
	/**
	 * get_instance()
	 *
	 * 
	 * @return current static instance (singleton)
	 */
	public static function get_instance()
    {
        if (!self::$instance)
        {
			self::$instance = new RotarySoapAuth();
        }

        return self::$instance;
    } 
    /**
	 * get_soap_client()
	 *
	 * 
	 * @return current soap client
	 */
	 function get_soap_client() {
		 return $this->client;
	 }
	 /**
	 * get_soap_token()
	 *
	 * 
	 * @return current soap client
	 */
	 function get_soap_token() {
		 return $this->token;
	 }
	
	/**
	 * soap_auth_check_login()
	 *
	 * This is the main authentication function of the plugin. Given both the username and password it will
	 * make use of the options set to authenticate against an external soap service. If a user is authenticated
	 * and already exists in the system then their details will be updated, otherwise it will generate a new
	 * user and set up their permissions based on the mappings.
	 *
	 * @param string $username
	 * @param string $password
	 * @return void
	 */
	function auth_check_login($user, $username, $password) {
     		
			if($username == '' || $password == '') return;
	 
			# carry out the soap call to authenticate the user
			
            //if token is 0, the user is not valid
			if ( $this->soap_auth_validate($username, $password)) {
				if ($this->superadmin) {
					return $user;
				}
				//set the user token for more calls
				if (is_email($username)) {
			  		$email = 	$username;
			  		$username = substr(trim($username), 0, strlen($username) - 4);
			  	}
			  	else {
					//temp email until we get the rest of the data
					//used when a new user is created
				  	$email = 	$username . '@hotmail.com';
			 	}
				if ( $user_id = username_exists($username)) {
					update_user_meta( $user_id, 'rotary_user_session', $this->token);
					wp_update_user( array('user_pass' => $password) );
                    $user = new WP_User ($user_id );
				}
				else {
					remove_action('user_register', array($this, 'disable_function'));	
				  	$user_id = wp_create_user( $username, $password, $email );
				  	add_user_meta( $user_id, 'rotary_user_session', $this->token, true );
					$user = new WP_User ($user_id );
				}
					
	
				
			}
			else {
				$user = new WP_Error( 'denied', __("<strong>ERROR</strong>: Invalid User Name or Password ") );
				return $user;	
			}
			remove_filter( 'authenticate', 'wp_authenticate_username_password', 20, 3 );
			add_action('user_register', array($this, 'disable_function'));
			add_filter('login_errors', array($this, 'soap_errors'));	
			$memberData = new RotaryDacdbMemberData($this);	
			return $user;	
	}
	/**
	 * object2array()
	 *
	 * Convenient (recursive) function to convert an object to an array
	 *
	 * @param mixed $object The object to convert to an array, typically a simpleXML Object in this instance
	 * @return mixed Returns either the array or the object if it can't be converted
	 */
	private function object2array($object) {
		# ensure we are dealing with an object before converting to an array
		if (is_array($object) || is_object($object)) {
			$array = array();
			foreach($object as $key => $value){
				$array[$key] = self::Object2Array($value);
			}
			return $array;
		}
		return $object;
	}
    /**
	 * Soap_Auth::soap_auth_validate()
	 *
	 * checks against DacDB to see if user is valid
	 *
	 * @param string $username
	 * @param string $password
	 * @return true/false
	 */	
	 function soap_auth_validate($username, $password)	{
         //user id 1 is the first admin
		if (is_email($username)) {
			 $testusername = substr(trim($username), 0, strlen($username) - 4);
		}
		else {
					
			$testusername = 	$username;
		}
		 $user_id = username_exists($testusername);
		 if (1 == $user_id) {
			 $this->superadmin = true;
			 $valid = true;
		 }
		 else {
		 	$valid = false;
		 	try{
				$response = $this->client->Authenticate($username, $password);
				} catch(SoapFault $e) {
				$response = $e;
				global $error_type;
				$error_type = "soap";
				global $error_msg;
				$error_msg = "There was a problem with the soap service: " . $e->getMessage();
			}
            //if token is 0, the user is not valid
			//print_r($response);
			$options = get_option('rotary_dacdb');	
			if ( $response->AuthorizationToken->Token != 0 && $response->AuthorizationToken->ClubID == $options['rotary_dacdb_club']) {
				$this->token = $response->AuthorizationToken->Token;
				$valid = true;
			}
			else {
				$this->token = 0;
			}
		 }
		return $valid;
	 } 
	/**
	 * Soap_Auth::soap_auth_warning()
	 *
	 * Prints out a the message to be displayed on the login screen,
	 * this needs to be set up in the WP options page
	 *
	 * @return void
	 */
	function soap_auth_warning() {
		//$opts = Soap_Auth::getOptions();
		//echo "<div class=\"message\">".$opts['login_message']."</div>";
	}

	/**
	 * soap_errors()
	 *
	 * A function for building the error messages that can be used throughout the
	 * system, typically on the login page though
	 *
	 * @return string Returns the error message from the soap authentication call
	 */
	function soap_errors() {
		global $error;
		global $error_type;
		global $error_msg;

		if ($error != "") {
			$error = "<br /><br />" . $error;
		}
		if ($error_msg != "") {
			$error_msg = "<br /><br />The error returned was: " . $error_msg;
		}

		switch($error_type){
			case 'noauth':
				$error_out = "There was an error authenticating your details.".$error_msg . $error;
				break;
			case 'soap':
				$error_out = $error_msg;
				break;
			case 'nosoap':
				$error_out = $error_msg;
				break;
			default:
				$error_out = "There was an error, contact an admin".$error_msg . $error;
				break;
		} // switch

		return $error_out;
	}

	/**
	 * soap_warning()
	 *
	 * This function outputs a warning to the main user profile section to inform
	 * the users that changes to personal information will be overwritten if changed
	 * the next time they log in
	 *
	 * @return void
	 */
	function soap_warning() {
		echo '<strong style="color:red;">Any changes made below WILL NOT be preserved when you login again. You have to change your personal information per instructions found in the <a href="../wp-login.php">login box</a>.</strong>';
	}


	/**
	 * rotary_email_login_authenticate
	 *
	 * Use email as user name
	 *
	 * @return
	 */
	function rotary_email_login_authenticate( $user, $username, $password ) {
		$user = get_user_by('email', $username);
		if ( $user )
			$username = $user->user_login;
		return wp_authenticate_username_password( null, $username, $password );
	} 
}