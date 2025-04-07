<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mybrain.nl/
 * @since      1.0.0
 *
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/admin
 * @author     My Brain <support@mybrain.nl>
 */

class Mybrain_Utilities_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        //20240901 - add settings link to admin menu
        add_action('admin_menu', array($this, 'add_mybrain_utilities_plugin_admin_menu'));

        //20240909 - admin_footer!
        add_filter('admin_footer_text', array($this, 'mybrain_utilities_admin_footer_text'));
        add_filter('update_footer', array($this, 'mybrain_utilities_update_footer'), 11);

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mybrain-utilities-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/mybrain-utilities-admin.js', array( 'jquery' ), $this->version, false);

    }

    //20240901 - add settings link to admin menu
    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */
    public function add_mybrain_utilities_plugin_admin_menu()
    {
        add_submenu_page(
            'options-general.php',
            'MyBrain Utilities '.__('Settings', 'mybrain-utilities'), //'Plugin settings',
            'MyBrain Utilities ', //'Plugin',
            'manage_options',
            'mybrain-utilities',
            array($this, 'display_mybrain_utilities_plugin_admin_page')
        );
    }
    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_mybrain_utilities_plugin_admin_page()
    {
        include_once(MYBRAIN_UTILITIES_PLUGIN_PATH.'admin/partials/mybrain-utilities-admin-display.php');
    }

    //20240901 - settings link on the plugins-page
    public function mybrain_utilities_plugin_add_settings_link($links)
    {
        $settings_link = '<a href="options-general.php?page=mybrain-utilities">' . __('Settings', 'mybrain-utilities') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Admin footer text
     *
     * A function to add footer text to the settings page of the plugin.
     * @since	1.0.2
     * @refer	https://codex.wordpress.org/Function_Reference/get_current_screen
     */
    public function mybrain_utilities_admin_footer_text($default)
    {
        // Return default on non-plugin pages
        $screen = get_current_screen();
        if (isset($screen->id) && (strpos($screen->id, 'mybrain-utilities') === false)) {
            return $default;
        }

        $default = '<span class="has-small-font-size">"MyBrain Utilities" '.esc_html__('Wordpress Plugin by', 'mybrain-utilities');
        $default .= '&nbsp;<a href="'.esc_url('https://mybrain.nl/en/').'" target="_blank">My Brain</a> - ';
        $default .= esc_html__('Have a nice day!', 'mybrain-utilities').'</span>';

        return $default;
    }


    /**
     * Admin footer version
     *
     * @since	1.0.2
     */
    public function mybrain_utilities_update_footer($default)
    {
        // Return default on non-plugin pages
        $screen = get_current_screen();
        if (isset($screen->id) && (strpos($screen->id, 'mybrain-utilities') === false)) {
            return $default;
        }

        return 'MyBrain Utilities - v' . MYBRAIN_UTILITIES_VERSION;
    }

}
