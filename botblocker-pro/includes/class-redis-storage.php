<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER_PRO')) {
	exit;
}

/*

$storage = new RedisStorage(
    '127.0.0.1',  // Хост Redis
    6379,         // Порт Redis
    '',           // Пароль Redis
    0,            // База данных Redis
    'bbcs_req_'   // Префикс для ключей
);

try {
    $storage->connect();
    if ($storage->isAvailable()) {
        echo "Redis доступен.\n";
    } else {
        echo "Redis не доступен.\n";
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

class RedisStorage
{
    private $redis;
    private $host;
    private $port;
    private $password;
    private $database;
    private $prefix;

    /**
     * Конструктор для инициализации параметров подключения к Redis.
     *
     * @param string $host Хост Redis-сервера.
     * @param int $port Порт Redis-сервера.
     * @param string $password Пароль для подключения.
     * @param int $database Номер базы данных Redis.
     * @param string $prefix Префикс для ключей Redis.
     */
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        string $password = '',
        int $database = 0,
        string $prefix = 'bbcs_req_'
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->database = $database;
        $this->prefix = $prefix;
    }

    /**
     * Подключение к Redis.
     *
     * @return bool Возвращает true, если подключение успешно.
     * @throws Exception Если не удалось подключиться.
     */
    public function connect(): bool
    {
        $this->redis = new Redis();
        try {
            $this->redis->connect($this->host, $this->port);

            if (!empty($this->password)) {
                if (!$this->redis->auth($this->password)) {
                    throw new Exception('Ошибка аутентификации к Redis-серверу.');
                }
            }

            $this->redis->select($this->database);
        } catch (Exception $e) {
            throw new Exception('Не удалось подключиться к Redis: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Проверка доступности Redis-сервера.
     *
     * @return bool Возвращает true, если сервер доступен.
     */
    public function isAvailable(): bool
    {
        try {
            return $this->redis->ping() === '+PONG';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Сохранение данных в Redis с указанным TTL.
     *
     * @param string $cid Идентификатор клика для генерации ключа.
     * @param array $data Данные для хранения в Redis.
     * @param int $ttl Время жизни записи в секундах (по умолчанию 3600).
     * @return bool Возвращает true, если запись успешно сохранена.
     */
    public function set(string $cid, array $data, int $ttl = 3600): bool
    {
        $key = $this->generateKey($cid);
        $jsonData = json_encode($data);

        if ($this->redis->set($key, $jsonData)) {
            return $this->redis->expire($key, $ttl);
        }

        return false;
    }

    /**
     * Получение данных из Redis по ключу.
     *
     * @param string $cid Идентификатор клика для генерации ключа.
     * @return array|null Возвращает массив данных или null, если ключ не найден.
     */
    public function get(string $cid): ?array
    {
        $key = $this->generateKey($cid);
        $jsonData = $this->redis->get($key);

        return $jsonData ? json_decode($jsonData, true) : null;
    }

    /**
     * Удаление данных из Redis по ключу.
     *
     * @param string $cid Идентификатор клика для генерации ключа.
     * @return bool Возвращает true, если ключ успешно удален.
     */
    public function delete(string $cid): bool
    {
        $key = $this->generateKey($cid);
        return $this->redis->del($key) > 0;
    }

    /**
     * Очистка всех данных по префиксу bbcs_req_.
     *
     * @return void
     */
    public function flushByPrefix(): void
    {
        $pattern = $this->prefix . '*';
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }

    /**
     * Генерация уникального ключа для хранения в Redis.
     *
     * @param string $cid Идентификатор клика.
     * @return string Уникальный ключ для хранения.
     */
    private function generateKey(string $cid): string
    {
        return $this->prefix . md5($cid);
    }
}