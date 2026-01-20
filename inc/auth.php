<?php
/**
 * Функционал авторизации, регистрации и восстановления пароля
 *
 * @package The_Capon
 */

// ======================================================================
// 1. Запрещаем логин неподтверждённым юзерам
// ======================================================================
add_filter('wp_authenticate_user', function($user, $password) {
    $status = get_user_meta($user->ID, 'account_status', true);

    if ($status === 'pending') {
        return new WP_Error('pending', 'Ваш аккаунт ещё не одобрен администратором');
    }

    return $user;
}, 10, 2);

// ======================================================================
// 2. Фильтр для user_login (убираем дичь)
// ======================================================================
add_filter('sanitize_user', function($username, $raw_username) {
    $username = wp_strip_all_tags($raw_username);
    $username = preg_replace('/[^а-яА-Яa-zA-Z0-9_\-]/u', '', $username);
    return $username;
}, 10, 3);

// ======================================================================
// 3. Вспомогательные функции для защиты от брутфорса и rate limiting
// ======================================================================

/**
 * Получает IP адрес пользователя
 */
function the_capon_get_user_ip() {
    $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Если это список IP (через прокси), берем первый
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Проверяет rate limit для действия
 * @param string $action - Тип действия (login, register, password_reset)
 * @param int $max_attempts - Максимум попыток
 * @param int $time_window - Временное окно в секундах
 * @return bool|WP_Error - true если разрешено, WP_Error если превышен лимит
 */
function the_capon_check_rate_limit($action, $max_attempts = 5, $time_window = 900) {
    $ip = the_capon_get_user_ip();
    $transient_key = 'the_capon_rate_limit_' . $action . '_' . md5($ip);
    
    $attempts = get_transient($transient_key);
    
    if ($attempts === false) {
        $attempts = 0;
    }
    
    if ($attempts >= $max_attempts) {
        $remaining_time = get_option('_transient_timeout_' . $transient_key) - time();
        return new WP_Error(
            'rate_limit_exceeded',
            sprintf('Слишком много попыток. Попробуйте через %d минут.', ceil($remaining_time / 60))
        );
    }
    
    // Увеличиваем счетчик
    set_transient($transient_key, $attempts + 1, $time_window);
    
    return true;
}

/**
 * Логирует подозрительную активность
 */
function the_capon_log_security_event($event_type, $details = []) {
    $log_entry = [
        'time' => current_time('mysql'),
        'ip' => the_capon_get_user_ip(),
        'event' => $event_type,
        'details' => $details
    ];
    
    // Сохраняем в опции (можно заменить на файловый лог или БД)
    $logs = get_option('the_capon_security_logs', []);
    $logs[] = $log_entry;
    
    // Храним только последние 100 записей
    if (count($logs) > 100) {
        $logs = array_slice($logs, -100);
    }
    
    update_option('the_capon_security_logs', $logs);
}

/**
 * Проверяет сложность пароля
 */
function the_capon_validate_password_strength($password) {
    if (strlen($password) < 6) {
        return new WP_Error('weak_password', 'Пароль должен содержать минимум 6 символов');
    }
    
    // Дополнительные проверки (опционально)
    // if (!preg_match('/[A-Z]/', $password)) {
    //     return new WP_Error('weak_password', 'Пароль должен содержать хотя бы одну заглавную букву');
    // }
    // if (!preg_match('/[0-9]/', $password)) {
    //     return new WP_Error('weak_password', 'Пароль должен содержать хотя бы одну цифру');
    // }
    
    return true;
}

/**
 * Валидация телефона
 */
function the_capon_validate_phone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) < 10) {
        return false;
    }
    return true;
}

/**
 * Валидация URL
 */
