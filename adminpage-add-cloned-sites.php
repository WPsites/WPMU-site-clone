<?php 
// check for subdir or subdomain install
if ( is_multisite() ) { $subdomain_install = is_subdomain_install(); }

// check if form is posted and if is safe
if($_POST['wpmusc_hidden'] == 'Y' && check_admin_referer('go_do_magic','nonce_field')) {
	//Form data sent -> do the magic
	include('magic-add-cloned-sites.php');	
} else {
	
//Normal adminpage display

// fetch existing blogs
//$the_blogs = get_blog_list( 1, 'all' ); could this also work? ->later
$tbl_blogs = $wpdb->prefix ."blogs";
$the_blogs = $wpdb->get_results( "SELECT blog_id, domain, path FROM $tbl_blogs WHERE blog_id <> '1'" );

if (!$subdomain_install) {
	// trim each value in the array from slashes (subdirs)
	function removeslash(&$value) { $value = str_replace("/", "", $value); } 
	for ( $i = 0; $i < sizeof( $the_blogs ); $i++ ) {
		array_walk($the_blogs[$i], 'removeslash');
	}
}

// fetch existing users
$tbl_users = $wpdb->prefix ."users";
$the_users = $wpdb->get_results( "SELECT ID, user_login FROM $tbl_users" );

// check for errors
if(!$the_blogs) { $error['blogs'] = "there are no templates to choose from"; }
if(!$the_users) { $error['users'] = "there are no users, which is impossible.."; }

// if there are no errors continue
if(!$error) {
?>
<div class="wrap">
	<?php    echo "<h2>" . __( 'Batch Add Cloned Sites for WPMU', 'wpmusc_trdom' ) . "</h2>"; ?>
	<form id="wpmusc_form" name="wpmusc_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <?php wp_nonce_field('go_do_magic','nonce_field'); ?>

		<input type="hidden" name="wpmusc_hidden" value="Y">
		<?php    echo "<h4>" . __( 'Select the blog which will act as a template for the new sites', 'wpmusc_trdom' ) . "</h4>"; ?>
		<p><?php _e("Select the template site which will be cloned:", 'wpmusc_trdom' ); ?>
		<select name="wpmusc_template_id" id="wpmusc_template_id">
		<?php // loop through blogs and echo them
		if ($subdomain_install) {
			foreach ($the_blogs as $a_blog) {
				echo "<option value=\"$a_blog->blog_id\">$a_blog->domain</option>";
			}
		} else { 
			foreach ($the_blogs as $a_blog) {
				echo "<option value=\"$a_blog->blog_id\">$a_blog->path</option>";
			}
		}
		?>
		</select>
		</p>
		<p><?php _e("Select the user who will become the admin for the new site(s):", 'wpmusc_trdom' ); ?>
		<select name="wpmusc_userid" id="wpmusc_userid">
		<?php // loop through users and echo them
		foreach ($the_users as $a_user) {
			echo "<option value=\"$a_user->ID\">$a_user->user_login</option>";
		}
		?>
		</select>
		</p>
        <? /*
		<p><?php _e("Add a single site or batch add multiple sites?", 'wpmusc_trdom' ); ?>
		Single <input type="radio" name="wpmusc_single" value="single"> / 
		<input type="radio" name="wpmusc_single" value="multiple" checked> Multiple
		</input>
		</p>
		*/ ?>
        <p>
		<label for="wpmusc_single" class="checkboxlabel"><?php _e("Choose multiple sites, single doesnt work:", 'wpmusc_trdom' ); ?></label>
        <input type="checkbox" id="wpmusc_single" name="wpmusc_multiple" data-on="Multiple sites" data-off="Single site" checked="checked" />
        </p>
        <p>
		<label for="wpmusc_domainmap" class="checkboxlabel"><?php _e("Domainmap or just clone the new sites:", 'wpmusc_trdom' ); ?></label>
        <input type="checkbox" id="wpmusc_domainmap" name="wpmusc_domainmap" data-on="Automatically domainmap please" data-off="Just clone please" checked="checked" />
        </p>
      <p>
		<label for="wpmusc_copyimages" class="checkboxlabel"><?php _e("Copy all images and uploads from template to new blog(s):", 'wpmusc_trdom' ); ?></label>
        <input type="checkbox" id="wpmusc_copyimages" name="wpmusc_copyimages" data-on="Yes, copy images" data-off="No, don't copy images" checked="checked" />
      </p>
            <p>
    	<label for="wpmusc_replaceurl" class="checkboxlabel"><?php _e("Replace any occurrence of the template sites URLs with the new URL :", 'wpmusc_trdom' ); ?></label>
        <input type="checkbox" id="wpmusc_replaceurl" name="wpmusc_replaceurl" data-on="Yes, replace URLs" data-off="No, don't replace URLs" checked="checked" />
      </p>
       
<?php /*
        <p>
		<label for="wpmusc_posts" class="checkboxlabel"><?php _e("Copy all current Posts to the new sites:", 'wpmusc_trdom' ); ?></label>
        <input type="checkbox" id="wpmusc_posts" name="wpmusc_posts" data-on="Yes, copy them" data-off="No, clear them" checked="checked" />
        </p>       
        <p>
		<label for="wpmusc_pages" class="checkboxlabel"><?php _e("Copy all current Pages to the new sites:", 'wpmusc_trdom' ); ?></label>
        <input type="checkbox" id="wpmusc_pages" name="wpmusc_pages" data-on="Yes, copy them" data-off="No, clear them" checked="checked" />
        </p>
*/ ?>
		<hr/>
		<div id="singlebox">
		<?php echo "<h4>" . __( 'Add a single site - Enter details for the new site', 'wpmusc_trdom', 'wpmusc_trdom' ) . "</h4>"; ?>
		<p><label for="wpmusc_siteurl"><?php _e("New Site URL (without www):", 'wpmusc_trdom' ); ?></label>
		<input type="text" name="wpmusc_siteurl" size="30"><?php _e(" example: newdomain.com", 'wpmusc_trdom' ); ?></p>
		<p><label for="wpmusc_blogname"><?php _e("New Site Title:", 'wpmusc_trdom' ); ?></label>
		<input type="text" name="wpmusc_blogname" size="30"><?php _e(" example: My new blog (Leave empty for domainname!)", 'wpmusc_trdom' ); ?></p>
		<p><label for="wpmusc_blogdescription"><?php _e("New Site Description:", 'wpmusc_trdom' ); ?></label>
		<input type="text" name="wpmusc_blogdescription" size="30"><?php _e(" example: Just another wordpress site", 'wpmusc_trdom' ); ?></p><br/>
		<hr/>
		</div>
		<div id="multiplebox">
		<?php echo "<h4>" . __( 'Batch add multiple sites - Type/paste in details for the new sites', 'wpmusc_trdom' ) . "</h4>"; ?>
		<?php _e('Paste your sites in this textarea, one site on each line.<br/>Separate the values with a comma.', 'wpmusc_trdom' ) ?>
		<?php _e('The template is: "new_site_url, site_description, site_name".<br/><br/>
		Example: newdomain.com, Just another wordpress site, My new blog ', 'wpmusc_trdom' ) ?>
		<p><textarea rows="6" cols="80" name="wpmusc_new_sites"></textarea></p>
		<?php _e('Note: when leaving \'site_name\' empty, the new_site_url will be used as the site name!', 'wpmusc_trdom' ) ?><br/>
        <?php _e('Note: when just cloning, enter the name of the subdirectory or subdomain instead of the new_site_url. JUST THE NAME, not the full URL!', 'wpmusc_trdom' ) ?><br/>
        <p><?php _e('-> please consider donating, especially when you use this plugin to make money out of your cloned sites. Thanks! <-', 'wpmusc_trdom' ) ?></p>
		<hr/>
		</div>
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Add Cloned Sites', 'wpmusc_trdom' ) ?>" />
		</p>
	</form>
</div>
<?php // and if there are errors what to do?
} else { 
?>
<div class="wrap">
	<?php echo "<h2>" . __( 'Add Cloned Sites for WPMU Options - errors found', 'wpmusc_trdom' ) . "</h2>"; ?>
	<?php if ($error['blogs']){ // error message for no templates found ?>
	<?php echo "<h4>" . __( 'Could not find any sites which could be used as a templates', 'wpmusc_trdom' ) . "</h4>"; ?>
	<?php _e( 'You need to add at least one site before you can clone a site', 'wpmusc_trdom' ); ?>
	<?php } ?>
</div>
<?php } // end error check ?>
<?php } // end posted check ?>
