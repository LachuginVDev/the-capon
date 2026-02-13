<?php
/**
 * Функционал личного кабинета и избранного
 *
 * @package The_Capon
 */

// ======================================================================
// 1. Скрываем админ бар для не-администраторов
// ======================================================================
add_filter('show_admin_bar', function($show) {
    if (!current_user_can('administrator')) {
        return false;
    }
    return $show;
});

// ======================================================================
// 2. AJAX добавить в избранное
// ======================================================================
add_action('wp_ajax_make_order', 'make_order');
add_action('wp_ajax_nopriv_make_order', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы', 'login' => '/войти/']);
});

function make_order() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Требуется вход']);
    }

    if (empty($_POST['model_id'])) {
        wp_send_json_error(['message' => 'ID модели не указан']);
    }

    $model_id = intval($_POST['model_id']);
    
    // Проверяем, что пост существует
    if (!get_post($model_id)) {
        wp_send_json_error(['message' => 'Модель не найдена']);
    }

    // Получаем текущий список избранного
    $favorites = get_user_meta($user_id, 'favorite_models', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }

    // Проверяем, не добавлена ли уже модель
    if (in_array($model_id, $favorites)) {
        wp_send_json_success(['message' => 'Модель уже в избранном', 'url' => home_url('/lk/')]);
        return;
    }

    // Добавляем модель в избранное
    $favorites[] = $model_id;
    update_user_meta($user_id, 'favorite_models', $favorites);

    wp_send_json_success([
            'message' => 'Модель добавлена в избранное',
            'url' => home_url('/lk/')
    ]);
}

// ======================================================================
// 3. AJAX удалить из избранного
// ======================================================================
add_action('wp_ajax_remove_favorite', 'remove_favorite');
add_action('wp_ajax_nopriv_remove_favorite', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

function remove_favorite() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Требуется вход']);
    }

    if (empty($_POST['model_id'])) {
        wp_send_json_error(['message' => 'ID модели не указан']);
    }

    $model_id = intval($_POST['model_id']);

    // Получаем текущий список избранного
    $favorites = get_user_meta($user_id, 'favorite_models', true);
    if (!is_array($favorites)) {
        $favorites = array();
    }

    // Удаляем модель из избранного
    $favorites = array_values(array_diff($favorites, array($model_id)));
    update_user_meta($user_id, 'favorite_models', $favorites);

    wp_send_json_success(['message' => 'Модель удалена из избранного']);
}

// ======================================================================
// 4. AJAX обновление данных пользователя
// ======================================================================
add_action('wp_ajax_update_user_account', 'update_user_account');
add_action('wp_ajax_nopriv_update_user_account', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

function update_user_account() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Требуется вход']);
    }

    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_user_account_nonce')) {
        wp_send_json_error(['message' => 'Ошибка безопасности. Обновите страницу и попробуйте снова.']);
    }

    // Обновляем основные поля пользователя
    $update_data = array('ID' => $user_id);
    
    if (isset($_POST['user_email'])) {
        $email = sanitize_email($_POST['user_email']);
        if (is_email($email)) {
            // Проверяем, не занят ли email другим пользователем
            $email_exists = email_exists($email);
            if ($email_exists && $email_exists != $user_id) {
                wp_send_json_error(['message' => 'Этот email уже используется другим пользователем']);
            }
            $update_data['user_email'] = $email;
        }
    }
    
    if (isset($_POST['first_name'])) {
        $update_data['first_name'] = sanitize_text_field($_POST['first_name']);
    }
    
    if (isset($_POST['last_name'])) {
        $update_data['last_name'] = sanitize_text_field($_POST['last_name']);
    }
    
    if (!empty($update_data) && count($update_data) > 1) {
        $result = wp_update_user($update_data);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
    }

    // Обновляем мета-поля
    $meta_fields = ['phone', 'shop_name', 'country', 'city', 'shop_address', 'website', 'social'];
    
    foreach ($meta_fields as $field) {
        if (isset($_POST[$field])) {
            $value = sanitize_text_field($_POST[$field]);
            if ($field === 'website') {
                $value = esc_url_raw($value);
            }
            update_user_meta($user_id, $field, $value);
        }
    }

    wp_send_json_success(['message' => 'Данные успешно сохранены']);
}

