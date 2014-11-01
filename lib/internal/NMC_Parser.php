<?php

/**
 * Network Menu Copier Arguments Parser
 * @package network-menu-copier
 * @author Samer Bechara <sam@thoughtengineer.com>
 * 
 * This class parses network menu item arguments before they are sent to wp_update_nav_menu_item function
 * 
 */

class NMC_Parser{
    
    // The site ID we're copying from
    private $origin_site_id;
    
    // The site ID we're copying to 
    private $destination_site_id;
    
    // The object's source (origin or destination site)
    // Possible values: origin, destination
    private $object_source;
    
    function __construct($origin_site_id, $destination_site_id) {
	
	$this->origin_site_id = $origin_site_id;
	$this->destination_site_id = $destination_site_id;
    }
    
    public function prepare_arguments($old_menu_item, $linked_object, $object_source){
	
	// Set object source
	$this->object_source = $object_source;
	
	// Return arguments for a wordpress post
	if(is_a($linked_object, 'WP_Post')){

	    return $this->prepare_post_arguments($old_menu_item, $linked_object);

	}
	// If this is an object, but not a WP_Post, then for certainly it's a taxonomy
	elseif(is_object($linked_object)){
	    return $this->prepare_taxonomy_arguments($old_menu_item, $linked_object);
	}

    }
    
    private function prepare_post_arguments($old_menu_item, $linked_object){
	
	// Create new fetcher object
	$fetcher = new NMC_Fetcher($this->origin_site_id);
	
	// Get menu meta fields (title, description, xfn...)
	$old_menu_meta = $fetcher->get_post_meta($old_menu_item->ID);	

	// TODO: Shouldn't this only apply to a custom menu type? TEST
	// Replace links to reflect new site URLs
	$link = NetworkMenuCopier::replace_links($old_menu_meta['_menu_item_url'][0], get_site_url(intval ($_POST['origin_site'])), get_site_url() );

	// Get a string of item classes from the array
	$item_classes = $this->get_item_classes(unserialize($old_menu_meta['_menu_item_classes'][0]));

	// The object we have is the source object, meaning that we're linking to an invalid object
	if($this->object_source == 'source'){
	    $arguments = $this->generate_invalid_arguments($old_menu_item, $linked_object);
	}
	
	// We have a valid link
	elseif($this->object_source == 'destination'){

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
		'menu-item-object' => $old_menu_meta['_menu_item_object'][0],
		'menu-item-parent-id' => $this->parent_id,
		'menu-item-position' => $old_menu_item->menu_order,
		'menu-item-object-id' => $object_id
	    );	

	}

	return $arguments;
    }
    
    private function prepare_taxonomy_arguments($old_menu_item, $linked_object) {
	
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
    
    // Generates invalid arguments for invalid links
    private function generate_invalid_arguments($old_menu_item, $linked_object){
	    // Create array for menu options
	    $arguments = array(
		'menu-item-title' => 'Invalid Link', 
		//'menu-item-url' => $link,
		//'menu-item-description' => $old_menu_item->post_content,
		//'menu-item-attr-title' => $old_menu_item->post_excerpt,
		//'menu-item-target' => $old_menu_meta['_menu_item_target'][0],
		//'menu-item-classes' => $item_classes,
		//'menu-item-xfn' => $old_menu_meta['_menu_item_xfn'][0],
		'menu-item-status' => 'draft',
		'menu-item-type' => 'post_type',
		'menu-item-object' => 'Invalid',
		'menu-item-parent-id' => $this->parent_id,
		'menu-item-position' => $old_menu_item->menu_order,
		//'menu-item-object-id' => $object_id
	    );	
    }
    
}

