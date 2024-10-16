<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

/**
 * Displays the daily hits chart.
 *
 * This function is responsible for rendering and displaying a chart that shows the daily hits of a website.
 * It retrieves the necessary data from the database and uses a chart library to generate the chart.
 *
 * @return void
 */
function bbcs_display_daily_hits_chart($atts)
{
    global $wpdb;
    global $BBCS;
    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';

    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => '400px',
    ), $atts);

    $gmt_offset = !empty($BBCS['admin_gmt_offset']) ? $BBCS['admin_gmt_offset'] : 0;

    $current_date = new DateTime("now", new DateTimeZone('UTC'));
    $current_date->modify(sprintf("%+d hours", $gmt_offset));

    $start_of_day = $current_date->setTime(0, 0, 0)->getTimestamp();
    $end_of_day = $current_date->setTime(23, 59, 59)->getTimestamp();

    $sql = $wpdb->prepare(
        "SELECT FLOOR((date - %d + %f * 3600) / 3600) AS hour, COUNT(*) AS hits
         FROM $table_name_hits
         WHERE date BETWEEN %d - %f * 3600 AND %d - %f * 3600
         AND page NOT LIKE '%/wp-admin/%'
         AND page NOT LIKE '%/wp-content/%'
         AND page NOT LIKE '%/wp-includes/%'
         AND page NOT LIKE '%/favicon.ico%'
         GROUP BY hour
         ORDER BY hour",
        $start_of_day,
        $gmt_offset,
        $start_of_day,
        $gmt_offset,
        $end_of_day,
        $gmt_offset
    );

    $results = $wpdb->get_results($sql);

    $chart_data = array_fill(0, 24, 0);
    foreach ($results as $row) {
        $hour = $row->hour % 24;  // Ensure the hour is within 0-23 range
        $chart_data[$hour] = (int)$row->hits;
    }

    ob_start();
?>
    <div id="bbcs_daily_hits_chart" style="width: <?php echo esc_attr($atts['width']) ?>; height: <?php echo esc_attr($atts['height']) ?>;"></div>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Hour');
            data.addColumn('number', 'Hits');

            <?php
            for ($i = 0; $i < 24; $i++) {
                echo "data.addRow(['" . sprintf("%02d:00", $i) . "', " . $chart_data[$i] . "]);\n";
            }
            ?>

            var options = {
                legend: {
                    position: 'none'
                },
                bars: 'vertical',
                backgroundColor: 'transparent',
                chartArea: {
                    left: '5%',
                    top: 10,
                    width: '90%',
                    height: '80%'
                },
                hAxis: {
                    textPosition: 'out',
                    textStyle: {
                        fontSize: 10
                    },
                    slantedText: true,
                    slantedTextAngle: 45
                },
                vAxis: {
                    textPosition: 'none',
                    gridlines: {
                        color: 'transparent',
                    },
                    baselineColor: 'transparent'
                },
                //tooltip: { isHtml: true },
                enableInteractivity: true
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('bbcs_daily_hits_chart'));
            chart.draw(data, options);
        }
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('bbcs_daily_hits_chart', 'bbcs_display_daily_hits_chart');

/**
 * Displays the hits and unique visitors chart by day.
 *
 * This function is responsible for rendering and displaying a chart that shows the hits and unique visitors of a website by day.
 * It retrieves the necessary data from the database and uses a chart library to generate the chart.
 *
 * @param int $days The number of days to display in the chart.
 * @return void
 */
