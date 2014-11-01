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
    
    // Site ID we're going to fetch from
    public $site_id;
    
    // Class constructor
    public function __construct($site_id) {
	$this->site_id = $site_id;
    }
    
    
    /**
     * Fetches a post associated with our menu item
     * @param int $tax_id The ID of the taxonomy item to fetch
     * @param string $taxonomy The taxonomy type (category, tag...)
     * @return Taxonomy Object on success, false if not found
     */    
    
    public function get_post($post_id){
	
	// Get current blog ID before switching
	$current_id = get_current_blog_id();
	
	// Switch to blog we're copying from 
	switch_to_blog($this->origin_site_id);	    

	// Get post we're linking to
	$origin_post = get_post($post_id);

	// Switch back to our current blog
	switch_to_blog($current_id);

	// Get destination object
	$destination_results = get_posts( array('name' => $origin_post->post_name,
					    'post_type' => $origin_post->post_type,
					    'numberposts' => 1));

	// Zero results or more than one have been returned (although the latter should never happen, but just in case)
	if(count($destination_results) != 1){
	    $destination_object = false;
	}

	// valid object
	else {
	    $destination_object = $destination_results[0];
	}
	
	return $destination_object;	
    }
    
    public function get_post_meta(){
	
    }
    
    
    /**
     * Fetches a taxonomy object
     * @param int $tax_id The ID of the taxonomy item to fetch
     * @param string $taxonomy The taxonomy type (category, tag...)
     * @return Taxonomy Object on success, false if not found
     */    
    public function get_taxonomy_entry($tax_id, $taxonomy){
	
	// Get current blog ID before switching
	$current_id = get_current_blog_id();

	// Switch to blog we're copying from 
	switch_to_blog($this->site_id);	    

	// Get the original object
	$entry = get_term($tax_id, $taxonomy);

	// Switch back to our current blog
	switch_to_blog($current_id);	    

	// Get destination object
	$destination_object = get_term_by('slug', $entry->slug, $taxonomy);
	
	return $destination_object;
	
    }
    
    public function get_custom_link(){
	
    }
}

