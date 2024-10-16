<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}


/**
 * Retrieves bot blocker hits data via AJAX.
 *
 * This function is an AJAX callback that retrieves bot blocker hits data from the database
 * and sends it back as a JSON response. It is hooked to the 'wp_ajax_get_botblocker_hits' action.
 *
 * @since 1.0.0
 *
 * @return void
 */
function get_botblocker_hits_callback() {
    check_ajax_referer('botblocker_nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';
    
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];
    
    $where = "WHERE page NOT LIKE '%/wp-admin/%' AND page NOT LIKE '%/wp-login.php%' AND page NOT LIKE '%/favicon.ico%'";
    if (!empty($search)) {
        $where .= " AND (ip LIKE '%$search%' OR ptr LIKE '%$search%' OR lang LIKE '%$search%' OR useragent LIKE '%$search%' OR country LIKE '%$search%' OR referer LIKE '%$search%' OR page LIKE '%$search%')";
    }
    
    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);
    
    $query = "SELECT date, ip, ptr, asnum, asname, lang, useragent, js_w, js_h, js_cw, js_ch, js_co, js_pi, adblock, country, referer, page
              FROM $table_name
              $where
              ORDER BY date DESC
              LIMIT $start, $length";
    
    $results = $wpdb->get_results($query, ARRAY_A);
    
    $data = array();
    foreach ($results as $row) {
        $datetime = new DateTime("@{$row['date']}");
        $data[] = array(
            'date' => $datetime->format('Y-m-d'),
            'time' => $datetime->format('H:i:s'),
            'ip' => $row['ip'],
            'ptr' => $row['ptr'],
            'as_info' => array('asnum' => $row['asnum'], 'asname' => $row['asname']),
            'country' => $row['country'],
            'lang' => substr($row['lang'], 0, 2),
            'useragent' => $row['useragent'],
            'referer' => $row['referer'],
            'page' => $row['page'],
            'js_info' => array(
                'js_w' => $row['js_w'] != '0' ? $row['js_w'] : '-',
                'js_h' => $row['js_h'] != '0' ? $row['js_h'] : '-',
                'js_cw' => $row['js_cw'] != '0' ? $row['js_cw'] : '-',
                'js_ch' => $row['js_ch'] != '0' ? $row['js_ch'] : '-',
                'js_co' => $row['js_co'] != '0' ? $row['js_co'] : '-',
                'js_pi' => $row['js_pi'] != '0' ? $row['js_pi'] : '-'
            ),
            'adblock' => $row['adblock']
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
add_action('wp_ajax_get_botblocker_hits', 'get_botblocker_hits_callback');


/**
 * Retrieves the bot blocker admin hits via AJAX.
 *
 * This function is responsible for retrieving the bot blocker admin hits data via AJAX request. It checks the AJAX referer for security purposes and retrieves the hits data from the database table. The hits data includes information such as date, IP address, PTR record, AS number and name, language, user agent, JavaScript information, adblock status, country, referer, and page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function get_botblocker_admin_hits_callback() {
    check_ajax_referer('botblocker_nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';
    
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];
    
    $where = "WHERE (page LIKE '%/wp-admin/%' OR page LIKE '%/wp-login.php%')";
    if (!empty($search)) {
        $where .= " AND (ip LIKE '%$search%' OR ptr LIKE '%$search%' OR lang LIKE '%$search%' OR useragent LIKE '%$search%' OR country LIKE '%$search%' OR referer LIKE '%$search%' OR page LIKE '%$search%')";
    }
    
    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);
    
    $query = "SELECT date, ip, ptr, asnum, asname, lang, useragent, js_w, js_h, js_cw, js_ch, js_co, js_pi, adblock, country, referer, page
              FROM $table_name
              $where
              ORDER BY date DESC
              LIMIT $start, $length";
    
    $results = $wpdb->get_results($query, ARRAY_A);
    
    $data = array();
    foreach ($results as $row) {
        $datetime = new DateTime("@{$row['date']}");
        $data[] = array(
            'date' => $datetime->format('Y-m-d'),
            'time' => $datetime->format('H:i:s'),
            'ip' => $row['ip'],
            'ptr' => $row['ptr'],
            'as_info' => array('asnum' => $row['asnum'], 'asname' => $row['asname']),
            'country' => $row['country'],
            'lang' => substr($row['lang'], 0, 2),
            'useragent' => $row['useragent'],
            'referer' => $row['referer'],
            'page' => $row['page'],
            'js_info' => array(
                'js_w' => $row['js_w'] != '0' ? $row['js_w'] : '-',
                'js_h' => $row['js_h'] != '0' ? $row['js_h'] : '-',
                'js_cw' => $row['js_cw'] != '0' ? $row['js_cw'] : '-',
                'js_ch' => $row['js_ch'] != '0' ? $row['js_ch'] : '-',
                'js_co' => $row['js_co'] != '0' ? $row['js_co'] : '-',
                'js_pi' => $row['js_pi'] != '0' ? $row['js_pi'] : '-'
            ),
            'adblock' => $row['adblock']
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
add_action('wp_ajax_get_botblocker_admin_hits', 'get_botblocker_admin_hits_callback');

/**
 * Retrieves other hits data from the botblocker database table via AJAX request.
 *
 * This function is an AJAX callback that retrieves other hits data from the botblocker database table
 * based on the provided start, length, and search parameters. The retrieved data is then formatted
 * and returned as a JSON response.
 *
 * @since 1.0.0
 *
 * @return void
 */
function get_botblocker_other_hits_callback() {
    check_ajax_referer('botblocker_nonce', 'nonce');
    
    global $wpdb;
    $table_name = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';
    
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'];
    
    $where = "WHERE page NOT LIKE '%/wp-admin/%' AND page NOT LIKE '%/wp-login.php%' AND (
        page LIKE '%/wp-cron.php%' OR
        page LIKE '%/feed/%' OR
        page LIKE '%/xmlrpc.php%' OR
        page LIKE '%/wp-json/%' OR
        page LIKE '%/robots.txt%' OR
        page LIKE '%/sitemap%' OR
        page LIKE '/favicon.ico'
    )";
    if (!empty($search)) {
        $where .= " AND (ip LIKE '%$search%' OR ptr LIKE '%$search%' OR lang LIKE '%$search%' OR useragent LIKE '%$search%' OR country LIKE '%$search%' OR referer LIKE '%$search%' OR page LIKE '%$search%')";
    }
    
    $total_query = "SELECT COUNT(*) FROM $table_name $where";
    $total = $wpdb->get_var($total_query);
    
    $query = "SELECT date, ip, ptr, asnum, asname, lang, useragent, js_w, js_h, js_cw, js_ch, js_co, js_pi, adblock, country, referer, page
              FROM $table_name
              $where
              ORDER BY date DESC
              LIMIT $start, $length";
    
    $results = $wpdb->get_results($query, ARRAY_A);
    
    $data = array();
    foreach ($results as $row) {
        $datetime = new DateTime("@{$row['date']}");
        $data[] = array(
            'date' => $datetime->format('Y-m-d'),
            'time' => $datetime->format('H:i:s'),
            'ip' => $row['ip'],
            'ptr' => $row['ptr'],
            'as_info' => array('asnum' => $row['asnum'], 'asname' => $row['asname']),
            'country' => $row['country'],
            'lang' => substr($row['lang'], 0, 2),
            'useragent' => $row['useragent'],
            'referer' => $row['referer'],
            'page' => $row['page'],
            'js_info' => array(
                'js_w' => $row['js_w'] != '0' ? $row['js_w'] : '-',
                'js_h' => $row['js_h'] != '0' ? $row['js_h'] : '-',
                'js_cw' => $row['js_cw'] != '0' ? $row['js_cw'] : '-',
                'js_ch' => $row['js_ch'] != '0' ? $row['js_ch'] : '-',
                'js_co' => $row['js_co'] != '0' ? $row['js_co'] : '-',
                'js_pi' => $row['js_pi'] != '0' ? $row['js_pi'] : '-'
            ),
            'adblock' => $row['adblock']
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
add_action('wp_ajax_get_botblocker_other_hits', 'get_botblocker_other_hits_callback');

