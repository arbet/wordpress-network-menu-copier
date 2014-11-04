<?php

class NetworkMenuCopier {

    // Stores the activity log for all of the websites
    var $combined_activity_log = array();
    
    // Initial site ID
    var $initial_site_id;
    public function __construct() {

	 // Add menu item to network admin page
	 add_action('network_admin_menu', array($this, 'add_network_menus'));
	 add_action( 'admin_init', array($this, 'register_settings' ));	 
	 
	// Set initial site ID
	$this->initial_site_id = get_current_blog_id();
	 
    }
    
/**
 * Adds the appropriate menu links on the network admin page
 *
 * <p>This function creates the menu links under the network administrator pages</p>
 */
    public function add_network_menus() {
         
	// Add menu link to menu copier
	$page_hook_suffix = add_submenu_page( 'settings.php', 'Network Menu Copier', 'Menu Copier', 'manage_network_options', 'network_menu_copier', array($this, 'display_options_page') );
	
	// Add activity log page without specifying a menu parent
	add_submenu_page( NULL, 'Network Menu Copier Activity Log', 'Network Menu Copier Activity Log', 'manage_network_options', 'network_menu_copier_log', array($this, 'display_activity_log') );
	
	// Initialize admin scripts
	add_action('admin_print_scripts-' . $page_hook_suffix, array($this, 'initialize_admin_scripts'));

    }
    
/**
 * Displays the options page on the network admin settings page
 *
 */    
    public function display_options_page(){
	require (NMC_PATH.'/inc/options.php'); 
    }
 
/**
 * Registers the settings that we want to show on our menu pages
 *
 */        
    public function register_settings(){
	register_setting( 'network-menu-copier', 'new_option_name' );
	register_setting( 'network-menu-copier', 'some_other_option' );
	register_setting( 'network-menu-copier', 'option_etc' );	
	
	// Add action for ajax requests from option page
	add_action('wp_ajax_get_menus', array($this, 'get_site_menus'));	
	add_action('wp_ajax_get_sites_by_theme', array($this, 'get_sites_by_theme'));
	add_action('wp_ajax_get_menu_locations', array($this, 'get_menu_locations'));
    }
    
/**
 * Adds the necessary admin scripts for out plugin to function properly
 */
    public function initialize_admin_scripts(){
	
	// Initialize chosen script
	wp_enqueue_script( 'jquery-chosen', NMC_URL.'inc/chosen.jquery/chosen.jquery.min.js', array('jquery') );
	
	// Initialize chosen script
	wp_enqueue_script( 'nmc-js', NMC_URL.'inc/nmc.js', array('jquery-chosen') );
	
	// Include CSS for chosen script
	wp_enqueue_style('jquery-chosen-css', NMC_URL.'inc/chosen.jquery/chosen.min.css');
		
    }
    
    public function get_site_menus(){
	
	// Switch to posted blog ID
	switch_to_blog(intval( $_POST['blog_id'] ));
	
	$menus = get_terms( 'nav_menu'  );
	
	//var_dump($menus);die();
	echo json_encode($menus);
	
	restore_current_blog();
	
	die();

    }
    
    public function get_sites_by_theme(){
	
	// Switch to posted blog ID
	switch_to_blog(intval( $_POST['blog_id'] ));
	
	// Get site theme
	$theme = wp_get_theme();

	// Get theme name
	$theme_name = $theme->Name;
	
	// Get all sites which have this active theme
	$sites_to_send = array();
	
	// Get a list of all websites
	$all_sites = wp_get_sites();
	
	foreach($all_sites as $key=>$site){
	    
	    // Skip if our site is the current site, we don't want to include that
	    if($site['blog_id'] == intval ($_POST['blog_id'])){
		continue;
	    }
	    
	    // switch to that blog
	    switch_to_blog($site['blog_id']);
	    
	    // Get the active theme for that blog
	    $blog_theme = wp_get_theme();

	    // Get theme name
	    $blog_theme_name = $blog_theme->Name;
	    
	    // Add to list of our sites if the blog has the same active theme
	    if( ($blog_theme_name == $theme_name) ){
		$sites_to_send[] = array('blog_id' => $site['blog_id'], 'domain' => $site['domain']);
	    }
	    
	    restore_current_blog();		    
	}

	echo json_encode($sites_to_send);

	die();	
    }
    
