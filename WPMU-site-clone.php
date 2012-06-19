<?php
/*
Plugin Name: WPMU site clone
Plugin URI: https://github.com/WPsites/WPMU-site-clone
Description: With this plugin you can simply batch add a bunch of domain names / sites to your WPMU install and use one of your existing sites as a template for the new sites. The existing blog will be cloned exactly including posts, layout, settings, etc. The plugin also takes care of domainmapping the newly created sites. (needs 'WordPress MU Domain Mapping' by Donncha to be installed). New is the option to clone without domainmapping, so pure cloning in batch! Please donate after each batch, I do need the caffeine, thanks!
Version: 0.9
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


// initialize plugin
add_action( 'admin_init', 'wpmusc_admin_init' );
// Add a menu to the network admin page
add_action('network_admin_menu', 'wpmusc_plugin_menu');

function wpmusc_admin_init() {
       /* Register our stylesheet. */
       wp_register_style( 'wpmuscStylesheet', WP_PLUGIN_URL . '/WPMU-site-clone/css/style.css' );
       wp_register_style( 'checkboxStylesheet', WP_PLUGIN_URL . '/WPMU-site-clone/jquery.tzCheckbox/jquery.tzCheckbox.css' );
       wp_register_style( 'jqueryUIStylesheet', WP_PLUGIN_URL . '/WPMU-site-clone/css/smoothness/smoothness.css' );
	   /* Register our script. */
       wp_register_script( 'wpmuscScript', plugins_url('/js/myscript.js', __FILE__) );
       wp_register_script( 'checkboxScript', plugins_url('/jquery.tzCheckbox/jquery.tzCheckbox.js', __FILE__) );

   }

function wpmusc_plugin_menu() {
	/* register the pluginpage */
	$page = add_submenu_page('sites.php', 'Add Cloned Sites for WPMU', 'Add Cloned Sites', 'manage_options', 'wpmusc_admin_page', 'wpmusc_admin_page');
	
	/* Using registered $page handle to hook stylesheet loading */
	add_action( 'admin_print_styles-' . $page, 'my_plugin_admin_styles' );
}

function my_plugin_admin_styles() {
   /* It will be called only on your plugin admin page, enqueue our stylesheet here */
   wp_enqueue_style( 'wpmuscStylesheet' );
   wp_enqueue_style( 'checkboxStylesheet' );
   wp_enqueue_style( 'jqueryUIStylesheet' );
   // We will be using jquery to make everyting look neat.
   wp_enqueue_script( 'jquery' ); 
   wp_enqueue_script( 'jquery-ui-core', array('jquery') );
   wp_enqueue_script( 'jquery-ui-widget', array('jquery-ui-core') );
   wp_enqueue_script( 'jquery-ui-tabs', array('jquery-ui-widget') );
   wp_enqueue_script( 'checkboxScript', array('jquery') );
   wp_enqueue_script( 'wpmuscScript' );
}

// Get the Add Cloned sites admin page
function wpmusc_admin_page() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'wpmusc_trdom') );
	}
	// register globals
	global $wpdb;
	// include the adminpage
	include('adminpage-add-cloned-sites.php');
}


/*
 * replace page content with template site if featured enabled
*/

//main function to set actions/filters
function try_use_parent_content(){
  $linked_to_parent = get_option('add-cloned-sites-linked-pages');
  if ($linked_to_parent){
      
        add_filter( 'the_title', 'my_title_filter_parent', 1, 2); //title
        add_filter( 'the_content', 'my_the_content_filter_parent', 1 ); //content
        
        add_action('do_meta_boxes', 'wpmusc_topof_pageedit_screen', 10, 3);  //output message to page edit screen
        add_action('pre_post_update', 'wpmusc_doc_make_master'); //make this page a master when it gets edited
        
        add_action('wp_head', 'wpmusc_noindex_nonmasters');
        
  }
}
add_action('init', 'try_use_parent_content'); 
add_action('admin_init', 'try_use_parent_content'); 


// content
function my_the_content_filter_parent($content) { 
  
    if ( wpmusc_doc_is_master() ){
       return $content; //return this doc
    }else{
       
        if ( is_array( $from_master = wpmusc_doc_from_master() ) ) {
          
            return $from_master['post_content'];
            
        }else{ //fallback on title passed in
            return $content;
        }
       
    }
  
}

