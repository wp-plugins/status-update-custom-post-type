=== Plugin Name ===
Contributors: shellab
Donate link: http://blog.andrewshell.org/
Tags: admin, custom post types, post type
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 0.1.2

Adds a Status Update Custom Post Type

== Description ==

This plugin creates a new custom post type of Status Update

By default a status update does not have a title.  You can post a status update
with embedded images and html markup if you wish.  The excerpt should not have
any html in it and by default the plugin will attempt to create a plain text
version of your status update.

For example, if your body contains the html:
  `<p>Wow I really love using <a href="http://shll.me/statustype">Status Update Custom Post Type</a>!</p>`
    
It will automatically generate an excerpt of:
  `Wow I really love using Status Update Custom Post Type - http://shll.me/statustype`
    
It will also try to pull the alt or title attributes out of images so:
  `<img src="http://example.com/image.jpg" alt="Example Image" />`
    
Should turn into:
  `Example Image - http://example.com/image.jpg`
    
By default your status updates do not have title, do not generate titles and
I remove the title tag from the RSS feeds.  This may mean your feeds won't
look correct in Google Reader but this is intentional.  For more information
consult the following post: 
http://scripting.com/stories/2011/03/15/twitterPostsDontHaveTitles.html#p5574

This plugin also makes sure your status updates show up on the homepage and in
your main RSS feed.  I don't know if the way I'm doing it will cause problems
so please contact me if it interferes with anything your doing.  I'm not finding
a lot of documentation about this.  So for now it's trial and error.

== Installation ==

1. Create a status-type directory in your plugins directory. Typically that's wp-content/plugins/status-type/.
2. Into this new directory upload the plugin files (status_type.php, etc.)
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I ask a question? =

Send the author, [Andrew Shell](http://blog.andrewshell.org/contact-andrew) a 
message or post a comment [here](http://blog.andrewshell.org/status-type).

== Screenshots ==

1. List of Status Updates 

2. Status Update edit page

== Changelog ==

= 0.1.0 =
Initial release

== Upgrade Notice ==

Nothing at this time.

== Feedback? ==

Got a bug to report? Or an enhancement to recommend? Or perhaps even some code
to submit for inclusion in the next release? Great! Share your feedback with
the author, [Andrew Shell](http://blog.andrewshell.org/contact-andrew) or post a
comment [here](http://blog.andrewshell.org/status-type).
