=== NGO-branding ===
Contributors: George Bredberg
Donate link: https://ngo-portal.org/donera/
Tags: branding, network sites, multisite, site, portal, NGO
Requires at least: 3.0.1
Tested up to: 4.6.1
Stable tag: 1.3.4
License GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Cleans up WordPress admin for NGO-sites, adds a field for meta-keywords, disables some updates, adds feature guest speakers, provides some extra security and makes the NGO Portal to feel like home.

== Description ==
NGO-branding cleans up WordPress admin for NGO-sites, changes menu items in backoffice, to link to NGO-portal instead of WP, set permalinks, removes WP-version from frontoffice and add some extra secutity to the portal. It also adds a field for meta-keywords, disables some updates that might cause problems if done unattended, adds feature guest writers to posts and makes the NGO Portal to feel like home. Most settings are based on user permissions, so for super-admin, it does not change much, but for site-admin it cleans up the backoffice a great deal. It also provides a custom login page.

See documentation on [https://ngo-portal.org](https://ngo-portal.org) for more information and documentation about NGO-portal and this plugin.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. This plugin has no settings, but by commenting out changes you don't need you can adapt it for your needs.

== Screenshots ==
1. Shows the admin page for a site admin. Changes can be changed in ngo-branding.php at will. It's well documented and many extra settings are there to make it easier to adapt. Just uncomment them at will.

== Frequently Asked Questions ==

= How do I change the logo at the login page? =
You find the logo for the login page in ngo-branding/custom_login/custom_login.png

== Changelog ==
= 1.3.4 =
* Latest stable release
Added nonce for saving, replaced inlince css with a css file and did some cleanup.

= 1.3.3 =
I changed the base language from Swedish to English to make it easier to translate. If you do not need to translate this plugin, you can skip this update.

= 1.3.2 =
I had the wrong header for this plugin, sorry. Now it's correct.

= 1.3.1 =
Minor update. Moved blocking from backoffice for writers and less to ngo-menu-deactivate and opened up access to look and users in admin for site admins.
This makes this plugin a branding plugin, and put all the other adaptations of the backoffice in ngp-menu-deactivate, making it easier to adapt this plugin at will.
= 1.3 =
Added setting in settings -> general to set the slug for site-list page link and moved some functions to other plugins, where they belong.

= 1.2.2 =
Bug fix. Network activating would have locked you out of admin

= 1.2.1 =
Changed donation link (important stuff ;) )

= 1.2 =
Initial release to the Wordpress repositorium
