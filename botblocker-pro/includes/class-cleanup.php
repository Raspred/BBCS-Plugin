<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER_PRO')) {
	exit;
}

/*

$cleanup = new WP_Cleanup();
$cleanup->clearAllCaches();
$cleanup->clearAllLogs();
$cleanup->clearDatabaseLogs();

*/

class WP_Cleanup
{
    /**
     * Очистка всех известных кэшей в WordPress.
     *
     * @return void
     */
    public function clearAllCaches(): void
    {
        // Очистка Object Cache (например, Redis, Memcached)
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            echo "Object Cache очищен.\n";
        } else {
            echo "Object Cache не используется или не найден.\n";
        }

        // Очистка всех транзиентов
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_site\_transient\_%'");
        echo "Все транзиенты очищены.\n";

        // Дополнительная очистка кешей популярных плагинов
        if (class_exists('W3TC\Dispatcher')) {
            \W3TC\Dispatcher::flush_all();
            echo "Кеш W3 Total Cache очищен.\n";
        }

        if (class_exists('WP_Super_Cache')) {
            wp_cache_clear_cache();
            echo "Кеш WP Super Cache очищен.\n";
        }

        if (class_exists('autoptimizeCache')) {
            autoptimizeCache::clearall();
            echo "Кеш Autoptimize очищен.\n";
        }
    }

    /**
     * Очистка всех известных логов в WordPress.
     *
     * @return void
     */
    public function clearAllLogs(): void
    {
        // Путь к стандартному лог-файлу WordPress
        $debugLogPath = WP_CONTENT_DIR . '/debug.log';
        $this->clearLogFile($debugLogPath);

        // Очистка пользовательских логов в папке wp-content/logs (если такая папка существует)
        $customLogDir = WP_CONTENT_DIR . '/logs';
        $this->clearLogFilesInDirectory($customLogDir);

        // Очистка логов плагинов, если они определены
        $pluginLogs = [
            WP_CONTENT_DIR . '/wflogs/', // Папка логов Wordfence
            WP_CONTENT_DIR . '/uploads/wp-rocket-config/', // Папка логов WP Rocket
        ];

        foreach ($pluginLogs as $logDir) {
            $this->clearLogFilesInDirectory($logDir);
        }
    }

    /**
     * Очистка указанного лог-файла.
     *
     * @param string $filePath Путь к файлу лога.
     * @return void
     */
    private function clearLogFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            file_put_contents($filePath, ''); // Очистка содержимого файла
            echo "Лог файл очищен: $filePath\n";
        } else {
            echo "Лог файл не найден: $filePath\n";
        }
    }

    /**
     * Очистка всех лог-файлов в указанной директории.
     *
     * @param string $directory Путь к директории с логами.
     * @return void
     */
    private function clearLogFilesInDirectory(string $directory): void
    {
        if (is_dir($directory)) {
            $files = glob($directory . '/*.log');
            foreach ($files as $file) {
                $this->clearLogFile($file);
            }
            echo "Все лог-файлы в директории $directory очищены.\n";
        } else {
            echo "Директория логов не найдена: $directory\n";
        }
    }

    /**
     * Полная очистка базы данных WordPress от логов и временных данных.
     *
     * @return void
     */
    public function clearDatabaseLogs(): void
    {
        global $wpdb;

        // Удаление логов плагинов, если они известны
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'plugin_log_%'");
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_transient_%'");
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE '_site_transient_%'");

        echo "База данных очищена от временных данных и логов.\n";
    }
}