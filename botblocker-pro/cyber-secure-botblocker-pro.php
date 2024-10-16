<?php

/**
 * The BotBlocker PRO bootstrap file
 *
 * @link              https://globus.studio
 * @since             1.4.0
 * @package           Cyber_Secure_Botblocker_PRO
 * @version           1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       BotBlocker PRO
 * Plugin URI:        https://globus.studio/wordpress-toolkit/
 * Description:       BotBlocker PRO by CyberSecure is a powerful WordPress plugin designed to safeguard your website from unwanted bots and malicious activities.
 * Version:           1.0.0
 * Author:            GLOBUS.studio @ CyberSecure @ Leonidov Eugene
 * Author URI:        https://globus.studio/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       botblocker-pro
 */

if (!defined('ABSPATH') || !defined('WPINC')) {
    exit;
}

/**
 * Constants for the BotBlocker plugin.
 */
define('BOTBLOCKER_PRO', true);
define('BOTBLOCKER_PRO_PLUGIN_NAME', 'Cyber Secure BotBlocker PRO');
define('BOTBLOCKER_PRO_SHORT_NAME', 'BotBlockerPRO');
define('BOTBLOCKER_PRO_VERSION', '1.0.0');
define('BOTBLOCKER_PRO_DIR', plugin_dir_path(__FILE__));
define('BOTBLOCKER_PRO_URL', plugin_dir_url(__FILE__));
define('BOTBLOCKER_PRO_BASENAME', plugin_basename(__FILE__));

// Include the plugin.php file to access is_plugin_active() function
if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

// Check if the main plugin is active
function is_botblocker_main_active() {
    // Check if the BOTBLOCKER constant is defined
    if (defined('BOTBLOCKER') && BOTBLOCKER === true) {
        return true;
    }

    // Check if the main plugin file exists in the 'botblocker' folder
    $main_plugin_path = WP_PLUGIN_DIR . '/botblocker/cyber-secure-botblocker.php';
    if (file_exists($main_plugin_path)) {
        // Check if the main plugin is active
        if (is_plugin_active('botblocker/cyber-secure-botblocker.php')) {
            return true;
        }
    }

    return false;
}

// Prevent activation if the main plugin is not active
function activate_botblocker_pro() {
    if (!is_botblocker_main_active()) {
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate this plugin
        wp_die(
            __('BotBlocker PRO cannot be activated because the main BotBlocker plugin is not active. Please activate the main plugin first.', 'botblocker-pro'),
            __('Plugin Activation Error', 'botblocker-pro'),
            array('back_link' => true)
        );
    }
    require_once BOTBLOCKER_PRO_DIR . 'includes/class-botblocker-pro-activator.php';
    Botblocker_PRO_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_botblocker_pro');

// Deactivate the plugin if the main plugin is deactivated or missing
function deactivate_botblocker_pro() {
    require_once BOTBLOCKER_PRO_DIR . 'includes/class-botblocker-pro-deactivator.php';
    Botblocker_PRO_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_botblocker_pro');

// Check and deactivate the plugin if the main plugin is not active
function check_and_deactivate_botblocker_pro() {
    if (!is_botblocker_main_active()) {
        deactivate_plugins(plugin_basename(__FILE__)); // Deactivate this plugin
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>' . __('BotBlocker PRO has been deactivated because the main BotBlocker plugin is not active.', 'botblocker-pro') . '</p></div>';
        });
    }
}
add_action('plugins_loaded', 'check_and_deactivate_botblocker_pro');

// Include the main plugin class and run the plugin
if (!class_exists('Cyber_Secure_Botblocker_PRO') && is_botblocker_main_active()) {
    require_once BOTBLOCKER_PRO_DIR . 'includes/class-cyber-secure-botblocker-pro.php';
}

function run_cyber_secure_botblocker_pro() {
    if (is_botblocker_main_active()) {
        $plugin = new Cyber_Secure_Botblocker_PRO();
        $plugin->run();
    }
}
add_action('plugins_loaded', 'run_cyber_secure_botblocker_pro', -9999);
