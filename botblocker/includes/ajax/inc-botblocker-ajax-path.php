<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

function get_botblocker_paths_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];

    $where = "";
    if (!empty($search)) {
        $where = "WHERE id LIKE '%$search%' OR search LIKE '%$search%' OR rule LIKE '%$search%' OR comment LIKE '%$search%'";
    }

    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);

    $query = "SELECT id, priority, search, rule, comment, disable
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
add_action('wp_ajax_get_botblocker_paths', 'get_botblocker_paths_callback');

function get_path_details_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $id = intval($_POST['id']);

    $query = "SELECT * FROM $table_name WHERE id = %d";
    $path = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);

    if ($path) {
        wp_send_json_success($path);
    } else {
        wp_send_json_error('Path not found');
    }
}
add_action('wp_ajax_get_path_details', 'get_path_details_callback');

function update_path_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $id = intval($_POST['id']);
    $data = array(
        'priority' => intval($_POST['priority']),
        'search' => sanitize_textarea_field($_POST['search']),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment'])
    );

    $result = $wpdb->update($table_name, $data, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('Path updated successfully');
    } else {
        wp_send_json_error('Failed to update path');
    }
}
add_action('wp_ajax_update_path', 'update_path_callback');

function delete_path_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $id = intval($_POST['id']);

    $result = $wpdb->delete($table_name, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('Path deleted successfully');
    } else {
        wp_send_json_error('Failed to delete path');
    }
}
add_action('wp_ajax_delete_path', 'delete_path_callback');

function toggle_path_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $id = intval($_POST['id']);

    $query = "UPDATE $table_name SET disable = 1 - disable WHERE id = %d";
    $result = $wpdb->query($wpdb->prepare($query, $id));

    if ($result !== false) {
        wp_send_json_success('Path toggled successfully');
    } else {
        wp_send_json_error('Failed to toggle path');
    }
}
add_action('wp_ajax_toggle_path', 'toggle_path_callback');

function create_path_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $data = array(
        'priority' => intval($_POST['priority']),
        'search' => sanitize_textarea_field($_POST['search']),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'disable' => 0
    );

    $result = $wpdb->insert($table_name, $data);

    if ($result !== false) {
        wp_send_json_success('Path created successfully');
    } else {
        wp_send_json_error('Failed to create path');
    }
}
add_action('wp_ajax_create_path', 'create_path_callback');

function export_paths_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $paths = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    wp_send_json_success($paths);
}
add_action('wp_ajax_export_paths', 'export_paths_callback');

function import_paths_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $paths = json_decode(stripslashes($_POST['paths']), true);
    if (is_array($paths)) {
        $imported = 0;
        $skipped = 0;
        foreach ($paths as $path) {
            $search = sanitize_textarea_field($path['search']);
            $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE search = '$search'");
            if ($existing == 0) {
                $data = array(
                    'priority' => intval($path['priority']),
                    'search' => $search,
                    'rule' => sanitize_text_field($path['rule']),
                    'comment' => sanitize_textarea_field($path['comment']),
                    'disable' => intval($path['disable'])
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
add_action('wp_ajax_import_paths', 'import_paths_callback');

function clear_all_paths_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    $result = $wpdb->query("TRUNCATE TABLE $table_name");

    if ($result !== false) {
        wp_send_json_success('All paths have been cleared');
    } else {
        wp_send_json_error('Failed to clear paths');
    }
}
add_action('wp_ajax_clear_all_paths', 'clear_all_paths_callback');

function clear_all_paths()
{
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'path';

    return $result = $wpdb->query("TRUNCATE TABLE $table_name");
}
