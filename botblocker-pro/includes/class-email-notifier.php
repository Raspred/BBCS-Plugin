<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER_PRO')) {
	exit;
}

/*
$emailNotifier = new WP_EmailNotifier('noreply@example.com', 'Example Sender');

$extraHeaders = [
    'Reply-To: support@example.com',
    'Cc: cc@example.com',
];
$emailNotifier->setHeaders($extraHeaders);

$attachments = [
    WP_CONTENT_DIR . '/uploads/your-file.pdf',
    WP_CONTENT_DIR . '/uploads/another-file.jpg'
];
$emailNotifier->setAttachments($attachments);

$sent = $emailNotifier->sendEmail('recipient@example.com', 'Тестовое письмо', '<h1>Привет!</h1><p>Это тестовое HTML письмо.</p>', 'html');

if ($sent) {
    echo 'Письмо успешно отправлено!';
} else {
    echo 'Не удалось отправить письмо.';
}

*/

class WP_EmailNotifier
{
    private $fromEmail;
    private $fromName;
    private $headers;
    private $attachments;

    /**
     * Конструктор класса для инициализации параметров отправки писем.
     *
     * @param string $fromEmail Email отправителя.
     * @param string $fromName Имя отправителя.
     */
    public function __construct(string $fromEmail = '', string $fromName = '')
    {
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->headers = [];
        $this->attachments = [];

        // Устанавливаем заголовки по умолчанию, если указаны email и имя отправителя
        if (!empty($this->fromEmail)) {
            $this->headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        }
    }

    /**
     * Установка заголовков для отправки почты.
     *
     * @param array $headers Массив заголовков для отправки.
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Установка вложений для отправки почты.
     *
     * @param array $attachments Массив вложений (путей к файлам).
     */
    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * Отправка письма.
     *
     * @param string $to Email получателя.
     * @param string $subject Тема письма.
     * @param string $message Сообщение письма.
     * @param string $format Формат письма: 'html' или 'text'.
     * @return bool Возвращает true, если письмо успешно отправлено.
     */
    public function sendEmail(string $to, string $subject, string $message, string $format = 'html'): bool
    {
        // Устанавливаем заголовок Content-Type в зависимости от формата
        if ($format === 'html') {
            $this->headers[] = 'Content-Type: text/html; charset=UTF-8';
        } else {
            $this->headers[] = 'Content-Type: text/plain; charset=UTF-8';
        }

        // Используем wp_mail() для отправки
        return wp_mail($to, $subject, $message, $this->headers, $this->attachments);
    }

    /**
     * Установить "От" email.
     *
     * @param string $email Email отправителя.
     */
    public function setFromEmail(string $email)
    {
        $this->fromEmail = $email;
        $this->updateFromHeader();
    }

    /**
     * Установить "От" имя.
     *
     * @param string $name Имя отправителя.
     */
    public function setFromName(string $name)
    {
        $this->fromName = $name;
        $this->updateFromHeader();
    }

    /**
     * Обновление заголовка "From".
     */
    private function updateFromHeader()
    {
        // Удаляем старый заголовок "From"
        $this->headers = array_filter($this->headers, function($header) {
            return stripos($header, 'From:') !== 0;
        });

        // Добавляем новый заголовок "From", если указаны email и имя
        if (!empty($this->fromEmail)) {
            $this->headers[] = "From: {$this->fromName} <{$this->fromEmail}>";
        }
    }
}