function the_capon_validate_url($url) {
    if (empty($url)) {
        return true; // Необязательное поле
    }
    
    // Добавляем протокол, если его нет
    if (!preg_match('/^https?:\/\//i', $url)) {
        $url = 'http://' . $url;
    }
    
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// ======================================================================
// 4. AJAX регистрация
// ======================================================================
add_action('wp_ajax_nopriv_custom_register', 'custom_register');
add_action('wp_ajax_custom_register', 'custom_register');

function custom_register() {
    // Проверка nonce для безопасности
    if (!isset($_POST['register_nonce']) || !wp_verify_nonce($_POST['register_nonce'], 'custom_register_nonce')) {
        the_capon_log_security_event('register_nonce_failed');
        wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.']);
    }

    // Rate limiting
    $rate_check = the_capon_check_rate_limit('register', 3, 3600); // 3 попытки в час
    if (is_wp_error($rate_check)) {
        wp_send_json_error(['message' => $rate_check->get_error_message()]);
    }

    // Валидация обязательных полей
    $required = [
        'email', 'password', 'password_confirm',
        'first_name', 'last_name', 'phone', 'country', 'city'
    ];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(['message' => 'Заполните все обязательные поля']);
        }
    }

    $email = sanitize_email($_POST['email']);

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Неверный email']);
    }

    if (email_exists($email)) {
        wp_send_json_error(['message' => 'Email уже зарегистрирован']);
    }

    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if ($password !== $password_confirm) {
        wp_send_json_error(['message' => 'Пароли не совпадают']);
    }

    // Улучшенная валидация пароля
    $password_check = the_capon_validate_password_strength($password);
    if (is_wp_error($password_check)) {
        wp_send_json_error(['message' => $password_check->get_error_message()]);
    }

    // Валидация телефона
    if (!the_capon_validate_phone($_POST['phone'])) {
        wp_send_json_error(['message' => 'Неверный формат телефона']);
    }

    // Валидация URL (если указан)
    if (!empty($_POST['website']) && !the_capon_validate_url($_POST['website'])) {
        wp_send_json_error(['message' => 'Неверный формат URL сайта']);
    }

    $user_id = wp_insert_user([
        'user_login'    => $email,
        'user_email'    => $email,
        'user_pass'     => $password,
        'role'          => 'subscriber',
        'first_name'    => sanitize_text_field($_POST['first_name']),
        'last_name'     => sanitize_text_field($_POST['last_name']),
        'display_name'  => sanitize_text_field($_POST['first_name'] . ' ' . $_POST['last_name']),
    ]);

    if (is_wp_error($user_id)) {
        the_capon_log_security_event('register_error', ['error' => $user_id->get_error_message()]);
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    // Сохранение дополнительных полей
    $fields = [
        'first_name', 'last_name', 'phone', 'shop_name',
        'country', 'city', 'shop_address', 'website', 'social'
    ];

    foreach ($fields as $f) {
        if (isset($_POST[$f]) && !empty($_POST[$f])) {
            update_user_meta($user_id, $f, sanitize_text_field($_POST[$f]));
        }
    }

    update_user_meta($user_id, 'account_status', 'pending');

    // Уведомление администратора о новой регистрации
    $admin_email = get_option('admin_email');
    $subject = 'Новая регистрация на сайте';
    $message = sprintf(
        '<p>Новый пользователь зарегистрировался на сайте:</p>
        <ul>
            <li>Email: %s</li>
            <li>Имя: %s %s</li>
            <li>Телефон: %s</li>
            <li>Магазин: %s</li>
        </ul>
        <p><a href="%s">Перейти к модерации пользователей</a></p>',
        esc_html($email),
        esc_html($_POST['first_name']),
        esc_html($_POST['last_name']),
        esc_html($_POST['phone']),
        esc_html($_POST['shop_name']),
        admin_url('admin.php?page=user_moderation')
    );
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    wp_mail($admin_email, $subject, $message, $headers);

    the_capon_log_security_event('register_success', ['user_id' => $user_id, 'email' => $email]);

    wp_send_json_success(['message' => 'Регистрация прошла. Ожидайте одобрения администратора.']);
}

// ======================================================================
// 5. AJAX login с защитой от брутфорса
// ======================================================================
add_action('wp_ajax_nopriv_login_user', 'login_user');
add_action('wp_ajax_login_user', 'login_user');