// ======================================================================
// 5. AJAX создание заказа из избранного
// ======================================================================
add_action('wp_ajax_create_order_from_favorites', 'create_order_from_favorites');
add_action('wp_ajax_nopriv_create_order_from_favorites', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

function create_order_from_favorites() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Требуется вход']);
    }

    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_order_nonce')) {
        wp_send_json_error(['message' => 'Ошибка безопасности']);
    }

    // Получаем избранное
    $favorite_ids = get_user_meta($user_id, 'favorite_models', true);
    if (!is_array($favorite_ids) || empty($favorite_ids)) {
        wp_send_json_error(['message' => 'У вас нет товаров в избранном']);
    }

    // Получаем информацию о товарах
    $order_items = array();
    $total_price = 0;

    foreach ($favorite_ids as $model_id) {
        $model = get_post($model_id);
        if (!$model) continue;

        $slides = get_field('model_slides', $model_id);
        $price = 0;
        $parameters = array();

        if ($slides && is_array($slides) && !empty($slides) && isset($slides[0])) {
            if (isset($slides[0]['price']) && !empty($slides[0]['price'])) {
                $price = floatval($slides[0]['price']);
            }
            if (isset($slides[0]['parameters']) && is_array($slides[0]['parameters'])) {
                $parameters = $slides[0]['parameters'];
            }
        }

        // Fallback на старое поле
        if (!$price) {
            $price = floatval(get_post_meta($model_id, '_model_price', true));
        }

        // Получаем статус наличия
        $in_stock = get_field('model_in_stock', $model_id);
        if ($in_stock === null) {
            $in_stock = true; // По умолчанию в наличии
        }

        $order_items[] = array(
            'model_id' => $model_id,
            'title' => $model->post_title,
            'price' => $price,
            'parameters' => $parameters,
            'in_stock' => $in_stock
        );

        $total_price += $price;
    }

    if (empty($order_items)) {
        wp_send_json_error(['message' => 'Не удалось получить информацию о товарах']);
    }

    // Создаем заказ
    $order_data = array(
        'user_id' => $user_id,
        'items' => $order_items,
        'total_price' => $total_price,
        'status' => 'pending', // pending, processing, completed, cancelled
        'created_at' => current_time('mysql'),
        'updated_at' => current_time('mysql')
    );

    // Получаем существующие заказы
    $orders = get_user_meta($user_id, 'user_orders', true);
    if (!is_array($orders)) {
        $orders = array();
    }

    // Генерируем ID заказа
    $order_id = 'ORD-' . time() . '-' . $user_id;

    // Добавляем заказ
    $orders[$order_id] = $order_data;
    update_user_meta($user_id, 'user_orders', $orders);

    // Удаляем товары из избранного после создания заказа
    update_user_meta($user_id, 'favorite_models', array());

    wp_send_json_success([
        'message' => 'Заказ успешно создан',
        'order_id' => $order_id
    ]);
}

// ======================================================================
// 6. AJAX получение заказов пользователя
// ======================================================================
add_action('wp_ajax_get_user_orders', 'get_user_orders');
add_action('wp_ajax_nopriv_get_user_orders', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

function get_user_orders() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Требуется вход']);
    }

    $orders = get_user_meta($user_id, 'user_orders', true);
    if (!is_array($orders)) {
        $orders = array();
    }

    // Сортируем по дате создания (новые первые)
    uasort($orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    wp_send_json_success(['orders' => $orders]);
}

