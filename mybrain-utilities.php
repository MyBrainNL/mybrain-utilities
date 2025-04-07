<?php
/**
 * @link              https://mybrain.nl/en/
 * @since             1.0.0
 * @package           Mybrain_Utilities
 *
 * @wordpress-plugin
 * Plugin Name:       MyBrain Utilities
 * Plugin URI:        https://github.com/MyBrainNL/mybrain-utilities
 * Description:       My Brain Wordpress Utilities - Backup of configuration files .htaccess and wp-config, stay logged-in longer & a simple OpenStreetMap Map.
 * Version:           1.0.5
 * Requires at least: 5.2
 * Requires PHP:      7.0
 * Author:            My Brain - Marko Hoven
 * Author URI:        https://mybrain.nl/en/
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       mybrain-utilities
 * Domain Path:       /languages
 *
 * Copyright (C) 1999-2025 My Brain
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

//20240901 - check other installations
if (defined('MYBRAIN_UTILITIES_VERSION') || class_exists('Mybrain_Utilities')) {
    die('ERROR: It looks like you have more than one instance of this plugin installed. Please remove additional instances for this plugin to work again.');
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('MYBRAIN_UTILITIES_VERSION', '1.0.5');
define('MYBRAIN_UTILITIES_PLUGIN_PATH', plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mybrain-utilities-activator.php
 */
function activate_mybrain_utilities()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-mybrain-utilities-activator.php';
    Mybrain_Utilities_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mybrain-utilities-deactivator.php
 */
function deactivate_mybrain_utilities()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-mybrain-utilities-deactivator.php';
    Mybrain_Utilities_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_mybrain_utilities');
register_deactivation_hook(__FILE__, 'deactivate_mybrain_utilities');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-mybrain-utilities.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mybrain_utilities()
{

    $plugin = new Mybrain_Utilities();
    $plugin->run();

}
run_mybrain_utilities();
