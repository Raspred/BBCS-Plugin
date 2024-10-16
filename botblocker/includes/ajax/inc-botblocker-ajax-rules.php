<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

/**
 * Retrieves the bot blocker rules via AJAX request.
 *
 * This function is responsible for retrieving the bot blocker rules from the database
 * and sending the response as a JSON object via an AJAX request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function get_botblocker_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];

    $where = "";
    if (!empty($search)) {
        $where = "WHERE id LIKE '%$search%' OR type LIKE '%$search%' OR data LIKE '%$search%' OR rule LIKE '%$search%' OR comment LIKE '%$search%'";
    }

    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);

    $query = "SELECT id, priority, type, data, expires, disable, rule, comment
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
            'type' => $row['type'],
            'data' => $row['data'],
            'expires' => date('Y-m-d H:i:s', $row['expires']),
            'disable' => $row['disable'],
            'rule' => $row['rule'],
            'comment' => $row['comment']
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
add_action('wp_ajax_get_botblocker_rules', 'get_botblocker_rules_callback');



/**
 * Retrieves the details of a specific rule via AJAX request.
 *
 * This function is responsible for retrieving the details of a specific rule from the database
 * and sending the response as a JSON object via an AJAX request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function get_rule_details_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $id = intval($_POST['id']);

    $query = "SELECT * FROM $table_name WHERE id = %d";
    $rule = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);

    if ($rule) {
        wp_send_json_success($rule);
    } else {
        wp_send_json_error('Rule not found');
    }
}
add_action('wp_ajax_get_rule_details', 'get_rule_details_callback');

/**
 * Updates a rule in the database via AJAX request.
 *
 * This function is responsible for updating a rule in the database based on the provided data
 * and sending the response as a JSON object via an AJAX request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function update_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $id = intval($_POST['id']);
    $data = array(
        'priority' => intval($_POST['priority']),
        'type' => sanitize_text_field($_POST['type']),
        'data' => sanitize_textarea_field($_POST['data']),
        'expires' => strtotime($_POST['expires']),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'search' => sanitize_text_field($_POST['type']) . '=' . sanitize_textarea_field($_POST['data'])
    );

    $result = $wpdb->update($table_name, $data, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('Rule updated successfully');
    } else {
        wp_send_json_error('Failed to update rule');
    }
}
add_action('wp_ajax_update_rule', 'update_rule_callback');

/**
 * Deletes a rule from the database via AJAX request.
 *
 * This function is responsible for deleting a rule from the database based on the provided ID
 * and sending the response as a JSON object via an AJAX request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function delete_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $id = intval($_POST['id']);

    $result = $wpdb->delete($table_name, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('Rule deleted successfully');
    } else {
        wp_send_json_error('Failed to delete rule');
    }
}
add_action('wp_ajax_delete_rule', 'delete_rule_callback');

/**
 * Toggles a rule in the database via AJAX request.
 *
 * This function is responsible for toggling a rule in the database based on the provided ID
 * and sending the response as a JSON object via an AJAX request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function toggle_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $id = intval($_POST['id']);

    $query = "UPDATE $table_name SET disable = 1 - disable WHERE id = %d";
    $result = $wpdb->query($wpdb->prepare($query, $id));

    if ($result !== false) {
        wp_send_json_success('Rule toggled successfully');
    } else {
        wp_send_json_error('Failed to toggle rule');
    }
}
add_action('wp_ajax_toggle_rule', 'toggle_rule_callback');


/**
 * Adds a new rule to the database via AJAX request.
 *
 * This function is responsible for adding a new rule to the database based on the provided data
 * and sending the response as a JSON object via an AJAX request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function create_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $data = array(
        'priority' => intval($_POST['priority']),
        'type' => sanitize_text_field($_POST['type']),
        'data' => sanitize_textarea_field($_POST['data']),
        'expires' => strtotime($_POST['expires']),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'search' => sanitize_text_field($_POST['type']) . '=' . sanitize_textarea_field($_POST['data']),
        'disable' => 0
    );

    $result = $wpdb->insert($table_name, $data);

    if ($result !== false) {
        wp_send_json_success('Rule created successfully');
    } else {
        wp_send_json_error('Failed to create rule');
    }
}
add_action('wp_ajax_create_rule', 'create_rule_callback');


function export_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $rules = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    wp_send_json_success($rules);
}
add_action('wp_ajax_export_rules', 'export_rules_callback');

function import_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $rules = json_decode(stripslashes($_POST['rules']), true);
    if (is_array($rules)) {
        $imported = 0;
        $skipped = 0;
        foreach ($rules as $rule) {
            $search = sanitize_text_field($rule['search']);
            $existing = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE search = '$search'");
            if ($existing == 0) {
                $data = array(
                    'search' => $search,
                    'priority' => intval($rule['priority']),
                    'type' => sanitize_text_field($rule['type']),
                    'data' => sanitize_textarea_field($rule['data']),
                    'expires' => intval($rule['expires']),
                    'disable' => intval($rule['disable']),
                    'rule' => sanitize_text_field($rule['rule']),
                    'comment' => sanitize_textarea_field($rule['comment']),
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
add_action('wp_ajax_import_rules', 'import_rules_callback');

function clear_all_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    $result = $wpdb->query("TRUNCATE TABLE $table_name");

    if ($result !== false) {
        wp_send_json_success('All rules have been cleared');
    } else {
        wp_send_json_error('Failed to clear rules');
    }
}
add_action('wp_ajax_clear_all_rules', 'clear_all_rules_callback');

function clear_all_rules()
{
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'rules';

    return $result = $wpdb->query("TRUNCATE TABLE $table_name");
}