function login_user() {
    // Проверка nonce для безопасности
    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'custom_login_nonce')) {
        the_capon_log_security_event('login_nonce_failed');
        wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.']);
    }

    // Rate limiting для входа (более строгий)
    $rate_check = the_capon_check_rate_limit('login', 5, 900); // 5 попыток за 15 минут
    if (is_wp_error($rate_check)) {
        the_capon_log_security_event('login_rate_limit', ['ip' => the_capon_get_user_ip()]);
        wp_send_json_error(['message' => $rate_check->get_error_message()]);
    }

    if (empty($_POST['log']) || empty($_POST['pwd'])) {
        wp_send_json_error(['message' => 'Заполните все поля']);
    }

    $user_login = sanitize_text_field($_POST['log']);
    $user_password = $_POST['pwd'];

    // Проверяем, существует ли пользователь (для проверки блокировки и логирования)
    $check_user = get_user_by('email', $user_login);
    if (!$check_user) {
        $check_user = get_user_by('login', $user_login);
    }
    
    // Проверяем блокировку аккаунта перед попыткой входа
    if ($check_user) {
        $blocked_until = get_user_meta($check_user->ID, 'login_blocked_until', true);
        if ($blocked_until && intval($blocked_until) > time()) {
            $remaining = ceil((intval($blocked_until) - time()) / 60);
            wp_send_json_error(['message' => sprintf('Аккаунт временно заблокирован. Попробуйте через %d минут.', $remaining)]);
        }
    }

    $creds = [
        'user_login'    => $user_login,
        'user_password' => $user_password,
        'remember'      => isset($_POST['remember']) ? true : false
    ];

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        // Логируем неудачную попытку входа
        the_capon_log_security_event('login_failed', [
            'login' => $user_login,
            'error' => $user->get_error_code()
        ]);
        
        // Увеличиваем счетчик неудачных попыток для этого пользователя
        if ($check_user && isset($check_user->ID)) {
            $failed_attempts = get_user_meta($check_user->ID, 'failed_login_attempts', true);
            $failed_attempts = $failed_attempts ? intval($failed_attempts) + 1 : 1;
            update_user_meta($check_user->ID, 'failed_login_attempts', $failed_attempts);
            
            // Блокируем после 10 неудачных попыток на 1 час
            if ($failed_attempts >= 10) {
                update_user_meta($check_user->ID, 'login_blocked_until', time() + 3600);
                wp_send_json_error(['message' => 'Слишком много неудачных попыток входа. Аккаунт временно заблокирован на 1 час.']);
            }
        }
        
        wp_send_json_error(['message' => $user->get_error_message()]);
    }

    // Успешный вход - сбрасываем счетчик неудачных попыток
    delete_user_meta($user->ID, 'failed_login_attempts');
    delete_user_meta($user->ID, 'login_blocked_until');
    
    // Сохраняем дату последнего входа
    update_user_meta($user->ID, 'last_login', current_time('mysql'));
    
    the_capon_log_security_event('login_success', ['user_id' => $user->ID]);

    wp_send_json_success(['message' => 'ok']);
}

// ======================================================================
// 6. AJAX запрос на восстановление пароля с rate limiting
// ======================================================================
add_action('wp_ajax_nopriv_request_password_reset', 'request_password_reset');
add_action('wp_ajax_request_password_reset', 'request_password_reset');

function request_password_reset() {
    // Проверка nonce для безопасности
    if (!isset($_POST['reset_nonce']) || !wp_verify_nonce($_POST['reset_nonce'], 'password_reset_nonce')) {
        the_capon_log_security_event('password_reset_nonce_failed');
        wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.']);
    }

    // Rate limiting
    $rate_check = the_capon_check_rate_limit('password_reset', 3, 3600); // 3 попытки в час
    if (is_wp_error($rate_check)) {
        wp_send_json_error(['message' => $rate_check->get_error_message()]);
    }

    if (empty($_POST['user_email'])) {
        wp_send_json_error(['message' => 'Введите email']);
    }

    $email = sanitize_email($_POST['user_email']);

    if (!is_email($email)) {
        wp_send_json_error(['message' => 'Неверный формат email']);
    }

    $user = get_user_by('email', $email);

    if (!$user) {
        // Не раскрываем, существует ли пользователь (безопасность)
        // Но логируем попытку
        the_capon_log_security_event('password_reset_attempt', ['email' => $email, 'user_exists' => false]);
        wp_send_json_success(['message' => 'Если аккаунт с таким email существует, на него отправлена ссылка для восстановления пароля.']);
        return;
    }

    // Проверяем, не заблокирован ли пользователь
    $blocked_until = get_user_meta($user->ID, 'login_blocked_until', true);
    if ($blocked_until && intval($blocked_until) > time()) {
        wp_send_json_error(['message' => 'Аккаунт временно заблокирован. Попробуйте позже.']);
    }

    // Генерируем ключ восстановления
    $key = get_password_reset_key($user);

    if (is_wp_error($key)) {
        wp_send_json_error(['message' => 'Ошибка генерации ключа восстановления']);
    }

    // Формируем ссылку для сброса пароля
    $reset_url = add_query_arg([
        'key' => $key,
        'login' => rawurlencode($user->user_login)
    ], home_url('/сброс-пароля/'));

    // Отправляем email
    $subject = 'Восстановление пароля';
    $message = sprintf(
        '<p>Здравствуйте!</p><p>Вы запросили восстановление пароля для вашего аккаунта.</p><p>Для восстановления пароля перейдите по ссылке:</p><p><a href="%s">%s</a></p><p>Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.</p><p>Ссылка действительна в течение 24 часов.</p>',
        esc_url($reset_url),
        esc_url($reset_url)
    );
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $sent = wp_mail($email, $subject, $message, $headers);

    if ($sent) {
        the_capon_log_security_event('password_reset_sent', ['user_id' => $user->ID]);
        wp_send_json_success(['message' => 'Ссылка для восстановления пароля отправлена на ваш email.']);
    } else {
        wp_send_json_error(['message' => 'Ошибка отправки email. Попробуйте позже.']);
    }
}