function bbcs_display_hits_and_uniques_chart($atts)
{
    global $wpdb;
    global $BBCS;
    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';

    // Define default attributes
    $defaults = array(
        'width' => '100%',
        'height' => '400px',
        'days' => 7 // По умолчанию показываем 7 дней
    );

    $atts = shortcode_atts($defaults, $atts, 'bbcs_hits_and_uniques_chart');

    $days = min(max((int)$atts['days'], 1), 31); // Ограничиваем от 1 до 31 дня

    $gmt_offset = !empty($BBCS['admin_gmt_offset']) ? $BBCS['admin_gmt_offset'] : 0;
    $current_date = new DateTime("now", new DateTimeZone('UTC'));
    $current_date->modify(sprintf("%+d hours", $gmt_offset));

    $end_date = $current_date->format('Y-m-d 23:59:59');
    $start_date = $current_date->modify("-" . ($days - 1) . " days")->format('Y-m-d 00:00:00');

    $sql = $wpdb->prepare(
        "SELECT 
            DATE(FROM_UNIXTIME(date + %f * 3600)) AS visit_date,
            COUNT(DISTINCT ip) AS uniques,
            COUNT(*) AS hits
         FROM $table_name_hits
         WHERE 
            date BETWEEN UNIX_TIMESTAMP(%s) - %f * 3600 AND UNIX_TIMESTAMP(%s) + %f * 3600
            AND page NOT LIKE '%/wp-admin/%'
            AND page NOT LIKE '%/wp-content/%'
            AND page NOT LIKE '%/wp-includes/%'
            AND page NOT LIKE '%/favicon.ico%'
         GROUP BY visit_date
         ORDER BY visit_date ASC",
        $gmt_offset,
        $start_date,
        $gmt_offset,
        $end_date,
        $gmt_offset
    );
    $results = $wpdb->get_results($sql);

    // Подготовка данных для графика
    $chart_data = array();
    $current = new DateTime($start_date);
    $end = new DateTime($end_date);
    while ($current <= $end) {
        $date_string = $current->format('Y-m-d');
        $chart_data[$date_string] = array('uniques' => 0, 'hits' => 0);
        $current->modify('+1 day');
    }

    foreach ($results as $row) {
        $chart_data[$row->visit_date]['uniques'] = (int)$row->uniques;
        $chart_data[$row->visit_date]['hits'] = (int)$row->hits;
    }

    // Вывод HTML и JavaScript для графика
    ob_start();
?>
    <div id="bbcs_hits_and_uniques_chart" style="width: <?php echo esc_attr($atts['width']) ?>; height: <?php echo esc_attr($atts['height']) ?>;"></div>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', 'Unique Visitors');
            data.addColumn('number', 'Hits');
            data.addColumn({
                type: 'string',
                role: 'tooltip',
                'p': {
                    'html': true
                }
            }); // Добавьте эту строку

            <?php
            ksort($chart_data);
            foreach ($chart_data as $date => $values) {
                $dayOnly = date('d', strtotime($date));
                $tooltip = '<div style="padding:5px;"><b style="font-size:11px; white-space: nowrap; word-wrap: normal; overflow-wrap: normal;">' . $date . '</b><br/>' .
                    '<span style="font-size:10px;">Uniques: ' . $values['uniques'] . '</span><br/>' .
                    '<span style="font-size:10px;">Hits: ' . $values['hits'] . '</span></div>';
                echo "data.addRow(['" . $dayOnly . "', " . $values['uniques'] . ", " . $values['hits'] . ", '" . $tooltip . "']);\n";
            }
            ?>

            var options = {
                legend: {
                    position: 'none'
                },
                crosshair: {
                    trigger: 'both',
                    orientation: 'vertical'
                },
                backgroundColor: 'transparent',
                chartArea: {
                    width: '85%',
                    height: '80%'
                },
                hAxis: {
                    textStyle: {
                        fontSize: 10
                    }
                    /*,
                                    slantedText: true,
                                    slantedTextAngle: 45*/
                },
                vAxis: {
                    minValue: 0
                },
                series: {
                    0: {
                        color: '#4285F4'
                    },
                    1: {
                        color: '#DB4437'
                    }
                },
                tooltip: {
                    isHtml: true
                }
            };

            var chart = new google.visualization.LineChart(document.getElementById('bbcs_hits_and_uniques_chart'));
            chart.draw(data, options);
        }
    </script>

<?php

    return ob_get_clean();
}
add_shortcode('bbcs_hits_and_uniques_chart', 'bbcs_display_hits_and_uniques_chart');


