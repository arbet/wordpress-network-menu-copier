<?php

/* 
 * Contains the options page for the plugin
 */

// Form has been submitted
if(!empty($_POST)){
    
    $copier = new NetworkMenuCopier();
    $copier->copy_menus();
    
}
?>

<div class="wrap">
<h2>Network Menu Copier</h2>


<p><a href='settings.php?page=network_menu_copier_log'>View Activity Log of Last Run</a></p>


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
        <th scope="row">Origin site*</th>
        <td><select name='origin_site' id='origin_site' class='select_chosen'>
<?php    foreach($sites as $key=>$site){
	echo "<option value='".$site['blog_id']."'>".$site['domain']."</option>";
    }
?>
	    </select>
	    <p class='description'>Site to copy menu from</p>
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
        <th scope="row">Destination sites*</th>
        <td><select name='destination_sites[]' id="destination_sites" multiple class='select_chosen'> 
	    </select>
	    <p class="description">
		Where will you be copying this menu to? Only sites that have the same active theme as origin site will be displayed here since every theme has a different menu structure. 
	    </p>	    
	</td>
        </tr>
	<tr valign="top">
        <th scope="row">New Menu Name</th>
        <td><input type='text' name='menu_name' value='' />
	    <p class="description">
		If you would like to give your copied menu a different name, enter it here. Otherwise, leave it blank and we will use the name of the menu to be copied.
	    </p>
	</td>
        </tr>	
        <tr valign='top'>		
        <th scope="row">Menu location for subsites*</th>
        <td><select name='menu_location'  id="menu_location" class='select_chosen'>
	    </select>
	    <p class="description">
		The menu will be copied and displayed on your theme's menu location at the location you specify in this field. Only fields that are assigned a menu in your origin site will be listed here.
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
