<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://mybrain.nl/
 * @since      1.0.0
 *
 * @package    Mybrain_Utilities
 * @subpackage Mybrain_Utilities/admin/partials
 */
if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

//20240901
if (!current_user_can("manage_options")) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'mybrain-utilities'));
}
// TABS! <!-- This file should primarily consist of HTML with a little bit of PHP. -->
?>
<div class="wrap">
<h1><?php echo esc_html(get_admin_page_title()); ?> <span class="mbu-version"> v<?php echo esc_html(MYBRAIN_UTILITIES_VERSION); ?></span></h1>
<form action="options.php" method="post">
    <h2 class="nav-tab-wrapper">
        <a class="nav-tab" href="#tab1" title="About"><?php esc_html_e('About', 'mybrain-utilities'); ?></a>
        <a class="nav-tab" href="#tab2" title="HTAccess_Keeper">HTAccess_Keeper</a>
        <a class="nav-tab" href="#tab3" title="Keep_me_logged_in">Keep_me_logged_in</a>
        <a class="nav-tab" href="#tab4" title="Console_Warning">Console_Warning</a>
        <a class="nav-tab" href="#tab6" title="Clean_Head">Clean_Head</a>
        <a class="nav-tab" href="#tab5" title="Map"><?php esc_html_e('Map', 'mybrain-utilities'); ?></a>
        <a class="nav-tab hidden" href="#tab9" title="Sample Settings">Sample Settings</a>
    </h2>
<?php

settings_fields('mybrain_utilities');
do_settings_sections('mybrain_utilities');
submit_button(__('Save Settings', 'mybrain-utilities'));

?>
</form>
</div>