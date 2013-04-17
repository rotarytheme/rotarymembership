<?php
/*
Plugin Name: Rotary Membership
Description: This is a plugin for Rotary Clubs to Maintain Membership from DacDB. This plugin auto updates from github.
Version: 1.6
Author: Merrill M. Mayer
Author URI: http://www.koolkatwebdesigns.com/
License: GPL2
*/
define( 'ROTARY_MEMBERSHIP_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'ROTARY_MEMBERSHIP_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'ROTARY_MEMBERSHIP_PLUGIN_FILE', plugin_basename( __FILE__ ) );
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
		add_action( 'add_meta_boxes', array($this, 'add_committee_metabox'));
		add_action( 'save_post', array($this, 'save_committee_metabox'), 10, 2);
		add_action( 'p2p_init', array($this, 'rotary_connection_types' ));
		add_shortcode( 'MEMBER_DIRECTORY', array($this, 'get_rotary_club_members') );
		add_action('init', array($this, 'register_script_for_shortcodes') );
		add_action('template_redirect', array($this, 'enqueue_scripts_for_shortcodes') );
		//ajax to get members
		add_action( 'wp_ajax_nopriv_rotarymembers', array($this, 'rotary_get_members' ));
		add_action( 'wp_ajax_rotarymembers', array($this, 'rotary_get_members' ));
		add_action( 'wp_ajax_nopriv_rotarymemberdetails', array($this, 'rotary_get_member_details' ));
		add_action( 'wp_ajax_rotarymemberdetails', array($this, 'rotary_get_member_details' ));	
		/*the next code is to register plugins for inclusion with the theme*/
		add_action( 'tgmpa_register', array($this, 'rotary_register_required_plugins' ));
		$this->setup_plugin_updates();
	}
	//activation creates a table to store rotary members user id from DacDb. 
	//This will be used to delete users that are no longer Rotary members
	function activate() {
		global $wpdb;
   		$table_name = $wpdb->prefix . 'rotarymembers';
		$sql = 'CREATE TABLE ' . $table_name .'(
     		id int(11) unsigned NOT NULL auto_increment,
			dacdbuser varchar(60),
     		PRIMARY KEY  (id)
  		);';
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
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
		add_settings_field('rotary_dacdb_club', '<span class="dacdb">Rotary Club Number</span>', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_dacdb_club'));
		add_settings_field('rotary_dacdb_club_name', '<span class="nodacdb">Rotary Club Name</span>', array($this, 'rotary_form_field'), 'general', 'rotary_settings_section', array('fieldName' => 'rotary_dacdb_club_name'));
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
			   $dacdbClubName = '<input type="number" class="nodacdb" name="rotary_dacdb[rotary_dacdb_club_name]" id="rotary_dacdb_club_name" value="'.esc_attr( $options['rotary_dacdb_club_name'] ) .'" class="regular-text"/>';
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
	//register the custom post type for committees
	function register_commitee_post_type() {
		$labels = array(
			'add_new_item' => 'Add Committee',
			'edit_item' => 'Edit Committee',
			'new_item' => 'New Committees',
			'view_item' => 'View Committee',
			'search_items' => 'Search Committees',
			'not_found' => 'No Committees Found'
		);   
  
        $args = array(  
            'label' => __('Committees'),  
			'labels' => $labels,
            'singular_label' => __('Committee'),
			'query_var' => true,  
            'public' => true,  
            'show_ui' => true, 
	        'capability_type' => 'post',  
            'hierarchical' => false,  
			'exclude_from_search' => true,
			'rewrite' => array("slug" => "committees"),
            'supports' => array('title')  
           );  
      
        register_post_type( 'rotary-committees' , $args );  
		
	}
	//add a metabox for the committee number
	function add_committee_metabox() {
		add_meta_box( 'committeenumber', __( 'Committee Number' ),  array($this, 'show_committee_metabox'), 'rotary-committees', 'normal', 'high' );
	}
	//save the committee number
	function save_committee_metabox($post_id, $post) {
	    if ( !isset( $_POST['rotary_commmittee_nonce'] ) || !wp_verify_nonce( $_POST['rotary_commmittee_nonce'], basename( __FILE__ ) ) )
         return $post_id;
		 
		/* Get the post type object. */
	    $post_type = get_post_type_object( $post->post_type );
	 
	    /* Check if the current user has permission to edit the post. */
	    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
	        return $post_id;	
		}
		if (!isset($_POST['committeenumber'])) {	 
			return $post_id;	
		} 
	    /* Get the meta key. */
    	$meta_key = 'committeenumber';	 	    /* Get the meta value of the custom field key. */
	    $meta_value = get_post_meta( $post_id, $meta_key, true );
		$new_meta_value = absint(strip_tags($_POST['committeenumber']));
		/* If a new meta value was added and there was no previous value, add it. */
	    if ( $new_meta_value && '' == $meta_value )
	        add_post_meta( $post_id, $meta_key, $new_meta_value, true );
	 
	    /* If the new meta value does not match the old value, update it. */
	    elseif ( $new_meta_value && $new_meta_value != $meta_value )
	        update_post_meta( $post_id, $meta_key, $new_meta_value );	 
	    /* If there is no new meta value but an old value exists, delete it. */
	    elseif ( '' == $new_meta_value && $meta_value )
	        delete_post_meta( $post_id, $meta_key, $meta_value );
		 
		
	}
	//print HTML for the committee metabox
	function show_committee_metabox($object) {
		 wp_nonce_field( basename( __FILE__ ), 'rotary_commmittee_nonce' );?>
		 
		 <p><label for="committeenumber">Committee Number:<br />
	        <input id="committeenumberfield" size="20" name="committeenumber" value="<?php echo esc_attr( get_post_meta( $object->ID, 'committeenumber', true ) ); ?>" /></label></p>
		 
<?php }
	function rotary_connection_types() {
		p2p_register_connection_type( array(
		'name' => 'committees_to_users',
		'from' => 'rotary-committees',
		'to' => 'user'
	) );
	}
	//shortcodes to display rotary club members
	function get_rotary_club_members($attr) { 
	 	if (!is_user_logged_in() ) {
			$memberTable = '<p>You must be logged in to view the Rotary member data</p>
			<p>'.wp_loginout( get_permalink(), false ).'</p>';
			
	 	}
		else { 
			include('rotarymembership-layout.php');
			$memberTable = get_memberhsip_layout($this);
		}
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
		wp_localize_script( 'rotarydatatables', 'rotarydatatables', array('ajaxURL' => get_admin_url().'admin-ajax.php','tableNonce' => wp_create_nonce( 'rotary-table-nonce' )) );
	 }
	 //get the list of members
	 function rotary_get_members() {

		 die(json_encode($this->rotaryProfiles->get_users_json($_GET['nameorder'])));
		
	 }
	 //get the member details
	 function rotary_get_member_details() {
		 if (!isset( $_GET['memberID'])) {
			 die (json_encode( array( 'memberName' => 'Invalid Member ID')));  
		 }
		 die(json_encode($this->rotaryProfiles->get_users_details_json($_GET['memberID'])));
		
	 }
	  //get the committees from the post
	function get_committees_for_membertable() {
		$args = array(
			'posts_per_page' => -1,
			'post_type' 	 => 'rotary-committees'
		);
		$query = new WP_Query( $args );
		$options = '<option value="all">All</option>';
		while ( $query->have_posts() ) : $query->the_post();
		  $options .= '<option value="'.get_the_ID().'">'.get_the_title().'</option>';
		endwhile;
		wp_reset_postdata();
		return $options;
	 }
	 /**
 * Register the required plugins for this theme.
 *
 * The variable passed to tgmpa_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into tgmpa_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function rotary_register_required_plugins() {

	/**
	 * Array of plugin arrays. Required keys are name and slug.
	 * If the source is NOT from the .org repo, then source is also required.
	 */
	$plugins = array(



		// This is an example of how to include a plugin from the WordPress Plugin Repository
		array(
			'name' 		=> 'Posts 2 Posts',
			'slug' 		=> 'posts-to-posts',
			'required' 	=> true,
			'force_activation' => true
		),

	);

	// Change this to your theme text domain, used for internationalising strings
	$theme_text_domain = 'rotary';

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'strings'      		=> array(
		),
	);

	tgmpa( $plugins, $config );

}
}//end class
$rotaryMembership = new RotaryMembership();
?>