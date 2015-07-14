<?php
/*
Rotary Membership Data
*/
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once( dirname( __FILE__ ) . '/rotarysoapauth.php');
class RotaryDacdbMemberData extends RotaryMemberData{
	private $rotaryAuth;
	private $rotaryImageURL;
	private static $countryCodes;
	function __construct($rotaryAuth) {
		$this->rotaryAuth = $rotaryAuth;
		self::$countryCodes = array(  
			'AFG'=>'AFGHANISTAN',  
			'ALB'=>'ALBANIA',  
			'DZA'=>'ALGERIA',  
			'ASM'=>'AMERICAN SAMOA',  
			'AND'=>'ANDORRA',  
			'AGO'=>'ANGOLA',  
			'AIA'=>'ANGUILLA',  
			'ATA'=>'ANTARCTICA',  
			'ATG'=>'ANTIGUA AND BARBUDA',  
			'ARG'=>'ARGENTINA',  
			'ARM'=>'ARMENIA',  
			'ABW'=>'ARUBA',  
			'AUS'=>'AUSTRALIA',  
			'AUT'=>'AUSTRIA',  
			'AZE'=>'AZERBAIJAN',  
			'BHS'=>'BAHAMAS',  
			'BHR'=>'BAHRAIN',  
			'BGD'=>'BANGLADESH',  
			'BRB'=>'BARBADOS',  
			'BLR'=>'BELARUS',  
			'BEL'=>'BELGIUM',  
			'BLZ'=>'BELIZE',  
			'BEN'=>'BENIN',  
			'BMU'=>'BERMUDA',  
			'BTN'=>'BHUTAN',  
			'BOL'=>'BOLIVIA',  
			'BIH'=>'BOSNIA AND HERZEGOWINA',  
			'BWA'=>'BOTSWANA',  
			'BVT'=>'BOUVET ISLAND',  
			'BRA'=>'BRAZIL',  
			'IOT'=>'BRITISH INDIAN OCEAN TERRITORY',  
			'BRN'=>'BRUNEI DARUSSALAM',  
			'BGR'=>'BULGARIA',  
			'BFA'=>'BURKINA FASO',  
			'BDI'=>'BURUNDI',  
			'KHM'=>'CAMBODIA',  
			'CMR'=>'CAMEROON',  
			'CAN'=>'CANADA',  
			'CPV'=>'CAPE VERDE',  
			'CYM'=>'CAYMAN ISLANDS',  
			'CAF'=>'CENTRAL AFRICAN REPUBLIC',  
			'TCD'=>'CHAD',  
			'CHL'=>'CHILE',  
			'CHN'=>'CHINA',  
			'CXR'=>'CHRISTMAS ISLAND',  
			'CCK'=>'COCOS ISLANDS',  
			'COL'=>'COLOMBIA',  
			'COM'=>'COMOROS',  
			'COG'=>'CONGO',  
			'COD'=>'CONGO, THE DRC',  
			'COK'=>'COOK ISLANDS',  
			'CRI'=>'COSTA RICA',  
			'CIV'=>'COTE D IVOIRE',  
			'HRV'=>'CROATIA',  
			'CUB'=>'CUBA',  
			'CYP'=>'CYPRUS',  
			'CZE'=>'CZECH REPUBLIC',  
			'DNK'=>'DENMARK',  
			'DJI'=>'DJIBOUTI',  
			'DMA'=>'DOMINICA',  
			'DOM'=>'DOMINICAN REPUBLIC',  
			'TMP'=>'EAST TIMOR',  
			'ECU'=>'ECUADOR',  
			'EGY'=>'EGYPT',  
			'SLV'=>'EL SALVADOR',  
			'GNQ'=>'EQUATORIAL GUINEA',  
			'ERI'=>'ERITREA',  
			'EST'=>'ESTONIA',  
			'ETH'=>'ETHIOPIA',  
			'FLK'=>'FALKLAND ISLANDS',  
			'FRO'=>'FAROE ISLANDS',  
			'FJI'=>'FIJI',  
			'FIN'=>'FINLAND',  
			'FRA'=>'FRANCE',  
			'FXX'=>'FRANCE, METROPOLITAN',  
			'GUF'=>'FRENCH GUIANA',  
			'PYF'=>'FRENCH POLYNESIA',  
			'ATF'=>'FRENCH SOUTHERN TERRITORIES',  
			'GAB'=>'GABON',  
			'GMB'=>'GAMBIA',  
			'GEO'=>'GEORGIA',  
			'DEU'=>'GERMANY',  
			'GHA'=>'GHANA',  
			'GIB'=>'GIBRALTAR',  
			'GRC'=>'GREECE',  
			'GRL'=>'GREENLAND',  
			'GRD'=>'GRENADA',  
			'GLP'=>'GUADELOUPE',  
			'GUM'=>'GUAM',  
			'GTM'=>'GUATEMALA',  
			'GIN'=>'GUINEA',  
			'GNB'=>'GUINEA-BISSAU',  
			'GUY'=>'GUYANA',  
			'HTI'=>'HAITI',  
			'HMD'=>'HEARD AND MC DONALD ISLANDS',  
			'VAT'=>'HOLY SEE (VATICAN CITY STATE)',  
			'HND'=>'HONDURAS',  
			'HKG'=>'HONG KONG',  
			'HUN'=>'HUNGARY',  
			'ISL'=>'ICELAND',  
			'IND'=>'INDIA',  
			'IDN'=>'INDONESIA',  
			'IRN'=>'IRAN',  
			'IRQ'=>'IRAQ',  
			'IRL'=>'IRELAND',  
			'ISR'=>'ISRAEL',  
			'ITA'=>'ITALY',  
			'JAM'=>'JAMAICA',  
			'JPN'=>'JAPAN',  
			'JOR'=>'JORDAN',  
			'KAZ'=>'KAZAKHSTAN',  
			'KEN'=>'KENYA',  
			'KIR'=>'KIRIBATI',  
			'PRK'=>'D.P.R.O. KOREA',  
			'KOR'=>'REPUBLIC OF KOREA',  
			'KWT'=>'KUWAIT',  
			'KGZ'=>'KYRGYZSTAN',  
			'LAO'=>'LAOS',  
			'LVA'=>'LATVIA',  
			'LBN'=>'LEBANON',  
			'LSO'=>'LESOTHO',  
			'LBR'=>'LIBERIA',  
			'LBY'=>'LIBYAN ARAB JAMAHIRIYA',  
			'LIE'=>'LIECHTENSTEIN',  
			'LTU'=>'LITHUANIA',  
			'LUX'=>'LUXEMBOURG',  
			'MAC'=>'MACAU',  
			'MKD'=>'MACEDONIA',  
			'MDG'=>'MADAGASCAR',  
			'MWI'=>'MALAWI',  
			'MYS'=>'MALAYSIA',  
			'MDV'=>'MALDIVES',  
			'MLI'=>'MALI',  
			'MLT'=>'MALTA',  
			'MHL'=>'MARSHALL ISLANDS',  
			'MTQ'=>'MARTINIQUE',  
			'MRT'=>'MAURITANIA',  
			'MUS'=>'MAURITIUS',  
			'MYT'=>'MAYOTTE',  
			'MEX'=>'MEXICO',  
			'FSM'=>'FEDERATED STATES OF MICRONESIA',  
			'MDA'=>'REPUBLIC OF MOLDOVA',  
			'MCO'=>'MONACO',  
			'MNG'=>'MONGOLIA',  
			'MSR'=>'MONTSERRAT',  
			'MAR'=>'MOROCCO',  
			'MOZ'=>'MOZAMBIQUE',  
			'MMR'=>'MYANMAR',  
			'NAM'=>'NAMIBIA',  
			'NRU'=>'NAURU',  
			'NPL'=>'NEPAL',  
			'NLD'=>'NETHERLANDS',  
			'ANT'=>'NETHERLANDS ANTILLES',  
			'NCL'=>'NEW CALEDONIA',  
			'NZL'=>'NEW ZEALAND',  
			'NIC'=>'NICARAGUA',  
			'NER'=>'NIGER',  
			'NGA'=>'NIGERIA',  
			'NIU'=>'NIUE',  
			'NFK'=>'NORFOLK ISLAND',  
			'MNP'=>'NORTHERN MARIANA ISLANDS',  
			'NOR'=>'NORWAY',  
			'OMN'=>'OMAN',  
			'PAK'=>'PAKISTAN',  
			'PLW'=>'PALAU',  
			'PAN'=>'PANAMA',  
			'PNG'=>'PAPUA NEW GUINEA',  
			'PRY'=>'PARAGUAY',  
			'PER'=>'PERU',  
			'PHL'=>'PHILIPPINES',  
			'PCN'=>'PITCAIRN',  
			'POL'=>'POLAND',  
			'PRT'=>'PORTUGAL',  
			'PRI'=>'PUERTO RICO',  
			'QAT'=>'QATAR',  
			'REU'=>'REUNION',  
			'ROM'=>'ROMANIA',  
			'RUS'=>'RUSSIAN FEDERATION',  
			'RWA'=>'RWANDA',  
			'KNA'=>'SAINT KITTS AND NEVIS',  
			'LCA'=>'SAINT LUCIA',  
			'VCT'=>'SAINT VINCENT AND THE GRENADINES',  
			'WSM'=>'SAMOA',  
			'SMR'=>'SAN MARINO',  
			'STP'=>'SAO TOME AND PRINCIPE',  
			'SAU'=>'SAUDI ARABIA',  
			'SEN'=>'SENEGAL',  
			'SYC'=>'SEYCHELLES',  
			'SLE'=>'SIERRA LEONE',  
			'SGP'=>'SINGAPORE',  
			'SVK'=>'SLOVAKIA',  
			'SVN'=>'SLOVENIA',  
			'SLB'=>'SOLOMON ISLANDS',  
			'SOM'=>'SOMALIA',  
			'ZAF'=>'SOUTH AFRICA',  
			'SGS'=>'SOUTH GEORGIA AND SOUTH S.S.',  
			'ESP'=>'SPAIN',  
			'LKA'=>'SRI LANKA',  
			'SHN'=>'ST. HELENA',  
			'SPM'=>'ST. PIERRE AND MIQUELON',  
			'SDN'=>'SUDAN',  
			'SUR'=>'SURINAME',  
			'SJM'=>'SVALBARD AND JAN MAYEN ISLANDS',  
			'SWZ'=>'SWAZILAND',  
			'SWE'=>'SWEDEN',  
			'CHE'=>'SWITZERLAND',  
			'SYR'=>'SYRIAN ARAB REPUBLIC',  
			'TWN'=>'TAIWAN, PROVINCE OF CHINA',  
			'TJK'=>'TAJIKISTAN',  
			'TZA'=>'UNITED REPUBLIC OF TANZANIA',  
			'THA'=>'THAILAND',  
			'TGO'=>'TOGO',  
			'TKL'=>'TOKELAU',  
			'TON'=>'TONGA',  
			'TTO'=>'TRINIDAD AND TOBAGO',  
			'TUN'=>'TUNISIA',  
			'TUR'=>'TURKEY',  
			'TKM'=>'TURKMENISTAN',  
			'TCA'=>'TURKS AND CAICOS ISLANDS',  
			'TUV'=>'TUVALU',  
			'UGA'=>'UGANDA',  
			'UKR'=>'UKRAINE',  
			'ARE'=>'UNITED ARAB EMIRATES',  
			'GBR'=>'UNITED KINGDOM',  
			'USA'=>'UNITED STATES',  
			'UMI'=>'U.S. MINOR ISLANDS',  
			'URY'=>'URUGUAY',  
			'UZB'=>'UZBEKISTAN',  
			'VUT'=>'VANUATU',  
			'VEN'=>'VENEZUELA',  
			'VNM'=>'VIET NAM',  
			'VGB'=>'VIRGIN ISLANDS (BRITISH)',  
			'VIR'=>'VIRGIN ISLANDS (U.S.)',  
			'WLF'=>'WALLIS AND FUTUNA ISLANDS',  
			'ESH'=>'WESTERN SAHARA',  
			'YEM'=>'YEMEN',  
			'YUG'=>'Yugoslavia',  
			'ZMB'=>'ZAMBIA',  
			'ZWE'=>'ZIMBABWE' 
		);  
		$options = get_option('rotary_dacdb');
		$this->rotaryImageURL= 'http://www.directory-online.com/Rotary/Accounts/'.$options['rotary_dacdb_district'].'/Pics/';
		$this->getMemberData();
	}
	private function addProfilePhoto($user_id, $newUser, $value, $membername) {
		$addPhoto = false;
		$newPhoto = trim($value);
		if ($newUser ) {
			$addPhoto = true;
		}
		else {
			$currUserPhoto = basename(get_user_meta( $user_id, 'profilepicture', true));
			if (strcasecmp($currUserPhoto, $newPhoto) != 0) {
				$addPhoto = true;
			}
		}
		if ($addPhoto && $newPhoto) {
			$photoHTML = media_sideload_image($this->rotaryImageURL.$value,1,$membername);
			if(!is_wp_error($photoHTML)) {
				$doc = new DOMDocument();
				@$doc->loadHTML($photoHTML);
				$tags = $doc->getElementsByTagName('img');
				update_user_meta( $user_id, 'profilepicture', $tags->item(0)->getAttribute('src') );				
			}
		}
		
	}
	function getMemberData() {
		
		$options = get_option('rotary_dacdb');	
	 	if (false ===  get_transient('dacdb_'.$options['rotary_dacdb_club']) ||  defined( 'WP_ROTARY_MEMBERS_FORCE_UPDATE')) {
		  	$this->updateMemberData();
			set_transient('dacdb_'.$options['rotary_dacdb_club'], 'dacdb', 60*60*24*7); 
	 	}
	 	//members are updated first so that they are in place to add to committees
		if ( defined ( 'WP_ROTARY_COMMITTEES_FORCE_UPDATE' ) ) {
			$this->updateCommitteeData();
		}
	 	
	}
	function updateCommitteeData() {

		global $wpdb;		
		$todayDate = date("Y-m-d");
    	$thisMonth = date("m");
    	$dateOneYearAdded = strtotime(date("Y-m-d", strtotime($todayDate)) . "+1 year");
    	$dateOneYearSubtracted = strtotime(date("Y-m-d", strtotime($todayDate)) . "-1 year");

		if (intval($thisMonth) < 7) {
			$committeeDate = strval(date('Y', $dateOneYearSubtracted)) . '-' . strval(date("y"));
		}
		else {
			$committeeDate = strval(date("Y")) . '-' . strval(date('y', $dateOneYearAdded));
		}
		$client = $this->rotaryAuth->get_soap_client();
		$token = $this->rotaryAuth->get_soap_token();
  		$header = new SoapHeader('http://xWeb', 'Token', $token, false );
  		$client->__setSoapHeaders(array($header));  
		$options = get_option('rotary_dacdb');
  		try {
 	 		$rotaryclubcommittees = $client->Committees($options['rotary_dacdb_club'], $committeeDate, '0', 'CommitteeName');  	
  			}
   		catch (SoapFault $exception) {
  
   		echo $exception;
  		} 
        if (count($rotaryclubcommittees->COMMITTEES->COMMITTEE)) {
			$member_table_name = $wpdb->prefix . 'rotarycommittees';
			$wpdb->query('TRUNCATE TABLE '.$member_table_name);
			foreach($rotaryclubcommittees->COMMITTEES->COMMITTEE as $committee) {
				//header("Content-type: text/plain");
				//print_r($committee);
				//ob_end_flush();
				//ob_flush();
				//first try to get committee by number, this won't work at year end went committee numbers changes
				$args = array(
				'post_type' => 'rotary-committees',
				'post_status' => 'publish',
				'meta_query' => array(
						array(
							'key' => 'committeenumber',
							'value' => $committee->COMMITTEEID,
						)	
					) 
				);  
				$query = new WP_Query($args);
				//if committe post by id is found found, look for committee by name
				if (!$query->have_posts()) {
					$args = array(
						'post_type' => 'rotary-committees',
						'post_status' => 'publish',
						's' => htmlspecialchars($committee->COMMITTEENAME),
						'exact' => true, //(bool) - flag to make it only match whole titles/posts - Default value is false. For more information see: https://gist.github.com/2023628#gistcomment-285118
						'sentence' => true //(bool) - flag to make it do a phrase search - Default value is false. For more information see: https://gist.github.com/2023628#gistcomment-285118
					);  
					$query = new WP_Query($args);
				}
				//now we know that there are really no posts!	
				if (!$query->have_posts()) {
					//add committee to custom table to possibly reset status later
					$rows_affected = $wpdb->insert( $member_table_name, array('committeenum' => esc_sql( $committee->COMMITTEEID  )));
					// commenting this out because it s creating dupicate committees
					// $this->addNewCommittee($committee);
				} 
				else {
					//add committee to custom table to possibly reset status later
					while ( $query->have_posts() ) {
						$query->the_post();
						update_field('field_5351b9ef109fe', $committee->COMMITTEEID, get_the_id());
						$rows_affected = $wpdb->insert( $member_table_name, array('committeenum' => esc_sql( get_field('committeenumber')  )));
						wp_update_post( get_post(get_the_id()) );
						$this->connectMemberToCommittee($committee->COMMITTEEID, get_the_id());
					}//endwhile
				}//end check for posts
				
				
					
			}//end foreach committee
				$this->updateDeletedCommitteeStatus();
		}
	}
	function updateDeletedCommitteeStatus() {
		global $wpdb;
		$member_table_name = $wpdb->prefix . 'rotarycommittees';
		$sql = "UPDATE {$wpdb->posts}  INNER JOIN {$wpdb->postmeta} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID SET {$wpdb->posts}.post_status = 'draft' WHERE {$wpdb->posts}.post_type = 'rotary-committees' AND {$wpdb->postmeta}.meta_key = 'committeenumber' AND {$wpdb->postmeta}.meta_value != '' AND {$wpdb->postmeta}.meta_value NOT IN (SELECT committeenum FROM {$member_table_name})";
		$rows_affected = $wpdb->get_results($sql);
		

	}	
	function updateMemberData() {
		global $wpdb;
		$client = $this->rotaryAuth->get_soap_client();
		$token = $this->rotaryAuth->get_soap_token();
	  	$memberArray = array();
		
		$header = new SoapHeader('http://xWeb', 'Token', $token, false );
 		$client->__setSoapHeaders(array($header)); 
		try {	
			$rotaryclubmembers = $client->ClubMembers('0,1,5,148,154', 'UserName'); 
		}
		catch (SoapFault $exception) {
			echo $exception;	
		}
		$member_table_name = $wpdb->prefix . 'rotarymembers';
		//print_r($rotaryclubmembers->MEMBERS);
		//$wpdb->query('TRUNCATE TABLE '.$member_table_name);
  		foreach($rotaryclubmembers->MEMBERS->MEMBER as $member) {
	  		//the following is old code from when WP did not allow usernames
			if (is_email($member->LOGINNAME)) {
			  	$username = substr(trim($member->LOGINNAME), 0, strlen($member->LOGINNAME) - 4);
			 }
			 else {
				$username = $member->LOGINNAME;
			 }
			 //add to a DacDB user ids to a custom table that we check to see if a WordPress User is no longer a RotaryMember
			 $rows_affected = $wpdb->insert( $member_table_name, array('dacdbuser' => esc_sql($username)));
			 $memberArray['clubname'] = strval($member->CLUBNAME);
			 $memberArray['first_name'] = strval($member->FIRSTNAME);
			 $memberArray['last_name'] = strval($member->LASTNAME);
			 $memberArray['classification'] = strval($member->CLASSIFICATION);
			 $memberArray['partnername'] = strval($member->PARTNERFIRSTNAME);
	 		 $memberArray['cellphone'] = strval($member->CELLPHONE);
	 		 $memberArray['busphone'] = strval($member->OFFICEPHONE);
			 $memberArray['homephone'] = strval($member->HOMEPHONE);
			 $memberArray['email'] = strlen(strval($member->PREFERRED_EMAIL))  > 0 ? strval($member->PREFERRED_EMAIL)  : $username.'@hotmail.com';

			 $memberArray['anniversarydate'] = strval($member->ANNIVERSARYDATE);
			 $memberArray['streetaddress1'] = strval($member->PREFERRED_ADDRESS1);
			 $memberArray['streetaddress2'] = strval($member->PREFERRED_ADDRESS2);
			 $memberArray['city'] = strval($member->PREFERRED_CITY);
			 $memberArray['country'] =  ucwords(strtolower(self::$countryCodes[strval($member->PREFERRED_COUNTRYCODE)]));
			 $memberArray['state'] = strval($member->PREFERRED_STATECODE);
			 $memberArray['county'] = strval($member->PREFERRED_COUNTY);
			 $memberArray['zip'] = strval($member->PREFERRED_POSTALZIP);
			 $memberArray['company'] = strval($member->BUSNAME);
			 $memberArray['jobtitle'] = strval($member->BUSPOSITION);
			 $memberArray['birthday'] = strval($member->BIRTHDATE);
			 $memberArray['membersince'] = strval($member->STARTDATE);
			 $memberArray['busweb'] = strval($member->BUSWEB);
			 $memberArray['memberyesno'] = true;
			 $memberArray['profilepicture'] = strval($member->IMAGE);
			 $user_id = email_exists($memberArray['email']);
			 $newUser = false;
			 if ( !$user_id) {
				  remove_action('user_register', array($this->rotaryAuth, 'disable_function'));
				  $password = wp_generate_password( $length=12, $include_standard_special_chars=false );	
				  $user_id = wp_create_user( $username, $password, $memberArray['email'] );
				  $newUser = true;
			  }
			  else {
              	wp_update_user( array ('ID' => $user_id, 'user_email' => $memberArray['email']) ) ;
	
			  }

			  if (!is_wp_error($user_id) && 1 != $user_id ) {
				  
			  	foreach ($memberArray as $key => $value) {
				  	if ('profilepicture' == $key) {
					  	$this->addProfilePhoto($user_id, $newUser, $value, $memberArray['first_name'].' ' .$memberArray['last_name']);
				  	}
				  	else {
				  		update_user_meta( $user_id, $key, $value );
				  	}//end profilepicture if
				  
			  	}//end foreach
			  
			 }//end userid = 1 check
 		}//end foreach
		//update users who are no longer Rotary Members to change their member status
		
		//$query = 'DELETE FROM '  .$wpdb->users .' WHERE '.$wpdb->users.'.ID != 1 AND '.$wpdb->users.'.user_login NOT IN (SELECT dacdbuser FROM ' .$member_table_name.')';
		//$wpdb->query($query);
		$query = 'UPDATE '.$wpdb->usermeta .', ' . $wpdb->users   .' SET meta_value = 0 WHERE meta_key ="memberyesno" AND '.$wpdb->users.'.user_login NOT IN (SELECT dacdbuser FROM ' .$member_table_name.') AND '. $wpdb->usermeta.'.user_id = '. $wpdb->users.'.ID';
		//echo $query;
		$wpdb->query($query);
		$query = 'UPDATE '.$wpdb->usermeta .', ' . $wpdb->users   .' SET meta_value = "" WHERE meta_key ="membersince" AND '.$wpdb->users.'.user_login NOT IN (SELECT dacdbuser FROM ' .$member_table_name.') AND '. $wpdb->usermeta.'.user_id = '. $wpdb->users.'.ID';
		//echo $query;
		$wpdb->query($query);
	}
	
