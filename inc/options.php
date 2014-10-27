<?php

/* 
 * Contains the options page for the plugin
 */

// Form has been submitted
if(!empty($_POST)){
    NetworkMenuCopier::copy_menus();
}
?>

<div class="wrap">
<h2>Network Menu Copier</h2>

<form method="post" action="settings.php?page=network_menu_copier"> 

<?php 

// Tell my options page which settings to handle
settings_fields( 'network-menu-copier' );

// replaces the form field markup in the form itself
do_settings_sections( 'network-menu-copier' );


// Get list of sites in the system
$sites = wp_get_sites();
?>
<table class="form-table">
        <tr valign="top">
        <th scope="row">Site to copy menu from*</th>
        <td><select name='origin_site' id='origin_site' class='select_chosen'>
<?php    foreach($sites as $key=>$site){
	echo "<option value='".$site['blog_id']."'>".$site['domain']."</option>";
    }
?>
	    </select>
	</td>
        </tr>
        <tr valign="top">
        <th scope="row">Menu to be copied*</th>
        <td><select name='origin_menu' id='origin_menu' class='select_chosen'>
	    </select>
	    <p class="description">This lists all the menus in Appearance->Menus for the site you selected.</p>
	</td>
        </tr>	
        <tr valign="top">
        <th scope="row">Sites to copy menu to*</th>
        <td><select name='destination_sites[]' id="destination_sites" multiple class='select_chosen'> 
	    </select>
	    <p class="description">
		Only sites that have the same active theme as the site you are copying from will be displayed here since every theme has a different menu structure. 
	    </p>	    
	</td>
        </tr>		
        <th scope="row">New Menu Name</th>
        <td><input type='text' name='menu_name' value='' />
	    <p class="description">
		If you would like to give your copied menu a different name, enter it here. Otherwise, leave it blank and we will use the name of the menu to be copied.
	    </p>
	</td>
        </tr>	
        </tr>		
        <th scope="row">Menu location for subsites*</th>
        <td><select name='menu_location'  id="menu_location" class='select_chosen'>
	    </select>
	    <p class="description">
		The menu will be copied and displayed on your theme's menu location at the location you specify in this field. This will show one or more fields depending on your specific theme.
	    </p>
	</td>
        </tr>	
</table> 
    
<?php
// Submit button
submit_button(); 

?>
</form>
</div>
