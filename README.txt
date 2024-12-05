=== MyBrain Utilities ===
Contributors: @MarkoHoven
Donate link: https://mybrain.nl/
Tags: utilities
Requires at least: 3.0.1
Requires PHP: 7.0
Tested up to: 6.7.0
Stable tag: 1.0.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

My Brain Wordpress Utilities - Backup of configuration files .htaccess and wp-config, stay logged-in longer & a simple OpenStreetMap Map.


== Copyright ==

Copyright (C) 1999-2024 My Brain

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.


MyBrain Utilities uses the following third-party resources:

WordPress plugin boilerplate generator, WPPB by Enrique Chavez
License: GPLv2
Source: https://wppb.me/

OpenStreetMap, by the OpenStreetMap Foundation (OSMF)
License: Open Data Commons Open Database License (ODbL)
Source: https://www.openstreetmap.org

Leaflet, created by Volodymyr Agafonkin
License: MIT, BSD 2 clause
Source: https://leafletjs.com/


== Description ==

This plugin is a collection of various utilities, used by My Brain for different websites.
We released this to the Wordpress Plugin repository as we think it is also useful to others.

= HTAccess_Keeper =
Checks, backs up and restores your .htaccess and wp-config.php files.
Makes a backup when files are modified and restores when a 0 bytes file has been detected. An e-mail will be sent to the Administration Email Address, whenever a damaged file has been restored.
Originally developed because some plugins keep (re)writing to these two files and sometimes leave them damaged as empty file. That broke the complete website, but /index.php was still being executed, allowing this plugin to restore the damaged file.

= Keep_me_logged_in =
Stay logged in longer. WordPress will keep you logged in for 48 hours. If you've clicked the "Remember Me" checkbox at login, you get remembered for 14 days.
This option keeps your login active longer than the standard Wordpress Login Cookie time-out. You may need to login again after you change the time-out value.

= Openstreetmap/Leaflet Shortcode =
Adds a simple OpenStreetMap/Leaflet Shortcode to be used in your content/contact-page.
Inludes a standard marker and optional information popup.


== Installation ==

= USING WORDPRESS PLUGIN INSTALLER =

1. Go to your WordPress Dashboard, 'Plugins > Add New'.
2. Search for 'MyBrain Utilities'.
3. Click 'Install' and then 'Activate'.
4. Navigate to your WordPress dashboard, 'Settings > MyBrain Utilities' and configure as needed.
5. Done!

= MANUAL INSTALLATION =

1. Download the 'mybrain-utilities' zip file.
2. Extract the content and copy to the `/wp-content/plugins/` directory of your WordPress installation.
3. Navigate to your WordPress dashboard, 'Plugins > Installed Plugins'.
4. Find the 'MyBrain Utilities' plugin and activate.
5. Navigate to your WordPress dashboard, 'Settings > MyBrain Utilities' and configure as needed.
6. Done!


== Frequently Asked Questions ==

= Can I change the alert e-mail address =

No, alerts are only e-mailed to the Administrator address as defined in your Wordpress dashboard, 'Settings > General'.

= Where can I report problems or bugs? =

You can report problems on [this support forum](https://wordpress.org/support/plugin/mybrain-utilities/).


== Screenshots ==

1. MyBrain Utilities About Information.
2. MyBrain Utilities HTAccess Keeper Settings.
3. MyBrain Utilities Keep My Login Settings.
4. MyBrain Utilities Maps Settings.
5. Sample Shortcode and Popup-information on Page-Edit screen.
6. Sample Openstreetmap/Leaflet Map on the website.


== Changelog ==

= 1.0.2 =
* Initial release - 5 December 2024

= 1.0.1 =
* Merged the HTAccess_Keeper plugin into the MyBrain Utilities Plugin

= 1.0.0 =
* Improved keep_me_logged_in_for_1_year
* Added OpenStreetMap/Leaflet Map


== Upgrade Notice ==

= 1.0.2 =
* Latest version. Just install and configure. No other action needed.
