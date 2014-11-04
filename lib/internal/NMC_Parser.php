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
	$link = $this->replace_links($old_menu_meta['_menu_item_url'][0], get_site_url(intval ($_POST['origin_site'])), get_site_url() );

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
	$link = $this->replace_links($old_menu_meta['_menu_item_url'][0], get_site_url(intval ($_POST['origin_site'])), get_site_url() );

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
	
	// Get menu meta fields (title, description, xfn...)
	$old_menu_meta = $this->object_fetcher->get_post_meta($old_menu_item->ID);	

	// TODO: Shouldn't this only apply to a custom menu type? TEST
	// Replace links to reflect new site URLs
	$link = $this->replace_links($old_menu_meta['_menu_item_url'][0], get_site_url(intval ($_POST['origin_site'])), get_site_url() );

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
	    'menu-item-type' => 'taxonomy',
	    'menu-item-object' => $old_menu_meta['_menu_item_object'][0],
	    'menu-item-position' => $old_menu_item->menu_order,
	    'menu-item-object-id' => $linked_object->term_id
	);	



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
    
    /*
     * Replaces the domain links properly inside menu links
     */
    private function replace_links($link, $old_site_url, $new_site_url){
	
	// Get possible link variations (e.g. user put www. ) 
	$old_urls = $this->get_url_variations($old_site_url);
	
	// Replace old in new
	foreach($old_urls as $url){
	    
	    // Attempt to replace old link by new link
	    $new_link = str_replace($url, $new_site_url, $link);
	    
	    // Variation has been replaced
	    if($new_link != $link){
		return $new_link;
	    }
	}		
	
	// No replacement has been made, return the link as is
	return $link;
    }
    
   /*
     * Returns the URL variations for a site URL
     */
    private static function get_url_variations($url){
	
	// Check if this an http or https site
	
	// This is an http:// site
	if(strpos($url, 'http://') !== FALSE){
	    $prefix = 'http://';
	}
	
	elseif(strpos($url, 'https://')){
	    $prefix = 'https://';
	}
	
	else 
	    return false; // Incorrect prefix
	
	// Check if old site has www. in front of it
	if(strpos($url, $prefix.'www.') !== FALSE){
	    $urls[] = $url;
	    $urls[] = str_replace($prefix.'www.', $prefix, $url);
	}
	// In case it does not have www.
	elseif(strpos($url, $prefix)!==FALSE){
	    $urls[] = $url;
	    $urls[] = str_replace($prefix, $prefix.'www.', $url);
	}
	
	// return list of urls
	return $urls;
    }    
    
}

