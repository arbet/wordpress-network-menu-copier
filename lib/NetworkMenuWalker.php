<?php

/* 
 * Custom Implementation of the walker class in order to easily copy menu items and their parents
 * 
 */

class NetworkMenuWalker extends Walker_Nav_Menu {

    // Define our tree type
    public $tree_type = array( 'post_type', 'taxonomy', 'custom' );
    
    /*
     * Define the database fields to use
     */
    public $db_fields = array( 'parent' => 'menu_item_parent', 'id' => 'db_id' );
    
    // The ID of the parent just copied - defaults as top level
    public $parent_id = 0;
    
    // The ID of the parent menu of the items
    public $menu_id ;
    
    // The ID of the site we're copying from
    public $origin_site_id ;
    
    // Status of menu: publish or draft. Initially set to published, if invalid links are found, we'll set it to draft
    public $menu_status = 'publish';
    
    /*
     * Class constructor
     * @param int  $menu_id ID of the parent menu of the items tree
     */
    public function __construct($menu_id, $site_id) {
	$this->menu_id = $menu_id;
	$this->origin_site_id = $site_id;
    }
    
    /*
     * This function runs at the start of each element, it will copy itself and associate the direct parent ID with it
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $item  Name of the item
     * @param array  $args   An array of arguments.
     *	    'parent_id' - The parent ID of the current node
     */
    public function start_el(&$output, $item = 0, $depth = 0, $args = array()){
	
		// Item classes need to be a string, not an array
		$item_classes = '';
		
		foreach($item->classes as $key=> $class){
		    $item_classes.= ' '.$class;
		}		

		// If this is a top level element, it should not have a parent
		if($depth == 0){
		    $this->parent_id = 0;
		}
		
		// Get destination object
		$linked_object = $this->get_destination_object($item);
		
		// Destination object is invalid, link to origin object and set and invalid
		if($linked_object === FALSE){
		    $linked_object = $this->get_origin_object($item);
		}
		
		// Prepare arguments for copying
		$arguments = $this->prepare_copy_arguments($item, $linked_object);		
		
		// Add the item to the database
		$item_id = wp_update_nav_menu_item( $this->menu_id, 0 , $arguments);	
		
		// Update the previous parent record, so we now how to assign the parent ID for the newly created node
		$this->parent_id = $item_id;    
		
		
		
		
	
    }
    