function bbcs_display_visitors_jsvectormap($atts)
{
    global $wpdb;
    global $BBCS;
    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';

    $atts = shortcode_atts(array(
        'days' => 30,
        'width' => '100%',
        'height' => '500px',
    ), $atts);
    $days = min(max((int)$atts['days'], 1), 365);

    $gmt_offset = !empty($BBCS['admin_gmt_offset']) ? $BBCS['admin_gmt_offset'] : 0;

    $current_date = new DateTime("now", new DateTimeZone('UTC'));
    $current_date->modify(sprintf("%+d hours", $gmt_offset));

    $end_date = $current_date->format('Y-m-d 23:59:59');
    $start_date = (clone $current_date)->modify("-" . ($days - 1) . " days")->format('Y-m-d 00:00:00');

    $sql = $wpdb->prepare(
        "SELECT country, COUNT(DISTINCT ip) AS unique_visitors 
         FROM $table_name_hits 
         WHERE date BETWEEN UNIX_TIMESTAMP(%s) - %f * 3600 AND UNIX_TIMESTAMP(%s) - %f * 3600
         AND page NOT LIKE '%/wp-admin/%'
         AND page NOT LIKE '%/wp-content/%'
         AND page NOT LIKE '%/wp-includes/%'
         AND country != '' AND country != '-'
         GROUP BY country
         ORDER BY unique_visitors DESC",
        $start_date,
        $gmt_offset,
        $end_date,
        $gmt_offset
    );

    $results = $wpdb->get_results($sql);

    $chart_data = array();
    foreach ($results as $row) {
        $chart_data[$row->country] = (int)$row->unique_visitors;
    }

    $chart_data_json = json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    ob_start();
?>
    <div id="bbcs_visitors_jsvectormap" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;"></div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            var mapData = JSON.parse('<?php echo html_entity_decode($chart_data_json, ENT_QUOTES | ENT_HTML401); ?>');

            var map = new jsVectorMap({
                selector: '#bbcs_visitors_jsvectormap',
                map: 'world',
                visualizeData: {
                    scale: ['#e3f2fd', '#1565c0'],
                    values: mapData
                },
                regionStyle: {
                    initial: {
                        fill: '#CCCCCC',
                        stroke: "#ffffff",
                        strokeWidth: 1,
                        fillOpacity: 1
                    }
                },
                onRegionTooltipShow(event, tooltip, code) {
                    var visitors = mapData[code] || 0;
                    tooltip.css({
                        backgroundColor: '#61639F'
                    });
                    tooltip.text(
                        `<h5 class="bbcs-map-label-h">${tooltip.text()}</h5>` +
                        `<p class="bbcs-map-label-p">Visitors: ${visitors}</p>`,
                        true // Enables HTML
                    );
                }
            });
        });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('bbcs_visitors_jsvectormap', 'bbcs_display_visitors_jsvectormap');

/**
 * Retrieves the system status information.
 *
 * This function returns a string containing the system status information such as the operating system, 
 * web server, database version, PHP version, WordPress version, and BotBlocker version (if defined). 
 * It also includes PHP configuration variables such as memory_limit, max_execution_time, post_max_size, 
 * and upload_max_filesize.
 *
 * @return string The system status information.
 */
function bbcs_system_status_view()
{
    global $wpdb;
    // global $BBCS;

    $output = "<pre class=\"bbcs_pre\">";
    $output .= "OS: " . php_uname('s') . " " . php_uname('r') . "\n";
    $output .= "Web: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    $output .= "DB v." . $wpdb->db_version() . "\n";
    $output .= "PHP v." . phpversion() . "\n";

    $output .= "\nWordPress v." . get_bloginfo('version') . "\n";
    if (defined('BOTBLOCKER_VERSION')) {
        $output .= "BotBlocker v." . BOTBLOCKER_VERSION . "\n";
    }

    $output .= "\nPHP vars:\n";
    $output .= "memory_limit: " . ini_get('memory_limit') . "\n";
    $output .= "max_execution_time: " . ini_get('max_execution_time') . "\n";
    $output .= "post_max_size: " . ini_get('post_max_size') . "\n";
    $output .= "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    /*
    $output .= "\nYour info:\n";
    $output .= "OS: " . $BBCS['os'] . "\n";
    $output .= "Browser: " .  $BBCS['browser'] . "\n";
    $output .= "IP: " . $BBCS['ip'] . "\n";
    $output .= "PTR: " . $BBCS['ptr'] . "\n";
*/
    $output .= "</pre>";
    return $output;
}
add_shortcode('bbcs_system_status', 'bbcs_system_status_view');


