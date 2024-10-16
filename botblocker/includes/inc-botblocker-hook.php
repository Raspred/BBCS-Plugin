<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

function bbcs_clean_old_hits_data() {
    global $wpdb;
    global $BBCS;

    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';
    
    $store_period = isset($BBCS['admin_store_period']) ? intval($BBCS['admin_store_period']) : 30;
    $store_period = max(1, min($store_period, 30)); 
    
    $gmt_offset = !empty($BBCS['admin_gmt_offset']) ? $BBCS['admin_gmt_offset'] : 0;
    $delete_before = time() - ($store_period * 24 * 60 * 60) + ($gmt_offset * 3600);
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $table_name_hits WHERE date < %d",
            $delete_before
        )
    );
}
add_action('bbcs_daily_data_cleanup', 'bbcs_clean_old_hits_data');


/* Add rewrite rules */
// MOVED to Activarion hook + flush rules 
/*function bbcs_addRewriteRules()
{
    add_rewrite_rule('^botblocker_license/?', 'index.php?botblocker_license=1', 'top');
}
add_action('init', 'bbcs_addRewriteRules');*/

/* Handle the BotBlocker license request from SERVER */
function bbcs_addQueryVars($vars)
{
    $vars[] = 'botblocker_license';
    return $vars;
}
add_filter('query_vars', 'bbcs_addQueryVars');

/*

//Uncomment this code to schedule the data cleanup task

function bbcs_schedule_data_cleanup() {
    if (!wp_next_scheduled('bbcs_daily_data_cleanup')) {
        wp_schedule_event(time(), 'daily', 'bbcs_daily_data_cleanup');
    }
}
register_activation_hook(__FILE__, 'bbcs_schedule_data_cleanup');

function bbcs_unschedule_data_cleanup() {
    $timestamp = wp_next_scheduled('bbcs_daily_data_cleanup');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'bbcs_daily_data_cleanup');
    }
}
register_deactivation_hook(__FILE__, 'bbcs_unschedule_data_cleanup');
*/