// ======================================================================
// 7. AJAX подтверждение сброса пароля
// ======================================================================
add_action('wp_ajax_nopriv_reset_password_confirm', 'reset_password_confirm');
add_action('wp_ajax_reset_password_confirm', 'reset_password_confirm');

function reset_password_confirm() {
    // Проверка nonce для безопасности
    if (!isset($_POST['reset_confirm_nonce']) || !wp_verify_nonce($_POST['reset_confirm_nonce'], 'password_reset_confirm_nonce')) {
        the_capon_log_security_event('password_reset_confirm_nonce_failed');
        wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.']);
    }

    if (empty($_POST['key']) || empty($_POST['login']) || empty($_POST['new_password'])) {
        wp_send_json_error(['message' => 'Заполните все поля']);
    }

    $key = sanitize_text_field($_POST['key']);
    $login = sanitize_text_field($_POST['login']);
    $new_password = $_POST['new_password'];

    // Улучшенная валидация пароля
    $password_check = the_capon_validate_password_strength($new_password);
    if (is_wp_error($password_check)) {
        wp_send_json_error(['message' => $password_check->get_error_message()]);
    }

    // Проверяем ключ
    $user = check_password_reset_key($key, $login);

    if (is_wp_error($user)) {
        the_capon_log_security_event('password_reset_invalid_key', ['login' => $login]);
        wp_send_json_error(['message' => $user->get_error_message()]);
    }

    // Сбрасываем пароль
    reset_password($user, $new_password);
    
    // Сбрасываем счетчики неудачных попыток
    delete_user_meta($user->ID, 'failed_login_attempts');
    delete_user_meta($user->ID, 'login_blocked_until');
    
    the_capon_log_security_event('password_reset_success', ['user_id' => $user->ID]);

    wp_send_json_success(['message' => 'Пароль успешно изменен. Теперь вы можете войти с новым паролем.']);
}

// ======================================================================
// 8. Изменяем URL страницы восстановления пароля
// ======================================================================
add_filter('lostpassword_url', function($lostpassword_url, $redirect) {
    return home_url('/восстановление-пароля/');
}, 10, 2);

// ======================================================================
// 9. Уведомление пользователя об одобрении аккаунта
// ======================================================================
add_action('update_user_meta', function($meta_id, $user_id, $meta_key, $meta_value) {
    // Проверяем, изменился ли статус аккаунта на 'approved'
    if ($meta_key === 'account_status' && $meta_value === 'approved') {
        $old_value = get_user_meta($user_id, 'account_status', true);
        if ($old_value === 'pending') {
            $user = get_userdata($user_id);
            if ($user) {
                $subject = 'Ваш аккаунт одобрен';
                $message = sprintf(
                    '<p>Здравствуйте, %s!</p><p>Ваш аккаунт был одобрен администратором. Теперь вы можете войти на сайт.</p><p><a href="%s">Войти в личный кабинет</a></p>',
                    esc_html($user->display_name),
                    esc_url(home_url('/войти/'))
                );
                $headers = ['Content-Type: text/html; charset=UTF-8'];
                wp_mail($user->user_email, $subject, $message, $headers);
            }
        }
    }
}, 10, 4);
