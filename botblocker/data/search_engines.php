<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

return [
    'bbcs_rule' => [
        'Googlebot' => 'allow',
        'yandex.com' => 'allow',
        'Mail.RU_Bot' => 'allow',
        'bingbot' => 'allow',
        'msnbot' => 'allow',
        'Google-Site-Verification' => 'allow',
    ],
    
    'bbcs_se' => [
        'Googlebot' => ['.googlebot.com'],
        'yandex.com' => ['.yandex.ru', '.yandex.net', '.yandex.com'],
        'Mail.RU_Bot' => ['.mail.ru', '.smailru.net'],
        'bingbot' => ['search.msn.com'],
        'msnbot' => ['search.msn.com'],
        'Google-Site-Verification' => ['.googlebot.com', '.google.com'],
    ]
];
