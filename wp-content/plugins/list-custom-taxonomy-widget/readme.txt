=== List Custom Taxonomy Widget ===
Contributors: celloexpressions
Tags: custom taxonomy, custom tax, widget, sidebar, category, categories, taxonomy, custom category, custom categories, post types, custom post types, custom post type categories
Requires at least: 3.3
Tested up to: 3.6
Stable tag: 3.3
Description: Adds a multi-widget that lists a custom taxonomy (often used as categories or category types for a custom post type).
License: GPLv2

== Description ==
The List Custom Taxonomy Widget is a quick and easy way to display custom taxonomies. Simply choose the taxonomy name you want to display from an auto-populated list. You can also set a title to display for the widget. Multiple list custom taxonomy widgets can be added to the same and other sidebars as well. There are several display options (including as a dropdown), and it generally behaves similarly to the built-in categories widget but with the addition of custom taxonomies.

Please feel free to offer any feature suggestions you might have and I WILL consider them for future updates.

== Installation ==
1. Take the easy route and install through the wordpress plugin adder :) OR
1. Download the .zip file and upload the unzipped folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add the widget to your sidebar(s) with wordpress' drag-and-drop interface and configure the options.

== Frequently Asked Questions ==
= Can I Show Custom Taxonomies in a [Page/Header/non-widget-area] =
I'll save you the hassle of downloading the plugin and digging through its source code. Just use the `wp_list_categories()` function in your theme (or plugin) to display custom taxonomies as a list, and use `wp_dropdown_categories` for a dropdown (this one does require some supplementary html). This plugin is essentially a widget UI shell for these functions. If you want to use the plugin's code for it, go right ahead, but it really does just widgetize those functions.

= Where's the settings page? =
There is no (need for a) settings page. Simply go to appearance->widgets and drag the List Custom Taxonomy Widget into your sidebar(s) where you want to list the category/taxonomy.

= [insert option/feature] isn't working... =
The widgets API can sometimes get messy when I've added features in an update since you first used the widget. If you're having trouble seeing all of the options, first try clicking "save" on the widget, and if that doesn't work you can refresh individual widgets by removing them and creating a new instance (drag a new list custom taxonomy widget into your sidebar and set up the options from scratch).

= Can I Do ___? =
If you'd like to do something outside of the configuration options of this plugin, look at the <a href="http://codex.wordpress.org/Template_Tags/wp_list_categories" target="_blank">WordPress Codex documentation on the function this plugin implements, wp_list categories</a>. If this function can do it, the plugin can do it with minor edits, just look in list-custom-taxonomy-widget.php in the plugin editor. If it's a more universally helpful feature, let me know and I'll add support in the widget UI. 


== Changelog ==
= 3.3 = 
* Fixed bug where dropdown input 404d unless it was the built-in category taxonomy; dropdown option should work properly now
* Plugin is WordPress 3.6 compatible

= 3.2.1 =
* Added classes/ids and containers to widgets to alow easier selecting with CSS and JavaScript

= 3.2 =
* Added ability to display categories with dropdown (and go button) instead of as a list (the default), find it at the bottom of the "more options" section'
* Improved documentation

= 3.1.1 =
* For some reason, I have no idea why, the line that executed the "exclude" capability was commented out. Update fixes the bug.

= 3.1 =
* Fixed major bug where "categories" was displayed first, with everything else as a child, for many users
* Updated readme

= 3.0 =
* Confirmed compatibility with WordPress 3.5
* Added many new features. Click the more options button to view all of the available configuration options. Features include:
* Order by count, id, slug, name, or term group; ascending or descending
* Exclude categories by id
* Only show children of a particular category (useful for nested/hierarchical category systems)
* Detailed configuration options are hidden by default

= 2.0 =
* Available Taxonomies are now listed in a automatically, so it isn't necessary to go searching for the taxonomy's name
* Ability to show count (or hide it)
* Ability to specify to display hierarchically (like in builtin categories widget)
* Confirmed Wordpress 3.4.2 compatibility

= 1.0 =
* First publically available version of the plugin.
* Compatible with Wordpress 3.3.0 through 3.4.1

== Upgrade Notice ==
= 3.3 =
Bugfix: dropdowns should work now.