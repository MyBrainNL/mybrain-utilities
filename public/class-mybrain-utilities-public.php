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

        $options = get_option('mybrain_utilities_options');
        if (isset($options['htaccesskeeperenabled']) && ($options['htaccesskeeperenabled'] == 'yes')) {
            if (is_plugin_active('htaccess-keeper.php') == false) {
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
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mybrain_Utilities_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mybrain_Utilities_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        $options = get_option('mybrain_utilities_options');
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

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mybrain_Utilities_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mybrain_Utilities_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script('mybrain-utilities', plugin_dir_url(__FILE__) . 'js/mybrain-utilities-public.js', array( 'jquery' ), $this->version, false);

        $options = get_option('mybrain_utilities_options');
        if (isset($options['mapenabled']) && ($options['mapenabled'] == 'yes')) {

            global $post;
            if ((is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'mbumap'))) {
                wp_enqueue_script('mbu-leaflet-scripts', plugin_dir_url(__FILE__) . 'js/leaflet.js', array( 'jquery' ), $this->version, false);
                wp_enqueue_script('mybrain-utilities-maps', plugin_dir_url(__FILE__) . 'js/mybrain-utilities-public-maps.js', array( 'jquery' ), $this->version, false);
            }
        }
    }



    //20240901 - For storage of all the settings
    /**
     * custom option and settings
     */
    public function mybrain_utilities_settings_init()
    {
        // register a new setting for "mybrain_utilities" page
        register_setting('mybrain_utilities', 'mybrain_utilities_options');

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
            __('HTAccess Keeper', 'mybrain-utilities'),
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
                // 'mybrain_utilities_custom_data' => 'custom',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_map',
            __('Maps Shortcode', 'mybrain-utilities'),
            array($this, 'mybrain_utilities_section_map_intro'),
            'mybrain_utilities',
            [
                'before_section' => '<div id="tab4" class="%s" style="display:none;">',
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
                'description' => __('Activate the shortcode [mbumap]', 'mybrain-utilities'),
                'toggled' => 'yes',
                'type' => 'yesno', //yesno or onoff
                'default' => 'yes', // unused - a toggle can not have a default
                'direction' => 'vertical',
                'class' => '',
                // 'mybrain_utilities_custom_data' => 'custom',
            ]
        );

        // register a new section in the "mybrain_utilities" page
        add_settings_section(
            'mybrain_utilities_section_keeplogin',
            __('Keep My Login', 'mybrain-utilities'),
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                    '86400' => __('1 Day', 'mybrain-utilities'),
                    '172800' => __('2 Days', 'mybrain-utilities'),
                    '604800' => __('7 Days', 'mybrain-utilities'),
                    '1209600' => __('14 Days', 'mybrain-utilities'),
                    '2592000' => __('30 Days', 'mybrain-utilities'),
                    '7776000' => __('90 Days', 'mybrain-utilities'),
                    '15552000' => __('180 Days', 'mybrain-utilities'),
                    '31556926' => __('365 Days', 'mybrain-utilities'),
                    '126230400' => __('1461 Days', 'mybrain-utilities'),
                ],
                'default' => '15552000',
                'class' => '',
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
                // 'mybrain_utilities_custom_data' => 'custom',
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
        esc_html_e('HTAccess_Keeper', 'mybrain-utilities');
        echo '</li><li>';
        esc_html_e('Keep_me_logged_in', 'mybrain-utilities');
        echo '</li><li>';
        esc_html_e('OpenStreetMap/Leaflet Shortcode', 'mybrain-utilities');
        echo '</li></ul>';
        echo '</p>';
        // echo '<pre class="hidden">';
        // $options = get_option('mybrain_utilities_options');
        // print_r($options);
        // echo '</pre>';
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
            esc_html_e('Please ', 'mybrain-utilities');
            echo '<a href="/wp-admin/plugins.php">';
            esc_html_e('deactivate', 'mybrain-utilities');
            echo '</a>';
            esc_html_e(' the old stand-alone plugin.', 'mybrain-utilities');
            echo '</p>';
        }
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
        echo '[mbumap center="52.145634,5.04855" coords="52.145634,5.04855" zoom="15" class="myclass"]';
        echo '<br/>* ';
        esc_html_e('center = map center', 'mybrain-utilities');
        echo '<br/>* ';
        esc_html_e('coords = places a marker', 'mybrain-utilities');
        echo '<br/><br/><b>';
        esc_html_e('How to add a marker popup?', 'mybrain-utilities');
        echo '</b><br/>* ';
        esc_html_e('Use a Paragraph Block with Extra CSS-class(es) "mbumap-popup" to add a balloon with that content to the map.', 'mybrain-utilities');
        echo '<br/>* ';
        esc_html_e('Use CSS-class(es) "mbumap-popup hidden" have it hidden in the front-end.', 'mybrain-utilities');
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
        echo '">';
        esc_html_e('Stay logged in longer.', 'mybrain-utilities');
        echo '<br/>';
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
        esc_html_e('Settings below are not in use, and for code demonstration purposes only.', 'mybrain-utilities');
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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');

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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');
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
        $options = get_option('mybrain_utilities_options');
        if (isset($options['keeploginenabled']) && ($options['keeploginenabled'] == 'yes')) {
            if (isset($options['keeplogintimeout']) && is_numeric($options['keeplogintimeout']) && ($options['keeplogintimeout'] > 0)) {
                $expirein = $options['keeplogintimeout'];
                // return 31556926; // 1 year in seconds
            }
        }
        return $expirein;
    }



    // //20240904 - maps!
    public function mybrain_utilities_extra_leaflet_map($atts)
    {
        $atts = shortcode_atts(
            array(
                'center' => '52.145634,5.04855',
                'coords' => '', //52.145634,5.04855
                'zoom' => '15',
                'class' => 'mbu-map',
            ),
            $atts,
            'mbu_map'
        );
        return '<div id="mbu-map" center="'.$atts['center'].'" coords="'.$atts['coords'].'" zoom="'.$atts['zoom'].'" class="'.$atts['class'].'"></div>';
    }

}
