<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER_PRO')) {
	exit;
}

/*

$options = [
    'instanceId' => 'YOUR_INSTANCE_ID',
    'secretKey' => 'YOUR_SECRET_KEY'
];

$pusher = new PusherNotifier($options);

$response = $pusher->publishToInterests(['example-interest'], [
    'fcm' => [
        'notification' => [
            'title' => 'Привет!',
            'body' => 'Это тестовое сообщение.'
        ]
    ]
]);
var_dump($response);

*/

class PusherNotifier
{
    private $options;
    private $endpoint;

    /**
     * Конструктор класса для инициализации параметров Pusher.
     *
     * @param array $options Массив параметров, включающий:
     *  - 'instanceId' => (string) Идентификатор приложения.
     *  - 'secretKey' => (string) Секретный ключ.
     *  - 'endpoint' => (string) (опционально) Адрес сервера Pusher.
     */
    public function __construct(array $options)
    {
        if (empty($options['instanceId']) || empty($options['secretKey'])) {
            throw new Exception("Параметры 'instanceId' и 'secretKey' обязательны.");
        }

        $this->options = $options;
        $this->endpoint = !empty($options['endpoint']) 
            ? $options['endpoint'] 
            : "https://{$options['instanceId']}.pushnotifications.pusher.com";
    }

    /**
     * Отправка уведомления в указанные интересы.
     *
     * @param array $interests Список интересов.
     * @param array $publishRequest Данные для отправки.
     * @return mixed Результат отправки уведомления.
     * @throws Exception
     */
    public function publishToInterests(array $interests, array $publishRequest)
    {
        if (count($interests) === 0) {
            throw new Exception("Публикация должна быть направлена на как минимум один интерес.");
        }

        $publishRequest['interests'] = $interests;
        $path = "/publish_api/v1/instances/{$this->options['instanceId']}/publishes/interests";

        return $this->makeRequest('POST', $path, $publishRequest);
    }

    /**
     * Отправка уведомления указанным пользователям.
     *
     * @param array $userIds Список пользователей.
     * @param array $publishRequest Данные для отправки.
     * @return mixed Результат отправки уведомления.
     * @throws Exception
     */
    public function publishToUsers(array $userIds, array $publishRequest)
    {
        if (count($userIds) === 0) {
            throw new Exception("Публикация должна быть направлена на как минимум одного пользователя.");
        }

        $publishRequest['users'] = $userIds;
        $path = "/publish_api/v1/instances/{$this->options['instanceId']}/publishes/users";

        return $this->makeRequest('POST', $path, $publishRequest);
    }

    /**
     * Генерация токена для пользователя.
     *
     * @param string $userId Идентификатор пользователя.
     * @return array Токен в формате JWT.
     */
    public function generateToken(string $userId): array
    {
        $this->checkUserId($userId);

        $instanceId = $this->options['instanceId'];
        $secretKey = $this->options['secretKey'];

        $claims = [
            "iss" => "https://$instanceId.pushnotifications.pusher.com",
            "sub" => $userId,
            "exp" => time() + 86400  // 24 часа
        ];

        $token = $this->jwtEncode($claims, $secretKey);

        return ["token" => $token];
    }

    /**
     * Удаление пользователя по его идентификатору.
     *
     * @param string $userId Идентификатор пользователя.
     * @throws Exception
     */
    public function deleteUser(string $userId)
    {
        $this->checkUserId($userId);

        $path = "/customer_api/v1/instances/{$this->options['instanceId']}/users/$userId";
        $this->makeRequest('DELETE', $path);
    }

    /**
     * Отправка HTTP-запроса на сервер Pusher.
     *
     * @param string $method Метод запроса (POST, DELETE и т.д.).
     * @param string $path Путь к API.
     * @param array|null $body Данные запроса (опционально).
     * @return mixed Ответ сервера или исключение в случае ошибки.
     * @throws Exception
     */
    private function makeRequest(string $method, string $path, array $body = null)
    {
        $url = $this->endpoint . $path;
        $headers = [
            "Authorization: Bearer {$this->options['secretKey']}",
            "X-Pusher-Library: pusher-push-notifications-php/2.0.0",
            "Content-Type: application/json"
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            $jsonData = json_encode($body);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) || $httpCode >= 400) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Ошибка запроса: $error. Код ответа: $httpCode");
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * Кодирование данных в формат JWT.
     *
     * @param array $payload Данные для кодирования.
     * @param string $secretKey Секретный ключ.
     * @return string Закодированный JWT.
     */
    private function jwtEncode(array $payload, string $secretKey): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secretKey, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * Проверка идентификатора пользователя.
     *
     * @param string $userId Идентификатор пользователя.
     * @throws Exception
     */
    private function checkUserId(string $userId)
    {
        if (empty($userId)) {
            throw new Exception("Идентификатор пользователя не может быть пустым.");
        }
        if (mb_strlen($userId) > 164) {
            throw new Exception("Идентификатор пользователя \"$userId\" превышает допустимую длину в 164 символа.");
        }
    }
}