// ======================================================================
// 7. AJAX обновление статуса заказа (для админа)
// ======================================================================
add_action('wp_ajax_update_order_status', 'update_order_status');
add_action('wp_ajax_nopriv_update_order_status', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

function update_order_status() {
    if (!current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Недостаточно прав']);
    }

    if (empty($_POST['order_id']) || empty($_POST['user_id']) || empty($_POST['status'])) {
        wp_send_json_error(['message' => 'Не указаны необходимые параметры']);
    }

    $order_id = sanitize_text_field($_POST['order_id']);
    $target_user_id = intval($_POST['user_id']);
    $new_status = sanitize_text_field($_POST['status']);

    $allowed_statuses = array('pending', 'processing', 'completed', 'cancelled');
    if (!in_array($new_status, $allowed_statuses)) {
        wp_send_json_error(['message' => 'Недопустимый статус']);
    }

    $orders = get_user_meta($target_user_id, 'user_orders', true);
    if (!is_array($orders) || !isset($orders[$order_id])) {
        wp_send_json_error(['message' => 'Заказ не найден']);
    }

    $orders[$order_id]['status'] = $new_status;
    $orders[$order_id]['updated_at'] = current_time('mysql');
    update_user_meta($target_user_id, 'user_orders', $orders);

    wp_send_json_success(['message' => 'Статус заказа обновлен']);
}

// ======================================================================
// 8. AJAX обновление статуса наличия товара (для админа)
// ======================================================================
add_action('wp_ajax_update_model_stock', 'update_model_stock');
add_action('wp_ajax_nopriv_update_model_stock', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

function update_model_stock() {
    if (!current_user_can('administrator')) {
        wp_send_json_error(['message' => 'Недостаточно прав']);
    }

    if (empty($_POST['post_id']) || !isset($_POST['in_stock'])) {
        wp_send_json_error(['message' => 'Не указаны необходимые параметры']);
    }

    $post_id = intval($_POST['post_id']);
    $in_stock = $_POST['in_stock'] === '1' ? true : false;

    // Проверка nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_model_stock_' . $post_id)) {
        wp_send_json_error(['message' => 'Ошибка безопасности']);
    }

    // Проверяем, что пост существует
    if (!get_post($post_id)) {
        wp_send_json_error(['message' => 'Товар не найден']);
    }

    // Обновляем статус наличия
    update_field('model_in_stock', $in_stock, $post_id);
    
    wp_send_json_success(['message' => 'Статус наличия обновлен']);
}

// ======================================================================
// 9. AJAX магазины пользователя (для вкладки "Магазины" в ЛК)
// ======================================================================
add_action('wp_ajax_get_user_stores', 'the_capon_get_user_stores');
add_action('wp_ajax_nopriv_get_user_stores', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

add_action('wp_ajax_save_user_stores', 'the_capon_save_user_stores');
add_action('wp_ajax_nopriv_save_user_stores', function() {
    wp_send_json_error(['message' => 'Вы не авторизованы']);
});

/**
 * Геокодирование адреса через Nominatim (OpenStreetMap), бесплатно.
 *
 * @param string $query Полный адрес.
 * @return array [ 'lat' => '', 'lng' => '', 'country' => '', 'city' => '' ].
 */
function the_capon_geocode_address( $query ) {
    $empty = array( 'lat' => '', 'lng' => '', 'country' => '', 'city' => '' );
    $query = trim( (string) $query );
    if ( '' === $query ) {
        return $empty;
    }

    $url = add_query_arg(
        array(
            'q'      => $query,
            'format' => 'json',
            'limit'  => 1,
        ),
        'https://nominatim.openstreetmap.org/search'
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout'   => 10,
            'sslverify' => true,
            'headers'   => array(
                'User-Agent'      => 'TheCaponWedding/1.0 (WordPress)',
                'Accept-Language' => 'ru',
            ),
        )
    );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return $empty;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    if ( ! is_array( $data ) || empty( $data[0] ) ) {
        return $empty;
    }

    $first = $data[0];
    $lat   = isset( $first['lat'] ) ? trim( (string) $first['lat'] ) : '';
    $lng   = isset( $first['lon'] ) ? trim( (string) $first['lon'] ) : '';

    if ( $lat === '' || $lng === '' ) {
        return $empty;
    }
    $lat_float = floatval( $lat );
    $lng_float = floatval( $lng );
    if ( $lat_float === 0.0 && $lng_float === 0.0 ) {
        return $empty;
    }

    $country = '';
    $city    = '';
    $addr    = isset( $first['address'] ) && is_array( $first['address'] ) ? $first['address'] : array();
    if ( ! empty( $addr['country'] ) ) {
        $country = trim( (string) $addr['country'] );
    }
    foreach ( array( 'city', 'town', 'village', 'municipality', 'county' ) as $key ) {
        if ( ! empty( $addr[ $key ] ) && $city === '' ) {
            $city = trim( (string) $addr[ $key ] );
            break;
        }
    }

    return array(
        'lat'     => $lat,
        'lng'     => $lng,
        'country' => $country,
        'city'    => $city,
    );
}

/**
 * Обратное геокодирование: координаты → страна и город (Nominatim). Кэш в transient.
 *
 * @param string|float $lat Широта.
 * @param string|float $lng Долгота.
 * @return array [ 'country' => '', 'city' => '' ].
 */
function the_capon_reverse_geocode( $lat, $lng ) {
    $empty = array( 'country' => '', 'city' => '' );
    $lat   = floatval( $lat );
    $lng   = floatval( $lng );
    if ( $lat === 0.0 && $lng === 0.0 ) {
        return $empty;
    }

    $cache_key = 'the_capon_rev_' . round( $lat, 4 ) . '_' . round( $lng, 4 );
    $cached    = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    static $last_request_time = 0;
    $now = time();
    if ( $last_request_time > 0 && ( $now - $last_request_time ) < 1 ) {
        sleep( 1 );
    }
    $last_request_time = time();

    $url = add_query_arg(
        array(
            'lat'    => $lat,
            'lon'    => $lng,
            'format' => 'json',
        ),
        'https://nominatim.openstreetmap.org/reverse'
    );

    $response = wp_remote_get(
        $url,
        array(
            'timeout'   => 5,
            'sslverify' => true,
            'headers'   => array(
                'User-Agent'    => 'TheCaponWedding/1.0 (WordPress)',
                'Accept-Language' => 'ru',
            ),
        )
    );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return $empty;
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    if ( ! is_array( $data ) || empty( $data['address'] ) ) {
        return $empty;
    }

    $addr    = $data['address'];
    $country = isset( $addr['country'] ) ? trim( (string) $addr['country'] ) : '';
    $city    = '';
    foreach ( array( 'city', 'town', 'village', 'municipality', 'county' ) as $key ) {
        if ( ! empty( $addr[ $key ] ) ) {
            $city = trim( (string) $addr[ $key ] );
            break;
        }
    }

    $result = array( 'country' => $country, 'city' => $city );
    set_transient( $cache_key, $result, DAY_IN_SECONDS );

    return $result;
}

add_action( 'wp_ajax_geocode_address', 'the_capon_ajax_geocode_address' );
add_action( 'wp_ajax_nopriv_geocode_address', function() {
    wp_send_json_error( array( 'message' => 'Вы не авторизованы' ) );
} );

function the_capon_ajax_geocode_address() {
    if ( ! get_current_user_id() ) {
        wp_send_json_error( array( 'message' => 'Требуется вход' ) );
    }
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'update_user_stores_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Ошибка безопасности' ) );
    }
    $address = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';
    $result  = the_capon_geocode_address( $address );
    wp_send_json_success( $result );
}

