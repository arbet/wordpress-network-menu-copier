<?php
/**
 * Network Menu Copier Object Fetcher
 * @package network-menu-copier
 *
 * 
 * This class fetches objects our menu items are linking to on any site in the network
 * 
 */

class NMC_Fetcher {
    
    // Site ID we're linking from
    public $origin_site_id;

    // Site ID we're fetching from
    public $destination_site_id;    
    
    // Class constructor
    public function __construct($origin_site_id, $destination_site_id) {
	
	$this->origin_site_id = $origin_site_id;
	$this->destination_site_id = $destination_site_id;
    }
    
    // Returns the object associated with the menu item
    public function get_object($old_menu_item){    

	/* Call function based on object type we're linking to  */
	
	// We're linking to a WP Post
	if($old_menu_item->type == 'post_type'){
	    $linked_object = $this->get_post($old_menu_item);
	}
	
	// We're linking to a custom link
	elseif($old_menu_item->type == 'custom'){
	    $linked_object = $this->get_custom_link($old_menu_item);
	}
	
	// We're linking to a taxonomy object
	elseif($old_menu_item->type == 'taxonomy'){
	    $linked_object = $this->get_taxonomy_entry($old_menu_item->object_id, $old_menu_item->object);
	}
	
	return $linked_object;
	
    }
    /**
     * Fetches a post associated with our menu item
     * @param array $args The arguments to be supplied to get_posts function
     * @return StdClass on success
     * @return false if object is not found
     */    
    
    public function get_post($old_menu_item){    

	// Switch to origin site to get the post our menu item is linking to
	switch_to_blog($this->origin_site_id);
	
	// Get the post by its ID
	$origin_post = get_post($old_menu_item->object_id);
	
	// Switch back to our current site
	switch_to_blog($this->destination_site_id);
	
	// Get the posts by slug and type
	$fetched_posts = get_posts( array(
	    'name' => $origin_post->post_name,
	    'post_type' => $origin_post->post_type,
	    'posts_per_page' => 1
	));

	// One result has been found, return it
	if( count($fetched_posts) == 1 ) {	    
	    return $fetched_posts[ 0 ];
	}

	// Object we're linking to not found
	return false;
	
    }
    
    public function get_post_meta($menu_id){
	
	// Switch to blog we're copying from 
	switch_to_blog($this->origin_site_id);	    

	// Get the metadata for the old menu item
	$menu_meta = get_post_meta($menu_id, '', true);

	// Switch back to our current blog
	switch_to_blog($this->destination_site_id);
	
	return $menu_meta;
    }
    
    
    /**
     * Fetches a taxonomy object
     * @param int $tax_id The ID of the taxonomy item to fetch
     * @param string $taxonomy The taxonomy type (category, tag...)
     * @return Taxonomy Object on success, false if not found
     */    
    public function get_taxonomy_entry($tax_id, $taxonomy){

	// Switch to blog we're copying from 
	switch_to_blog($this->origin_site_id);	    

	// Get the original object
	$entry = get_term($tax_id, $taxonomy);

	// Switch back to our current blog
	switch_to_blog($this->destination_site_id);	    

	// Get linked object
	$linked_object = get_term_by('slug', $entry->slug, $taxonomy);
	
	return $linked_object;
	
    }
    
    public function get_custom_link($old_menu_item){
	
	// Just return the old menu item, since we'll be recreating it anyway
	return $old_menu_item;
    }

}

