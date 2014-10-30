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
    
    /*
     * Class constructor
     * @param int  $menu_id ID of the parent menu of the items tree
     */
    public function __construct($menu_id) {
	$this->menu_id = $menu_id;
    }
    
    /*
     * This function runs at the start of each element, it will copy itself and associate the direct parent ID with it
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $item  Name of the item
     * @param array  $args   An array of arguments.
     *	    'parent_id' - The parent ID of the current node
     */
    public function start_el(&$output, $item = 0, $depth = 0, $args = array()){
	
	//echo "Current site URL is ".get_site_url()."<br/>";return;
		// Item classes need to be a string, not an array
		$item_classes = '';
		
		foreach($item->classes as $key=> $class){
		    $item_classes.= ' '.$class;
		}
		
		// Replace links to reflect new site URLs
		$link = NetworkMenuCopier::replace_links($item->url, get_site_url(intval ($_POST['origin_site'])), get_site_url() );

		// If this is a top level element, it should not have a parent
		if($depth == 0){
		    $this->parent_id = 0;
		}
		
		// Create array for menu options
		$arguments = array('menu-item-title' => $item->title, 
				    'menu-item-url' => $link,
				    'menu-item-description' => $item->description,
				    'menu-item-attr-title' => $item->attr_title,
				    'menu-item-target' => $item->target,
				    'menu-item-classes' => $item_classes,
				    'menu-item-xfn' => $item->xfn,
				    'menu-item-status' => 'publish',
				    'menu-item-type' => 'custom', // All new menu links will be marked as custom, because it will show an invalid page sometimes
				    'menu-item-parent-id' => $this->parent_id
				    
				    		    );
		
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
     
    // Returns the object associated with a menu item 
    private function get_object($item){
	
    }
}
