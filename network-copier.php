<?php
/**
 * Plugin Name: Network Menu Copier
 * Plugin URI: http://thoughtengineer.com/
 * Description:  Copies a specific menu from one sub site to one or more other subsites on the same network installation.
 * Version: 1.2.2
 * Author: Samer Bechara
 * Author URI: http://thoughtengineer.com/
 * Text Domain: network-menu-copier
 * Domain Path: /languages
 * Network: true 
 * License: GPL2
 */

/*  Copyright 2014  Samer Bechara  (email : sam@thoughtengineer.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Define plugin path and URL constant - makes it easier to include files 
define( 'NMC_PATH', plugin_dir_path( __FILE__ ) );
define( 'NMC_URL', plugin_dir_url(__FILE__));

// Require NetworkCopier class
require_once (NMC_PATH.'/lib/NetworkMenuCopier.php'); 

// Require Walker class - needed to properly copy parent-child menu items
require_once (NMC_PATH.'/lib/NetworkMenuWalker.php');

// Require Internal Classes used by the plugin
require_once (NMC_PATH.'/lib/internal/NMC_Fetcher.php');
require_once (NMC_PATH.'/lib/internal/NMC_Parser.php');