function the_capon_get_user_stores() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( array( 'message' => 'Требуется вход' ) );
    }

    $stores = get_user_meta( $user_id, 'user_stores', true );
    if ( ! is_array( $stores ) ) {
        $stores = array();
    }

    wp_send_json_success( array( 'stores' => $stores ) );
}

function the_capon_save_user_stores() {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        wp_send_json_error( array( 'message' => 'Требуется вход' ) );
    }

    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'update_user_stores_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Ошибка безопасности' ) );
    }

    if ( ! isset( $_POST['stores'] ) ) {
        wp_send_json_error( array( 'message' => 'Нет данных магазинов' ) );
    }

    $raw = wp_unslash( $_POST['stores'] );
    $data = json_decode( $raw, true );

    if ( ! is_array( $data ) ) {
        wp_send_json_error( array( 'message' => 'Неверный формат данных' ) );
    }

    $clean = array();

    foreach ( $data as $store ) {
        if ( ! is_array( $store ) ) {
            continue;
        }

        $name    = isset( $store['name'] ) ? sanitize_text_field( $store['name'] ) : '';
        $address = isset( $store['address'] ) ? sanitize_text_field( $store['address'] ) : '';

        // Без названия и адреса не сохраняем
        if ( '' === $name && '' === $address ) {
            continue;
        }

        // Координаты из поля (формат: "lat, lng")
        $lat = isset( $store['lat'] ) ? sanitize_text_field( $store['lat'] ) : '';
        $lng = isset( $store['lng'] ) ? sanitize_text_field( $store['lng'] ) : '';

        // Валидация координат
        $lat_float = floatval( $lat );
        $lng_float = floatval( $lng );
        if ( $lat_float === 0.0 || $lng_float === 0.0 || abs( $lat_float ) > 90 || abs( $lng_float ) > 180 ) {
            $lat = '';
            $lng = '';
        }

        $clean[] = array(
            'name'        => $name,
            'country'     => isset( $store['country'] ) ? sanitize_text_field( $store['country'] ) : '',
            'city'        => isset( $store['city'] ) ? sanitize_text_field( $store['city'] ) : '',
            'address'     => $address,
            'phone'       => isset( $store['phone'] ) ? sanitize_text_field( $store['phone'] ) : '',
            'email'       => isset( $store['email'] ) ? sanitize_email( $store['email'] ) : '',
            'website'     => isset( $store['website'] ) ? esc_url_raw( $store['website'] ) : '',
            'socials'     => isset( $store['socials'] ) ? sanitize_textarea_field( $store['socials'] ) : '',
            'lat'         => $lat,
            'lng'         => $lng,
            'description' => isset( $store['description'] ) ? sanitize_textarea_field( $store['description'] ) : '',
        );
    }

    update_user_meta( $user_id, 'user_stores', $clean );

    wp_send_json_success( array( 'message' => 'Магазины сохранены', 'stores' => $clean ) );
}
