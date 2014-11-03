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
		
		// Prepare arguments for copying
		$arguments = $this->prepare_copy_arguments($item);		
		
		// Object was not found on destination site, stop copying
		if($arguments === FALSE){
		    return;
		}
		
		// Set parent ID for arguments
		$arguments['menu-item-parent-id'] = $this->parent_id;		
		
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