    // Function is here to override output of parent class
    public function  end_el( &$output, $item, $depth = 0, $args = array() ) {
    
	
    }
    // Function is here to override output of parent class
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
	
    }
    // Function is here to override output of parent class
    public function end_lvl( &$output, $depth = 0, $args = array() ) {
	 
     }
     
    // Gets the destination object, basically a wrapper for get object
    private function get_destination_object($item){
	
	return $this->get_object($item, get_current_blog_id());
    }
    
    // Gets the destination object, basically a wrapper for get object
    private function get_origin_object($item){
	
	return $this->get_object($item, $this->origin_site_id);
    }    
    
    // Check if the object menu item has a valid copy on the destination site
    // Returns corresponding object on destination site, or false on failure
    private function get_object($item, $site_id){	
	
	// Create new fetcher object
	$fetcher = new NMC_Fetcher($site_id);

	// Link is to a taxonomy object
	if($item->type == 'taxonomy'){	    
	    
	    // Fetch taxonomy object
	    $fetched_object = $fetcher->get_taxonomy_entry($item->object_id, $item->object);
	}
	
	// Menu link belongs to a post type
	elseif($item->type == 'post_type'){

	    // Get the destination post object we're linking to
	    $fetched_object = $fetcher->get_post($item->object_id);
	    
	}
	
	// Menu link is for a custom link (e.g. google.com)
	elseif($item->type == 'custom'){
	    // custom links are valid in all cases, destination object is the item itself
	    $fetched_object = $item; 
	}


	// Object is invalid, set menu status to draft
	if($fetched_object === FALSE){
	    $this->menu_status = 'draft';
	}
		
	// Return the destination object
	return $fetched_object;

	
    }
    
    // Prepare the copy arguments based on the destination object the menu item is pointing to
    private function prepare_copy_arguments($old_menu_item, $destination_object){
	
	/* First thing, we need to get the old menu item metadata (link, xfn, description, title...) */
	
	// Get current blog ID before switching
	$current_id = get_current_blog_id();
	
	// Switch to blog we're copying from 
	switch_to_blog($this->origin_site_id);	    

	// Get the metadata for the old menu item
	$old_menu_meta = get_post_meta($old_menu_item->ID, '', true);

	// Switch back to our current blog
	switch_to_blog($current_id);
	
	// Check if we're linking to a wordpress post
	if(is_a($destination_object, 'WP_Post')){

	    // TODO: Shouldn't this only apply to a custom menu type?
	    // Replace links to reflect new site URLs
	    $link = NetworkMenuCopier::replace_links($old_menu_meta['_menu_item_url'][0], get_site_url(intval ($_POST['origin_site'])), get_site_url() );

	    // Get a string of item classes from the array
	    $item_classes = $this->get_item_classes(unserialize($old_menu_meta['_menu_item_classes'][0]));

	    // Create array for menu options
	    $arguments = array(
		'menu-item-title' => $old_menu_item->post_title, 
		'menu-item-url' => $link,
		'menu-item-description' => $old_menu_item->post_content,
		'menu-item-attr-title' => $old_menu_item->post_excerpt,
		'menu-item-target' => $old_menu_meta['_menu_item_target'][0],
		'menu-item-classes' => $item_classes,
		'menu-item-xfn' => $old_menu_meta['_menu_item_xfn'][0],
		'menu-item-status' => 'publish',
		'menu-item-type' => 'post_type',
		'menu-item-object' => $destination_object->post_type,
		'menu-item-parent-id' => $this->parent_id,
		'menu-item-position' => $old_menu_item->menu_order,
		'menu-item-object-id' => $destination_object->ID   
	    );	    
	}
	
	// No destination object exists, just use placeholder links
	elseif($destination_object === FALSE){
	    
	    // Get origin object
	    //$origin_object = $this->get_origin_object($old_menu_item->object_id, $old_menu_item->object);
	    // Create array for menu options
	    $arguments = array(
		'menu-item-title' => $old_menu_item->post_title, 
		'menu-item-url' => '',
		'menu-item-description' => $old_menu_item->post_content,
		'menu-item-attr-title' => $old_menu_item->post_excerpt,
		'menu-item-target' => $old_menu_meta['_menu_item_target'][0],
		'menu-item-classes' => $item_classes,
		'menu-item-xfn' => $old_menu_meta['_menu_item_xfn'][0],
		'menu-item-status' => 'draft',
		'menu-item-type' => 'post_type',
		'menu-item-object' => $old_menu_item->object,
		'menu-item-parent-id' => $this->parent_id,
		'menu-item-position' => $old_menu_item->menu_order,
		//'menu-item-object-id' => 0   
	    );
	}

	
	return $arguments;
    }
    
    
    // Returns the classes items as as string from the supplied array
    // Required to pass the data correctly to the add menu item function
    private function get_item_classes($item_classes){
	
	// Item classes should be a string, not an array
	$classes_string = '';

	foreach($item_classes as $key=> $class){
	    $classes_string.= ' '.$class;
	}
	
	return $classes_string;
    }
    
    // Function is called when no destination object exists, to act as a placeholder for invalid links
    private function get_origin_object($object_id, $object_name){
	
	// Get current blog ID before switching
	$current_id = get_current_blog_id();

	// Switch to blog we're copying from 
	switch_to_blog($this->origin_site_id);	    

	// Get the original object
	$origin_object = get_term($object_id, $object_name);

	// Switch back to our current blog
	switch_to_blog($current_id);	
	
	return $origin_object;
    }
}
