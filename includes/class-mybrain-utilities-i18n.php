<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://mybrain.nl/
 * @since      1.0.0
 *
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/includes
 * @author     My Brain <support@mybrain.nl>
 */
class Mybrain_Utilities_i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

		// based on: https://stackoverflow.com/questions/18826977/override-a-wordpress-plugin-translation-file-on-load
		$text_domain = 'mybrain-utilities';
		$mybrain_utilities_language_file = plugin_dir_path(dirname(__FILE__)) . 'languages' . DIRECTORY_SEPARATOR . $text_domain. '-nl_NL.mo';
		
		// Unload the translation for the text domain of the plugin
		unload_textdomain($text_domain);
		// Then load my own file
		load_textdomain($text_domain, $mybrain_utilities_language_file, 'nl_NL');

		// load_plugin_textdomain(
        //     'mybrain-utilities',
        //     false,
        //     dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        // );

    }

}