/**
 * Retrieves and displays a list of cybersecure news items from a specified feed URL.
 *
 * @return string The HTML markup containing the list of news items.
 */
function bbcs_get_cybersecure_news()
{
    if (!defined('BOTBLOCKER_FEED_URL')) {
        return 'Ошибка: URL фида не определен';
    }
    $rss = fetch_feed(BOTBLOCKER_FEED_URL);
    if (is_wp_error($rss)) {
        return 'Ошибка при получении фида';
    }
    $maxitems = $rss->get_item_quantity(0);
    $rss_items = $rss->get_items(0, $maxitems);
    if ($maxitems == 0) {
        return 'Нет доступных новостей';
    }
    usort($rss_items, function ($a, $b) {
        return $b->get_date('U') - $a->get_date('U');
    });
    $rss_items = array_slice($rss_items, 0, 5);
    $output = '<ul class="bbcs_cybersecure-news">';

    foreach ($rss_items as $item) {
        $output .= '<li class="bbcs_news-item">';
        $output .= '<a href="' . esc_url($item->get_permalink()) . '" target="_blank" class="bbcs_news_a">' . esc_html($item->get_title()) . '</a>';
        $output .= '<span class="bbcs_news-date">' . $item->get_date('j F Y') . ' at ' . $item->get_date('H:i') . '</span>';
        $output .= '</li>';
    }

    $output .= '</ul>';
    return $output;
}
add_shortcode('bbcs_cybersecure_news', 'bbcs_get_cybersecure_news');


function bbcs_healthGaugeShortcode($atts)
{
    // Define default attributes
    $defaults = array(
        'id' => 'gauge_' . uniqid(),
        'value' => 0,
        'min' => 0,
        'max' => 100,
        'decimals' => 0,
        'gauge_width_scale' => 0.6,
        'label' => 'Security Health Status',
        'symbol' => '%',
        'pointer' => 'true',
        'pointer_top_length' => -15,
        'pointer_bottom_length' => 10,
        'pointer_bottom_width' => 12,
        'pointer_color' => '#8e8e93',
        'pointer_stroke' => '#ffffff',
        'pointer_stroke_width' => 3,
        'pointer_stroke_linecap' => 'round',
        'level_colors' => '["#ff3b30", "#d8ca00", "#43bf58"]'
    );

    // Merge user attributes with defaults
    $atts = shortcode_atts($defaults, $atts, 'bbcs_health_gauge');

    // Ensure boolean values are correctly formatted for JavaScript
    $atts['pointer'] = filter_var($atts['pointer'], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

    $unique_id = esc_attr($atts['id']);
    $name = 'jg_' . $unique_id;
    $html = '<div id="' . $unique_id . '"></div>';
    $html .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var ' . esc_attr($name) . ' = new JustGage({
                id: "' . $unique_id . '", 
                value: ' . esc_attr($atts['value']) . ',
                min: ' . esc_attr($atts['min']) . ',
                max: ' . esc_attr($atts['max']) . ',
                decimals: ' . esc_attr($atts['decimals']) . ',
                gaugeWidthScale: ' . esc_attr($atts['gauge_width_scale']) . ',
                label: "' . esc_attr($atts['label']) . '",
                symbol: "' . esc_attr($atts['symbol']) . '",
                pointer: ' . $atts['pointer'] . ',
                pointerOptions: {
                  toplength: ' . esc_attr($atts['pointer_top_length']) . ',
                  bottomlength: ' . esc_attr($atts['pointer_bottom_length']) . ',
                  bottomwidth: ' . esc_attr($atts['pointer_bottom_width']) . ',
                  color: "' . esc_attr($atts['pointer_color']) . '",
                  stroke: "' . esc_attr($atts['pointer_stroke']) . '",
                  stroke_width: ' . esc_attr($atts['pointer_stroke_width']) . ',
                  stroke_linecap: "' . esc_attr($atts['pointer_stroke_linecap']) . '"
                },
                levelColors: ' . $atts['level_colors'] . '
            });
        });
    </script>';

    return $html;
}

