<?php

/**
 * The BotBlocker bootstrap file
 *
 * This file is responsible for loading the necessary files and initializing the plugin functionality.
 * It defines various constants and includes the main class file, which is responsible for running the plugin.
 * The plugin is activated and deactivated using the activate_botblocker() and deactivate_botblocker() functions.
 * The run_cyber_secure_botblocker() function initializes the plugin and its admin functionality.
 *
 * @link              https://globus.studio
 * @package           Cyber_Secure_Botblocker
 * @version           1.2.0
 *
 * @wordpress-plugin
 * Plugin Name:       BotBlocker
 * Plugin URI:        https://globus.studio/wordpress-toolkit/
 * Description:       BotBlocker by CyberSecure is a powerful WordPress plugin designed to safeguard your website from unwanted bots and malicious activities. With advanced detection algorithms, BotBlocker identifies and blocks harmful bots, reducing spam and protecting your site's resources. The plugin provides real-time monitoring and customizable rules, allowing you to control access and enhance site security effortlessly. Easy to install and configure, BotBlocker ensures a smooth user experience while keeping your site safe from automated threats. Keep your WordPress site secure and running efficiently with BotBlocker.
 * Version:           1.2.0
 * Author:            GLOBUS.studio @ CyberSecure @ Leonidov Eugene
 * Author URI:        https://globus.studio/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       botblocker
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC')) {
    exit;
}

/**
 * Constants for the BotBlocker plugin.
 * These constants define various settings and values used throughout the plugin.
 */
define( 'BOTBLOCKER', true ); // A constant to indicate that the plugin is active
define( 'BOTBLOCKER_PLUGIN_NAME', 'Cyber Secure BotBlocker' ); // The name of the plugin
define( 'BOTBLOCKER_SHORT_NAME', 'BotBlocker' ); // A shorter version of the plugin name
define( 'BOTBLOCKER_VERSION', '1.2.0' ); // The version number of the plugin
define( 'BOTBLOCKER_DIR', plugin_dir_path(__FILE__) ); // The directory path of the plugin
define( 'BOTBLOCKER_URL', plugin_dir_url(__FILE__) ); // The URL of the plugin directory
define( 'BOTBLOCKER_BASENAME', plugin_basename(__FILE__) ); // The basename of the main plugin file
/* The user agent string used for requests to the CyberSecure API */
define( 'BOTBLOCKER_USER_AGENT', 'CyberSecureBotBlocker/ ' . BOTBLOCKER_VERSION . ' by https://globus.studio; Client:' . get_bloginfo('url') ); 
define( 'BOTBLOCKER_DB_VERSION', '2.0' ); // The database version of the plugin
define( 'BOTBLOCKER_TABLE_PREFIX', 'bbcs_' ); // The prefix used for database tables
define( 'BOTBLOCKER_PREFIX', 'botblocker_' ); // The prefix used for settings and options
define( 'BOTBLOCKER_API_URL', 'https://api.cybersecure.top/v2/' ); // The URL of the CyberSecure API
define( 'BOTBLOCKER_API_GS_URL', 'https://api.globus.studio/v2/' ); // The URL of the Globus Studio API
define( 'BOTBLOCKER_WIDGETS', false ); // A constant to indicate that the plugin includes dashboard widgets
define( 'BOTBLOCKER_FEED_URL', 'https://mindcraft.top/feed/' ); // The URL of the CyberSecure feed

define( 'BOTBLOCKER_SITE_ROOT', ABSPATH); // The root directory of the site
define( 'BOTBLOCKER_SITE_URL', get_site_url()); // The URL of the site
define( 'BOTBLOCKER_SITE_NAME', get_bloginfo('name')); // The name of the site
define( 'BOTBLOCKER_SITE_EMAIL', get_bloginfo('admin_email')); // The email address of the site
define( 'BOTBLOCKER_EXP_INF', 9999999999 ); // The maximum value for the expires field in the Unix timestamp format

define( 'BOTBLOCKER_LICENSE_ENDPOINT', ' https://cybersecure.top/botblocker_license' ); 

/**
 * Determines whether to integrate settings into wp-config.php.
 * Set to true to integrate, false otherwise.
 */
define( 'BOTBLOCKER_INTEGRATE_WP_CONFIG', false );

/**
 * Determines whether to integrate settings into mu-plugins.
 * Set to true to integrate, false otherwise.
 */
define( 'BOTBLOCKER_INTEGRATE_MU_PLUGINS', true );

