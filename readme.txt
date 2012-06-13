=== Add Clone Sites for WPMU (batch) ===
Contributors: fritsjan
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paypal%40fritsjan%2enl&lc=NL&item_name=Add%20Clone%20Sites%20for%20WPMU%20plugin&item_number=donatelink%2dacswpmu&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: clone, wpmu, batch create, multi site, add cloned sites, add site, domainmapping, domainmap, donncha, wordpress mu domain mapping, cloning, clone blog, template
Requires at least: 2.9.2
Tested up to: 3.1.2
Stable tag: trunk

Batch add new sites on Wordpress MU / Wordpress Network while using a template site to clone, includes batch domainmapping of the new sites.

== Description ==

With this plugin you can simply batch create / add a bunch of sites to your WPMU install (Wordpress Network) and use one of your existing sites as a template for the new sites. The existing blog will be cloned exactly including posts, layout, settings, etc. The plugin also takes care of domainmapping the newly created sites. (depends on 'WordPress MU Domain Mapping' by Donncha to be installed)

This plugin will save you a lot of time.
Even adding one site is faster with this pluging compared to the normal way: add site, domainmap it, set everything up.

Update: You can now also use it without domainmapping, so just cloning sites in a batch! Insert the name of the subdir or subdomain you want to make in the place of the url.

== Installation ==

1. Install the plugin in the usual way into the WordPress plugins folder.
1. Network activate the plugin (it has only effect on the network admin area, so it is safe to network activate.

That's it. Now you can go to the Network Admin area and there you will see the plugin in the sites menu

The preferred workflow is as follows:

After setting up a Wordpress network based on subdomains and installing and activating 'Wordpress MU Domain Mapping' by Donncha and this plugin you are good to go.

1. First you need a template site setup to clone.
1. Add a new site, set it up completely with plugins, themes, posts, etc.
1. I suggest you give the sitename a prefix like tmp-, for example 'tmp-foobarsites'. This will make it easyer for you to recognise the template sites from the normal sites. Also, in a future release of this plugin filtering on a prefix will be supported.
1. prepare a textfile / excel file with the url's, blognames, and blogdescriptions you want to create. Use the format: "new_site_url, site_description, site_name". If you leave the site_name empty, it will take the url as the sitename. Don't use http:// and think if you want the site to use the prefix www. or not.
1. Go to the Network Admin area and click on add clone sites in the sites menu.
1. choose your template site from the dropdown list and select a admin user for the domains.
1. copy paste the prepared textfile in the field and hit clone.
1. That's it, just wait till it is finished.
1. After saving so much time for you, make sure you buy me a coffee!

== Frequently Asked Questions ==

= Where can I find the plugin after activation? =

You can find it in the Network Admin area in the Sites Menu.

= It gives an error on domainmapping =

Because this plugin safes you time adding sites by cloning a template AND domainmapping them in one batch, this plugin depends on the 'WordPress MU Domain Mapping' plugin by Donncha. In order to make the domainmapping plugin work you might need to log out and in again after installing it.

= Why can't I clone the main site? =

I chose not to because the main site can be slightly different in structure than a new network site.
Therefore the plugin is not looking for the main site in the template selection.
Make a new site manually first, edit it to your whishing (no domainmapping required), and use that site as a template to clone.

Tip: give the template site a distinctive name by giving it a prefix. For example: 'tmp-foobarsites'.  
Selecting on prefixes like 'tmp-' will be a future feature of this plugin.

= It doensn't work =

Please mention this on my pluginpage, so I can have a look at it.

= How does it handle links to images in posts from the template? =

You can now choose to copy all images and uploads from the template. Not that this will have impact on your hosting diskspace depending on how many uploads and clones you make.

== Screenshots ==

No screenshots yet.

== Changelog ==

= 0.8.4.2 =
* Minor bugfix in jquery code which stopped the buttons from showing up

= 0.8.4.1 =
* Stopped logging for development purposes, due to too much spam

= 0.8.4 =
* Made it possible to just batch clone without using domainmapping. So no valid new url is needed.
* Added the option to copy images and uploads from the template to the new blogs (note that this will take up extra space on your account!)

= 0.8.3 =
* Minor change: renamed some functions in order not to collide with functions of other plugins.
* Fixed duplicity check, in some cases you could make sites with the same url..

= 0.8.2 =
* Added support for subdirectory installs, now both subdomain as well as subdirectory network installs will work.
* Changed the urls of the sites back to wp network urls (http://site.maindomain.com or http://maindomain.com/site) in stead of using its domainmapped urls.

= 0.8.1 =
* This is the first stable version, sorry due to wrong links it did not work properly...
* Wordpress made a different directory for the plugin which made links to css and javascript files fail. Now fixed.

= 0.8 =
* The first 'stable' version to be released public
* Added permalink updating (permalinks did only work after viewing the permalinks settings in the dashboard)
* Added nice graphics

== Upgrade Notice ==

= 0.8.4.1 =
* Stopped logging for development purposes, due to too much spam

= 0.8.4 =
* Made it possible to just batch clone without using domainmapping. So no valid new url is needed.
* Added the option to copy images and uploads from the template to the new blogs (note that this will take up extra space on your account!)

= 0.8.3 =
* Minor change: renamed some functions in order not to collide with functions of other plugins.
* Fixed duplicity check, in some cases you could make sites with the same url..

= 0.8.2 =
This plugin worked only for subsomain network installs, now it should also work for subdirectory installs