<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

function bbcs_loadHeadersArray()
{
    return array(
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 Switch Proxy',
        307 => '307 Temporary Redirect',
        308 => '308 Permanent Redirect',
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        410 => '410 Gone',
        429 => '429 Too Many Requests',
        451 => '451 Unavailable For Legal Reasons',
        500 => '500 Internal Server Error',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Time-out',
        505 => '505 HTTP Version Not Supported',
        507 => '507 Insufficient Storage',
        508 => '508 Loop Detected',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required',
        520 => '520 Unknown Error',
        521 => '521 Web Server Is Down',
        522 => '522 Connection Timed Out',
        523 => '523 Origin Is Unreachable',
        524 => '524 A Timeout Occurred',
        525 => '525 SSL Handshake Failed',
        526 => '526 Invalid SSL Certificate',
        527 => '527 Railgun Error',
        530 => '530 Origin DNS Error',
    );
}


/**
 * Check if popular plugins are installed in WordPress
 *
 * @return array List of installed plugins
 */
function bbcs_checkPopularPlugins()
{
    $plugins = [
        'Akismet' => 'akismet/akismet.php',
        'Yoast SEO' => 'wordpress-seo/wp-seo.php',
        'Jetpack' => 'jetpack/jetpack.php',
        'WooCommerce' => 'woocommerce/woocommerce.php',
        'Contact Form 7' => 'contact-form-7/wp-contact-form-7.php',
        'Elementor' => 'elementor/elementor.php',
        'WPForms' => 'wpforms/wpforms.php',
        'UpdraftPlus' => 'updraftplus/updraftplus.php',
        'Wordfence' => 'wordfence/wordfence.php',
        'All in One SEO Pack' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
        'WP Super Cache' => 'wp-super-cache/wp-cache.php',
        'W3 Total Cache' => 'w3-total-cache/w3-total-cache.php',
        'Smush' => 'wp-smushit/wp-smush.php',
        'Redirection' => 'redirection/redirection.php',
        'Broken Link Checker' => 'broken-link-checker/broken-link-checker.php',
        'Contact Form by WPForms' => 'wpforms-lite/wpforms.php',
        'Ninja Forms' => 'ninja-forms/ninja-forms.php',
        'Mailchimp for WordPress' => 'mailchimp-for-wp/mailchimp-for-wp.php',
        'Advanced Custom Fields' => 'advanced-custom-fields/acf.php',
        'Yoast Duplicate Post' => 'duplicate-post/duplicate-post.php',
        'Classic Editor' => 'classic-editor/classic-editor.php',
        'Google Analytics for WordPress' => 'google-analytics-for-wordpress/googleanalytics.php',
        'WP Mail SMTP' => 'wp-mail-smtp/wp_mail_smtp.php',
        'TablePress' => 'tablepress/tablepress.php',
        'Really Simple SSL' => 'really-simple-ssl/really-simple-ssl.php',
        'Cookie Notice' => 'cookie-notice/cookie-notice.php',
        'WP-PageNavi' => 'wp-pagenavi/wp-pagenavi.php',
        'WP Rocket' => 'wp-rocket/wp-rocket.php',
        'WPML' => 'sitepress-multilingual-cms/sitepress.php',
        'NextGEN Gallery' => 'nextgen-gallery/nggallery.php',
        'WP User Avatar' => 'wp-user-avatar/wp-user-avatar.php',
        'WP-PostViews' => 'wp-postviews/wp-postviews.php',
        'WP Fastest Cache' => 'wp-fastest-cache/wpFastestCache.php',
        'Jetpack by WordPress.com' => 'jetpack/jetpack.php',
        'All In One WP Security & Firewall' => 'all-in-one-wp-security-and-firewall/wp-security.php',
        'WP Statistics' => 'wp-statistics/wp-statistics.php',
        'WP Google Maps' => 'wp-google-maps/wpGoogleMaps.php',
        'WP Maintenance Mode' => 'wp-maintenance-mode/wp-maintenance-mode.php',
        'WP File Manager' => 'wp-file-manager/wp-file-manager.php',
        'WP-Optimize' => 'wp-optimize/wp-optimize.php',
        'WP Migrate DB' => 'wp-migrate-db/wp-migrate-db.php',
        'WP Content Copy Protection' => 'wp-content-copy-protection/wp-content-copy-protection.php',
        'WP RSS Aggregator' => 'wp-rss-aggregator/wp-rss-aggregator.php',
        'WP Live Chat Support' => 'wp-live-chat-support/wp-live-chat-support.php',
        'WP Google Fonts' => 'wp-google-fonts/wp-google-fonts.php',
        'WP-PostRatings' => 'wp-postratings/wp-postratings.php',
    ];

    $installedPlugins = [];

    foreach ($plugins as $name => $pluginFile) {
        if (is_plugin_active($pluginFile)) {
            $installedPlugins[] = $name;
        }
    }

    return $installedPlugins;
}


