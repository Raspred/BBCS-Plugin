<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

function get_botblocker_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];

    $where = "";
    if (!empty($search)) {
        $where = "WHERE id LIKE '%$search%' OR search LIKE '%$search%' OR data LIKE '%$search%' OR rule LIKE '%$search%' OR comment LIKE '%$search%'";
    }

    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);

    $query = "SELECT id, priority, search, data, rule, comment, disable
              FROM $table_name
              $where
              ORDER BY priority DESC
              LIMIT $start, $length";

    $results = $wpdb->get_results($query, ARRAY_A);

    $data = array();
    foreach ($results as $row) {
        $data[] = array(
            'id' => $row['id'],
            'priority' => $row['priority'],
            'search' => $row['search'],
            'data' => $row['data'],
            'rule' => $row['rule'],
            'comment' => $row['comment'],
            'disable' => $row['disable']
        );
    }

    $response = array(
        'draw' => intval($_POST['draw']),
        'recordsTotal' => $total,
        'recordsFiltered' => $total,
        'data' => $data
    );

    wp_send_json($response);
}
add_action('wp_ajax_get_botblocker_white', 'get_botblocker_white_callback');

function get_white_details_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $id = intval($_POST['id']);

    $query = "SELECT * FROM $table_name WHERE id = %d";
    $white = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);

    if ($white) {
        wp_send_json_success($white);
    } else {
        wp_send_json_error('White bot not found');
    }
}
add_action('wp_ajax_get_white_details', 'get_white_details_callback');

function update_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $id = intval($_POST['id']);
    $data = array(
        'priority' => intval($_POST['priority']),
        'search' => sanitize_text_field($_POST['search']),
        'data' => sanitize_textarea_field($_POST['data']),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'distance' => sanitize_text_field($_POST['distance'])
    );

    $result = $wpdb->update($table_name, $data, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('White bot updated successfully');
    } else {
        wp_send_json_error('Failed to update white bot');
    }
}
add_action('wp_ajax_update_white', 'update_white_callback');

function delete_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $id = intval($_POST['id']);

    $result = $wpdb->delete($table_name, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('White bot deleted successfully');
    } else {
        wp_send_json_error('Failed to delete white bot');
    }
}
add_action('wp_ajax_delete_white', 'delete_white_callback');

function toggle_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $id = intval($_POST['id']);

    $query = "UPDATE $table_name SET disable = 1 - disable WHERE id = %d";
    $result = $wpdb->query($wpdb->prepare($query, $id));

    if ($result !== false) {
        wp_send_json_success('White bot toggled successfully');
    } else {
        wp_send_json_error('Failed to toggle white bot');
    }
}
add_action('wp_ajax_toggle_white', 'toggle_white_callback');

function create_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $data = array(
        'priority' => intval($_POST['priority']),
        'search' => sanitize_text_field($_POST['search']),
        'data' => sanitize_textarea_field($_POST['data']),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'disable' => 0,
        'distance' => sanitize_text_field($_POST['distance'])
    );

    $result = $wpdb->insert($table_name, $data);

    if ($result !== false) {
        wp_send_json_success('White bot created successfully');
    } else {
        wp_send_json_error('Failed to create white bot');
    }
}
add_action('wp_ajax_create_white', 'create_white_callback');

function export_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $white_bots = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    wp_send_json_success($white_bots);
}
add_action('wp_ajax_export_white', 'export_white_callback');

function import_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $white_bots = json_decode(stripslashes($_POST['white_bots']), true);
    if (is_array($white_bots)) {
        $imported = 0;
        $skipped = 0;
        foreach ($white_bots as $bot) {
            $search = sanitize_text_field($bot['search']);
            $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE search = '$search'");
            if ($existing == 0) {
                $data = array(
                    'priority' => intval($bot['priority']),
                    'search' => $search,
                    'data' => sanitize_textarea_field($bot['data']),
                    'rule' => sanitize_text_field($bot['rule']),
                    'comment' => sanitize_textarea_field($bot['comment']),
                    'disable' => intval($bot['disable']),
                    'distance' => sanitize_text_field($bot['distance'])
                );
                $result = $wpdb->insert($table_name, $data);
                if ($result !== false) {
                    $imported++;
                }
            } else {
                $skipped++;
            }
        }
        wp_send_json_success(array(
            'imported' => $imported,
            'skipped' => $skipped,
        ));
    } else {
        wp_send_json_error('Invalid JSON format');
    }
}
add_action('wp_ajax_import_white', 'import_white_callback');

function clear_all_white_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    $result = $wpdb->query("TRUNCATE TABLE $table_name");

    if ($result !== false) {
        wp_send_json_success('All white bots have been cleared');
    } else {
        wp_send_json_error('Failed to clear white bots');
    }
}
add_action('wp_ajax_clear_all_white', 'clear_all_white_callback');

function clear_all_white()
{
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'se';

    return $result = $wpdb->query("TRUNCATE TABLE $table_name");
}
