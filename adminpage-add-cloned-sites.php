<?php 
// check for subdir or subdomain install
if ( is_multisite() ) { $subdomain_install = is_subdomain_install(); }

// check if form is posted and if is safe
if($_POST['acswpmu_hidden'] == 'Y' && check_admin_referer('go_do_magic','nonce_field')) {
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
	<?php    echo "<h2>" . __( 'Batch Add Cloned Sites for WPMU', 'acswpmu_trdom' ) . "</h2>"; ?>
	<form id="acswpmu_form" name="acswpmu_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
    <?php wp_nonce_field('go_do_magic','nonce_field'); ?>

		<input type="hidden" name="acswpmu_hidden" value="Y">
		<?php    echo "<h4>" . __( 'Select the blog which will act as a template for the new sites', 'acswpmu_trdom' ) . "</h4>"; ?>
		<p><?php _e("Select the template site which will be cloned:", 'acswpmu_trdom' ); ?>
		<select name="acswpmu_template_id" id="acswpmu_template_id">
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
		<p><?php _e("Select the user who will become the admin for the new site(s):", 'acswpmu_trdom' ); ?>
		<select name="acswpmu_userid" id="acswpmu_userid">
		<?php // loop through users and echo them
		foreach ($the_users as $a_user) {
			echo "<option value=\"$a_user->ID\">$a_user->user_login</option>";
		}
		?>
		</select>
		</p>
        <? /*
		<p><?php _e("Add a single site or batch add multiple sites?", 'acswpmu_trdom' ); ?>
		Single <input type="radio" name="acswpmu_single" value="single"> / 
		<input type="radio" name="acswpmu_single" value="multiple" checked> Multiple
		</input>
		</p>
		*/ ?>
        <p>
		<label for="acswpmu_single" class="checkboxlabel"><?php _e("Choose multiple sites, single doesnt work:", 'acswpmu_trdom' ); ?></label>
        <input type="checkbox" id="acswpmu_single" name="acswpmu_multiple" data-on="Multiple sites" data-off="Single site" checked="checked" />
        </p>
        <p>
		<label for="acswpmu_domainmap" class="checkboxlabel"><?php _e("Domainmap or just clone the new sites:", 'acswpmu_trdom' ); ?></label>
        <input type="checkbox" id="acswpmu_domainmap" name="acswpmu_domainmap" data-on="Automatically domainmap please" data-off="Just clone please" checked="checked" />
        </p>
      <p>
		<label for="acswpmu_copyimages" class="checkboxlabel"><?php _e("Copy all images and uploads from template to new blog(s):", 'acswpmu_trdom' ); ?></label>
        <input type="checkbox" id="acswpmu_copyimages" name="acswpmu_copyimages" data-on="Yes, copy images" data-off="No, don't copy images" checked="checked" />
      </p>
       
<?php /*
        <p>
		<label for="acswpmu_posts" class="checkboxlabel"><?php _e("Copy all current Posts to the new sites:", 'acswpmu_trdom' ); ?></label>
        <input type="checkbox" id="acswpmu_posts" name="acswpmu_posts" data-on="Yes, copy them" data-off="No, clear them" checked="checked" />
        </p>       
        <p>
		<label for="acswpmu_pages" class="checkboxlabel"><?php _e("Copy all current Pages to the new sites:", 'acswpmu_trdom' ); ?></label>
        <input type="checkbox" id="acswpmu_pages" name="acswpmu_pages" data-on="Yes, copy them" data-off="No, clear them" checked="checked" />
        </p>
*/ ?>
		<hr/>
		<div id="singlebox">
		<?php echo "<h4>" . __( 'Add a single site - Enter details for the new site', 'acswpmu_trdom', 'acswpmu_trdom' ) . "</h4>"; ?>
		<p><label for="acswpmu_siteurl"><?php _e("New Site URL (without www):", 'acswpmu_trdom' ); ?></label>
		<input type="text" name="acswpmu_siteurl" size="30"><?php _e(" example: newdomain.com", 'acswpmu_trdom' ); ?></p>
		<p><label for="acswpmu_blogname"><?php _e("New Site Title:", 'acswpmu_trdom' ); ?></label>
		<input type="text" name="acswpmu_blogname" size="30"><?php _e(" example: My new blog (Leave empty for domainname!)", 'acswpmu_trdom' ); ?></p>
		<p><label for="acswpmu_blogdescription"><?php _e("New Site Description:", 'acswpmu_trdom' ); ?></label>
		<input type="text" name="acswpmu_blogdescription" size="30"><?php _e(" example: Just another wordpress site", 'acswpmu_trdom' ); ?></p><br/>
		<hr/>
		</div>
		<div id="multiplebox">
		<?php echo "<h4>" . __( 'Batch add multiple sites - Type/paste in details for the new sites', 'acswpmu_trdom' ) . "</h4>"; ?>
		<?php _e('Paste your sites in this textarea, one site on each line.<br/>Separate the values with a comma.', 'acswpmu_trdom' ) ?>
		<?php _e('The template is: "new_site_url, site_description, site_name".<br/><br/>
		Example: newdomain.com, Just another wordpress site, My new blog ', 'acswpmu_trdom' ) ?>
		<p><textarea rows="6" cols="80" name="acswpmu_new_sites"></textarea></p>
		<?php _e('Note: when leaving \'site_name\' empty, the new_site_url will be used as the site name!', 'acswpmu_trdom' ) ?><br/>
        <?php _e('Note: when just cloning, enter the name of the subdirectory or subdomain instead of the new_site_url. JUST THE NAME, not the full URL!', 'acswpmu_trdom' ) ?><br/>
        <p><?php _e('-> please consider donating, especially when you use this plugin to make money out of your cloned sites. Thanks! <-', 'acswpmu_trdom' ) ?></p>
		<hr/>
		</div>
		<p class="submit">
		<input type="submit" name="Submit" value="<?php _e('Add Cloned Sites', 'acswpmu_trdom' ) ?>" />
		</p>
	</form>
</div>
<?php // and if there are errors what to do?
} else { 
?>
<div class="wrap">
	<?php echo "<h2>" . __( 'Add Cloned Sites for WPMU Options - errors found', 'acswpmu_trdom' ) . "</h2>"; ?>
	<?php if ($error['blogs']){ // error message for no templates found ?>
	<?php echo "<h4>" . __( 'Could not find any sites which could be used as a templates', 'acswpmu_trdom' ) . "</h4>"; ?>
	<?php _e( 'You need to add at least one site before you can clone a site', 'acswpmu_trdom' ); ?>
	<?php } ?>
</div>
<?php } // end error check ?>
<?php } // end posted check ?>