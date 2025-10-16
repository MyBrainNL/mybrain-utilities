<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mybrain.nl/
 * @since      1.0.0
 *
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/public
 * @author     My Brain <support@mybrain.nl>
 */
class Mybrain_Utilities_Public
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
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        //20240901 - settings
        add_action('admin_init', array($this, 'mybrain_utilities_settings_init'));

        $options = get_option('mybrain_utilities_options', array());
        if (isset($options['htaccesskeeperenabled']) && ($options['htaccesskeeperenabled'] == 'yes')) {
            // 20250326 - to prevent "Call to undefined function is_plugin_active()" - if (is_plugin_active('htaccess-keeper.php') == false) {
            if (!in_array('htaccess-keeper.php', (array) get_option('active_plugins', array()))) {
                //20240904 - htaccess keeper!
                add_action('init', array($this, 'mybrain_utilities_run_htaccess_keeper'), 1);
            }
        }
        if (isset($options['mapenabled']) && ($options['mapenabled'] == 'yes')) {
            //20240904 - map!
            add_shortcode('mbumap', array($this, 'mybrain_utilities_extra_leaflet_map'));
        }
        if (isset($options['keeploginenabled']) && ($options['keeploginenabled'] == 'yes')) {
            //20240904 - keeplogin!
            add_filter('auth_cookie_expiration', array($this, 'mybrain_utilities_keep_me_logged_in_for_1_year'));
        }
        if (isset($options['cleanhead']) && ($options['cleanhead'] == 'yes')) {
            //20251016 - cleanhead!
            add_action('after_setup_theme', array($this, 'mybrain_utilities_run_cleanhead'), 1);
        }

    }


    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        $options = get_option('mybrain_utilities_options', array());
        if (isset($options['mapenabled']) && ($options['mapenabled'] == 'yes')) {
            global $post;
            if ((is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'mbumap'))) {
                wp_enqueue_style('mbu-leaflet-styles', plugin_dir_url(__FILE__) . 'css/leaflet.css', array(), $this->version, 'all');
                wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/mybrain-utilities-public.css', array(), $this->version, 'all');
            }
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $options = get_option('mybrain_utilities_options', array());

        if (!(isset($options['warningdisabled']) && ($options['warningdisabled'] == 'yes'))) {
            wp_enqueue_script('mybrain-utilities', plugin_dir_url(__FILE__) . 'js/mybrain-utilities-public.js', array( 'jquery' ), $this->version, false);
        }

        if (isset($options['mapenabled']) && ($options['mapenabled'] == 'yes')) {
            global $post;
            if ((is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'mbumap'))) {
                wp_enqueue_script('mbu-leaflet-scripts', plugin_dir_url(__FILE__) . 'js/leaflet.js', array( 'jquery' ), $this->version, false);
                wp_enqueue_script('mybrain-utilities-maps', plugin_dir_url(__FILE__) . 'js/mybrain-utilities-public-maps.js', array( 'jquery' ), $this->version, false);
            }
        }
    }



    //20250130 - CUSTOM sanitize_callback FOR register_setting
    /**
     * custom sanitize settings
     */
    public function mybrain_utilities_sanitize_settings_callback(array $options): array
    {
        $original_value = $options;
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'this_is_empty':
                    $value = '';
                    break;
                case 'htaccesskeeperenabled':
                case 'keeploginenabled':
                case 'warningdisabled':
                case 'cleanhead':
                case 'mapenabled':
                case 'this_is_toggled':
                    $value = sanitize_text_field($value);
                    if ($value !== 'yes') {
                        $value = 'no';
                    }
                    break;
                case 'this_is_active':
                    $value = sanitize_text_field($value);
                    if ($value !== 'enabled') {
                        $value = 'disabled';
                    }
                    break;
                case 'this_is_text':
                    $value = sanitize_text_field($value);
                    break;
                case 'this_is_textarea':
                    $value = sanitize_textarea_field($value);
                    break;
                case 'this_is_email':
                    $value = sanitize_email($value);
                    break;
                case 'this_is_url':
                    $value = sanitize_url($value);
                    break;
                case 'this_is_selected':
                case 'this_is_checked':
                case 'this_is_radio':
                    $value = sanitize_text_field($value);
                    if (!in_array($value, array('option1','option2','option3'))) {
                        $value = 'option1';
                    }
                    break;
                case 'keeplogintimeout':
                    $value = sanitize_text_field($value);
                    if (!in_array($value, array('86400','172800','604800','1209600','2592000','7776000','15552000','31556926','126230400'))) {
                        $value = '86400';
                    }
                    break;
            }
            $options[$key] = $value;
        }
        return apply_filters('mybrain_utilities_sanitize_settings_filter', $options, $original_value);
    }



    //20240901 - For storage of all the settings
    /**
     * custom option and settings
     */
    public function mybrain_utilities_settings_init()
    {
        // register a new setting for "mybrain_utilities" page
        register_setting(
            'mybrain_utilities',
            'mybrain_utilities_options',
            array(
                'type'              => 'array',
                'sanitize_callback' => array($this, 'mybrain_utilities_sanitize_settings_callback'),
            )
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_about',
            __('About', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_section_about_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab1" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        // section must have a field otherwise after_section is not added in template.php line 1787
        add_settings_field(
            'this_is_empty',
            '',
            array($this, 'mybrain_utilities_field_hidden'),
            'mybrain_utilities',
            'mybrain_utilities_section_about',
            [
                'label_for' => 'this_is_empty',
                'description' => '',
                'default' => 'empty',
                'class' => '',
                // 'mybrain_utilities_custom_data' => 'section must have a field otherwise after_section is not added in template.php line 1787',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_htaccess',
            'HTAccess Keeper',
            array($this, 'mybrain_utilities_section_htaccess_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab2" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        add_settings_field(
            'htaccesskeeperenabled',
            __('Enable Protection', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_toggle'),
            'mybrain_utilities',
            'mybrain_utilities_section_htaccess',
            [
                'label_for' => 'htaccesskeeperenabled',
                'description' => __('An e-mail will be sent to the Administration Email Address, whenever a damaged file has been restored.', 'mybrain-utilities'),
                'toggled' => 'yes',
                'type' => 'onoff', //yesno or onoff
                'default' => 'yes', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_console',
            __('Console Warning', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_section_console_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab4" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        add_settings_field(
            'warningdisabled',
            __('Disable Warning', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_toggle'),
            'mybrain_utilities',
            'mybrain_utilities_section_console',
            [
                'label_for' => 'warningdisabled',
                'description' => __('Disable the browser console warning', 'mybrain-utilities'),
                'toggled' => 'yes',
                'type' => 'yesno', //yesno or onoff
                'default' => 'no', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_cleanhead',
            'Clean Head',
            array($this, 'mybrain_utilities_section_cleanhead_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab6" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        add_settings_field(
            'cleanhead',
            __('Clean HEAD', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_toggle'),
            'mybrain_utilities',
            'mybrain_utilities_section_cleanhead',
            [
                'label_for' => 'cleanhead',
                'description' => __('Remove the wp_head actions', 'mybrain-utilities'),
                'toggled' => 'yes',
                'type' => 'yesno', //yesno or onoff
                'default' => 'yes', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_map',
            __('Map Shortcode', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_section_map_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab5" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        add_settings_field(
            'mapenabled',
            __('Enable Shortcode', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_toggle'),
            'mybrain_utilities',
            'mybrain_utilities_section_map',
            [
                'label_for' => 'mapenabled',
                'description' => __('Activate the shortcode', 'mybrain-utilities').' [mbumap]',
                'toggled' => 'yes',
                'type' => 'yesno', //yesno or onoff
                'default' => 'yes', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_keeplogin',
            'Keep Me Logged In',
            array($this, 'mybrain_utilities_section_keeplogin_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab3" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        add_settings_field(
            'keeploginenabled',
            __('Enable Keep_me_logged_in', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_toggle'),
            'mybrain_utilities',
            'mybrain_utilities_section_keeplogin',
            [
                'label_for' => 'keeploginenabled',
                'description' => __('Keep your login active longer than the standard Wordpress Login Cookie time-out.', 'mybrain-utilities'),
                'toggled' => 'yes',
                'type' => 'yesno', //yesno or onoff
                'default' => 'yes', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
            ]
        );
        add_settings_field(
            'keeplogintimeout',
            __('Time-out', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_select'),
            'mybrain_utilities',
            'mybrain_utilities_section_keeplogin',
            [
                'label_for' => 'keeplogintimeout',
                'description' => __('Select how long you wish to stay logged in.', 'mybrain-utilities'),
                'options' => [
                    '86400' => '1 '.__('Day', 'mybrain-utilities'),
                    '172800' => '2 '.__('Days', 'mybrain-utilities'),
                    '604800' => '7 '.__('Days', 'mybrain-utilities'),
                    '1209600' => '14 '.__('Days', 'mybrain-utilities'),
                    '2592000' => '30 '.__('Days', 'mybrain-utilities'),
                    '7776000' => '90 '.__('Days', 'mybrain-utilities'),
                    '15552000' => '180 '.__('Days', 'mybrain-utilities'),
                    '31556926' => '365 '.__('Days', 'mybrain-utilities'),
                    '126230400' => '1461 '.__('Days', 'mybrain-utilities'),
                ],
                'default' => '15552000',
                'class' => '',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_test',
            __('Unused Sample Settings', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_section_test_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab9" class="%s" style="display:none;">',
                'after_section' => '</div>',
                'section_class' => 'tab-body',
            ]
        );

        add_settings_field(
            'this_is_toggled',
            __('Toggle option', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_toggle'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_toggled',
                'description' => __('Generic toggle', 'mybrain-utilities'),
                'toggled' => 'yes',
                'type' => 'yesno', //yesno or onoff
                'default' => 'yes', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
            ]
        );

        add_settings_field(
            'this_is_active',
            __('Combobox enabled option', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_cb_enabled'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_active',
                'description' => __('Enabled/disabled selectbox', 'mybrain-utilities'),
                'default' => 'disabled',
                'class' => '',
            ]
        );

        add_settings_field(
            'this_is_text',
            __('Text option', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_text'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_text',
                'description' => __('Generic text field', 'mybrain-utilities'),
                'default' => 'powered by My Brain',
                'placeholder' => __('Type your text here', 'mybrain-utilities'),
                'class' => '',
            ]
        );

        add_settings_field(
            'this_is_textarea',
            __('Text option', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_textarea'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_textarea',
                'description' => __('Generic textarea field', 'mybrain-utilities'),
                'default' => 'powered by My Brain!',
                'placeholder' => __('Type your text here', 'mybrain-utilities'),
                'class' => '',
            ]
        );

        add_settings_field(
            'this_is_selected',
            __('Selectbox option', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_select'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_selected',
                'description' => __('Generic selectbox', 'mybrain-utilities'),
                'options' => [
                    'option1' => __('Option 1', 'mybrain-utilities'),
                    'option2' => __('Option 2', 'mybrain-utilities'),
                    'option3' => __('Option 3', 'mybrain-utilities'),
                ],
                'default' => '',
                'class' => '',
            ]
        );

        add_settings_field(
            'this_is_checked',
            __('Checkbox options', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_checkbox'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_checked',
                'description' => __('Generic checkboxes', 'mybrain-utilities'),
                'options' => [
                    'option1' => __('Option 1', 'mybrain-utilities'),
                    'option2' => __('Option 2', 'mybrain-utilities'),
                    'option3' => __('Option 3', 'mybrain-utilities'),
                ],
                'default' => '',
                'direction' => 'vertical',
                'class' => '',
            ]
        );

        add_settings_field(
            'this_is_radio',
            __('Radiobutton options', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_field_radio'),
            'mybrain_utilities',
            'mybrain_utilities_section_test',
            [
                'label_for' => 'this_is_radio',
                'description' => __('Generic radiobuttons', 'mybrain-utilities'),
                'options' => [
                    'option1' => __('Option 1', 'mybrain-utilities'),
                    'option2' => __('Option 2', 'mybrain-utilities'),
                    'option3' => __('Option 3', 'mybrain-utilities'),
                ],
                'default' => 'option2',
                'direction' => 'vertical',
                'class' => '',
            ]
        );

    }

    //20240901 - custom option and settings:  intro text callback functions

    // section callbacks can accept an $args parameter, which is an array.
    // $args have the following keys defined: title, id, callback.
    // the values are defined at the add_settings_section() function.
    public function mybrain_utilities_section_about_intro($args)
    {
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '">';
        esc_html_e('This plugin provides various utilities for Wordpress', 'mybrain-utilities');
        echo ':<ul class="ul-disc"><li>';
        echo 'HTAccess_Keeper';
        echo '</li><li>';
        echo 'Keep_me_logged_in';
        echo '</li><li>';
        echo 'Console_Warning';
        echo '</li><li>';
        echo 'Clean_Head';
        echo '</li><li>';
        echo 'OpenStreetMap/Leaflet Shortcode';
        echo '</li></ul>';
        echo '</p>';
        // if (isset($_GET['show']) && ($_GET['show'] == 'options')) {
        // $options = get_option('mybrain_utilities_options', array());
        // echo '<pre class="">';
        // print_r($options);
        // echo '</pre>';
        // }
    }


    public function mybrain_utilities_section_htaccess_intro($args)
    {
        echo '<div class="button-right-top">';
        submit_button();
        echo '</div>';
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '">';
        esc_html_e('Checks, backs up and restores your .htaccess and wp-config.php files.', 'mybrain-utilities');
        echo '<br/>';
        esc_html_e('Makes a backup when files are modified and restores when a 0 bytes file has been detected.', 'mybrain-utilities');
        echo '</p>';

        if (is_plugin_active('htaccess-keeper.php') == true) {
            echo '<p class="has-large-font-size red warning">';
            esc_html_e('WARNING: Old version of "HTAccess Keeper" detected - plugin already active!', 'mybrain-utilities');
            echo '<br/>';
            // esc_html_e('Please <a href="/wp-admin/plugins.php">deactivate</a> the old stand-alone plugin.', 'mybrain-utilities');
            esc_html_e('Please', 'mybrain-utilities');
            echo ', <a href="/wp-admin/plugins.php">';
            esc_html_e('deactivate', 'mybrain-utilities');
            echo '</a> ';
            esc_html_e('the old stand-alone plugin', 'mybrain-utilities');
            echo '.</p>';
        }
    }


    public function mybrain_utilities_section_console_intro($args)
    {
        echo '<div class="button-right-top">';
        submit_button();
        echo '</div>';
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '"><b>';
        esc_html_e('Disable the browser console warning', 'mybrain-utilities');
        echo ':</b><br/>';
        esc_html_e('This plugin adds a warning in the browser console log to any visitor, to only continue if they understand the code and trust the source.', 'mybrain-utilities');
        echo '<br/>';
        esc_html_e('Disable this option at your own risk.', 'mybrain-utilities');
        echo '</p>';
    }

    public function mybrain_utilities_section_cleanhead_intro($args)
    {
        echo '<div class="button-right-top">';
        submit_button();
        echo '</div>';
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '"><b>';
        esc_html_e('Cleanup some default Wordpress HEAD entries', 'mybrain-utilities');
        echo '.</b><br/>';
        esc_html_e('Keeps your HTML a little cleaner; remove the links to the rss-feeds and other lines, like', 'mybrain-utilities');
        echo ':<br/><pre>';
        echo "remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );\n";
        echo "remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );\n";
        echo "remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );\n";
        //echo "remove_action( 'wp_head', 'rel_canonical' );\n";
        echo "remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );\n";
        echo "remove_action( 'wp_head', 'feed_links_extra', 3 );\n";
        echo "remove_action( 'wp_head', 'feed_links', 2 );\n";
        echo "remove_action( 'wp_head', 'rsd_link' );\n";
        echo "remove_action( 'wp_head', 'wlwmanifest_link' );\n";
        echo "remove_action( 'wp_head', 'index_rel_link' );\n";
        echo "remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );\n";
        echo "remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );\n";
        echo "remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );\n";
        echo "remove_action( 'wp_head', 'wp_generator' );\n";
        echo "add_filter('wp_statistics_html_comment', '__return_false');\n";
        echo '</pre></p>';
    }


    public function mybrain_utilities_section_map_intro($args)
    {
        echo '<div class="button-right-top">';
        submit_button();
        echo '</div>';
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '"><b>';
        esc_html_e('Add a simple OpenStreetMap/Leaflet Shortcode', 'mybrain-utilities');
        echo ':</b><br/>';
        echo '[mbumap center="52.145634,5.04855" coords="52.145634,5.04855" zoom="15" class="myclasses"]';
        echo '<br/>* center = ';
        esc_html_e('map center', 'mybrain-utilities');
        echo '<br/>* coords = ';
        esc_html_e('places a marker', 'mybrain-utilities');
        echo '<br/><br/><b>';
        esc_html_e('How to add a marker popup?', 'mybrain-utilities');
        echo '</b><br/>* ';
        esc_html_e('Use a Paragraph Block with Extra CSS-class "mbumap-popup" to add a balloon with that content to the map.', 'mybrain-utilities');
        echo '<br/>* ';
        esc_html_e('Use CSS-classes "mbumap-popup hidden" to have the paragraph hidden in the front-end.', 'mybrain-utilities');
        echo '<br/>';
        echo '<br/>';
        echo '</p>';
    }


    public function mybrain_utilities_section_keeplogin_intro($args)
    {
        echo '<div class="button-right-top">';
        submit_button();
        echo '</div>';
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '"><b>';
        esc_html_e('Stay logged in longer.', 'mybrain-utilities');
        echo '</b><br/>';
        esc_html_e('You may need to login again after you change the time-out value.', 'mybrain-utilities');
        echo '<br/>';
        esc_html_e('WordPress will keep you logged in for 48 hours. If you\'ve clicked the "Remember Me" checkbox at login, you get remembered for 14 days.', 'mybrain-utilities');
        echo '</p>';
    }


    public function mybrain_utilities_section_test_intro($args)
    {
        echo '<div class="button-right-top">';
        submit_button();
        echo '</div>';
        echo '<p id="';
        echo esc_attr($args['id']);
        echo '">';
        echo 'Settings below are not in use, and for code demonstration purposes only.';
        echo '</p>';
    }


    //20240901 - custom option and settings: generic field callback functions

    // fields
    // field callbacks can accept an $args parameter, which is an array.
    // $args is defined at the add_settings_field() function.
    // wordpress has magic interaction with the following keys: label_for, class.
    // the "label_for" key value is used for the "for" attribute of the <label>.
    // the "class" key value is used for the "class" attribute of the <tr> containing the field.
    // you can add custom key value pairs to be used inside your callbacks.
    public function mybrain_utilities_field_cb_enabled($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        echo '<select id="'.esc_attr($args['label_for']).'"';
        if (!empty($args['mybrain_utilities_custom_data'])) {
            echo ' data-custom="'.esc_attr($args['mybrain_utilities_custom_data']).'"';
        }
        echo ' name="mybrain_utilities_options['.esc_attr($args['label_for']).']">';
        echo '<option value="enabled" ';
        echo esc_html(isset($options[ $args['label_for'] ]) ? (selected($options[ $args['label_for'] ], 'enabled', false)) : (isset($args['default']) ? (selected($args['default'], 'enabled', false)) : ('')));
        echo '>';
        esc_html_e('enabled', 'mybrain-utilities');
        echo '</option>';
        echo '<option value="disabled" ';
        echo esc_html(isset($options[ $args['label_for'] ]) ? (selected($options[ $args['label_for'] ], 'disabled', false)) : (isset($args['default']) ? (selected($args['default'], 'disabled', false)) : ('')));
        echo '>';
        esc_html_e('disabled', 'mybrain-utilities');
        echo '</option>';
        echo '</select>';
        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_select($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        echo '<select id="'.esc_attr($args['label_for']).'"';
        if (!empty($args['mybrain_utilities_custom_data'])) {
            echo ' data-custom="'.esc_attr($args['mybrain_utilities_custom_data']).'"';
        }
        echo ' name="mybrain_utilities_options['.esc_attr($args['label_for']).']">';
        foreach ($args['options'] as $value => $description) {
            echo '<option value="'.esc_html($value).'" ';
            echo esc_html(isset($options[ $args['label_for'] ]) ? (selected($options[ $args['label_for'] ], $value, false)) : (isset($args['default']) ? (selected($args['default'], $value, false)) : ('')));
            echo '>';
            echo esc_html($description);
            echo '</option>';
        }
        echo '</select>';
        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_checkbox($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        foreach ($args['options'] as $value => $description) {
            echo '<input type="checkbox" id="'.esc_attr($args['label_for']).'"';
            echo ' value="'.esc_html($value).'" name="mybrain_utilities_options[';
            echo esc_attr($args['label_for']).'][]" ';
            echo esc_html(isset($options[ $args['label_for'] ]) ? (in_array($value, $options[ $args['label_for'] ], false) ? checked(true) : ('')) : (isset($args['default']) ? (checked($args['default'], $value, false)) : ('')));
            echo '> ';
            echo esc_html($description);
            if ($args['direction'] == 'horizontal') {
                echo ' ';
            } else {
                echo '<br/>';
            }
        }
        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_toggle($args)
    {
        $options = get_option('mybrain_utilities_options', array());

        $value = $args['toggled'];
        echo '<input type="checkbox" id="'.esc_attr($args['label_for']).'"';
        echo ' value="'.esc_html($value).'" name="mybrain_utilities_options[';
        echo esc_attr($args['label_for']).']" ';
        // a toggle can not have a default
        echo(isset($options[ $args['label_for'] ]) ? checked($options[ $args['label_for'] ], $value, false) : (''));
        if ($args['type'] == 'yesno') {
            echo ' class="mbtoggle yesno"> ';
        } else {
            if ($args['type'] == 'janee') {
                echo ' class="mbtoggle janee"> ';
            } else {
                if ($args['type'] == 'aanuit') {
                    echo ' class="mbtoggle aanuit"> ';
                } else {
                    echo ' class="mbtoggle onoff"> ';
                }
            }
        }

        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_radio($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        foreach ($args['options'] as $value => $description) {
            echo '<input type="radio" id="'.esc_attr($args['label_for']).'"';
            echo ' value="'.esc_html($value).'" name="mybrain_utilities_options[';
            echo esc_attr($args['label_for']).']" ';
            echo isset($options[ $args['label_for'] ]) ? checked($options[ $args['label_for'] ], $value, false) : (isset($args['default']) ? (checked($args['default'], $value, false)) : (''));
            echo '> ';
            echo esc_html($description);
            if ($args['direction'] == 'horizontal') {
                echo ' ';
            } else {
                echo '<br/>';
            }
        }
        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_hidden($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        echo '<input type="hidden" id="'.esc_attr($args['label_for']).'"';
        if (!empty($args['mybrain_utilities_custom_data'])) {
            echo ' data-custom="'.esc_attr($args['mybrain_utilities_custom_data']).'"';
        }
        echo ' name="mybrain_utilities_options['.esc_attr($args['label_for']).']"';
        echo ' value="';
        echo esc_html(isset($options[ $args['label_for'] ]) ? ($options[ $args['label_for'] ]) : (isset($args['default']) ? ($args['default']) : ('')));
        echo '">';

        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_text($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        echo '<input type="text" id="'.esc_attr($args['label_for']).'"';
        if (!empty($args['mybrain_utilities_custom_data'])) {
            echo ' data-custom="'.esc_attr($args['mybrain_utilities_custom_data']).'"';
        }
        if (!empty($args['placeholder'])) {
            echo ' placeholder="'.esc_attr($args['placeholder']).'"';
        }
        echo ' name="mybrain_utilities_options['.esc_attr($args['label_for']).']"';
        echo ' value="';
        echo esc_html(isset($options[ $args['label_for'] ]) ? ($options[ $args['label_for'] ]) : (isset($args['default']) ? ($args['default']) : ('')));
        echo '">';

        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }

    public function mybrain_utilities_field_textarea($args)
    {
        $options = get_option('mybrain_utilities_options', array());
        echo '<textarea id="'.esc_attr($args['label_for']).'"';
        if (!empty($args['mybrain_utilities_custom_data'])) {
            echo ' data-custom="'.esc_attr($args['mybrain_utilities_custom_data']).'"';
        }
        if (!empty($args['placeholder'])) {
            echo ' placeholder="'.esc_attr($args['placeholder']).'"';
        }
        echo ' name="mybrain_utilities_options[';
        echo esc_attr($args['label_for']);
        echo ']">';
        echo esc_html(isset($options[ $args['label_for'] ]) ? ($options[ $args['label_for'] ]) : (isset($args['default']) ? ($args['default']) : ('')));
        echo '</textarea>';

        if (!empty($args['description'])) {
            echo '<p class="description"><small>';
            echo esc_html($args['description']);
            echo '</small></p>';
        }
    }



    //20240904 - htaccess keeper!
    public function mybrain_utilities_run_htaccess_keeper($args)
    {
        $filehta = ABSPATH.'.htaccess';
        $filebck = ABSPATH.'.htaccess-keeper';
        if (file_exists($filehta)) {
            if (filesize($filehta) > 0) {
                //check
                if (file_exists($filebck)) {
                    // 1.0.1
                    // if (filesize($filehta) != filesize($filebck)) {
                    // 1.0.2
                    if ((filesize($filehta) != filesize($filebck)) || (filectime($filehta) > filectime($filebck))) {
                        //changed
                        copy($filehta, $filebck);
                    }
                } else {
                    //copy
                    copy($filehta, $filebck);
                }
            } else {
                if (file_exists($filebck) && (filesize($filebck) > 0)) {
                    //restore
                    if (!copy($filebck, $filehta)) {
                        //sorry!
                        $to = get_option('admin_email');
                        $subject = 'HTAccess_Keeper 0 bytes ERROR on '.site_url();
                        $message = '<p>MyBrain Utilities HTAccess_Keeper detected a 0 bytes .htaccess file on <a href="'.site_url().'" target="_blank">'.site_url().'</a></p>';
                        $message .= '<p>ERROR RESTORING - PLEASE CORRECT MANUALLY!</p>';
                        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                        wp_mail($to, $subject, $message, $headers, array(''));
                    } else {
                        //restored!
                        $to = get_option('admin_email');
                        $subject = 'HTAccess_Keeper restored file '.site_url();
                        $message = '<p>MyBrain Utilities HTAccess_Keeper detected a 0 bytes .htaccess file on <a href="'.site_url().'" target="_blank">'.site_url().'</a></p>';
                        $message .= '<p>FILE RESTORED AT '.gmdate("YmdHis").'</p>';
                        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                        wp_mail($to, $subject, $message, $headers, array(''));
                    }
                } else {
                    //sorry!
                    $to = get_option('admin_email');
                    $subject = 'HTAccess_Keeper 0 bytes ERROR on '.site_url();
                    $message = '<p>MyBrain Utilities HTAccess_Keeper detected a 0 bytes .htaccess file on '.site_url().'</p>';
                    $message .= '<p>BACKUP NOT AVAILABLE - PLEASE CORRECT MANUALLY!</p>';
                    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                    wp_mail($to, $subject, $message, $headers, array(''));
                }
            }
        }
        $filehta = ABSPATH.'wp-config.php';
        $filebck = ABSPATH.'wp-config-keeper.php';
        if (file_exists($filehta)) {
            if (filesize($filehta) > 0) {
                //check
                if (file_exists($filebck)) {
                    // 1.0.1
                    // if (filesize($filehta) != filesize($filebck)) {
                    // 1.0.2
                    if ((filesize($filehta) != filesize($filebck)) || (filectime($filehta) > filectime($filebck))) {
                        //changed
                        copy($filehta, $filebck);
                    }
                } else {
                    //copy
                    copy($filehta, $filebck);
                }
            } else {
                if (file_exists($filebck) && (filesize($filebck) > 0)) {
                    //restore
                    if (!copy($filebck, $filehta)) {
                        //sorry!
                        $to = get_option('admin_email');
                        $subject = 'HTAccess_Keeper 0 bytes ERROR on '.site_url();
                        $message = '<p>MyBrain Utilities HTAccess_Keeper detected a 0 bytes wp-config.php file on <a href="'.site_url().'" target="_blank">'.site_url().'</a></p>';
                        $message .= '<p>ERROR RESTORING - PLEASE CORRECT MANUALLY!</p>';
                        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                        wp_mail($to, $subject, $message, $headers, array(''));
                    } else {
                        //restored!
                        $to = get_option('admin_email');
                        $subject = 'HTAccess_Keeper restored file '.site_url();
                        $message = '<p>MyBrain Utilities HTAccess_Keeper detected a 0 bytes wp-config.php file on <a href="'.site_url().'" target="_blank">'.site_url().'</a></p>';
                        $message .= '<p>FILE RESTORED AT '.gmdate("YmdHis").'</p>';
                        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                        wp_mail($to, $subject, $message, $headers, array(''));
                    }
                } else {
                    //sorry!
                    $to = get_option('admin_email');
                    $subject = 'HTAccess_Keeper 0 bytes ERROR on '.site_url();
                    $message = '<p>MyBrain Utilities HTAccess_Keeper detected a 0 bytes wp-config.php file on '.site_url().'</p>';
                    $message .= '<p>BACKUP NOT AVAILABLE - PLEASE CORRECT MANUALLY!</p>';
                    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                    wp_mail($to, $subject, $message, $headers, array(''));
                }
            }
        }
    }



    //20240904 - keeplogin!
    public function mybrain_utilities_keep_me_logged_in_for_1_year($expirein)
    {
        $options = get_option('mybrain_utilities_options', array());
        if (isset($options['keeploginenabled']) && ($options['keeploginenabled'] == 'yes')) {
            if (isset($options['keeplogintimeout']) && is_numeric($options['keeplogintimeout']) && ($options['keeplogintimeout'] > 0)) {
                $expirein = $options['keeplogintimeout'];
            }
        }
        return $expirein;
    }




    //20251016 - cleanhead!
    public function mybrain_utilities_run_cleanhead($args)
    {
        remove_action('wp_head', 'rest_output_link_wp_head', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
        // remove_action('wp_head', 'rel_canonical');
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
        remove_action('wp_head', 'wp_generator');
        add_filter('wp_statistics_html_comment', '__return_false');
    }




    // //20240904 - maps!
    public function mybrain_utilities_extra_leaflet_map($atts)
    {
        $atts = shortcode_atts(
            array(
                'center' => '52.145634,5.04855',
                'coords' => '',
                'zoom' => '15',
                'class' => 'mbu-map',
            ),
            $atts,
            'mbu_map'
        );
        //20250909 - sanitize!
        return '<div id="mbu-map" center="'.esc_attr($atts['center']).'" coords="'.esc_attr($atts['coords']).'" zoom="'.esc_attr($atts['zoom']).'" class="'.esc_attr($atts['class']).'"></div>';
    }

}
