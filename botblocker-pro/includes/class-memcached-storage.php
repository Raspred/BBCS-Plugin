<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER_PRO')) {
	exit;
}

/*

$storage = new MemcachedStorage(
    '127.0.0.1',  // Хост Memcached
    11211,        // Порт Memcached
    'bbcs_req_'   // Префикс для ключей
);

try {
    $storage->connect();
    if ($storage->isAvailable()) {
        echo "Memcached доступен.\n";
    } else {
        echo "Memcached не доступен.\n";
    }

  $storage->set('test_cid', ['bbcs_user' => 'john_doe', 'bbcs_check' => 'click'], 600);

    $data = $storage->get('test_cid');
    print_r($data);

    $storage->delete('test_cid');

    $storage->flushByPrefix();
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage();
}

*/

class MemcachedStorage
{
    private $memcached;
    private $host;
    private $port;
    private $prefix;

    /**
     * Конструктор для инициализации параметров подключения к Memcached.
     *
     * @param string $host Хост Memcached-сервера.
     * @param int $port Порт Memcached-сервера.
     * @param string $prefix Префикс для ключей Memcached.
     */
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 11211,
        string $prefix = 'bbcs_req_'
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->prefix = $prefix;
        $this->memcached = new Memcached();
    }

    /**
     * Подключение к Memcached.
     *
     * @return bool Возвращает true, если подключение успешно.
     * @throws Exception Если не удалось подключиться.
     */
    public function connect(): bool
    {
        if (!$this->memcached->addServer($this->host, $this->port)) {
            throw new Exception('Не удалось подключиться к Memcached серверу.');
        }
        return true;
    }

    /**
     * Проверка доступности Memcached-сервера.
     *
     * @return bool Возвращает true, если сервер доступен.
     */
    public function isAvailable(): bool
    {
        return $this->memcached->getStats() !== false;
    }

    /**
     * Сохранение данных в Memcached с указанным TTL.
     *
     * @param string $cid Идентификатор клика для генерации ключа.
     * @param array $data Данные для хранения в Memcached.
     * @param int $ttl Время жизни записи в секундах (по умолчанию 3600).
     * @return bool Возвращает true, если запись успешно сохранена.
     */
    public function set(string $cid, array $data, int $ttl = 3600): bool
    {
        $key = $this->generateKey($cid);
        return $this->memcached->set($key, $data, $ttl);
    }

    /**
     * Получение данных из Memcached по ключу.
     *
     * @param string $cid Идентификатор клика для генерации ключа.
     * @return array|null Возвращает массив данных или null, если ключ не найден.
     */
    public function get(string $cid): ?array
    {
        $key = $this->generateKey($cid);
        $data = $this->memcached->get($key);

        return $data !== false ? $data : null;
    }

    /**
     * Удаление данных из Memcached по ключу.
     *
     * @param string $cid Идентификатор клика для генерации ключа.
     * @return bool Возвращает true, если ключ успешно удален.
     */
    public function delete(string $cid): bool
    {
        $key = $this->generateKey($cid);
        return $this->memcached->delete($key);
    }

    /**
     * Очистка всех данных по префиксу bbcs_req_.
     *
     * @return void
     */
    public function flushByPrefix(): void
    {
        $keys = $this->getKeysByPrefix($this->prefix);
        if (!empty($keys)) {
            foreach ($keys as $key) {
                $this->memcached->delete($key);
            }
        }
    }

    /**
     * Генерация уникального ключа для хранения в Memcached.
     *
     * @param string $cid Идентификатор клика.
     * @return string Уникальный ключ для хранения.
     */
    private function generateKey(string $cid): string
    {
        return $this->prefix . md5($cid);
    }

    /**
     * Получение всех ключей, соответствующих префиксу.
     *
     * @param string $prefix Префикс ключей.
     * @return array Массив ключей, соответствующих указанному префиксу.
     */
    private function getKeysByPrefix(string $prefix): array
    {
        $keys = [];
        $allKeys = $this->memcached->getAllKeys();
        if ($allKeys) {
            foreach ($allKeys as $key) {
                if (strpos($key, $prefix) === 0) {
                    $keys[] = $key;
                }
            }
        }
        return $keys;
    }
}