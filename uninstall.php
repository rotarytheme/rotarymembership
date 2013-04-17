<?php
/*
this unintalls the options and extra user data
*/
	if(defined('WP_UNINSTALL_PLUGIN') ){  
		global $wpdb;
        $table_name = $wpdb->prefix . 'rotarymembers';

		$wpdb->query("DROP TABLE IF EXISTS $table_name");
      //delete the dacDb options
      delete_option( 'rotary_dacdb' );
	  $users = get_users();
	  //delete extra user data
		foreach ($users as $user) {
			delete_user_meta( $user->ID, 'classification');
		 	delete_user_meta( $user->ID, 'clubrole');
		 	delete_user_meta( $user->ID, 'anniversarydate');
		 	delete_user_meta( $user->ID, 'partnername');
		 	delete_user_meta( $user->ID, 'homephone');
		 	delete_user_meta( $user->ID, 'businessphone');
		 	delete_user_meta( $user->ID, 'cellphone');
		 	delete_user_meta( $user->ID, 'streetaddress1');
		 	delete_user_meta( $user->ID, 'streetaddress2');
		 	delete_user_meta( $user->ID, 'city');
		 	delete_user_meta( $user->ID, 'state');
		 	delete_user_meta( $user->ID, 'county');
		 	delete_user_meta( $user->ID, 'zip');
		 	delete_user_meta( $user->ID, 'country');
		 	delete_user_meta( $user->ID, 'profilepicture');
		 	delete_user_meta( $user->ID, 'company');
		 	delete_user_meta( $user->ID, 'jobtitle');
		 	delete_user_meta( $user->ID, 'birthday');
		 	delete_user_meta( $user->ID, 'membersince');
		 	delete_user_meta( $user->ID, 'memberyesno');
		}
	  	//delete committees and connections
	 		$query = '
				DELETE FROM '.$wpdb->posts. 
				' WHERE post_type = "rotary-committees" ';
			$wpdb->query($query);
			$query = '
				DELETE FROM ' .$wpdb->postmeta. 
				' WHERE post_id NOT IN (SELECT id FROM '.$wpdb->posts.')';
			$wpdb->query($query);
		
			//delete existing user connections
			$connect_table_name = $wpdb->prefix . 'p2p';
			$wpdb->query('TRUNCATE TABLE '.$connect_table_name);
			$wpdb->query($query);
        
    }  
?>