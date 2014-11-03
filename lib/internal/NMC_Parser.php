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
    
    // Fetcher object for origin site
    private $object_fetcher;
    
    function __construct($origin_site_id, $destination_site_id) {
	
	// Initialize the fetcher object for each of the sites
	$this->object_fetcher = new NMC_Fetcher($origin_site_id, $destination_site_id);
    }
    
    public function prepare_arguments($old_menu_item){
	
	// Get the linked object
	$linked_object  = $this->object_fetcher->get_object($old_menu_item);
		
	// Object we're linking to is a WP Post object, but not a custom link
	if(is_a($linked_object, 'WP_Post') && ($linked_object->post_type != 'nav_menu_item') ){

	    return $this->prepare_post_arguments($old_menu_item, $linked_object);

	}
	
	// We're linking to a custom link
	elseif($linked_object->post_type == 'nav_menu_item') {
	
	    return $this->prepare_link_arguments($linked_object);
	}
	
	// If this is an object, but not a WP_Post, then for certainly it's a taxonomy
	elseif(is_object($linked_object)){
	    return $this->prepare_taxonomy_arguments($old_menu_item, $linked_object);
	}
	
	// Return false in every other situation
	return false;

    }
    
    // Prepares menu item arguments for WP Post objects
    private function prepare_post_arguments($old_menu_item, $linked_post){	
	
	// Get menu meta fields (title, description, xfn...)
	$old_menu_meta = $this->object_fetcher->get_post_meta($old_menu_item->ID);	

	// TODO: Shouldn't this only apply to a custom menu type? TEST
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
	    'menu-item-object' => $old_menu_meta['_menu_item_object'][0],
	    'menu-item-position' => $old_menu_item->menu_order,
	    'menu-item-object-id' => $linked_post->ID
	);	



	return $arguments;
    }
    
    // Prepare arguments for a custom link
    private function prepare_link_arguments($old_menu_item){
	
	// Get menu meta fields (title, description, xfn...)
	$old_menu_meta = $this->object_fetcher->get_post_meta($old_menu_item->ID);
	
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
	    'menu-item-type' => 'custom',
	    'menu-item-object' => 'custom',
	    'menu-item-position' => $old_menu_item->menu_order,
	);
	
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
    
    // Prepares arguments for a custom link
    
    
}

