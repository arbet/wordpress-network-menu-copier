/* 
 * Custom JS code for NMC
*/

jQuery(document).ready(function($) {
    
    // Get site menus on page load
    get_site_menus();

    // Make beautiful select boxes
    jQuery(".select_chosen").chosen({width: "25%"});
    
    // Get sites by theme
    get_sites_by_theme();    
    
    // Get menu locations
    get_menu_locations();
    
    // When the site field is changed, we need to get the list of menus available to that site
    jQuery("#origin_site").chosen().change(function(){
        
            // Get menus for specific theme
            get_site_menus();
            
            // Get sites which have the active theme
            get_sites_by_theme();
            
            // Get menu locations
            get_menu_locations();
    });

function get_site_menus(){
    
            
        // Create post data
        var data = {
                'action': 'get_menus',
                'blog_id': jQuery( "#origin_site" ).val(),
        };            
        
       // console.log('Blog ID is ' + data.blog_id)
    // Get ajax value
    jQuery.post(ajaxurl, data, function(response) {
        
      //console.log(response);
        var menus = JSON.parse(response);
       // console.log(menus);
        // Empty existing values
        $('#origin_menu').empty();

        // Loop through all of the select fields
        $.each(menus, function(key, value) { 
            // Replace select boxes
            $("#origin_menu").append($("<option></option>").attr("value",value.term_id).text(value.name)); 
        });
        
        // Rebuild chosen select boxes
        $("#origin_menu").trigger("chosen:updated");
    });   
        
}

// Return sites which have a certain theme active
// Parameter passed - current blog ID
function get_sites_by_theme(){
    
        // Create post data
        var data = {
                'action': 'get_sites_by_theme',
                'blog_id': jQuery( "#origin_site" ).val(),
        };    
        
   // Get ajax value
    jQuery.post(ajaxurl, data, function(response) {

        var sites = JSON.parse(response);
        //console.log(sites);
        // Empty existing values
        $('#destination_sites').empty();

        // Loop through all of the select fields
        $.each(sites, function(key, value) { 

            // Replace select boxes
            $("#destination_sites").append($("<option></option>").attr("value",value.blog_id).text(value.domain)); 
        });
        
        // Rebuild chosen select boxes
        $("#destination_sites").trigger("chosen:updated");
    });   
    
}

// Displays the menu locations for the active site's theme
function get_menu_locations(){
    
        // Create post data
        var data = {
                'action': 'get_menu_locations',
                'blog_id': jQuery( "#origin_site" ).val(),
        };            
        
    // Get ajax value
    jQuery.post(ajaxurl, data, function(response) {
        
        
        console.log(response);

        // Empty existing values
        $('#menu_location').empty();
        
        // If response is null, return
        if(response ==  'null'){
            // Rebuild chosen select boxes
            $("#menu_location").trigger("chosen:updated");
            
            return;
        }

        var menus = JSON.parse(response);
        
        // Loop through all of the select fields
        $.each(menus, function(key, value) { 
            
            // Replace select boxes
            $("#menu_location").append($("<option></option>").attr("value",key).text(key)); 
        });
        
        // Rebuild chosen select boxes
        $("#menu_location").trigger("chosen:updated");
    });   
    
}


 
    
});
