<?php 
// STIL TODO for future releases:
// settings for choosing templates
// settings for options: - copy posts, data/ etc  
// set public or private (google)
// make update possible
// reward donations
// make translation ok ->

// some functions
function wpmusc_trim_value(&$value) { $value = trim(trim($value), ' ,');}
function wpmusc_valid_url($str){ return ( ! preg_match('/^([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $str)) ? FALSE : TRUE; }

// start timing
$time_start = microtime(true);
$error = NULL;
$cloned = NULL;
$failed = NULL;
 
// Get POST data
$template_id = $_POST['wpmusc_template_id'];
$user_id = $_POST['wpmusc_userid'];
if ($_POST['wpmusc_domainmap'] == 'on') { $domainmap = TRUE; } else { $domainmap = FALSE; }
if ($_POST['wpmusc_copyimages'] == 'on') { $copy_images = TRUE; } else { $copy_images = FALSE; }

if ($_POST['wpmusc_replaceurl'] == 'on') { $replaceUrl = TRUE; } else { $replaceUrl = FALSE; }

//if ($_POST['wpmusc_posts'] == 'on') { $copy_posts = TRUE; }
//if ($_POST['wpmusc_pages'] == 'on') { $copy_pages = TRUE; }

$pluginUrl = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));

// get admin email
$admin_info = get_userdata($user_id);
$admin_email = $admin_info->user_email;

// prepare/get data based on single / multi input
if ($_POST['wpmusc_multiple'] == 'on'){
	// get post and trim to remove whitespace
	$new_sites = trim($_POST['wpmusc_new_sites']);
	// explode the post to lines
	$the_array = explode( "\n", $new_sites );
	// explode each line 
	for ( $i = 0; $i < sizeof( $the_array ); $i++ ) {
		$the_array[$i] = explode( ",", $the_array[$i] );
		$the_array[$i][3] = $user_id;
		$the_array[$i][4] = $admin_email;
		$the_array[$i][5] = $template_id;
	}  
} else {
	// get data for single site
	$siteurl = $_POST['wpmusc_siteurl'];
	$blogname = $_POST['wpmusc_blogname'];
	$blogdescription = $_POST['wpmusc_blogdescription'];
	$the_array[0][0] = $siteurl;
	$the_array[0][1] = $blogdescription;
	$the_array[0][2] = $blogname;
	$the_array[0][3] = $user_id;
	$the_array[0][4] = $admin_email;
	$the_array[0][5] = $template_id;
}

// trim each value in the array from whitespaces and left comma's
for ( $i = 0; $i < sizeof( $the_array ); $i++ ) {
	array_walk($the_array[$i], 'wpmusc_trim_value');
}

	
// And now: here the loop starts
if (sizeof($the_array) > 0) {

echo "<h3>" . __( 'Adding cloned sites ', 'wpmusc_trdom' ) . "</h3>";

// create tabs ?>
<div id="tabs">
<ul>
	<li><a href="#tabs-1"><?php _e( 'Job log', 'wpmusc_trdom' ); ?></a></li>
	<li><a href="#tabs-2"><?php _e( 'Finished', 'wpmusc_trdom' ); ?></a></li>
</ul>
	
<?php
echo '<div id="tabs-1">';

foreach($the_array as $line) {

	//get the data from the array into strings
	$siteurl = $line[0];
	$blogdescription = $line[1];
	$blogname = $line[2];
	$user_id = $line[3];
	$admin_email = $line[4];
	$template_id = $line[5];
	// Prepare some data
	$dashedsiteurl = str_replace('.', '-', $siteurl);
	if ($subdomain_install) {
		$domain = $dashedsiteurl . "." . get_blog_details(1)->domain; 
		$fulldomain = $domain;
		$path = "/";
	} else {
		$domain = get_blog_details(1)->domain;
		$fulldomain = get_blog_details(1)->domain . "/" . $dashedsiteurl; 
		$path = "/" . $dashedsiteurl; 
	}
	echo "<h4>" . __( 'Start with creating site for ' . $siteurl, 'wpmusc_trdom' ) . "</h4>";

	//check for correct and non empty siteurl
	if ($domainmap){
		if (!wpmusc_valid_url($siteurl)){
			_e("<span class=\"error\">The url '$siteurl' is not a valid url.</span>", 'wpmusc_trdom' );
			$error = TRUE;
		}
	}
	
	// Check first if domain already exists then add a new site
	if ($subdomain_install){
		if(!$error){ 
			if($exist_id = $wpdb->get_var("SELECT blog_id FROM $wpdb->blogs WHERE domain = '$fulldomain'")) {
				_e("<span class=\"error\">The URL $fulldomain already exist, we skipped it!</span>", 'wpmusc_trdom' );
				$error = TRUE;		
			} else {
				// Start with adding the new blog to the blogs table
				$new_blog_id = insert_blog( $domain, $path, '1');
				if(is_integer($new_blog_id)) {
					_e("New site created with id: $new_blog_id<br/>", 'wpmusc_trdom' );
				} else {
					_e("<span class=\"error\">The URL $domain already exist, we skipped it!</span>", 'wpmusc_trdom' );
					$error = TRUE;
				}
			}
		}
	} else {
		if(!$error){ 
			if($exist_id = $wpdb->get_var("SELECT blog_id FROM $wpdb->blogs WHERE path = '$path/'")) {
				_e("<span class=\"error\">The URL $fulldomain already exist, we skipped it!</span>", 'wpmusc_trdom' );
				$error = TRUE;		
			} else {
				// Start with adding the new blog to the blogs table
				$new_blog_id = insert_blog( $domain, $path, '1');
				if(is_integer($new_blog_id)) {
					_e("New site created with id: $new_blog_id<br/>", 'wpmusc_trdom' );
				} else {
					_e("<span class=\"error\">The URL $fulldomain already exist, we skipped it!</span>", 'wpmusc_trdom' );
					$error = TRUE;
				}
			}
		}
	}
	
	//Next duplicate all tables from the template
	if(!$error){
			
		$template_like = $wpdb->prefix . $template_id . "_"; 
		$template_new = $wpdb->prefix . $new_blog_id . "_";
		$temp_like = str_replace('_', '\_', $template_like); //escape the _ for correct sql!!
		$template_tables = $wpdb->get_results( "SHOW TABLES LIKE '$temp_like%'", ARRAY_N );
		
		foreach ($template_tables as $old_table) {
			$new_table = str_replace($template_like, $template_new, $old_table[0]); 
			// check if table already exists
			if($wpdb->get_var("SHOW TABLES LIKE '$new_table'") != $new_table) {
				// duplicate the old table structure
				$result = $wpdb->query( "CREATE TABLE $new_table LIKE $old_table[0]" );
				if($result === FALSE) { 
					_e("<span class=\"error\">Failed to create $new_table.</span>", 'wpmusc_trdom' );
					$error = TRUE;
				} else { 
					_e("Table created: $new_table.<br>", 'wpmusc_trdom' );
					// copy data from old_table to new_table
					$result = $wpdb->query( "INSERT INTO $new_table SELECT * FROM $old_table[0]" );
					if($result === FALSE) {
						_e("<span class=\"error\">Failed to copy data from $old_table[0] to $new_table.</span>", 'wpmusc_trdom' );
						$error = TRUE;
					} else {
						_e("Copied data from $old_table[0] to $new_table.<br/>", 'wpmusc_trdom' );
					}						
				}
			} else { 
				_e("<span class=\"error\">The table $new_table already existed.</span>", 'wpmusc_trdom' );
				$error = TRUE;
			}
		}
	}

	// Then add user to the new blog
	if(!$error) {
		$role = "administrator";
		if ( add_user_to_blog( $new_blog_id, $user_id, $role ) ) {
			_e( 'Added user '.$user_id.' as '.$role.' to site '.$new_blog_id.'.<br/>',  'wpmusc_trdom' );
		} else {
			_e( 'Failed to add user '.$user_id.' as '.$role.' to site '.$new_blog_id.'.<br/>', 'wpmusc_trdom' );
			$error = TRUE;
		}
	}
	
	// Add custom data to newly duplicated blog
	if(!$error) {
		$full_url = "http://" . $fulldomain;
		if(!$blogname) { $blogname = $siteurl; }
		$fileupload_url = $full_url . "/files";
		
		// update the cloned table with the new data and blog_id
		update_blog_option ($new_blog_id, 'siteurl', $full_url);
		update_blog_option ($new_blog_id, 'blogname', $blogname);
		update_blog_option ($new_blog_id, 'blogdescription', $blogdescription);
		update_blog_option ($new_blog_id, 'admin_email', $admin_email);
    
		update_blog_option ($new_blog_id, 'home', $full_url);
		update_blog_option ($new_blog_id, 'fileupload_url', $fileupload_url);
		update_blog_option ($new_blog_id, 'upload_path', 'wp-content/blogs.dir/' . $new_blog_id . '/files');
		$new_options_table = $wpdb->prefix . $new_blog_id . '_options';
		$old_name = $wpdb->prefix . $template_id . '_user_roles';
		$new_name = $wpdb->prefix . $new_blog_id . '_user_roles';
		$result = $wpdb->update( $new_options_table, array('option_name' => $new_name), array('option_name' => $old_name));
		
		// 'check' if it went ok - NOTE: is just a basic check could give an error anyway...
		if(get_blog_option($new_blog_id, 'blogdescription') != $blogdescription) { 
			//$error = TRUE; 
			_e("<span class=\"error\">Maybe we had an error updating the options table with the new data.</span>", 'wpmusc_trdom' );
		} else { 
			_e("Updated the options table with cloned data<br>", 'wpmusc_trdom' );
		}
	}
	
	// add template_id to option table for later reference
	if(!$error) {
		$savearray = array ('template-id' => $template_id, 'lasttime' => time());
		add_blog_option ($new_blog_id, 'add-cloned-sites', serialize($savearray));
		//get it back with:
		//get_option('add-cloned-sites') == "" ? "" : $new = unserialize(get_option('add-cloned-sites'));
	}
	
	// Domainmap the newly cloned site
	if(!$error AND $domainmap) {
		$tbl_domain_map = $wpdb->prefix . "domain_mapping";
		// Check if domainmapping exists
		if($wpdb->get_var("SHOW TABLES LIKE '$tbl_domain_map'") == $tbl_domain_map) {
			// Map the domain
			$result = $wpdb->insert( $tbl_domain_map, array('blog_id' => $new_blog_id, 'domain' => $siteurl, 'active' => '1'));
			if($result) { 
				_e("New site mapped to domain: $siteurl<br/>", 'wpmusc_trdom' );
			} else { 
				_e("<span class=\"error\">Domain mapping for $siteurl failed</span>", 'wpmusc_trdom' );
				$error = TRUE;
			}
		} else {
			_e("<span class=\"error\">Domainmapping failed because the domainmap plugin is not activated!</span>", 'wpmusc_trdom' );
			$error = TRUE;
		}
	}
	
	// Copy images and uploads
	// SPECIAL NOTE: This part of the code (copy files) I got from the plugin:
	// "new blog templates" by Jason DeVelvis and Ulrich Sossou
	// Special thanks go to you guys!
	if(!$error AND $copy_images) {
		global $wp_filesystem;

		$dir_to_copy = ABSPATH . 'wp-content/blogs.dir/' . $template_id . '/files';
		$dir_to_copy_into = ABSPATH .'wp-content/blogs.dir/' . $new_blog_id . '/files';

		if ( is_dir( $dir_to_copy ) ) {

			if ( wp_mkdir_p( $dir_to_copy_into ) ) {

				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
				require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );

				if( isset( $wp_filesystem ) )
					$orig_filesystem = wp_clone( $wp_filesystem );
				$wp_filesystem = new WP_Filesystem_Direct( false );

				if ( ! defined('FS_CHMOD_DIR') )
					define('FS_CHMOD_DIR', 0755 );
				if ( ! defined('FS_CHMOD_FILE') )
					define('FS_CHMOD_FILE', 0644 );

				copy_dir( $dir_to_copy, $dir_to_copy_into );

				unset( $wp_filesystem );
				if( isset( $orig_filesystem ) )
					$wp_filesystem = wp_clone( $orig_filesystem );

				if ( @file_exists( $dir_to_copy_into . '/sitemap.xml' ) )
					@unlink( $dir_to_copy_into . '/sitemap.xml' );

			} else {
				_e("<span class=\"error\">Was unable to copy images and uploads!</span>", 'wpmusc_trdom' );
			}
		}
	}
    
    //replace old site urls with new sites URLs
    if(!$error AND $replaceUrl) {
        global $wpdb;
        require('safe-find-replace.php');
        
           $replace_old_url = get_blog_option ($template_id, 'siteurl'); //get old site url $siteurl is new site url
           
           $list_of_tables_toreplace = $wpdb->prefix . "${new_blog_id}_commentmeta," .
                                       $wpdb->prefix . "${new_blog_id}_comments," .
                                       $wpdb->prefix . "${new_blog_id}_links," .
                                       $wpdb->prefix . "${new_blog_id}_options," .
                                       $wpdb->prefix . "${new_blog_id}_postmeta," .
                                       $wpdb->prefix . "${new_blog_id}_posts," .
                                       $wpdb->prefix . "${new_blog_id}_terms," .
                                       $wpdb->prefix . "${new_blog_id}_relationships," .
                                       $wpdb->prefix . "${new_blog_id}_term_taxonomy";
        
        icit_srdb_replacer( $wpdb->dbh, $replace_old_url, $fullurl, split(',', $list_of_tables_toreplace ) ) ;
        
        
    }
	
	//reset permalink structure
	if(!$error) {
		switch_to_blog($new_blog_id);
		//_e("Switched from here to $new_blog_id to reset permalinks<br>", 'wpmusc_trdom' );
		global $wp_rewrite;
		$wp_rewrite->init();
		$wp_rewrite->flush_rules();
		//now that we are here, update the date of the new site
		wpmu_update_blogs_date( );
		//go back to admin
		restore_current_blog();
		_e("Permalinks updated.<br>", 'wpmusc_trdom' );	
	}

	// count succesfull and failed sites
	if(!$error) { 
		_e("Job done, sucesfully created $fulldomain with site id: $new_blog_id<br>", 'wpmusc_trdom' );
		if($domainmap) { _e("and mapped the site to: <a href=\"http://$siteurl\" target=\"test\">$siteurl</a>.<br>", 'wpmusc_trdom' ); }
		// count succesfull cloned sites
		$cloned[] = $siteurl;
	} else {
		// count failed sites
		$failed[] = $siteurl;
		$error = NULL;
	} 
	
}
echo '</div>';
}
?>
<?php
// feedback on statistics
$time_end = microtime(true);
$time = $time_end - $time_start;
$time = round($time, 4);
if (isset($cloned)) { $nr_sites = count($cloned); } else { $nr_sites = '0'; }
if (isset($failed)) { $nr_failed = count($failed); } else { $nr_failed = '0'; }
$timeforone = 3.5;
$normaltime = $nr_sites * $timeforone;

?>
    <div id="tabs-2" >
        <?php 
		echo '<h4>' . __( 'Job done, here are the results:', 'wpmusc_trdom' ) . '</h4>';
        if ($nr_sites > 0) { 
            _e("<p>It took only " .  $time . " seconds to create and clone ". $nr_sites . " sites!</p>", 'wpmusc_trdom' ); 
            
        }
        if ($failed) { 
            _e("<span class=\"error\"><p>Hey $nr_failed sites could not be cloned due to errors, you might want to check the log why they failed.</p></span>", 'wpmusc_trdom' );
        }
        ?>
    </div>
</div>