//title
function my_title_filter_parent($title, $id) {
  
    //if this page is the master then do nothing. Otherwise get the page content from the master/template site
    if ( wpmusc_doc_is_master($id) ){
       return $title; //return this doc
    }else{
       
        if ( is_array( $from_master = wpmusc_doc_from_master($id) ) ) {
          
            return $from_master['post_title'];
            
        }else{ //fallback on title passed in
            return $title;
        }
       
    }
   
}



//get the master doc from the template site
function wpmusc_doc_from_master($id=''){
   
    if ($id>0){
        $post_id = $id;
    }else{
        global $post;
        $post_id = $post->ID;
    }
    
    if ( isset($GLOBALS['wpmusc_doc_from_master']['ID'] ) && $GLOBALS['wpmusc_doc_from_master']['ID'] == $post_id ){
        
        return $GLOBALS['wpmusc_doc_from_master']; //use stored version
        
    }else{
        global $wpdb;
        
        $master_blog_info = unserialize(get_option('add-cloned-sites'));
        
        $post_table_name = str_replace($wpdb->blogid, $master_blog_info['template-id'], $wpdb->posts); //replace current blog id with master id to get correct table name
        
        $master_query = "SELECT * FROM {$post_table_name} ".
                        "WHERE post_type='page' AND ID='{$post_id}'; ";
        
        $GLOBALS['wpmusc_doc_from_master'] = $wpdb->get_row($master_query, ARRAY_A); //use the master version
        
        return $GLOBALS['wpmusc_doc_from_master'];
    }
    
}    

//check this page is master
function wpmusc_doc_is_master($id=''){
    
    if ($id>0){
        $post_id = $id;
    }else{
        global $post;
        $post_id = $post->ID;
    }
    
    return get_post_meta($post_id, 'wpmusc_doc_is_master', true);  
}


//remove quick edit link
function wpmusc_admin_pagelist( $actions ) {
 global $post;
 
 if ( wpmusc_doc_is_master($post->ID) ){ //if this page is a master then allow quick edit, otherwise remove
    

 
 }else{
     unset( $actions['inline hide-if-no-js'] ); //remove quick edit
 }
 
 return $actions;
}
add_filter('page_row_actions','wpmusc_admin_pagelist',10,1);


//indicate if the page is linked, in the page list (edit.php)
function wpmusc_admin_pagelist_linked_indicator( $h_time, $post ) {

 if ( wpmusc_doc_is_master($post->ID) ){ //if this page is a master then allow quick edit, otherwise remove
    return $h_time;
 }else{
     return $h_time . "<br /><strong>" . __('Linked to master &#8734;', 'wpmusc_trdom') . "</strong>";
 }
 
 return $actions;
}
add_filter('post_date_column_time','wpmusc_admin_pagelist_linked_indicator',50,2);


function wpmusc_topof_pageedit_screen($post_type, $pos, $post){
    
    if ($pos === 'normal'){
        if ( wpmusc_doc_is_master($post->ID) ){ 
            //do nothing as this page is the master
        }else{
            
            //show message to say this page is associated to the master page
            showMessage( __('This page is inked to a master version &#8734;', 'wpmusc_trdom').
                            '<br />' . __('If you make changes to the contents of this page, this page will no longer be associated to the master.', 'wpmusc_trdom')
                            , true);
        }
    }
}

function wpmusc_doc_make_master($id) {

    if ( wpmusc_doc_is_master() ){
       //do nothing this page is already a master
    }else{
       
        //give this page some meta to mark it as a master
       add_post_meta($id, 'wpmusc_doc_is_master', true);  
    }
}

function wpmusc_noindex_nonmasters(){
    
    if ( wpmusc_doc_is_master() ){
       //do nothing this page is already a master
    }else{
       
        //output no index but do follow links from the page, meta tag
       echo '<meta name="robots" content="noindex, follow" />';
    }
}



/**
 * Generic function to show a message to the user using WP's 
 * standard CSS classes to make use of the already-defined
 * message colour scheme.
 *
 * @param $message The message you want to tell the user.
 * @param $errormsg If true, the message is an error, so use 
 * the red message style. If false, the message is a status 
  * message, so use the yellow information message style.
 */
function showMessage($message, $errormsg = false)
{
    if ($errormsg) {
		echo '<div id="message" class="error">';
	}
	else {
		echo '<div id="message" class="updated fade">';
	}

    echo $message . "</div>";
}

?>
