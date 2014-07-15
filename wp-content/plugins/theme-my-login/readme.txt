=== Theme My Login ===
Contributors: jfarthing84
Tags: widget, login, registration, theme, custom, log in, register, sidebar, gravatar, redirection, e-mail
Requires at least: 3.1
Tested up to: 3.8
Stable tag: 6.3.9

Themes the WordPress login pages according to your theme.


== Description ==

This plugin themes the WordPress login, registration and forgot password pages according to your current theme.
It creates a page to use in place of wp-login.php, using a page template from your theme.
Also includes a widget for sidebar login.


= Features =
* Your registration, log in, and password recovery pages will all match the rest of your website
* Includes a customizable widget to login anywhere from your blog
* Redirect users upon log in and log out based upon their role
* Show gravatar to users who are logged in
* Assign custom links to users who are logged in based on their role
* Customize user emails for registration and/or password recovery
* Send user emails in HTML format
* Allow users to set their own password upon registration
* Require users to be approved or confirm e-mail address upon registration
* Theme user profiles


== Installation ==

1. Upload the plugin to your 'wp-content/plugins' directory
1. Activate the plugin


== Frequently Asked Questions ==

= How do I change the look of forms? =

The same way you would change the look of anything else on your site: CSS. Theme My Login uses it's own very basic stylesheet.
You can copy this to your theme's directory and add additional styling there or simply add additional styling to your theme's stylesheet.

= I can't get into my site! What do I do? =

Visit yoursite.com/wp-login.php.

= What happened to the language folder? =

As of version 6.3, Theme My Login no longer ships with translation files. Translations will be available at http://downloads.jfarthing.com.

= How do I obtain additional assistance? =

Please visit http://www.jfarthing.com/development/theme-my-login first and then visit http://www.jfarthing.com/support if you have any questions, concerns, suggestions or problems.


== Changelog ==

= 6.3.9 =
* Fix strict standards errors
* Replace deprecated functions

= 6.3.8 =
* Fix issue where pages would redirect to profile page with Themed Profiles active

= 6.3.7 =
* Revert tml_page post type back to default WP pages
* Fix issue where SSL warnings were displayed in reCAPTCHA module
* Fix issue where a blank page resulted when 404.php didn't exist in a theme
* Fix issue where User Links couldn't be deleted
* Fix issue where "Are you sure?" would display when attempting to log out
* Fix issue where strings weren't being translated on Profile page

= 6.3.6 =
* Fix issue where all module options were set once activated
* Fix issue where template tag was not being output
* Fix issue where install failed during new blog creation on multisite
* Fix issue where error messages were duplicated on login pages

= 6.3.5 =
* Fix issue with blank pages where page.php didn't exist in a theme
* Fix issue where activating Themed Profiles resulted in a 404 for profile page
* Fix issue where options were being deleted upon upgrade
* Fix issue with AJAX module not working properly in Internet Explorer

= 6.3.4 =
* Use verbose rewrite rules for TML pages

= 6.3.3 =
* Fix issue where actions weren't being appended to page links
* Fix issue where modules weren't being installed on upgrade
* Fix fatal error in Custom E-mail module where old function name wasn't replaced
* Fix private constructor issue for PHP versions less than 5.3

= 6.3.2 =
* Fix issue where pages weren't created when upgrading from previous versions

= 6.3.1 =
* Fix multisite 404 error when using Post Name permalink structure
* Fix multisite redirect to main site for register

= 6.3 =
* Introduce tml_page post type and give each action it's own page
* Introduce AJAX module
* Implement user lock notifications for the Security module.
* Add option to hide widget when logged out
* Add option to disable wp-login.php to Security module
* Removed languages from plugin
* Use Custom E-mail's New User template when a user is added via wp-admin
* Use Custom E-mail's User Activation template when an activation is resent via wp-admin

= 6.2.3 =
* Fix static front page bug
* Remove tab indexes from forms

= 6.2.2 =
* Fix redirect loop bug
* Add visual cues for permalinks
* Fix iframe bug

= 6.2.1 =
* Add post password handling
* Don't block admin when DOING_AJAX
* Add WordPress updated message
* Replace deprecated get_userdatabylogin with get_user_by

= 6.2 =
* Fix FORCE_SSL_ADMIN logic
* Add tabindex to password fields
* Fix removal of actions from "tml_new_user_registered" action in User Moderation module
* Add %username% variable to Custom User Links module
* Add custom permalinks to core
* Add option to disable e-mail login
* Fix potential XSS attack vulnerability
* Update admin bar settings for 3.3 in Themed Profiles module
* Update multisite templates for 3.3
* Fix autofocus scripts to only load on login page
* Require 3.1+
* Fix broken login redirect logic
* Add option to require login to view site in Security module
* Don't change profile URL for non-themed roles in Themed Profiles module
* Display failed login attempts to administrators on user profiles in Security module
* Fix capability check for non-standard table prefix in User Moderation module
* Add separate profile templates per user role in Themed Profiles module
* Fix password recovery admin e-mail in Custom E-mail module
* Don't show admin options when admin is blocked in Themed Profiles module
* Treat multisite users with no role as subscribers in all modules
* Fix multisite registration bug in Themed Profiles module