add_shortcode('bbcs_health_gauge', 'bbcs_healthGaugeShortcode');

/**
 * Retrieves the website statistics.
 *
 * Helper function for the bbcs_display_statistics_chart shortcode.
 * 
 * This function retrieves the website statistics for the current day and the specified period.
 * It returns an array containing the statistics for the current day and the specified period.
 *
 * @param int $period_days The number of days to retrieve the statistics for.
 * @return array The website statistics for the current day and the specified period.
 */
function bbcs_get_statistics($period_days = 7)
{
    global $wpdb;
    global $BBCS;
    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';

    $gmt_offset = !empty($BBCS['admin_gmt_offset']) ? $BBCS['admin_gmt_offset'] : 0;
    $current_date = new DateTime("now", new DateTimeZone('UTC'));
    $current_date->modify(sprintf("%+d hours", $gmt_offset));

    $today_start = $current_date->format('Y-m-d 00:00:00');
    $today_end = $current_date->format('Y-m-d 23:59:59');

    $period_start = $current_date->modify("-" . ($period_days - 1) . " days")->format('Y-m-d 00:00:00');

    $sql_template = "
    SELECT
    COUNT(*) as hits,
    COUNT(DISTINCT NULLIF(ip, '')) as uniques,
    COUNT(DISTINCT CASE WHEN IFNULL(device, 'NA') = 'pc' AND ip != '' THEN ip END) as pc,
    COUNT(DISTINCT CASE WHEN IFNULL(device, 'NA') = 'box' AND ip != '' THEN ip END) as box,
    COUNT(DISTINCT CASE WHEN IFNULL(device, 'NA') = 'phone' AND ip != '' THEN ip END) as phone,
    COUNT(DISTINCT CASE WHEN IFNULL(device, 'NA') = 'tablet' AND ip != '' THEN ip END) as tablet,
    COUNT(DISTINCT CASE WHEN IFNULL(device, 'NA') = 'tv' AND ip != '' THEN ip END) as tv,
    COUNT(DISTINCT CASE WHEN hit = 1 AND ip != '' THEN ip END) as hit_hosts,
    SUM(NULLIF(hit, 0)) as hit_count,
    GROUP_CONCAT(DISTINCT CONCAT(IFNULL(NULLIF(browser, ''), 'NA'), ':', ip) SEPARATOR ',') as browsers,
    GROUP_CONCAT(DISTINCT CONCAT(IFNULL(NULLIF(os, ''), 'NA'), ':', ip) SEPARATOR ',') as operating_systems,
    GROUP_CONCAT(DISTINCT CONCAT(IFNULL(NULLIF(wbot, ''), 'NA'), ':', ip) SEPARATOR ',') as white_bots
    FROM $table_name_hits
    WHERE date BETWEEN UNIX_TIMESTAMP(%s) - %f * 3600 AND UNIX_TIMESTAMP(%s) - %f * 3600
    AND page NOT LIKE '%/wp-admin/%'
    AND page NOT LIKE '%/wp-content/%'
    AND page NOT LIKE '%/wp-includes/%'
    AND page NOT LIKE '%/favicon.ico%';
    ";


    $today_sql = $wpdb->prepare($sql_template, $today_start, $gmt_offset, $today_end, $gmt_offset);
    $period_sql = $wpdb->prepare($sql_template, $period_start, $gmt_offset, $today_end, $gmt_offset);

    $today_results = $wpdb->get_row($today_sql, ARRAY_A);
    $period_results = $wpdb->get_row($period_sql, ARRAY_A);

    // Process browsers, operating systems, and white bots
    foreach (['today_results', 'period_results'] as $result_set) {
        $browsers = [];
        $operating_systems = [];
        $white_bots = [];

        $browser_data = explode(',', ${$result_set}['browsers']);
        $os_data = explode(',', ${$result_set}['operating_systems']);
        $bot_data = explode(',', ${$result_set}['white_bots']);

        if (!empty($browser_data)) {
            foreach ($browser_data as $item) {
                $data = explode(':', $item);
                if (count($data) === 2) {
                    list($browser, $ip) = $data;
                    if (!empty($browser) && !empty($ip)) {
                        $browsers[$browser][$ip] = true;
                    }
                }
            }
        } else {
            $browsers = [];
        }

        if (!empty($os_data)) {
            foreach ($os_data as $item) {
                $data = explode(':', $item);
                if (count($data) === 2) {
                    list($os, $ip) = $data;
                    if (!empty($os) && !empty($ip)) {
                        $operating_systems[$os][$ip] = true;
                    }
                }
            }
        } else {
            $operating_systems = [];
        }

        if (!empty($bot_data)) {
            foreach ($bot_data as $item) {
                $data = explode(':', $item);
                if (count($data) === 2) {
                    list($bot, $ip) = $data;
                    if (!empty($bot) && !empty($ip)) {
                        $white_bots[$bot][$ip] = true;
                    }
                }
            }
        } else {
            $white_bots = [];
        }

        ${$result_set}['browsers'] = array_map('count', $browsers);
        ${$result_set}['operating_systems'] = array_map('count', $operating_systems);
        ${$result_set}['white_bots'] = array_map('count', $white_bots);
    }


    $BBCS['counters'] = [
        'today' => $today_results,
        'period' => $period_results
    ];
}

