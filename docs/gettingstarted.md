Getting Started with SchoolPress
===========
This document will contain intrustions and tips for navigating the SchoolPress code and getting it running on your site.

Demo
---------------------
A working example of this code can be found at http://schoolpress.me

The code base in this respository is the exact code (minus any sensitive settings) that runs the site at http://schoolpress.me.

Note: There are parts of this codebase that are assuming specific domains, page names, and other settings that are in use at the SchoolPress site. We will be generalizing these bits of code as soon as we can. Help with that is appreciated.

Main Sections of the Repository (Note: Make these locations links to GitHub pages.)
---------------------
* The .php files in the root folder, and the wp-admin and wp-includes folders are from the latest version of WordPress.
* All documentation can be found in the /docs/ folder in Markdown format.
* The main application plugin can be found in /wp-content/plugins/schoolpress/
* The theme for the main SchoolPress site is in /wp-content/themes/schoolpress/. This is a child theme of the StartBox theme.
* The theme for the school subsites is in /wp-content/themes/schoolpress-school/. This is a child theme of the StartBox theme.

Plugins Used
---------------------
### akismet
Default WordPress plugin for controlling spam comments on a site.

### bbpress
Powers the forums for each class.

### bp-site-groups
Plugin created for SchoolPress. [Separate repository here](https://github.com/strangerstudios/bp-site-groups). By default BuddyPress creates groups network-wide in a WordPress multisite install. This plugin helps to make groups visible only on the subsite where they were created.

### buddypress
Powers the groups functionality for each class.

### coming-soon
Not currently used. Was used when SchoolPress was in closed alpha.

### js-display-name
Load the display name of a WordPress user via JavaScript. [Separate repository here](https://github.com/strangerstudios/js-display-name).

### kint-debugger
Used for debugging during development.

### paid-memberships-pro
Used to manage membership levels and charging for school members.

### pmpro-network
Used to automatically create a new subsite when a "school" member checks out.

### pmpro-network-subsite
Used on the open.schoolpress.me and testschool.schoolpress.me sites to link student/teacher/school membership levels with the levels from the main site. So at open.schoolpress.me, your membership level is based on your level on the main schoolpress.me site. With this plugin deactivated (say at privateschool.schoolpress.me) you would need a teacher membership at that specific site to create and manage classes.

### schoolpress
The main application plugin. Includes classes to manage schools, students, teachers, classes, and assignments. Also includes page templates for my classes, add/edit classes, and add/edit assignments pages.

### ss-file-folders
Not currently used. [Separate repository here](https://github.com/strangerstudios/ss-file-folders). Can be used to show a list of pages, sub pages, and attached documents as folders, sub folders, and files.

### theme-my-login
Used to show the WordPress login on the frontend of the site and keep non-admins from viewing the dashboard.

### wordpress-beta-tester
Used to download bleeding-edge versions of WordPress for testing.

### wp-doc
Not currently used. Example from BWAWWP to view a DOCX version of a WordPress page.

### wp-multisite-smtp
Used to send email via SMTP across the entire multisite network.


Installing SchoolPress on Your Server
---------------------
Installing SchoolPress is not a trivial matter. Among other things you should be familiar with setting up a WordPress.org powered site on your own server, setting up a WordPress Multisite Network, and should be comfortable configuring and tweaking plugins and themes in WordPress.

Note: These a rough steps for now. We plan to flesh them out soon.

1. Clone this repository into your web root.
1. Visit http://yoursite.com/ to install WordPress.
1. Make your WordPress install a multisite install.
1. Activate plugins on the main site.
1. Activate the "schoolpress" theme on the main site.
1. Setup PMPro and PMPro membership levels on the main site: Student, Teacher, School.
1. Create a subsite open.yoursite.com.
1. Activate the "PMPro Network Subsite Helper" plugin on open.yoursite.com.