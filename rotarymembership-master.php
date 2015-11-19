<?php
/*
Plugin Name: Rotary Membership
Description: This is a plugin for Rotary Clubs to Maintain Membership from DacDB. This plugin auto updates from github.
Version: 2.179
Author: Merrill M. Mayer
Author URI: http://www.koolkatwebdesigns.com/
License: GPL2
*/
// Set path to theme specific functions
define( 'ACF_LITE' , true );
define( 'ROTARY_MEMBERSHIP_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'ROTARY_MEMBERSHIP_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'ROTARY_MEMBERSHIP_PLUGIN_FILE', plugin_basename( __FILE__ ) );
include_once('advanced-custom-fields/acf.php' );
include_once('acf-repeater/acf-repeater.php');
include_once($includes_path . 'committee-fields.php');
require_once(ROTARY_MEMBERSHIP_PLUGIN_PATH . '/classes/rotaryprofiles.php');
require_once(ROTARY_MEMBERSHIP_PLUGIN_PATH . '/classes/rotarymemberdata.php');
require_once(ROTARY_MEMBERSHIP_PLUGIN_PATH . '/classes/rotarydacdbmemberdata.php');
require_once(ROTARY_MEMBERSHIP_PLUGIN_PATH . '/classes/rotarysoapauth.php');
require_once('class-tgm-plugin-activation.php'); 
include_once('rotarypluginupdater.php');
class RotaryMembership {
	private $rotaryProfiles;
	private $rotaryAuth;

