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
    
    // The ID of the node just copied - defaults as zero
    public $inserted_id = 0;
    
    // An array of the node parents and grandparents
    public $node_parents = array();
    
    // The ID of the parent menu of the items
    public $menu_id ;
    
    // The ID of the site we're copying from
    public $origin_site_id ;
    
    public $debug_storage; // Internal variable for debug storage, ignore
    
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
    
    public function __destruct() {
	return false;
    }
    
    /*
     * This function runs at the start of each element, it will copy itself and associate the direct parent ID with it
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $item  Name of the item
     * @param array  $args   An array of arguments.
     *	    'inserted_id' - The ID of the node last inserted
     */
    public function start_el(&$output, $item = 0, $depth = 0, $args = array()){
	
	$this->debug_storage[] = "Item $item->ID started";
	
	// Item classes need to be a string, not an array
	$item_classes = '';

	foreach($item->classes as $key=> $class){
	    $item_classes.= ' '.$class;
	}		

	// Prepare arguments for copying
	$arguments = $this->prepare_copy_arguments($item);		

	// Object was not found on destination site, stop copying
	if($arguments === FALSE){
	    return;
	}

	if($depth != 0){

	    // Set parent ID to the last parent in our parents array for non-top level elements
	    $arguments['menu-item-parent-id'] = end(array_values($this->node_parents));				    
	}

	// This is a top level element, parent ID is none
	else{
	    $arguments['menu-item-parent-id'] = 0;
	}


	// Add the item to the database
	$item_id = wp_update_nav_menu_item( $this->menu_id, 0 , $arguments);	

	// Update the previous parent record, so we now how to assign the parent ID for the newly created node
	$this->inserted_id = $item_id;    		
	
    }
    
    // Function is here to override output of parent class
    public function  end_el( &$output, $item, $depth = 0, $args = array() ) {
    
	
    }
    // When a level starts, add last inserted ID to parents array
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
	
	$this->debug_storage[] = "Level $depth started";
	

	$this->node_parents[] = $this->inserted_id;
	
	
	return;
	
    }
    // When a level ends, remove the last parent ID from the array
    public function end_lvl( &$output, $depth = 0, $args = array() ) {

	$this->debug_storage[] = "Level $depth ended";

	// Get post meta for the last inserted ID
	$menu_meta = get_post_meta($this->inserted_id, '', true);
	
	// Get parent ID
	$parent_id = $menu_meta['_menu_item_menu_item_parent'][0];

	// if the parent ID is in the array, pop it
	if(in_array($parent_id, $this->node_parents)){
	    array_pop($this->node_parents);
	}
	
    }
    
    // This function just initializes a parser object linked to the site we're copying from
    // @return array $arguments Array of arguments to be passed to create item
    private function prepare_copy_arguments($old_menu_item){
	
	// Create parser object
	$parser = new NMC_Parser($this->origin_site_id, get_current_blog_id());
	
	// Prepare arguments
	$arguments = $parser->prepare_arguments($old_menu_item);
	
	// Return arguments
	return $arguments;

    }
    
}