/**
 * Displays a chart of the website statistics.
 *
 * This function is responsible for rendering and displaying a chart that shows the website statistics.
 * It retrieves the necessary data from the database and uses a chart library to generate the chart.
 *
 * @param array $atts The attributes passed to the shortcode.
 * @return string The HTML markup containing the chart.
 */
function bbcs_display_statistics_chart($atts)
{
    global $BBCS;

    // Define default attributes
    $defaults = array(
        'type' => 'pie', // 'pie' or 'donut'
        'period' => 'today', // 'today' or 'period'
        'data' => 'ip_hits_hosts', // What data to display
        'width' => 'auto',
        'height' => 'auto'
    );

    $atts = shortcode_atts($defaults, $atts, 'bbcs_statistics_chart');

    // Ensure we have data
    if (!isset($BBCS['counters'][$atts['period']])) {
        return "No data available for the specified period.";
    }

    $data = $BBCS['counters'][$atts['period']];

    // Prepare chart data based on the 'data' parameter
    $chart_data = array();
    switch ($atts['data']) {
        case 'ip_hits_hosts':
            $chart_data = array(
                array('Category', 'Value'),
                array('Hits', (int)$data['hits']),
                array('Unique IPs', (int)$data['uniques'])
            );
            $title = 'IP Hits & Hosts';
            break;
        case 'cookie_hits_hosts':
            $chart_data = array(
                array('Category', 'Value'),
                array('Hits', (int)$data['hit_count']),
                array('Unique Hosts', (int)$data['hit_hosts'])
            );
            break;
        case 'device_types':
            $chart_data = array(
                array('Device', 'Count'),
                array('PC', (int)$data['pc']),
                array('Box', (int)$data['box']),
                array('Phone', (int)$data['phone']),
                array('Tablet', (int)$data['tablet']),
                array('TV', (int)$data['tv'])
            );
            $title = 'Device Types';
            break;
        case 'browsers':
            $chart_data = array(array('Browser', 'Count'));
            foreach ($data['browsers'] as $browser => $count) {
                $chart_data[] = array($browser, (int)$count);
            }
            $title = 'Browsers';
            break;
        case 'operating_systems':
            $chart_data = array(array('OS', 'Count'));
            foreach ($data['operating_systems'] as $os => $count) {
                $chart_data[] = array($os, (int)$count);
            }
            $title = 'Operating Systems';
            break;
        default:
            return "Invalid data parameter.";
    }

    // Prepare JavaScript for the chart
    ob_start();
?>
    <div class="bbcs-statistics-chart-title-div"><span class="bbcs-statistics-chart-title"><?php echo isset($title) ? $title : ''; ?></span></div>
    <div id="bbcs_statistics_chart_<?php echo esc_attr($atts['data']); ?>" style="width: <?php echo esc_attr($atts['width']); ?>; height: <?php echo esc_attr($atts['height']); ?>;"></div>
    <script type="text/javascript">
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable(<?php echo json_encode($chart_data); ?>);

            var options = {
                title: '<?php echo isset($title) ? $title : ''; ?>',
                backgroundColor: 'transparent',
                legend: 'none',
                chartArea: {
                    width: '90%',
                    height: '90%'
                },
                <?php if ($atts['type'] === 'donut'): ?>
                    pieHole: 0.4,
                <?php endif; ?>
            };

            var chart = new google.visualization.<?php echo ($atts['type'] === 'donut' ? 'PieChart' : 'PieChart'); ?>(
                document.getElementById('bbcs_statistics_chart_<?php echo esc_attr($atts['data']); ?>')
            );
            chart.draw(data, options);
        }
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('bbcs_statistics_chart', 'bbcs_display_statistics_chart');