	function __construct() {	
		register_activation_hook( __FILE__, array($this,'activate') );
		//register_deactivation_hook( __FILE__, array($this,'deactivate') );
		add_action( 'admin_init', array( $this, 'addOptions' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_and_styles'));
		$this->rotaryProfiles = new RotaryProfiles();
		$options = get_option('rotary_dacdb');
		if ('yes' == $options['rotary_use_dacdb']) {
			$this->rotaryAuth = RotarySoapAuth::get_instance(); 
			//$this->rotaryProfiles->getUsers(new RotaryDacdbMemberData($this->rotaryAuth));
		}
		add_action('init', array($this, 'register_commitee_post_type'));
		add_action('init', array($this, 'register_project_post_type'));
		add_action( 'p2p_init', array($this, 'rotary_connection_types' ));
		add_shortcode( 'MEMBER_DIRECTORY', array($this, 'get_rotary_club_members') );
		add_shortcode( 'DIRECTORY', array($this, 'get_rotary_club_members') );
		add_action('init', array($this, 'register_script_for_shortcodes') );
		add_action('template_redirect', array($this, 'enqueue_scripts_for_shortcodes') );
		//ajax to get members
		add_action( 'wp_ajax_nopriv_rotarymembers', array($this, 'rotary_get_members' ));
		add_action( 'wp_ajax_rotarymembers', array($this, 'rotary_get_members' ));
		add_action( 'wp_ajax_nopriv_rotaryform', array($this, 'rotary_get_form_entries' ));
		add_action( 'wp_ajax_rotaryform', array($this, 'rotary_get_form_entries' ));
		add_action( 'wp_ajax_nopriv_projectmembers', array($this, 'rotary_add_project_members' ));
		add_action( 'wp_ajax_projectmembers', array($this, 'rotary_add_project_members' ));
		add_action( 'wp_ajax_nopriv_deleteprojectmember', array($this, 'rotary_delete_project_member' ));
		add_action( 'wp_ajax_deleteprojectmember', array($this, 'rotary_delete_project_member' ));
		add_action( 'wp_ajax_nopriv_rotarymemberdetails', array($this, 'rotary_get_member_details' ));
		add_action( 'wp_ajax_rotarymemberdetails', array($this, 'rotary_get_member_details' ));	
		$this->setup_plugin_updates();
	}
	//activation creates a table to store rotary members user id from DacDb. 
	//This will be used to delete users that are no longer Rotary members
	//the same will be done for committees
	function activate() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
   		$table_name = $wpdb->prefix . 'rotarymembers';
		$sql = 'CREATE TABLE ' . $table_name .'(
     		id int(11) unsigned NOT NULL auto_increment,
			dacdbuser varchar(60),
     		PRIMARY KEY  (id)
  		);';
		
  		dbDelta($sql); 
  		
  		$table_name = $wpdb->prefix . 'rotarycommittees';
  		$wpdb->query("DROP TABLE IF EXISTS $table_name");
  		$sql = 'CREATE TABLE ' . $table_name .'(
     		id int(11) unsigned NOT NULL auto_increment,
			committeenum int(11),
     		PRIMARY KEY  (id)
  		);';
  		dbDelta($sql); 
  	}
	function deactivate() {
		
	}
	function setup_plugin_updates() {
		if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
			$config = array(
				'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
				'proper_folder_name' => 'rotarymembership', // this is the name of the folder your plugin lives in
				'api_url' => 'https://api.github.com/repos/rotarytheme/rotarymembership', // the github API url of your github repo
				'raw_url' => 'https://raw.github.com/rotarytheme/rotarymembership/master', // the github raw url of your github repo
				'github_url' => 'https://github.com/rotarytheme/rotarymembership', // the github url of your github repo
				'zip_url' => 'https://github.com/rotarytheme/rotarymembership/zipball/master', // the zip url of the github repo
				'sslverify' => true, // wether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
				'requires' => '3.5', // which version of WordPress does your plugin require?
				'tested' => '3.5.1', // which version of WordPress is your plugin tested up to?
				'readme' => 'README.md' // which file to use as the readme for the version number
			);
			new WP_GitHub_Updater($config);
		}
	}
	/*
	  register if DaCdb should be used in the general settings
	*/
    function addOptions() {
		//register a new setting for DacDb along with a validate callback
		register_setting('general', 'rotary_dacdb', array($this, 'validate_settings'));
		//add a section for DacDb to the general page
		add_settings_section('rotary_settings_section', 'Rotary Options', array($this, 'rotary_settings_page'), 'general' ); 
		//add fields for DacDb to the section just added to the general page
		add_settings_field('rotary_use_dacdb', 'Use DacDb for membership?', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_use_dacdb'));
		add_settings_field('rotary_instructions', '', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_instructions'));
		
		add_settings_field('rotary_dacdb_district', '<span class="dacdb">Rotary District Number</span>', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_dacdb_district'));
		add_settings_field('rotary_dacdb_club', '<span class="dacdb">Rotary/Rotaract Club Number</span>', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_dacdb_club'));
		add_settings_field('rotary_dacdb_club_name', '<span class="nodacdb">Rotary/Rotaract Club Name</span>', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_dacdb_club_name'));
		//add filter to add a setup link for the plugin on the plugin page
		add_filter('plugin_action_links_'. ROTARY_MEMBERSHIP_PLUGIN_FILE, array($this, 'rotary_base_plugin_link'), 10, 4);
	}
	
	/*
	  UI for DaCdb settings
	*/
    	
	function rotary_settings_page() {
		echo '<p>Rotary Membership</p>';  
	}
	function rotary_form_field($args) {
		$currFieldName = $args['fieldName'];
		$options = get_option('rotary_dacdb');
		if ('yes' == $options['rotary_use_dacdb']) {
			$disabled = '';
		}
		switch ($currFieldName) {
			case 'rotary_use_dacdb':
				$yeschecked = '';
				$nochecked = '';
				if ('yes' == $options['rotary_use_dacdb']) {
					$yeschecked = 'checked="checked"';
					$noschecked = '';
				}
				else {
					$noschecked = 'checked="checked"';
					$yeschecked = '';
				}
				$useDacDb = '<p id="rotary_use_dacdb">Yes <input type="radio" name="rotary_dacdb[rotary_use_dacdb]" value="yes" '.$yeschecked.' />' .
				' No <input type="radio" name="rotary_dacdb[rotary_use_dacdb]" value="no"  '.$noschecked.' /></p>' ;
				echo $useDacDb;		
				break;
			case 'rotary_dacdb_district':
			   $dacdbDistrict = '<input type="number" class="dacdb" name="rotary_dacdb[rotary_dacdb_district]" id="rotary_dacdb_district" value="'.esc_attr( $options['rotary_dacdb_district'] ) .'" class="regular-text"/>';
			   echo $dacdbDistrict;
				break;
			case 'rotary_dacdb_club':
				$dacdbClub = '<input type="number" class="dacdb" name="rotary_dacdb[rotary_dacdb_club]" id="rotary_dacdb_club" value="'.esc_attr( $options['rotary_dacdb_club'] ) .'" class="regular-text"/>';
				echo $dacdbClub;
				break;
			case 'rotary_dacdb_club_name':
			   $dacdbClubName = '<input type="text" class="nodacdb" name="rotary_dacdb[rotary_dacdb_club_name]" id="rotary_dacdb_club_name" value="'.esc_attr( $options['rotary_dacdb_club_name'] ) .'" class="regular-text"/>';
				echo $dacdbClubName;
			   	break;
			case 'rotary_instructions':
				echo '<p id="rotary_instructions" class="dacdb">Changes will take effect after you log out and then log back in with your <strong>DacDb</strong> username and password</p>';
				break;   			
		}
	}
	/*
	   adds a link from the plugin the the general settings area
	*/
	function rotary_base_plugin_link($actions, $plugin_file) {
		static $this_plugin;
		if( !$this_plugin ) {
			 $this_plugin = ROTARY_MEMBERSHIP_PLUGIN_FILE;
		}
		if( $plugin_file == $this_plugin ){
			$settingsLink = '<a href="'. admin_url('options-general.php#rotary_use_dacdb'). '">Setup</a>';
				return array_merge(
				array(
					'settings' => $settingsLink
				),
				$actions
			);
		}
	}
	function enqueue_scripts_and_styles() {
		wp_enqueue_script( 'rotarymembership', plugins_url('/js/rotarymembership.js', __FILE__) );
        wp_enqueue_media();
    	wp_enqueue_script( 'jquery-ui-datepicker');
    	wp_enqueue_script( 'jquery-ui-dialog');
        wp_register_style('rotary-style', plugins_url('/css/rotarymembership.css', __FILE__),false, 0.1);
		wp_enqueue_style( 'rotary-style' );
		
		
	}
	function validate_settings($input) {
		// var_dump($input);
   		//exit;
		if (!current_user_can('install_plugins')) {
		   	add_settings_error('rotary_dacdb', '100','You cannot install this plugin','error');
			return false;
		}
		else {
			$clean = array();
			if ('yes' == $input['rotary_use_dacdb']) {
				$clean[0] = absint(strip_tags($input['rotary_dacdb_district']));
				$clean[1] = absint(strip_tags($input['rotary_dacdb_club']));
				if ($clean[0] && $clean[1] ) {
					return $input;
				}
				else {
					add_settings_error('rotary_dacdb', '100','Please enter a valid district and club number','error');
					return false;
				}
			}
			else {
				$clean[2] = strip_tags($input['rotary_dacdb_club_name']);
				if ($clean[2]) {
					return $input;
				}
				else {
					add_settings_error('rotary_dacdb', '100','Please enter a valid club name','error');
					return false;
				}
			}
		}
	
	}
	//register the post type for projects
	function register_project_post_type() {
		// creating (registering) the custom type 
	register_post_type( 'rotary_projects', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
	 	// let's now add all the options for this post type
		array('labels' => array(
							'name' 				=> __('Projects', 'rotary'), /* This is the Title of the Group */
							'singular_name' 	=> __('Project', 'rotary'), /* This is the individual type */
							'all_items' 		=> __('All Projects', 'rotary'), /* the all items menu item */
							'add_new' 			=> __('Add New', 'rotary'), /* The add new menu item */
							'add_new_item' 		=> __('Add New Project', 'rotary'), /* Add New Display Title */
							'edit' 				=> __( 'Edit', 'rotary' ), /* Edit Dialog */
							'edit_item' 		=> __('Edit Project', 'rotary'), /* Edit Display Title */
							'new_item' 			=> __('New Project', 'rotary'), /* New Display Title */
							'view_item' 		=> __('View Project', 'rotary'), /* View Display Title */
							'search_items' 		=> __('Search Projects', 'rotary'), /* Search Custom Type Title */ 
							'not_found' 		=> __('Nothing found.', 'rotary'), /* This displays if there are no entries yet */ 
							'not_found_in_trash'=> __('Nothing found in Trash', 'rotary'), /* This displays if there is nothing in the trash */
							'parent_item_colon' => ''
						), /* end of arrays */
			'description' 		=> __( 'This is where the ', 'rotary' ), /* Custom Type Description */
			'public' 			=> true,
			'publicly_queryable' => true,
			'exclude_from_search'=> false,
			'show_ui' 			=> true,
			'query_var' 		=> true,
			'menu_position' 	=> 9, /* this is what order you want it to appear in on the left hand side menu */ 
			'rewrite'			=> array( 'slug' => 'project', 'with_front' => false ), /* you can specify its url slug */
			'capability_type' 	=> 'post',
			'has_archive' 		=> 'project_archive', /* you can rename the slug here */
			'hierarchical' 		=> false,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'author', 'custom-fields', 'revisions', 'thumbnail', 'comments')
	 	) /* end of options */
	); /* end of register post type */
	// now let's add custom categories (these act like categories)
    register_taxonomy( 'rotary_project_cat', 
    	array('rotary_projects'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    	array(
    		'hierarchical' => true,     /* if this is true, it acts like categories */             
    		'labels' => array(
			    			'name' 				=> __( 'Project Categories', 'rotary' ), /* name of the custom taxonomy */
			    			'singular_name' 	=> __( 'Project Category', 'rotary' ), /* single taxonomy name */
			    			'search_items' 		=> __( 'Search Project Categories', 'rotary' ), /* search title for taxomony */
			    			'all_items' 		=> __( 'All Project Categories', 'rotary' ), /* all title for taxonomies */
			    			'parent_item' 		=> __( 'Parent Project Category', 'rotary' ), /* parent title for taxonomy */
			    			'parent_item_colon' => __( 'Parent Project Category:', 'rotary' ), /* parent taxonomy title */
			    			'edit_item' 		=> __( 'Edit Project Category', 'rotary' ), /* edit custom taxonomy title */
			    			'update_item' 		=> __( 'Update Project Category', 'rotary' ), /* update title for taxonomy */
			    			'add_new_item' 		=> __( 'Add New Project Category', 'rotary' ), /* add new title for taxonomy */
			    			'new_item_name' 	=> __( 'New Project', 'rotary' ) /* name title for taxonomy */
			    		),
    		'show_ui' => true,
    		'query_var' => true,
    		'rewrite' => array( 'slug' => 'project-category' ),
    	)
    );   
	// now let's add custom tags (these act like tags)
    register_taxonomy( 'rotary_project_tag', 
    	array('rotary_projects'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
    	array('hierarchical' => false,    /* if this is false, it acts like tags */                
    		'labels' => array(
			    			'name' 				=> __( 'Project Tags', 'rotary' ), /* name of the custom taxonomy */
			    			'singular_name' 	=> __( 'Project Tag', 'rotary' ), /* single taxonomy name */
			    			'search_items' 		=> __( 'Search Project Tags', 'rotary' ), /* search title for taxomony */
			    			'all_items' 		=> __( 'All Projects Tags', 'rotary' ), /* all title for taxonomies */
			    			'parent_item' 		=> __( 'Parent Project Tag', 'rotary' ), /* parent title for taxonomy */
			    			'parent_item_colon' => __( 'Parent Project Tag:', 'rotary' ), /* parent taxonomy title */
			    			'edit_item' 		=> __( 'Edit Project Tag', 'rotary' ), /* edit custom taxonomy title */
			    			'update_item' 		=> __( 'Update Project Tag', 'rotary' ), /* update title for taxonomy */
			    			'add_new_item' 		=> __( 'Add New Project Tag', 'rotary' ), /* add new title for taxonomy */
			    			'new_item_name' 	=> __( 'New Project Tag Name', 'rotary' ) /* name title for taxonomy */
			    		),
    		'show_ui' => true,
    		'query_var' => true,
    	)
    ); 


	}
	//register the custom post type for committees
	function register_commitee_post_type() {
		$labels = array(
			'add_new_item' 	=> __( 'Add Committee', 'rotary' ),
			'edit_item' 	=> __( 'Edit Committee', 'rotary' ),
			'new_item' 		=> __( 'New Committees', 'rotary' ),
			'view_item' 	=> __( 'View Committee', 'rotary' ),
			'search_items' 	=> __( 'Search Committees', 'rotary' ),
			'not_found' 	=> __( 'No Committees Found', 'rotary' )
		);   
  
        $args = array(  
            'label' => __('Committees'),  
			'labels' => $labels,
            'singular_label' => __('Committee'),
			'query_var' => true,  
            'public' => true,  
            'show_ui' => true, 
	        'capability_type' => 'post',  
            'hierarchical' => true,  
			'exclude_from_search' => true,
			'rewrite' => array("slug" => "committees"),
            'supports' => array('title', 'comments', 'editor', 'thumbnail'),
            'has_archive' => true, 
           );  
      
        register_post_type( 'rotary-committees' , $args );  
		
	}
	function rotary_connection_types() {
	   // relate users to committees
		p2p_register_connection_type( array(
			'name' => 'committees_to_users',
			'from' => 'rotary-committees',
			'to' => 'user'
		) );
	//relate posts to committees
		p2p_register_connection_type( array(
			'name' => 'committees_to_posts',
			'from' => 'rotary-committees',
			'to' => 'post'
		) );
	// relate users to projects
		p2p_register_connection_type( array(
			'name' => 'projects_to_users',
			'from' => 'rotary_projects',
			'to' => 'user'
		) );
	//relate posts to projects
		p2p_register_connection_type( array(
			'name' => 'projects_to_posts',
			'from' => 'rotary_projects',
			'to' => 'post'
		) );
		
		//relate projects to committees
		p2p_register_connection_type( array(
			'name' => 'projects_to_committees',
			'from' => 'rotary_projects',
			'to' => 'rotary-committees'
		) );
	}
	
	
	/*************************************************
	  shortcodes to display rotary club members
	*************************************************/
	function get_rotary_club_members( $atts ) { 
		extract( shortcode_atts( array(
			'type' => '', 
			'id' => ''
		), $atts ) );
		
	 	if (!is_user_logged_in() ) :
	 		$not_loggedin_msg = (( 'rotary_projects' == get_post_type() )) 
	 										? _e ( 'You must be logged in to see project participants', 'Rotary' ) 
	 										: _e ( 'You must be logged in to see member information', 'Rotary' );
			$memberTable = '
					<div class="rotarymembernotloggedin">
						<p>' . $not_loggedin_msg. '</p>
						<p>' . wp_loginout( get_permalink(), false ) . '</p>
					</div>';
			
		else:
			include( 'rotarymembership-layout.php' );
			$memberTable = get_membership_layout( $this, $type, $id );
		endif;
		return $memberTable;
	}
	
	 //register the scripts for shortcodes 
	 function register_script_for_shortcodes() {
		 wp_register_script('datatables', plugins_url('/js/jquery.dataTables.min.js', __FILE__),  array( 'jquery' ) );
		 wp_register_script('datatablesreload', plugins_url('/js/jquery.datatables.reload.js', __FILE__),  array( 'jquery' ) );
		 wp_register_script('rotarydatatables', plugins_url('/js/rotary.datatables.js', __FILE__),  array( 'jquery' ) );
		 wp_register_style(
        	'rotary-datatables',plugins_url('/css/rotarydatatables.css', __FILE__), false, 0.1);
	 }
	 //the scripts included here are need for the shortcodes
	 function enqueue_scripts_for_shortcodes() {
		
		wp_enqueue_style('rotary-datatables');
		wp_enqueue_script(array('datatables','datatablesreload', 'rotarydatatables', 'jquery-ui-dialog'));
		wp_localize_script( 'rotarydatatables', 'rotarydatatables', array('ajaxURL' => admin_url('admin-ajax.php'),'tableNonce' => wp_create_nonce( 'rotary-table-nonce' )) );
	 }
	 
	 
	 // Get the list of form entries
	 function rotary_get_form_entries() {
		 die(json_encode($this->rotaryProfiles->get_form_entries_json( $_GET['form_id'], $_GET['post_id'] )));
	 }
	 
	 //get the list of members
	 function rotary_get_members() {
		 die(json_encode($this->rotaryProfiles->get_users_json( $_GET['nameorder'] )));
		
	 }
	 //get the member details
	 function rotary_get_member_details() {
		 if (!isset( $_GET['memberID'])) {
			 die (json_encode( array( 'memberName' => 'Invalid Member ID')));  
		 }
		 die(json_encode($this->rotaryProfiles->get_users_details_json($_GET['memberID'])));
		
	 }
	 //delete a member from a project
	 function rotary_delete_project_member() {
		 $current_user = wp_get_current_user();
		 $response = array(
		 	'status' => 'error',
		 	'message' => 'Invalid nonce',
		 );
	     //security check
	     $nonce = $_POST['nonce'];
	     if ( ! wp_verify_nonce( $nonce, 'rotary-table-nonce' ) ) {
	     	die( json_encode( $response ) );
	     }	
	     p2p_type( 'projects_to_users' )->disconnect( $_POST['project_id'], $_POST['user_id'], array('date' => current_time('mysql')));
		 $response['status'] = 'success';
		 $response['message'] = $current_user->ID;
		 die( json_encode( $response ) );
	 }
	 //add a new mmber to a project
	 function rotary_add_project_members() {
		 $current_user = wp_get_current_user();
		 $response = array(
		 	'status' => 'error',
		 	'message' => 'Invalid nonce',
		 );
	     //security check
	     $nonce = $_POST['nonce'];
	     if ( ! wp_verify_nonce( $nonce, 'rotary-table-nonce' ) ) {
	     	die( json_encode( $response ) );
	     }	
		 //check if connection exists
		 $p2p_id = p2p_type( 'projects_to_users' )->get_p2p_id( $_POST['project_id'], $_POST['user_id'] );
		 if ( ! $p2p_id ) {
		 	p2p_type( 'projects_to_users' )->connect( $_POST['project_id'], $_POST['user_id'], array('date' => current_time('mysql')));
		 	$response['status'] = 'success';
		 	$response['message'] = $current_user->ID;
		 	die( json_encode( $response ) );
		 }
		 else {
			 $response['message'] = $current_user->ID;
			 $response['status'] = 'not added';
			 die( json_encode( $response ) );
		 }
		 
	 }
	  //get the committees from the post
	function get_committees_for_membertable() {
		$args = array(
			'posts_per_page' => -1,
			'post_type' 	 => 'rotary-committees'
		);
		$query = new WP_Query( $args );
		$options = '
				<option value="all">' . _x( 'Filter by committee', 'Member directory dropdown for committees', 'rotary' ) . '</option>
				<option value="all">' . _x( 'All', 'Member directory dropdown for committees', 'rotary' ) . '</option>';
		while ( $query->have_posts() ) : $query->the_post();
		  $options .= '<option value="'.get_the_ID().'">'.get_the_title().'</option>';
		endwhile;
		wp_reset_postdata();
		return $options;
	 }
	 //used on project page to add a new user to the project
	 function get_users_for_membertable_select() {
	  	$args = array(
			 'orderby' => 'meta_value',
			 'meta_key' => 'last_name'
		);
		$users = get_users($args);
		$options = '<option value="">' . _x( 'Add a participant', 'Project participant dropdown', 'rotary' ) . '...</option>';
		foreach ($users as $user) {
		    
			$usermeta = get_user_meta($user->ID);
			if (!isset($usermeta['membersince'][0]) || '' == trim($usermeta['membersince'][0])) {
				continue;
			}
			$memberName = $usermeta['last_name'][0]. ', ' .$usermeta['first_name'][0];
		
			$options .= '<option value="'.$user->ID.'">'.$memberName.'</option>';
		}	
		
		return $options;
	 }
	 
	 
	 
}//end class
$rotaryMembership = new RotaryMembership();
?>