/**
 * Get the browser name from the user agent
 *
 * @param string $userAgent The user agent
 * @return string The browser name
 */
function bbcs_getBrowserType($userAgent)
{
    $browsers = [
        'Opera' => 'Opera',
        'OPR' => 'Opera',
        'Edge' => 'Microsoft Edge',
        'Edg' => 'Microsoft Edge',
        'Chrome' => 'Google Chrome',
        'Safari' => 'Safari',
        'Firefox' => 'Mozilla Firefox',
        'MSIE' => 'Internet Explorer',
        'Trident/7.0' => 'Internet Explorer 11',
        'Vivaldi' => 'Vivaldi',
        'Brave' => 'Brave',
        'UCBrowser' => 'UC Browser',
        'YaBrowser' => 'Yandex Browser',
        'SamsungBrowser' => 'Samsung Internet',
        'Silk' => 'Amazon Silk',
        'Maxthon' => 'Maxthon',
        'Avant Browser' => 'Avant Browser',
        'Seamonkey' => 'SeaMonkey',
        'Konqueror' => 'Konqueror',
        'Falkon' => 'Falkon',
        'Webkit' => 'Webkit-based browser',
        'Gecko' => 'Gecko-based browser',
        'KHTML' => 'KHTML-based browser',
        'NetFront' => 'NetFront',
        'iCab' => 'iCab',
        'OmniWeb' => 'OmniWeb',
        'Lynx' => 'Lynx',
        'Links' => 'Links',
        'ELinks' => 'ELinks',
        'BrowseX' => 'BrowseX',
        'Epiphany' => 'Epiphany',
        'K-Meleon' => 'K-Meleon',
        'Midori' => 'Midori',
        'QupZilla' => 'QupZilla',
        'Otter' => 'Otter Browser',
        'Dooble' => 'Dooble',
        'Pale Moon' => 'Pale Moon',
        'Basilisk' => 'Basilisk',
        'Waterfox' => 'Waterfox',
        'Comodo Dragon' => 'Comodo Dragon',
        'Sleipnir' => 'Sleipnir',
        'Lunascape' => 'Lunascape',
        'QQ' => 'QQ Browser',
        'Sogou' => 'Sogou Explorer',
        'Chromium' => 'Chromium'
    ];

    foreach ($browsers as $key => $value) {
        if (stripos($userAgent, $key) !== false) {
            return $value;
        }
    }

    return 'Unknown Browser';
}

/**
 * Get the operating system from the user agent
 *
 * @param string $userAgent The user agent
 * @return string The operating system
 */