/* Include PRO ix exists */
if ( file_exists( WP_PLUGIN_DIR . '/cyber-secure-botblocker-pro/cyber-secure-botblocker-pro.php' ) ) {
    include_once( WP_PLUGIN_DIR . '/cyber-secure-botblocker-pro/cyber-secure-botblocker-pro.php' );
}

// Include the helper functions file
require_once BOTBLOCKER_DIR . 'helpers.php';

// Include the installation file
require_once BOTBLOCKER_DIR . 'includes/inc-botblocker-install.php';

// Include license functionality
bbcs_handleBotblockerLicense();

/**
 * Checks if the request is an AJAX request and performs logic.
 *
 * This function is responsible for checking if the current request is an AJAX request and performing the necessary bot blocking logic. 
 * It prevents automated bots from accessing or submitting data through AJAX requests.
 *
 * @return void
 */
function botblocker_ajax_check() {
    check_ajax_referer('botblocker_nonce', 'nonce');
    
    /* Include the BotBlocker main class file */
    require_once(BOTBLOCKER_DIR . 'includes/class-botblocker.php');
    $botBlocker = new BotBlocker();
    $botBlocker->initialize();
    
    wp_die(); 
}
add_action('wp_ajax_botblocker_check', 'botblocker_ajax_check');
add_action('wp_ajax_nopriv_botblocker_check', 'botblocker_ajax_check');

/**
 * Activates the Cyber Secure BotBlocker plugin.
 *
 * This function is called when the plugin is activated.
 * It includes the necessary files, performs database operations, and creates rule files.
 *
 * @return void
 */
function activate_botblocker() {

    /* Check installation and create tables if necessary */
    bbcs_check_install();

    require_once BOTBLOCKER_DIR . 'includes/class-botblocker-activator.php';
    Botblocker_Activator::activate();

    // Insert code to wp-config.php
    if ( defined( 'BOTBLOCKER_INTEGRATE_WP_CONFIG' ) && BOTBLOCKER_INTEGRATE_WP_CONFIG ) {
        bbcs_insertCodeToWpConfig();
    }
    // Install mu-plugin
    if ( defined( 'BOTBLOCKER_INTEGRATE_MU_PLUGINS' ) && BOTBLOCKER_INTEGRATE_MU_PLUGINS ) {
        bbcs_installMuPlugin();
    }

    // License URL
    add_rewrite_rule('^botblocker_license/?', 'index.php?botblocker_license=1', 'top');
    flush_rewrite_rules(false);
}
register_activation_hook( __FILE__, 'activate_botblocker' );


/**
 * Deactivates the Cyber Secure BotBlocker plugin.
 *
 * This function is called when the plugin is deactivated.
 * It includes the necessary files and performs cleanup operations.
 *
 * @return void
 */
function deactivate_botblocker() {
    require_once BOTBLOCKER_DIR . 'includes/class-botblocker-deactivator.php';
    Botblocker_Deactivator::deactivate();

    // Remove code from wp-config.php
    if ( defined( 'BOTBLOCKER_INTEGRATE_WP_CONFIG' ) && BOTBLOCKER_INTEGRATE_WP_CONFIG ) {
        bbcs_removeCodeFromWpConfig();
    }
    // Uninstall mu-plugin
    if ( defined( 'BOTBLOCKER_INTEGRATE_MU_PLUGINS' ) && BOTBLOCKER_INTEGRATE_MU_PLUGINS ) {
        bbcs_uninstallMuPlugin();
    }   
    
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'deactivate_botblocker' );

// Include the main class file
if ( ! class_exists( 'Cyber_Secure_Botblocker' ) ) {
    require_once BOTBLOCKER_DIR . 'includes/class-cyber-secure-botblocker.php';
}

/**
 * Runs the Cyber Secure BotBlocker plugin.
 *
 * This function initializes the plugin and its admin functionality.
 *
 * @return void
 */
function run_cyber_secure_botblocker() {

    /* Check installation and create tables if necessary (for corrupted installations) */
    bbcs_check_install();

    /* Include the BotBlocker main interface class file */
    $plugin = new Cyber_Secure_Botblocker();
    $plugin->run();

    // Initialize the admin functionality
    $bbcs_admin = new Botblocker_Admin(BOTBLOCKER_SHORT_NAME, BOTBLOCKER_VERSION);
    add_action('admin_menu', array($bbcs_admin, 'add_admin_menu'));
    $bbcs_admin->run();
}

add_action('plugins_loaded', 'run_cyber_secure_botblocker', -9998);

/* Add preconnect for Google Fonts */
function add_google_fonts_preconnect() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action('wp_head', 'add_google_fonts_preconnect');

/* End of file cyber-secure-botblocker.php */