function botblocker_rules_statistics($atts)
{
    global $wpdb;

    // Параметры шорткода
    $atts = shortcode_atts(array(
        'show_chart' => 'yes',
        'chart_height' => '200'
    ), $atts);

    $prefix = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX;

    // IPv4 statistics
    $ipv4_total = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}ipv4rules");
    $ipv4_blocks = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}ipv4rules WHERE rule = 'block'");
    $ipv4_allows = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}ipv4rules WHERE rule = 'allow'");

    // IPv6 statistics
    $ipv6_total = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}ipv6rules");
    $ipv6_blocks = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}ipv6rules WHERE rule = 'block'");
    $ipv6_allows = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}ipv6rules WHERE rule = 'allow'");

    // Path statistics
    $paths_total = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}path");
    $paths_allowed = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}path WHERE rule = 'allow'");
    $paths_blocked = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}path WHERE rule = 'block'");

    // White bots and search engines statistics
    $white_bots_total = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}se");
    $white_bots_allowed = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}se WHERE rule = 'allow' AND disable = 0");

    // General rules statistics
    $rules_total = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}rules");
    $rules_blocks = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}rules WHERE rule = 'block'");
    $rules_allows = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}rules WHERE rule = 'allow'");

    // Additional useful information
    $active_rules = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}rules WHERE disable = 0");
    $expired_rules = $wpdb->get_var("SELECT COUNT(*) FROM {$prefix}rules WHERE expires < " . time());

    $output = '';

    // Add Google Charts if enabled
    if ($atts['show_chart'] === 'yes') {
        $output .= "
        <script type='text/javascript'>
          google.charts.load('current', {'packages':['corechart']});
          google.charts.setOnLoadCallback(drawChart);

          function drawChart() {
            var data = google.visualization.arrayToDataTable([
              ['Category', 'Blocked', 'Allowed'],
              ['IPv4', {$ipv4_blocks}, {$ipv4_allows}],
              ['IPv6', {$ipv6_blocks}, {$ipv6_allows}],
              ['Paths', {$paths_blocked}, {$paths_allowed}],
              ['Rules', {$rules_blocks}, {$rules_allows}]
            ]);

            var options = {
              title: 'BotBlocker Rules Statistics',
              chartArea: {
                width: '90%',
                height: '90%'
             },
              isStacked: true,
              height: {$atts['chart_height']},
              hAxis: {
                title: 'Count',
                minValue: 0,
              },
              vAxis: {
                title: 'Category'
              },
              colors: ['#FF0000', '#0000FF'],
                legend: { position: 'top', maxLines: 3 },
                bar: { groupWidth: '75%' },
            };

            var chart = new google.visualization.BarChart(document.getElementById('botblocker_rules_stats_chart'));
            chart.draw(data, options);
          }
        </script>
        <div id='botblocker_rules_stats_chart'></div>";
    }

    $output .= "<h3 class='bbcs-rule-stat-h'>IP Addresses</h3>";
    $output .= "<span class='bbcs-rule-stat-s'>Total IPv4 rules: {$ipv4_total} (Blocked: {$ipv4_blocks}, Allowed: {$ipv4_allows})</span>";
    $output .= "<span class='bbcs-rule-stat-s'>Total IPv6 rules: {$ipv6_total} (Blocked: {$ipv6_blocks}, Allowed: {$ipv6_allows})</span>";

    $output .= "<h3 class='bbcs-rule-stat-h'>WordPress Paths</h3>";
    $output .= "<span class='bbcs-rule-stat-s'>Total paths: {$paths_total} (Allowed: {$paths_allowed}, Blocked: {$paths_blocked})</span>";

    $output .= "<h3 class='bbcs-rule-stat-h'>White Bots and Search Engines</h3>";
    $output .= "<span class='bbcs-rule-stat-s'>Total white bots: {$white_bots_total} (Active and allowed: {$white_bots_allowed})</span>";

    $output .= "<h3 class='bbcs-rule-stat-h'>General Rules</h3>";
    $output .= "<span class='bbcs-rule-stat-s'>Total rules: {$rules_total} (Blocked: {$rules_blocks}, Allowed: {$rules_allows})</span>";

    $output .= "<h3 class='bbcs-rule-stat-h'>Additional Information</h3>";
    $output .= "<span class='bbcs-rule-stat-s'>Active rules: {$active_rules}</span>";
    $output .= "<span class='bbcs-rule-stat-s'>Expired rules: {$expired_rules}</span>";

    return $output;
}
add_shortcode('botblocker_rules_stats', 'botblocker_rules_statistics');







