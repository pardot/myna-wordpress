=== Myna for WordPress ===
Contributors: cliffseal
Donate link: http://pardot.com
Tags: myna, testing, MAB, decision theory, web content optimisation
Requires at least: 3.3.2
Tested up to: 3.3.2
Stable tag: 0.1

Plugin to incorporate Myna.

== Description ==

Manage your Myna experiments and variables from your WordPress installation, and include them in your template files or posts/pages/widgets.

== Installation ==

1. Upload the `myna-for-wp` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add login information to the settings page (Settings > Myna).
1. Add experiments and variables on the settings page as needed.
1. Use the shortcode in your posts and pages, use the `myna_link` function in your template file, or use the more advanced `get_myna_var` function.

= Shortcode =

The shortcode allows you to place a dynamic Myna link in a post (of any type) or page. The options are:

`[myna uuid="string" link="string" newwin=boolean]Link Text[/myna]`

*'uuid' is your Myna Experiment UUID.
*'link' is where your created link will point (href).
*'newwin' is a true/false that will make the link open in a new window (target="_blank").

The content between the shortcode brackets is the default link text. This is what will appear if a user has JavaScript disabled, as your Myna suggestion will replace this text after the page loads. It's a good practice to put one of your potential suggestions here to minimize the visual change. If you don't put anything in here, the default text will be 'Click Here'.

So, for intance:

`[myna uuid="72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d" link="http://google.com" newwin=true]Testing This[/myna]` 

produces:

`<a href="http://google.com" rel="72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d" target="_blank" class="mynaSuggest">Myna Suggestion (which replaced Testing This)</a>`

= Main Template Function =

This is the same function as the shortcode, but designed to be used in theme/template files. Using the same guidelines as above, the options are:

`myna_link($uuid,$link,$text='Click Here',$newwin=false)`

The only difference is that the default text (to be replaced on load by a Myna suggestion) is the third parameter in the function. So, for instance:

`myna_link('72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d','http://google.com','Default Text',true);`

produces:

`<a href="http://google.com" rel="72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d" class="mynaSuggest" target="_blank">Myna Suggestion (which replaced Default Text)</a>`

The default text is then replaced by the Myna suggestion when the document has loaded.

= Advanced Template Function =

This function fetches and returns the Myna Suggestion response for use in PHP. Instead of having JavaScript replace the text, you can fetch it prior to displaying the page—along with having access to the other options in the response. The only arguement is the UUID:

`get_myna_var($uuid)`

This returns the values for use. As of this plugin version, this response has the following name/value pairs (according to http://mynaweb.com/docs/api.html#suggest):

*`typename`: suggestion
*`token`: String This is a unique identifier that must be sent back to the server when reward is called
*`choice`: String The name of the variant the experiment has chosen

So, for instance:

`get_myna_var('72a3dd4f-73f2-4a18-a99f-a14a6b3e8e0d')`

will allow you to `echo $myna->choice` and so on. **Please note**: this function is for advanced integration, for whatever reason you may have. It will *not* automatically register the success of your experiment response; you'll need to find a way to send the token back to Myna.

== Requirements ==

*PHP 5
*Mcrypt

== Screenshots ==

1. Admin screenshot

== Changelog ==

= 0.1 =
Initial release.