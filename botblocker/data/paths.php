<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

return [
    'bbcs_path' => [
        'wp-cron.php' => 'allow',
        'wp-admin/admin-ajax.php' => 'allow',
        'wp-admin/post.php' => 'allow',
        '?wc-ajax=' => 'allow'
    ],
];