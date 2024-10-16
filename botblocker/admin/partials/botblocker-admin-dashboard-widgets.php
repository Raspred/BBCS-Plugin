<?php
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

// Регистрируем виджеты
function register_custom_dashboard_widgets() {
    wp_add_dashboard_widget(
        'custom_stats_widget',
        'BotBlocker Stats',
        'display_stats_widget'
    );
    wp_add_dashboard_widget(
        'custom_form_widget',
        'BotBlocker Quick Rule',
        'display_form_widget'
    );
}
add_action('wp_dashboard_setup', 'register_custom_dashboard_widgets');

// Функция отображения виджета статистики
function display_stats_widget() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'your_plugin_table';
    
    // Получаем данные из БД
    $total_entries = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $latest_entry = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");
    
    // Выводим статистику
    echo "<p>Всего записей: $total_entries</p>";
    if ($latest_entry) {
        echo "<p>Последняя запись: " . esc_html($latest_entry->some_field) . "</p>";
    }
}

// Функция отображения виджета с формой
function display_form_widget() {
    // Проверяем, была ли отправлена форма
    if (isset($_POST['submit_custom_data'])) {
        // Обрабатываем отправленные данные
        $data = sanitize_text_field($_POST['custom_data']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_plugin_table';
        
        // Вставляем данные в БД
        $wpdb->insert(
            $table_name,
            array('some_field' => $data),
            array('%s')
        );
        
        echo "<p>Данные успешно сохранены!</p>";
    }
    
    // Выводим форму
    ?>
    <form method="post">
        <label for="custom_data">Введите данные:</label>
        <input type="text" name="custom_data" id="custom_data" required>
        <input type="submit" name="submit_custom_data" value="Сохранить">
    </form>
    <?php
}