    public function get_menu_locations(){
		
	global $current_blog, $wpdb;

	// Switch to posted blog ID
	switch_to_blog(intval( $_POST['blog_id'] ));


	// Get theme mods option to get menu locations - 
	// get_registered_nav_menus not working with switch_to_blog since it uses global variables
	// More information: http://scotty-t.com/2012/03/13/switch_to_blog-is-an-unfunny-nightmare/

	// Get registered menu locations
	$theme_options = get_option('theme_mods_'. get_stylesheet());

	$theme_menu_locations = $theme_options['nav_menu_locations'];
	
	echo json_encode($theme_menu_locations);
	
	restore_current_blog();	
	die();		
    }
    
    // Copies the menus from origin site to destination sites
    public function copy_menus(){
	
	// Display notices if any and stop processing if invalid
	if(!$this->display_admin_notice()){
	    return false;
	}
	
	// Get the ID of the site we're copying from
	$origin_site = intval ($_POST['origin_site']);
	
	// Switch to posted blog ID
	switch_to_blog(intval( $origin_site ));

	// Get the ID of the menu we're copying
	$origin_menu_id = intval ($_POST['origin_menu']);
	
	// Get the location of the menu we're copying from and to
	$menu_location = (string) $_POST['menu_location'];
	
	// Get menu object based on menu ID - need this to copy it
	$menu_object = wp_get_nav_menu_object($origin_menu_id);
	
	// Get menu object items
	$menu_object_items = wp_get_nav_menu_items($origin_menu_id);
	
	//echo "<pre>";var_dump($menu_object_items);die();
	
	// Get menu name from origin menu
	$default_menu_name = $menu_object->name;
	
	// Get destination sites
	$destination_sites = $_POST['destination_sites'];
	
	// Copy to each of the destination sites
	foreach($destination_sites as $key => $site_id){
	    
	    // Switch to that site
	    switch_to_blog(intval ($site_id));
	    
	    // Get posted menu name, or use default
	    $menu_name = (!empty($_POST['menu_name']))?$_POST['menu_name']:$default_menu_name;
	    	    
	    // Check if the menu exists
	    $menu_exists = wp_get_nav_menu_object( $menu_name );
	    
	    // If it doesn't exist, let's create it
	    if( !$menu_exists){
		$menu_id = wp_create_nav_menu($menu_name);
	    }
	    // Otherwise delete menu and recreate it with new links
	    else{

		// Delete the old menu and recreate it
		wp_delete_nav_menu($menu_exists->{term_id});
		$menu_id = wp_create_nav_menu($menu_name);
	    }
	    
	    // Initialize network menu walker object, and pass the current menu and site ID to it
	    $walker = new NetworkMenuWalker($menu_id, $origin_site);
	    $walker->walk($menu_object_items,0);
	    
	    // Add current site activity log to combined activity log
	    $this->combined_activity_log[$site_id] = $walker->activity_log;	    
	    
	    // Now, we need to assign menus to the location we specified
	    
	    // Get registered menu locations in the destination site
	    $theme_options = get_option('theme_mods_'. get_stylesheet());
	    //$theme_menu_locations = $theme_options['nav_menu_locations'];
	    $theme_options['nav_menu_locations'][$menu_location] = $menu_id;
	    
	    // Update option with new location
	    update_option('theme_mods_'. get_stylesheet(), $theme_options);
	    
	    
	} // All sites looped through

	// Switch back to original site
	switch_to_blog($this->initial_site_id);
	
	// Store completed activity log in our database
	update_option('nmc_activity_log', $this->combined_activity_log);
	
	return;
    }
    
    /*
     * This function validates the user input and displays an error if invalid
     */
    public function validate_user_input(){
	
	if( empty($_POST['origin_site']) || empty($_POST['origin_menu'])
		|| empty($_POST['destination_sites']) || empty($_POST['menu_location'])){
	    
	    add_action( 'admin_notices', array($this, 'display_admin_notice' ) ) ;
	    
	    return false;
	    
	}
	
	return true;
	
    }
    
    /*
     * Displays admin notice (e.g. Input errors, Changes successfully applied...)
     */
    function display_admin_notice(){
	
	// Invalid user input
	if(!$this->validate_user_input()){
	        ?>
		    <div class="error">
			<p><?php _e( 'You need to fill all required fields before copying.', 'network-menu-copier' ); ?></p>
		    </div>
		<?php
		
	    return false; // invalid user input
	}
	
	// Valid user input
	else {

		?>
		    <div class="updated">
			<p><?php _e( 'Menus successfully copied.', 'network-menu-copier' ); ?></p>
		    </div>
		<?php
		
	    return true;
	}
    }
    
    
    // Displays the latest activity log
    public function display_activity_log(){
	require (NMC_PATH.'/inc/log.php'); 
    }    
}

$networkcopier = new NetworkMenuCopier();