function bbcs_getOSType($userAgent)
{
    $osArray = [
        '/windows nt 10/i'      => 'Windows 10',
        '/windows nt 6.3/i'     => 'Windows 8.1',
        '/windows nt 6.2/i'     => 'Windows 8',
        '/windows nt 6.1/i'     => 'Windows 7',
        '/windows nt 6.0/i'     => 'Windows Vista',
        '/windows nt 5.2/i'     => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i'     => 'Windows XP',
        '/windows xp/i'         => 'Windows XP',
        '/windows nt 5.0/i'     => 'Windows 2000',
        '/windows me/i'         => 'Windows ME',
        '/win98/i'              => 'Windows 98',
        '/win95/i'              => 'Windows 95',
        '/win16/i'              => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i'        => 'Mac OS 9',
        '/ubuntu/i'             => 'Ubuntu',
        '/iphone/i'             => 'iPhone',
        '/ipod/i'               => 'iPod',
        '/ipad/i'               => 'iPad',
        '/android/i'            => 'Android',
        '/blackberry/i'         => 'BlackBerry',
        '/webos/i'              => 'Mobile',
        '/windows phone/i'      => 'Windows Phone',
        '/cros/i'               => 'Chrome OS',
        '/sunos/i'              => 'Sun Solaris',
        '/beos/i'               => 'BeOS',
        '/freebsd/i'            => 'FreeBSD',
        '/openbsd/i'            => 'OpenBSD',
        '/netbsd/i'             => 'NetBSD',
        '/fedora/i'             => 'Fedora',
        '/centos/i'             => 'CentOS',
        '/redhat/i'             => 'Red Hat',
        '/debian/i'             => 'Debian',
        '/arch/i'               => 'Arch Linux',
        '/manjaro/i'            => 'Manjaro',
        '/gentoo/i'             => 'Gentoo',
        '/slackware/i'          => 'Slackware',
        '/mint/i'               => 'Linux Mint',
        '/elementary/i'         => 'elementary OS',
        '/opensuse/i'           => 'openSUSE',
        '/tizen/i'              => 'Tizen',
        '/sailfish/i'           => 'Sailfish OS',
        '/symbian/i'            => 'Symbian OS',
        '/xbox/i'               => 'Xbox',
        '/playstation/i'        => 'PlayStation',
        '/nintendo/i'           => 'Nintendo',
        '/linux/i'              => 'Linux'
    ];

    foreach ($osArray as $regex => $value) {
        if (preg_match($regex, $userAgent)) {
            return $value;
        }
    }

    return 'Unknown OS';
}

function bbcs_loadProxyHeaders()
{
    $proxy_headers = [ 
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_FORWARDED',
        'HTTP_VIA',
        'HTTP_TRUE_CLIENT_IP',
        'HTTP_CF_CONNECTING_IP',
        'HTTP_FASTLY_CLIENT_IP',
        'HTTP_X_PROXYUSER_IP',
        'X-Forwarded-For',
        'X-Real-IP',
        'Forwarded',
        'Via',
        'Client-IP',
        'True-Client-IP',
        'CF-Connecting-IP',
        'Fastly-Client-IP',
        'X-ProxyUser-IP',
    ];

    return $proxy_headers;
}

function bbcs_loadAnalyticsDomains()
{
    $analytics_domains = [
        'google-analytics.com',      // Google Analytics
        'analytics.google.com',      // Google Analytics Dashboard
        'doubleclick.net',           // Google Ad Manager 
        'googletagmanager.com',      // Google Tag Manager
        'facebook.com',              // Facebook Analytics и Pixel
        'facebook.net',              // Facebook Pixel
        'webvisor.com',              // Яндекс.Метрика Вебвизор
        'metrika.yandex.ru',         // Яндекс.Метрика
        'mc.yandex.ru',              // Яндекс.Метрика API
        'vk.com',                    // ВКонтакте аналитика и Pixel
        'ok.ru',                     // Одноклассники аналитика
        'linkedin.com',              // LinkedIn 
        'ads.linkedin.com',          // LinkedIn Ads
        'snapchat.com',              // Snapchat 
        'pixel.snapchat.com',        // Snapchat Pixel
        'twitter.com',               // Twitter 
        'quantserve.com',            // Quantcast
        'cloudflare.com',            // Cloudflare Web Analytics
        'hotjar.com',                // Hotjar 
        'matomo.org',                // Matomo (Piwik) 
        'mixpanel.com',              // Mixpanel 
        'clicky.com',                // Clicky 
        'heap.io',                   // Heap Analytics
        'segment.com',               // Segment 
        'kissmetrics.com',           // Kissmetrics
    ];

    return $analytics_domains;
}
