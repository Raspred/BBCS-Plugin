<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}


function get_botblocker_ipv6_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];

    $where = "";
    if (!empty($search)) {
        $where = "WHERE search LIKE '%$search%' OR rule LIKE '%$search%' OR comment LIKE '%$search%'";
    }

    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);

    $query = "SELECT id, priority, search, expires, disable, rule, comment
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
            'ip' => $row['search'],
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
add_action('wp_ajax_get_botblocker_ipv6_rules', 'get_botblocker_ipv6_rules_callback');

function delete_ipv6_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $id = intval($_POST['id']);

    $result = $wpdb->delete($table_name, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('IPv6 rule deleted successfully');
    } else {
        wp_send_json_error('Failed to delete IPv6 rule');
    }
}
add_action('wp_ajax_delete_ipv6_rule', 'delete_ipv6_rule_callback');

function toggle_ipv6_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $id = intval($_POST['id']);

    $query = "UPDATE $table_name SET disable = 1 - disable WHERE id = %d";
    $result = $wpdb->query($wpdb->prepare($query, $id));

    if ($result !== false) {
        wp_send_json_success('IPv6 rule toggled successfully');
    } else {
        wp_send_json_error('Failed to toggle IPv6 rule');
    }
}
add_action('wp_ajax_toggle_ipv6_rule', 'toggle_ipv6_rule_callback');

function create_ipv6_rule_callback()
{

    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $ip = sanitize_text_field($_POST['ip']);
    $ip_range = bbcs_IpRange($ip);

    $data = array(
        'priority' => intval($_POST['priority']),
        'search' => $ip,
        'ip1' => bbcs_ipToNumeric(bbcs_expandIPv6($ip_range[0])),
        'ip2' => bbcs_ipToNumeric(bbcs_expandIPv6($ip_range[1])),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'expires' => strtotime($_POST['expires']),
        'disable' => 0
    );

    $result = $wpdb->insert($table_name, $data);

    if ($result !== false) {
        wp_send_json_success('IPv6 rule created successfully');
    } else {
        wp_send_json_error('Failed to create IPv6 rule');
    }
}
add_action('wp_ajax_create_ipv6_rule', 'create_ipv6_rule_callback');

function update_ipv6_rule_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $id = intval($_POST['id']);
    $ip = sanitize_text_field($_POST['ip']);
    $ip_range = bbcs_IpRange($ip);

    $data = array(
        'priority' => intval($_POST['priority']),
        'search' => $ip,
        'ip1' => bbcs_ipToNumeric(bbcs_expandIPv6($ip_range[0])),
        'ip2' => bbcs_ipToNumeric(bbcs_expandIPv6($ip_range[1])),
        'rule' => sanitize_text_field($_POST['rule']),
        'comment' => sanitize_textarea_field($_POST['comment']),
        'expires' => strtotime($_POST['expires'])
    );

    $result = $wpdb->update($table_name, $data, array('id' => $id));

    if ($result !== false) {
        wp_send_json_success('IPv6 rule updated successfully');
    } else {
        wp_send_json_error('Failed to update IPv6 rule');
    }
}
add_action('wp_ajax_update_ipv6_rule', 'update_ipv6_rule_callback');

function export_ipv6_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $rules = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    wp_send_json_success($rules);
}
add_action('wp_ajax_export_ipv6_rules', 'export_ipv6_rules_callback');

function import_ipv6_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

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
                    'ip1' => intval($rule['ip1']),
                    'ip2' => intval($rule['ip2']),
                    'expires' => intval($rule['expires']),
                    'disable' => intval($rule['disable']),
                    'rule' => sanitize_text_field($rule['rule']),
                    'readonly' => intval($rule['readonly']),
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
add_action('wp_ajax_import_ipv6_rules', 'import_ipv6_rules_callback');

function clear_all_ipv6_rules_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $result = $wpdb->query("TRUNCATE TABLE $table_name");

    if ($result !== false) {
        wp_send_json_success('All IPv6 rules have been cleared');
    } else {
        wp_send_json_error('Failed to clear IPv6 rules');
    }
}
add_action('wp_ajax_clear_all_ipv6_rules', 'clear_all_ipv6_rules_callback');

function get_ipv6_rule_details_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $id = intval($_POST['id']);

    $query = "SELECT * FROM $table_name WHERE id = %d";
    $rule = $wpdb->get_row($wpdb->prepare($query, $id), ARRAY_A);

    if ($rule) {
        wp_send_json_success($rule);
    } else {
        wp_send_json_error('Rule not found');
    }
}
add_action('wp_ajax_get_ipv6_rule_details', 'get_ipv6_rule_details_callback');


function import_ipv6_whitelist_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');
    import_ipv6_list('allow');
}
add_action('wp_ajax_import_ipv6_whitelist', 'import_ipv6_whitelist_callback');

function import_ipv6_blacklist_callback()
{
    check_ajax_referer('botblocker_nonce', 'nonce');
    import_ipv6_list('block');
}
add_action('wp_ajax_import_ipv6_blacklist', 'import_ipv6_blacklist_callback');

function import_ipv6_list($rule_type)
{
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $file_content = $_POST['file_content'];
    $lines = explode("\n", $file_content);

    $imported = 0;
    $skipped = 0;

    foreach ($lines as $line) {
        $ip = trim($line);
        if (empty($ip)) continue;

        $existing = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE search = %s", $ip));
        if ($existing == 0) {
            $ip_range = bbcs_IpRange($ip);
            $data = array(
                'priority' => 10,
                'search' => $ip,
                'ip1' => bbcs_ipToNumeric(bbcs_expandIPv6($ip_range[0])),
                'ip2' => bbcs_ipToNumeric(bbcs_expandIPv6($ip_range[1])),
                'rule' => $rule_type,
                'comment' => "Imported " . ($rule_type == 'allow' ? 'whitelist' : 'blacklist') . " (IP: $ip)",
                'expires' => BOTBLOCKER_EXP_INF
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
}

function clear_all_ipv6_rules()
{
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'ipv6rules';

    $result = $wpdb->query("TRUNCATE TABLE $table_name");
}
