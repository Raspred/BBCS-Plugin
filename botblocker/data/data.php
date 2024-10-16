<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

return [
    'bbcs_good_bots' => [
        'Googlebot'             => ['.googlebot.com'], // GoogleBot (main indexer)
        'yandex.com'            => ['yandex.ru', 'yandex.net', 'yandex.com'], // Все боты Yandex
        'Mail.RU_Bot'           => ['mail.ru', 'smailru.net'], // Все боты Mail.RU Indexers
        'bingbot'               => ['search.msn.com'], // Bing.com indexer
        'msnbot'                => ['search.msn.com'], // Дополнительный индексатор Bing.com
        'Google-Site-Verification' => ['googlebot.com', 'google.com'], // Проверка Google Search Console
        'vkShare'               => ['.vk.com', '.vkontakte.ru', '.go.mail.ru', '.userapi.ru'], // ВКонтакте
        'facebookexternalhit'   => ['.fbsv.net', '66.220.149.', '31.13.', '2a03:2880:'], // Facebook
        'OdklBot'               => ['.odnoklassniki.ru'], // Одноклассники
        'MailRuConnect'         => ['.smailru.net'], // Мой мир (mail.ru)
        'TelegramBot'           => ['149.154.161'], // Telegram
        'Twitterbot'            => ['.twttr.com', '199.16.15'], // Twitter
        'googleweblight'        => ['google.com'], // Googleweblight
        'BingPreview'           => ['search.msn.com'], // Проверка Bing Mobile Page Adaptation
        'uptimerobot'           => ['uptimerobot.com'], // Uptime Robot
        'pingdom'               => ['pingdom.com'], // Pingdom
        'HostTracker'           => ['.'], // HostTracker
        'Yahoo! Slurp'          => ['.yahoo.net'], // Yahoo Bots
        'SeznamBot'             => ['.seznam.cz'], // Seznam.cz
        'Pinterestbot'          => ['.pinterest.com'], // Pinterest
        'Mediapartners'         => ['googlebot.com', 'google.com'], // AdSense bot
        'AdsBot-Google'         => ['google.com'], // Adwords bot
        'Google-Adwords'        => ['google.com'], // Adwords bot (Google-Adwords-Instant и Google-AdWords-Express)
        'Google-Ads'            => ['google.com'], // Adwords bot (Google-Ads-Creatives-Assistant)
        'Google Favicon'        => ['google.com'], // Google Favicon
        'FeedFetcher-Google'    => ['google.com'], // Google News
    ],
];