= 6.1.4 =
* Don't hijack non-related form posts

= 6.1.3 =
* Fix password change error
* Update POT file

= 6.1.2 =
* Replace "self" keyword with "$this" for PHP 4

= 6.1.1 =
* Implement 3.1 password reset routine
* Add 3.1 fields to Themed Profiles
* Better default stylesheet for Themed Profiles
* Add 'nofollow' attribute to action links
* Check for SSL
* Add nofollow and noindex to login page
* Fix missing argument notices
* Fix deprecated argument notices
* Fix undefined method notices
* Fix install/uninstall routines
* Fix Custom user Links AJAX
* Fix Custom E-mail "From" filters
* Fix disabling of admin password change notification
* Fix "resent" custom activation e-mail

= 6.1 =
* Fully support multisite
* Require WordPress 3.0+
* Add Bulgarian translation
* Add (Belgian) Dutch translation
* Add Romanian translation

= 6.0.4 =
* Fix admin e-mail notification disabling
* Fix labels for login form fields
* Fix wp-login.php form action URL

= 6.0.3 =
* Fix login reauth bug in redirection module

= 6.0.2 =
* Fix Login page creation during install
* Fix template tag argument parsing

= 6.0.1 =
* Fix logout link for wp_nav_menu()
* Fix issue admin page not always being tabbed
* Fix issue of assigning multiple roles per user when using Moderation
* Add German translation
* Add Farsi (Persian) translation
* Add Hebrew translation
* Add Russian translation
* Update other languages

= 6.0 =
* Complete code rewrite
* Users can now log in with e-mail address as well as username
* Remove option to disable template tag and widget in favor of always being enabled
* Remove option to rewrite login links in favor of always being rewritten
* Custom templates can now be defined per action (login, register, etc.)
* User moderation activation e-mails can be resent on demand
* Add various new hooks to help with custom integration with other plugins
* Make custom user links sortable
* Customize every aspect of every e-mail
* Add a cool new random tip widget in the TML admin
* Use WP 3.0 functions (such as 'network_site_url') if available
* phpDoc everywhere!

= 5.1.6 =
* Fix issue with spaces in usernames

= 5.1.5 =
* Fix blank page redirect bug

= 5.1.4 =
* Fix the_title() bug fro WP versions before 3.0 (again)
* Fix undefined is_user_logged_in() bug

= 5.1.3 =
* Make Themed Profiles work properly

= 5.1.2 =
* Fix the_title() bug for WP versions before 3.0
* Fix redirection bug caused by 5.1.1 update

= 5.1.1 =
* Fix bug that blocked users from entire site once logged in
* PROPERLY display "Log Out" when page is shown in pagelist and logged in

= 5.1 =
* Display "Log Out" when page is shown in pagelist and logged in
* Forward profile.php to themed profile when module is active
* Allow for %user_id% in custom user links
* Add inline descriptions to all settings
* Various tweaks and preps for WP 3.0
* Add Italian translation
* Add Danish translation
* Add Polish translation
* Add Spanish translation

= 5.0.6 =
* Pass $theme_my_login by reference in option functions
* Remove accidental invalid characters

= 5.0.5 =
* Add 'theme-my-login-page' shortcode before 'theme-my-login' shortcode

= 5.0.4 =
* Re-introduce 'theme-my-login-page' shortcode for main login page
* Add French translation
* Fix typo in function override notice functions
* Make 2nd argument optional in 'get_pages' filter
* Remove another 'self' reference in class.php
* Fix typo in readme.txt

= 5.0.3 =
* Fix an improper fix for PHP4 style constructor in class
* Only display function override notices on TML settings page properly

= 5.0.2 =
* Fix improper function call for PHP4 style constructor in class

= 5.0.1 =
* Only display function override notices on TML settings page
* Typecast arrays as arrays (Fixes invalid datatype notices)
* Add plugin domain to all gettext calls

= 5.0 =
* Rewrite code in a modular fashion in order to speed up plugin
* Convert custom e-mails, passwords, redirection, user links and user moderation to "modules"
* Add the option to enable/disable link rewriting, widget and template tag
* Simplify/optimize admin tabs style
* Remember current admin tab after save
* When using custom passwords, allow users to set their own password upon reset
* When using custom redirection, specify redirection type per user role/per link type
* New ajax interface for user links admin
* Theme My Profile now merged into module

= 4.4 =
* Added the option to require new registrations to confirm e-mail address
* Added the option to redirect users upon log out according to their role
* Allow 'theme-my-login.css' to be loaded from current theme directory
* Cleaned up and rewrote most code
* Drop support for WP versions below 2.8

= 4.3.4 =
* Added the option to force redirect upon login