	//add a new committee
	function addNewCommittee($committee) {
		$new_post = array(
			'post_title' => $committee->COMMITTEENAME,
			'post_content' => '',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'rotary-committees',
			'post_category' => array(0)
		);
		$post_id = wp_insert_post($new_post);
		//add_post_meta($post_id, 'committeenumber', $committee->COMMITTEEID, true);
		update_field('field_5351b9ef109fe', $committee->COMMITTEEID, $post_id);
		$committeeDesc = str_replace("&rsquo;", "'", $committee->DESCRIPTION);
		update_field('field_5351ba0f109ff', html_entity_decode(strip_tags($committeeDesc)), $post_id);
		$this->connectMemberToCommittee($committee->COMMITTEEID, $post_id);

	}
	//Build the connection from the user (Rotary Member to the Committee) 
	//This relies on the Post2Post plugin
	function connectMemberToCommittee($committeeNumber, $post_id) {
		
		$client = $this->rotaryAuth->get_soap_client();
		$token = $this->rotaryAuth->get_soap_token();
  		$header = new SoapHeader('http://xWeb', 'Token', $token, false );
  		$client->__setSoapHeaders(array($header)); 
  		$coChairCount = 0;
  		$chairArray = array('CHAIR', 'MEMBERSHIP CHAIR');
  		$cochairArray = array('COCHAIR', 'CO-CHAIR');
		try {	
			$rotaryclubmembers = $client->CommitteeMembersByID(floatval($committeeNumber), 'UserName');
			//print_r($rotaryclubmembers);
		}
		catch (SoapFault $exception) {
			echo $exception;	
		}
		if ( is_object($rotaryclubmembers->MEMBERS) && is_array($rotaryclubmembers->MEMBERS->MEMBER )) {
			//loop through all users for a committee and delete them
					$users = get_users( array(
						'connected_type' => 'committees_to_users',
						'connected_items' => $post_id,
						'connected_direction' => 'from',
					)); 
					if ( is_array( $users )) {
						foreach ( $users as $user ) {
							p2p_type( 'committees_to_users' )->disconnect( $post_id, $user->ID );
						}
					}	

			foreach($rotaryclubmembers->MEMBERS->MEMBER as $member) {
				$user_id = email_exists($member->EMAIL);
				if ($user_id) {
					//add chair and co-chairs
					
					if (in_array(strtoupper($member->COMMITTEEPOSITION), $chairArray)) {
						update_field('field_5356d453d36ac', $member->EMAIL, $post_id);
					}
					if (in_array(strtoupper($member->COMMITTEEPOSITION), $cochairArray)) {
					   $coChairCount++;
					   if (1 == $coChairCount) {
						   update_field('field_5356d48feb36b', $member->EMAIL, $post_id);
					   }
					   else  {
						   update_field('field_5356d4dbeecc0', $member->EMAIL, $post_id);
					   }
							
					}
					//from  committees to  user
					p2p_type( 'committees_to_users' )->connect( $post_id, $user_id, array('date' => current_time('mysql')
					) );
				}
			} //end foreach
		}
	}

}/*end class*/
?>