=== Roles & Capabilities ===
Contributors: arielhr1987, jlcd0894, grandeljay
Donate link: https://github.com/arielhr1987
Tags: role, capabilities, admin, permissions, edit
Requires at least: 4.1
Tested up to: 6.6
Stable tag: 1.1.10
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage your site user roles & capabilities the easy way.

== Description ==

Manage your roles and capabilities with simple, yet powerful tools in the admin area.

"Roles & Capabilities" provides a set of tools to help administrators to manage their site roles and capabilities as well as the site users capabilities.
Only administrator will be able to access the plugin features, there is no extra capability that will grant access to this tool.

With the plugin you will be able to:

* Create new roles.
* Edit role capabilities and display name.
* Clone a role into a new role.
* Grant or revoke capabilities to roles
* Create new capabilities.
* Delete user defined capabilities.
* Grant or revoke capabilities to user

= Development =

This plugin is open source. You can view the source code here: [Github](https://github.com/arielhr1987/leira-roles).

== Installation ==

1. Upload `leira-roles` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Happy coding :)

== Frequently Asked Questions ==

= Can I create new user roles? =
Yes, you can add new roles, and assign the capabilities that fit the most for that role.

= Can I remove Wordpress default roles? =
No, system default roles are not allowed to deleted. Wordpress system relay on those roles to work properly.

= Can I create a new roles with no capabilities? =
Yes.

= Can I create new capabilities to assign to a role? =
Yes, new capabilities can be created.

= Can I remove system capabilities? =
No, Wordpress relay on those capabilities to work properly.

== Screenshots ==

1. New menu items to handle roles & capabilities.
2. New quick action "Capabilities" in users list page.
3. Quick edit user capabilities.
4. Manage your site roles.
5. Edit role capabilities easily, grant or revoke role capabilities.
6. Bulk edit actions in users page.
7. Role actions.
8. Capabilities manager page. System capabilities are not deletable.
9. Capabilities list page bulk actions.

== Changelog ==

= 1.1.10 =
* Wordpress 6.6 compatibility check
* Fix issue reported by Wordfence,
* Sanitize inputs correctly
* Scape correctly data before output
* Improved UI, fix CSS errors

= 1.1.9 =
* Wordpress 6.4 compatibility check
* Remove trailing space in 5 star link
* Add GitHub link
* Apply WordPress coding style
* Ignore VS Code workspaces
* Use lf line endings for all text files

= 1.1.8 =
* Wordpress 5.9 compatibility check

= 1.1.7 =
* Wordpress 5.7 compatibility check

= 1.1.6 =
* Wordpress 5.6 compatibility check

= 1.1.5 =
* Fix typo, missing the 'v' in a closing div tag
* Fix typos in readme file

= 1.1.4 =
* Added Github Action automatic deploy
* Added Github Action automatic update readme.txt and assets
* Methods documentation updated

= 1.1.3 =
* Added rate us message to footer
* Added cookie based notifications
* Compatibility check with WP 5.5.* version
* Fix some typos

= 1.1.2 =
* Roles list column number correctly formatted
* Support link url updated
* Inline edit checkbox CSS fix

= 1.1.1 =
* Translated to spanish language

= 1.1.0 =
* Plugin refactored
* Fix bug creating new role, capabilities are show now upon role creation in the role list
* Fix bug cloning role, capabilities are show now upon role cloning in the role list

= 1.0.2 =
* Adding banners to assets

= 1.0.1 =
* Fix bug cloning a role
* Improve list table sort
* Improve capabilities sort
* New assets and banner for the plugin page

= 1.0.0 =
* The first plugin release