/*
Deprecated functions


function bbcs_display_visitors_map($atts) {
    global $wpdb;
    $table_name_hits = $wpdb->prefix . BOTBLOCKER_TABLE_PREFIX . 'hits';

    // Получаем количество дней из атрибутов шорткода или используем значение по умолчанию
    $atts = shortcode_atts(array(
        'days' => 30, // По умолчанию показываем за 30 дней
    ), $atts);
    $days = min(max((int)$atts['days'], 1), 365); // Ограничиваем от 1 до 365 дней

    $timezone = wp_timezone();
    $current_date = new DateTime("now", $timezone);
    $end_date = $current_date->format('Y-m-d H:i:s');
    $start_date = $current_date->modify("-{$days} days")->format('Y-m-d H:i:s');

    // SQL запрос для получения уникальных посетителей по странам
    $sql = $wpdb->prepare(
        "SELECT country, COUNT(DISTINCT ip) AS unique_visitors
         FROM $table_name_hits
         WHERE date BETWEEN UNIX_TIMESTAMP(%s) AND UNIX_TIMESTAMP(%s)
         AND page NOT LIKE '%/wp-admin%'
         AND page NOT LIKE '%wp-content%'
         AND page NOT LIKE '%wp-includes%'
         AND country != ''
         GROUP BY country
         ORDER BY unique_visitors DESC",
        $start_date,
        $end_date
    );

    $results = $wpdb->get_results($sql);

    // Подготовка данных для карты
    $chart_data = array();
    foreach ($results as $row) {
        $chart_data[] = array($row->country, (int)$row->unique_visitors);
    }

    // Вывод HTML и JavaScript для карты
    ob_start();
    ?>
    <div id="bbcs_visitors_map" style="width: 100%; height: 500px;"></div>
	<script type="text/javascript">
    google.charts.load('current', {'packages':['geochart']});
    google.charts.setOnLoadCallback(drawRegionsMap);

    function drawRegionsMap() {
        var data = google.visualization.arrayToDataTable([
            ['Country', 'Unique Visitors'],
            <?php
            foreach ($chart_data as $row) {
                echo "['" . esc_js($row[0]) . "', " . $row[1] . "],\n";
            }
            ?>
        ]);

        var options = {
            colorAxis: {colors: ['#e3f2fd', '#0d47a1']}, 
            backgroundColor: '#eeeeee',
            datalessRegionColor: '#ffffff',
            defaultColor: '#f5f5f5',
            //enableRegionInteractivity: false,  // Отключение интерактивности регионов
            resolution: 'countries',  // Ограничение отображения только странами
        };

        var chart = new google.visualization.GeoChart(document.getElementById('bbcs_visitors_map'));

        chart.draw(data, options);
    }
</script>
    <?php
    return ob_get_clean();
}
add_shortcode('bbcs_visitors_map', 'bbcs_display_visitors_map');

*/