<?php
/*
Plugin Name: Add Cloned Sites for WPMU (batch)
Plugin URI: http://productbakery.com/wordpress/add-clone-sites-for-wpmu-batch
Description: With this plugin you can simply batch add a bunch of domain names / sites to your WPMU install and use one of your existing sites as a template for the new sites. The existing blog will be cloned exactly including posts, layout, settings, etc. The plugin also takes care of domainmapping the newly created sites. (needs 'WordPress MU Domain Mapping' by Donncha to be installed). New is the option to clone without domainmapping, so pure cloning in batch! Please donate after each batch, I do need the caffeine, thanks!
Version: 0.8.4.2
Author: Frits Jan van Kempen
Author URI: http://productbakery.com
License: GPL2
*/

/*  Copyright 2011  Frits Jan van Kempen  (email : info@productbakery.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
	
	!!BUT I APPRECIATE IT IF YOU EMAIL ME ON FOREHAND BEFORE USING THIS CODE !!

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// Localization options

if(!load_plugin_textdomain('acswpmu','/wp-content/languages/')) {
	load_plugin_textdomain('acswpmu','/wp-content/plugins/add-cloned-sites-for-wpmu-batch/lang/');
}

// initialize plugin
add_action( 'admin_init', 'acswpmu_admin_init' );
// Add a menu to the network admin page
add_action('network_admin_menu', 'acswpmu_plugin_menu');

function acswpmu_admin_init() {
       /* Register our stylesheet. */
       wp_register_style( 'acswpmuStylesheet', WP_PLUGIN_URL . '/add-cloned-sites-for-wpmu-batch/css/style.css' );
       wp_register_style( 'checkboxStylesheet', WP_PLUGIN_URL . '/add-cloned-sites-for-wpmu-batch/jquery.tzCheckbox/jquery.tzCheckbox.css' );
       wp_register_style( 'jqueryUIStylesheet', WP_PLUGIN_URL . '/add-cloned-sites-for-wpmu-batch/css/smoothness/smoothness.css' );
	   /* Register our script. */
       wp_register_script( 'acswpmuScript', plugins_url('/js/myscript.js', __FILE__) );
       wp_register_script( 'checkboxScript', plugins_url('/jquery.tzCheckbox/jquery.tzCheckbox.js', __FILE__) );

   }

function acswpmu_plugin_menu() {
	/* register the pluginpage */
	$page = add_submenu_page('sites.php', 'Add Cloned Sites for WPMU', 'Add Cloned Sites', 'manage_options', 'acswpmu_admin_page', 'acswpmu_admin_page');
	
	/* Using registered $page handle to hook stylesheet loading */
	add_action( 'admin_print_styles-' . $page, 'my_plugin_admin_styles' );
}

function my_plugin_admin_styles() {
   /* It will be called only on your plugin admin page, enqueue our stylesheet here */
   wp_enqueue_style( 'acswpmuStylesheet' );
   wp_enqueue_style( 'checkboxStylesheet' );
   wp_enqueue_style( 'jqueryUIStylesheet' );
   // We will be using jquery to make everyting look neat.
   wp_enqueue_script( 'jquery' ); 
   wp_enqueue_script( 'jquery-ui-core', array('jquery') );
   wp_enqueue_script( 'jquery-ui-widget', array('jquery-ui-core') );
   wp_enqueue_script( 'jquery-ui-tabs', array('jquery-ui-widget') );
   wp_enqueue_script( 'checkboxScript', array('jquery') );
   wp_enqueue_script( 'acswpmuScript' );
}

// Get the Add Cloned sites admin page
function acswpmu_admin_page() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'acswpmu_trdom') );
	}
	// register globals
	global $wpdb;
	// include the adminpage
	include('adminpage-add-cloned-sites.php');
}
?>