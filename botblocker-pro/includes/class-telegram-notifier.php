<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER_PRO')) {
	exit;
}

/*

$apiKey = 'YOUR_API_KEY';
$chatId = 'YOUR_CHAT_ID';

$notifier = new TelegramNotifier($apiKey, $chatId);

$response = $notifier->sendMessage('Привет! Всё пропало, сайт упал!', 'html');
var_dump($response); 

$response = $notifier->sendPhoto('/path/to/your/image.jpg', 'Классная статистика', 'html');
var_dump($response); 
*/

class TelegramNotifier
{
    private $apiKey;
    private $chatId;
    private $baseUrl;

    /**
     * Конструктор класса для инициализации API ключа и ID чата.
     *
     * @param string $apiKey API-ключ Telegram бота.
     * @param string $chatId ID чата, в который будут отправляться сообщения.
     */
    public function __construct(string $apiKey, string $chatId)
    {
        $this->apiKey = $apiKey;
        $this->chatId = $chatId;
        $this->baseUrl = "https://api.telegram.org/bot{$this->apiKey}/";
    }

    /**
     * Отправка текстового сообщения в Telegram.
     *
     * @param string $message Текст сообщения.
     * @param string $format Формат сообщения: 'html' или 'text'.
     * @return array|false Возвращает массив с результатом или false в случае ошибки.
     */
    public function sendMessage(string $message, string $format = 'html')
    {
        $url = $this->baseUrl . 'sendMessage';

        $data = [
            'chat_id' => $this->chatId,
            'text'    => $message,
            'parse_mode' => in_array($format, ['html', 'text']) ? $format : 'text',
        ];

        return $this->sendRequest($url, $data);
    }

    /**
     * Отправка изображения в Telegram.
     *
     * @param string $filePath Путь к изображению на сервере.
     * @param string $caption Подпись к изображению (необязательно).
     * @param string $format Формат подписи: 'html' или 'text'.
     * @return array|false Возвращает массив с результатом или false в случае ошибки.
     */
    public function sendPhoto(string $filePath, string $caption = '', string $format = 'html')
    {
        $url = $this->baseUrl . 'sendPhoto';

        if (!file_exists($filePath)) {
            return false; // Файл не существует
        }

        $data = [
            'chat_id' => $this->chatId,
            'caption' => $caption,
            'parse_mode' => in_array($format, ['html', 'text']) ? $format : 'text',
        ];

        // Добавляем файл к запросу
        $data['photo'] = new CURLFile(realpath($filePath));

        return $this->sendRequest($url, $data, true);
    }

    /**
     * Отправка запроса на сервер Telegram.
     *
     * @param string $url URL запроса.
     * @param array $data Данные для отправки.
     * @param bool $isFile Указывает, является ли отправляемое сообщение файлом.
     * @return array|false Возвращает массив с результатом или false в случае ошибки.
     */
    private function sendRequest(string $url, array $data, bool $isFile = false)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Если мы отправляем файл, используем метод POST с multipart/form-data
        if ($isFile) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return false; // Возвращаем false в случае ошибки
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}