= 4.3.3 =
* Fixed a redirection bug where WordPress is installed in a sub-directory
* Add CSS style to keep "Remember Me" label inline with checkbox

= 4.3.2 =
* Added the option to redirect unapproved and/or denied users to a custom URL upon login attempt
* Fixed a bug where custom user password is lost if user moderation is enabled
* Fixed a PHP notice in the admin (Wish more plugin authors would do this; WP_DEBUG is your friend!)

= 4.3.1 =
* Fixed a MAJOR security hole that allowed anyone to login without a password!!

= 4.3 =
* Added the option to require approval for new registrations
* Added the option to enable/disable plugin stylesheet
* Removed form input fields from label tags
* Dropped support for WordPress versions older than 2.6

= 4.2.2 =
* Added the option to remove 'Register' and/or 'Lost Password' links
* Fixed a bug that sent e-mail from all plugins from this plugins setting

= 4.2.1 =
* Fixed a bug that broke other plugins e-mail format
* Fixed a bug that could break plugin upon upgrade

= 4.2 =
* Added the option to send e-mails in HTML format
* Fixed a bug that broke custom user role links if all links were deleted

= 4.1.2 =
* Added the ability to change main login page ID (Only needed for debugging)
* The login will now revert to default wp-login in the case of plugin failure

= 4.1.1 =
* Fixed a major bug dealing with saving options that broke the plugin
* Fixed a CSS bug causing interference with other interfaces that use jQuery UI Tabs

= 4.1 =
* Implemented custom user passwords
* Implemented custom e-mail from name & address
* Removed template tag & shortcode restriction on main login page

= 4.0 =
* Implemented custom links for logged in users based on role
* Implemented custom redirection upon log in based on role
* Implemented custom registration/password recovery emails

* Implemented true shortcode and template tag functionality
* Implemented true multi-instance functionality
* Implemented an easy-to-use jQuery tabbed administration menu
* Implemented both 'fresh' and 'classic' colors for administration menu

= 3.3.1 =
* Fixed a bug that broke password recovery due to the new system from WP 2.8.4

= 3.3 =
* Fixed a bug that disabled error display when GET variable 'loggedout' was set
* Added template tag access

= 3.2.8 =
* Fixed a security exploit regarding admin password reset addressed in WordPress 2.8.4

= 3.2.7 =
* Fixed a bug that determined how to create the widget

= 3.2.6 =
* Fixed a bug dealing with the version_compare() function
* Included French translation
* Included Spanish translation

= 3.2.5 =
* Fixed a bug that produced a 'headers aldready sent' error when uploading media
* Included Dutch translation

= 3.2.4 =
* Fixed the load_plugin_textdomain() call
* Added 'login_head' action hook

= 3.2.3 = 
* Fixed and updated many gettext calls for internationalization

= 3.2.2 =
* Added the option to leave widget links blank for default handling

= 3.2.1 =
* Fixed a XHTML validation issue

= 3.2 =
* Added the option to allow/disallow registration and password recovery within the widget
* Fixed a bug regarding color names within the CSS file that broke validation

= 3.1.1 =
* Fixed a bug that incorrectly determined current user role

= 3.1 =
* Added the ability to specify URL's for widget 'Dashboard' and 'Profile' links per user role
* Implemented WordPress 2.8 widget control for multiple widget instances
* Fixed a bug regarding the registration complete message

= 3.0.3 =
* Fixed a bug with the widget links

= 3.0.2 =
* Fixed a bug that didn't allow custom registration message to be displayed
* Fixed a few PHP unset variable notice's with a call to isset()

= 3.0.1 =
* Fixed a bug that caused a redirection loop when trying to access wp-login.php
* Fixed a bug that broke the widget admin interface
* Added the option to show/hide login page from page list

= 3.0 =
* Added a login widget

= 2.2 =
* Removed all "bloatware"

= 2.1 =
* Implemented login redirection based on user role

= 2.0.8 =
* Fixed a bug that broke the login with permalinks

= 2.0.7 =
* Fixed a bug that broke the Featured Content plugin

= 2.0.6 =
* Added the option to turn on/off subscriber profile theming

= 2.0.5 =
* Fixed a bug with default redirection and hid the login form from logged in users

= 2.0.4 =
* Fixed a bug regarding relative URL's in redirection

= 2.0.3 =
* Fixed various reported bugs and cleaned up code

= 2.0.2 =
* Fixed a bug that broke registration and broke other plugins using the_content filter

= 2.0.1 =
* Fixed a bug that redirected users who were not yet logged in to profile page

= 2.0 =
* Completely rewrote plugin to use page template, no more specifying template files & HTML

= 1.2 =
* Added capability to customize page titles for all pages affected by plugin

= 1.1.2 =
* Updated to allow customization of text below registration form

= 1.1.1 =
* Prepared plugin for internationalization and fixed a PHP version bug

= 1.1.0 =
* Added custom profile to completely hide the back-end from subscribers

= 1.0.1 =
* Made backwards compatible to WordPress 2.5+

= 1.0.0 =
* Initial release version
