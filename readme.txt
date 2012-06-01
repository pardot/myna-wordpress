=== Myna for WordPress ===
Contributors: cliffseal
Donate link: http://pardot.com
Tags: maya, testing, MAB, decision theory, web content optimisation
Requires at least: 3.3.2
Tested up to: 3.3.2
Stable tag: 0.1

Plugin to incorporate Myna.

== Description ==

Manage your Maya experiments and variables from your WordPress installation, and include them in your template files or posts/pages/widgets.

== Installation ==

1. Upload the `myna-for-wp` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add login information to the settings page (Settings > Myna).
1. Check the plugin file for return options, or use `myna_link($uuid,$link,$text='Click Here',$newwin=false,$nofollow=false)` in your template file. For instance:

`myna_link('2382dbab-3ed5-406b-be36-08032fab8042','http://google.com','Default Text',true);`

produces:

`<a href="http://google.com" rel="2382dbab-3ed5-406b-be36-08032fab8042" class="mynaSuggest" target="_blank">Default Text</a>`

The default text is then replaced by the Myna suggestion when the document has loaded.

== Requirements ==

*PHP 5
*Mcrypt

== Screenshots ==

1. Admin screenshot

== Changelog ==

= 0.1 =
Initial release.