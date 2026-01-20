<?php
/**
 * Админ страницы для управления пользователями
 *
 * @package The_Capon
 */

// ======================================================================
// 1. Регистрируем кастомный тип заказов
// ======================================================================
add_action('init', function() {
    register_post_type('orders', [
            'labels' => [
                    'name' => 'Заказы',
                    'singular_name' => 'Заказ',
            ],
            'public' => false, // не видно на фронте
            'show_ui' => true, // для админа
            'supports' => ['title', 'author', 'custom-fields']
    ]);
});

// ======================================================================
// 2. Добавляем страницу модерации пользователей в админку
// ======================================================================
add_action('admin_menu', function() {
    add_menu_page(
        'Модерация пользователей',   // Название
        'Модерация',                 // Название в меню
        'manage_options',            // Права доступа
        'user_moderation',           // Слаг страницы
        'render_user_moderation_page', // Функция вывода
        'dashicons-admin-users',     // Иконка
        60                           // Позиция
    );
});

// ======================================================================
// 3. Функция отображения страницы модерации пользователей
// ======================================================================
function render_user_moderation_page() {
    // Обработка удаления пользователя
    if (isset($_GET['delete_user']) && isset($_GET['_wpnonce'])) {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'delete_user_' . intval($_GET['delete_user']))) {
            wp_die('Ошибка безопасности');
        }
        $uid = intval($_GET['delete_user']);
        if ($uid > 0 && $uid != get_current_user_id()) {
            wp_delete_user($uid);
            wp_redirect(admin_url('admin.php?page=user_moderation&updated=deleted'));
            exit;
        }
    }

    // Обработка изменения статуса
    if (isset($_GET['change_status']) && isset($_GET['status']) && isset($_GET['_wpnonce'])) {
        $uid = intval($_GET['change_status']);
        $status = sanitize_text_field($_GET['status']);
        if (wp_verify_nonce($_GET['_wpnonce'], 'change_status_' . $uid) && in_array($status, ['pending', 'approved'])) {
            update_user_meta($uid, 'account_status', $status);
            wp_redirect(admin_url('admin.php?page=user_moderation&updated=status_changed'));
            exit;
        }
    }

    // Получаем всех пользователей (кроме администраторов)
    $users = get_users([
        'role__not_in' => ['administrator'],
        'orderby' => 'registered',
        'order' => 'DESC'
    ]);

    echo '<div class="wrap">';
    echo '<h1>Управление пользователями</h1>';

    // Сообщения
    if (isset($_GET['updated'])) {
        if ($_GET['updated'] === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>Пользователь удален.</p></div>';
        } elseif ($_GET['updated'] === 'status_changed') {
            echo '<div class="notice notice-success is-dismissible"><p>Статус изменен.</p></div>';
        }
    }

    if (empty($users)) {
        echo '<p>Нет пользователей.</p>';
        echo '</div>';
        return;
    }

    echo '<table class="widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Магазин</th>
                    <th>Страна/Город</th>
                    <th>Дата регистрации</th>
                    <th>Последний вход</th>
                    <th>Статус</th>
                    <th>Избранное</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($users as $u) {
        $account_status = get_user_meta($u->ID, 'account_status', true);
        if (empty($account_status)) {
            $account_status = 'approved'; // По умолчанию
        }
        
        $phone = esc_html(get_user_meta($u->ID, 'phone', true));
        $shop_name = esc_html(get_user_meta($u->ID, 'shop_name', true));
        $country = esc_html(get_user_meta($u->ID, 'country', true));
        $city = esc_html(get_user_meta($u->ID, 'city', true));
        
        // Получаем избранное
        $favorite_ids = get_user_meta($u->ID, 'favorite_models', true);
        $favorites_count = is_array($favorite_ids) ? count($favorite_ids) : 0;
        
        // Даты
        $registered_date = $u->user_registered ? date_i18n('d.m.Y H:i', strtotime($u->user_registered)) : '-';
        $last_login = get_user_meta($u->ID, 'last_login', true);
        $last_login_formatted = $last_login ? date_i18n('d.m.Y H:i', strtotime($last_login)) : 'Никогда';
        
        $status_label = $account_status === 'pending' ? 'Ожидает' : 'Одобрен';
        $status_class = $account_status === 'pending' ? 'warning' : 'success';
        
        $change_status_url = wp_nonce_url(
            admin_url('admin.php?page=user_moderation&change_status=' . $u->ID . '&status=' . ($account_status === 'pending' ? 'approved' : 'pending')),
            'change_status_' . $u->ID
        );
        
        $edit_url = admin_url('admin.php?page=user_moderation&edit_user=' . $u->ID);
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=user_moderation&delete_user=' . $u->ID),
            'delete_user_' . $u->ID
        );
        
        $favorites_url = admin_url('admin.php?page=user_moderation&view_favorites=' . $u->ID);

        echo "<tr>
                <td>{$u->ID}</td>
                <td><strong>" . esc_html($u->user_email) . "</strong></td>
                <td>" . esc_html($u->first_name . ' ' . $u->last_name) . "</td>
                <td>{$phone}</td>
                <td>{$shop_name}</td>
                <td>{$country} / {$city}</td>
                <td>{$registered_date}</td>
                <td>{$last_login_formatted}</td>
                <td><span class='status-{$status_class}'>{$status_label}</span></td>
                <td><a href='" . esc_url($favorites_url) . "'>" . $favorites_count . " товаров</a></td>
                <td>
                    <a href='" . esc_url($edit_url) . "' class='button button-small'>Редактировать</a>
                    <a href='" . esc_url($change_status_url) . "' class='button button-small'>" . ($account_status === 'pending' ? 'Одобрить' : 'Отклонить') . "</a>
                    <a href='" . esc_url($delete_url) . "' class='button button-small button-link-delete' onclick=\"return confirm('Вы уверены, что хотите удалить этого пользователя?');\">Удалить</a>
                </td>
               </tr>";
    }

    echo '</tbody></table>';
    
    // Если выбран пользователь для редактирования
    if (isset($_GET['edit_user'])) {
        $edit_user_id = intval($_GET['edit_user']);
        $edit_user = get_userdata($edit_user_id);
        if ($edit_user) {
            render_edit_user_form($edit_user);
        }
    }
    
    // Если выбран пользователь для просмотра избранного
    if (isset($_GET['view_favorites'])) {
        $view_user_id = intval($_GET['view_favorites']);
        $view_user = get_userdata($view_user_id);
        if ($view_user) {
            render_user_favorites($view_user);
        }
    }
    
    echo '</div>';
}

// ======================================================================
// 4. Форма редактирования пользователя
// ======================================================================
function render_edit_user_form($user) {
    if (isset($_POST['update_user']) && isset($_POST['user_id']) && wp_verify_nonce($_POST['user_edit_nonce'], 'edit_user_' . intval($_POST['user_id']))) {
        $user_id = intval($_POST['user_id']);
        
        $fields = ['first_name', 'last_name', 'phone', 'shop_name', 'country', 'city', 'shop_address', 'website', 'social', 'account_status'];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                if ($field === 'account_status') {
                    update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
                } elseif (in_array($field, ['first_name', 'last_name'])) {
                    wp_update_user(['ID' => $user_id, $field => sanitize_text_field($_POST[$field])]);
                } else {
                    update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
                }
            }
        }
        
        if (isset($_POST['user_email'])) {
            wp_update_user(['ID' => $user_id, 'user_email' => sanitize_email($_POST['user_email'])]);
        }
        
        echo '<div class="notice notice-success"><p>Данные пользователя обновлены.</p></div>';
        $user = get_userdata($user_id); // Обновляем данные
    }
    
    $phone          = get_user_meta($user->ID, 'phone', true);
    $shop_name      = get_user_meta($user->ID, 'shop_name', true);
    $country        = get_user_meta($user->ID, 'country', true);
    $city           = get_user_meta($user->ID, 'city', true);
    $shop_address   = get_user_meta($user->ID, 'shop_address', true);
    $website        = get_user_meta($user->ID, 'website', true);
    $social         = get_user_meta($user->ID, 'social', true);
    $account_status = get_user_meta($user->ID, 'account_status', true);

    // Магазины пользователя
    $user_stores = get_user_meta($user->ID, 'user_stores', true);
    if (!is_array($user_stores)) {
        $user_stores = array();
    }
    
    // Обработка смены пароля
    if (isset($_POST['change_password']) && isset($_POST['user_id']) && wp_verify_nonce($_POST['user_password_nonce'], 'change_password_' . intval($_POST['user_id']))) {
        $user_id = intval($_POST['user_id']);
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        if (empty($new_password)) {
            echo '<div class="notice notice-error"><p>Пароль не может быть пустым.</p></div>';
        } elseif (strlen($new_password) < 6) {
            echo '<div class="notice notice-error"><p>Пароль должен содержать минимум 6 символов.</p></div>';
        } elseif ($new_password !== $confirm_password) {
            echo '<div class="notice notice-error"><p>Пароли не совпадают.</p></div>';
        } else {
            wp_set_password($new_password, $user_id);
            echo '<div class="notice notice-success"><p>Пароль успешно изменен.</p></div>';
        }
    }
    
    echo '<div style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
    echo '<h2>Редактирование пользователя: ' . esc_html($user->user_email) . '</h2>';
    
    // Табы
    $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
    echo '<nav class="nav-tab-wrapper" style="margin-bottom: 20px;">';
    echo '<a href="' . esc_url(admin_url('admin.php?page=user_moderation&edit_user=' . $user->ID . '&tab=profile')) . '" class="nav-tab ' . ($current_tab === 'profile' ? 'nav-tab-active' : '') . '">Профиль</a>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=user_moderation&edit_user=' . $user->ID . '&tab=password')) . '" class="nav-tab ' . ($current_tab === 'password' ? 'nav-tab-active' : '') . '">Смена пароля</a>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=user_moderation&edit_user=' . $user->ID . '&tab=favorites')) . '" class="nav-tab ' . ($current_tab === 'favorites' ? 'nav-tab-active' : '') . '">Избранное</a>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=user_moderation&edit_user=' . $user->ID . '&tab=orders')) . '" class="nav-tab ' . ($current_tab === 'orders' ? 'nav-tab-active' : '') . '">Заказы</a>';
    echo '<a href="' . esc_url(admin_url('admin.php?page=user_moderation&edit_user=' . $user->ID . '&tab=stores')) . '" class="nav-tab ' . ($current_tab === 'stores' ? 'nav-tab-active' : '') . '">Магазины</a>';
    echo '</nav>';
    
    // Таб: Профиль
    if ($current_tab === 'profile') {
        echo '<form method="post">';
        wp_nonce_field('edit_user_' . $user->ID, 'user_edit_nonce');
        echo '<input type="hidden" name="user_id" value="' . esc_attr($user->ID) . '">';
        
        echo '<table class="form-table">
            <tr>
                <th><label>Email</label></th>
                <td><input type="email" name="user_email" value="' . esc_attr($user->user_email) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Имя</label></th>
                <td><input type="text" name="first_name" value="' . esc_attr($user->first_name) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Фамилия</label></th>
                <td><input type="text" name="last_name" value="' . esc_attr($user->last_name) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Телефон</label></th>
                <td><input type="text" name="phone" value="' . esc_attr($phone) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Название магазина</label></th>
                <td><input type="text" name="shop_name" value="' . esc_attr($shop_name) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Страна</label></th>
                <td><input type="text" name="country" value="' . esc_attr($country) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Город</label></th>
                <td><input type="text" name="city" value="' . esc_attr($city) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Адрес магазина</label></th>
                <td><input type="text" name="shop_address" value="' . esc_attr($shop_address) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Сайт</label></th>
                <td><input type="url" name="website" value="' . esc_attr($website) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Соцсети</label></th>
                <td><input type="text" name="social" value="' . esc_attr($social) . '" class="regular-text"></td>
            </tr>
            <tr>
                <th><label>Статус аккаунта</label></th>
                <td>
                    <select name="account_status">
                        <option value="pending" ' . selected($account_status, 'pending', false) . '>Ожидает одобрения</option>
                        <option value="approved" ' . selected($account_status, 'approved', false) . '>Одобрен</option>
                    </select>
                </td>
            </tr>
          </table>';
    
        echo '<p class="submit">
                <input type="submit" name="update_user" class="button button-primary" value="Сохранить изменения">
                <a href="' . admin_url('admin.php?page=user_moderation') . '" class="button">Отмена</a>
              </p>';
        echo '</form>';
    }
    
    // Таб: Смена пароля
    if ($current_tab === 'password') {
        echo '<form method="post">';
        wp_nonce_field('change_password_' . $user->ID, 'user_password_nonce');
        echo '<input type="hidden" name="user_id" value="' . esc_attr($user->ID) . '">';
        
        echo '<table class="form-table">
                <tr>
                    <th><label>Новый пароль</label></th>
                    <td>
                        <input type="password" name="new_password" class="regular-text" minlength="6" required>
                        <p class="description">Минимум 6 символов</p>
                    </td>
                </tr>
                <tr>
                    <th><label>Подтвердите пароль</label></th>
                    <td>
                        <input type="password" name="confirm_password" class="regular-text" minlength="6" required>
                    </td>
                </tr>
              </table>';
        
        echo '<p class="submit">
                <input type="submit" name="change_password" class="button button-primary" value="Изменить пароль">
                <a href="' . admin_url('admin.php?page=user_moderation') . '" class="button">Отмена</a>
              </p>';
        echo '</form>';
        
        // JavaScript для проверки совпадения паролей
        echo '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const form = document.querySelector("form[method=\'post\']");
                    if (form && form.querySelector("[name=\'change_password\']")) {
                        form.addEventListener("submit", function(e) {
                            const newPass = form.querySelector("[name=\'new_password\']").value;
                            const confirmPass = form.querySelector("[name=\'confirm_password\']").value;
                            if (newPass !== confirmPass) {
                                e.preventDefault();
                                alert("Пароли не совпадают");
                                return false;
                            }
                        });
                    }
                });
              </script>';
    }
    
    // Таб: Избранное
    if ($current_tab === 'favorites') {
        render_user_favorites($user);
    }
    
    // Таб: Заказы
    if ($current_tab === 'orders') {
        render_user_orders($user);
    }

    // Таб: Магазины (user_stores)
    if ($current_tab === 'stores') {
        // Сохранение магазинов из формы админа
        if (isset($_POST['update_user_stores']) && isset($_POST['user_id']) && wp_verify_nonce($_POST['user_stores_nonce'], 'edit_user_stores_' . intval($_POST['user_id']))) {
            $user_id = intval($_POST['user_id']);

            $names        = isset($_POST['stores_name']) ? (array) $_POST['stores_name'] : array();
            $addresses    = isset($_POST['stores_address']) ? (array) $_POST['stores_address'] : array();
            $phones       = isset($_POST['stores_phone']) ? (array) $_POST['stores_phone'] : array();
            $emails       = isset($_POST['stores_email']) ? (array) $_POST['stores_email'] : array();
            $websites     = isset($_POST['stores_website']) ? (array) $_POST['stores_website'] : array();
            $socials      = isset($_POST['stores_socials']) ? (array) $_POST['stores_socials'] : array();
            $descriptions = isset($_POST['stores_description']) ? (array) $_POST['stores_description'] : array();
            $lats         = isset($_POST['stores_lat']) ? (array) $_POST['stores_lat'] : array();
            $lngs         = isset($_POST['stores_lng']) ? (array) $_POST['stores_lng'] : array();
            $deletes      = isset($_POST['stores_delete']) ? (array) $_POST['stores_delete'] : array();

            $clean_stores = array();
            $max_count    = max(
                count($names),
                count($addresses),
                count($phones),
                count($emails),
                count($websites),
                count($socials),
                count($descriptions),
                count($lats),
                count($lngs)
            );

            for ($i = 0; $i < $max_count; $i++) {
                $name    = isset($names[$i]) ? sanitize_text_field($names[$i]) : '';
                $address = isset($addresses[$i]) ? sanitize_text_field($addresses[$i]) : '';

                // Пропускаем, если помечен на удаление
                $is_delete = isset($deletes[$i]) && $deletes[$i] === '1';
                if ($is_delete) {
                    continue;
                }

                // Пропускаем пустые строки (нет названия и адреса)
                if ($name === '' && $address === '') {
                    continue;
                }

                $lat = isset($lats[$i]) ? sanitize_text_field($lats[$i]) : '';
                $lng = isset($lngs[$i]) ? sanitize_text_field($lngs[$i]) : '';

                // Валидация координат
                $lat_float = floatval(str_replace(',', '.', $lat));
                $lng_float = floatval(str_replace(',', '.', $lng));
                if ($lat !== '' && $lng !== '' && (abs($lat_float) > 90 || abs($lng_float) > 180)) {
                    $lat = '';
                    $lng = '';
                }

                $clean_stores[] = array(
                    'name'        => $name,
                    'address'     => $address,
                    'phone'       => isset($phones[$i]) ? sanitize_text_field($phones[$i]) : '',
                    'email'       => isset($emails[$i]) ? sanitize_email($emails[$i]) : '',
                    'website'     => isset($websites[$i]) ? esc_url_raw($websites[$i]) : '',
                    'socials'     => isset($socials[$i]) ? sanitize_textarea_field($socials[$i]) : '',
                    'lat'         => $lat,
                    'lng'         => $lng,
                    'description' => isset($descriptions[$i]) ? sanitize_textarea_field($descriptions[$i]) : '',
                );
            }

            update_user_meta($user_id, 'user_stores', $clean_stores);
            echo '<div class="notice notice-success"><p>Магазины пользователя обновлены.</p></div>';

            // Обновляем локальную переменную, чтобы отобразить актуальные данные
            $user_stores = $clean_stores;
        }

        // Отрисовка формы магазинов
        echo '<form method="post" style="margin-top: 20px;">';
        wp_nonce_field('edit_user_stores_' . $user->ID, 'user_stores_nonce');
        echo '<input type="hidden" name="user_id" value="' . esc_attr($user->ID) . '">';

        echo '<h3>Магазины пользователя</h3>';
        echo '<p style="color:#666; max-width: 600px;">Здесь можно просмотреть и отредактировать магазины пользователя, которые используются на странице «Где купить».</p>';

        // Готовим данные для вывода: существующие магазины + один пустой
        $stores_for_form = $user_stores;
        $stores_for_form[] = array(
            'name'        => '',
            'address'     => '',
            'phone'       => '',
            'email'       => '',
            'website'     => '',
            'socials'     => '',
            'lat'         => '',
            'lng'         => '',
            'description' => '',
        );

        echo '<style>
            .stores-admin-table { width: 100%; table-layout: fixed; }
            .stores-admin-table th { padding: 10px 8px; font-weight: 600; }
            .stores-admin-table td { padding: 10px 8px; vertical-align: top; }
            .stores-admin-table input[type="text"],
            .stores-admin-table input[type="email"],
            .stores-admin-table input[type="url"] { width: 100%; max-width: 100%; box-sizing: border-box; margin-bottom: 5px; }
            .stores-admin-table textarea { width: 100%; max-width: 100%; box-sizing: border-box; margin-bottom: 5px; resize: vertical; }
            .stores-admin-table .description { font-size: 11px; color: #666; margin: 5px 0 0 0; line-height: 1.4; }
            .stores-admin-table .store-number { text-align: center; font-weight: 600; }
            .stores-admin-table .store-delete { text-align: center; vertical-align: middle; }
        </style>';
        
        echo '<table class="widefat fixed striped stores-admin-table" style="margin-top: 15px;">';
        echo '<thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 15%;">Название</th>
                    <th style="width: 20%;">Адрес</th>
                    <th style="width: 18%;">Контакты</th>
                    <th style="width: 15%;">Координаты</th>
                    <th style="width: 24%;">Описание / Соцсети</th>
                    <th style="width: 5%;">Удалить</th>
                </tr>
              </thead>';
        echo '<tbody>';

        foreach ($stores_for_form as $idx => $store) {
            $is_last_empty = ($idx === count($stores_for_form) - 1) && empty($store['name']) && empty($store['address']);
            echo '<tr>';
            echo '<td class="store-number">' . ($idx + 1) . '</td>';

            // Название
            echo '<td>';
            echo '<input type="text" name="stores_name[]" value="' . esc_attr(isset($store['name']) ? $store['name'] : '') . '" class="regular-text" placeholder="Название магазина">';
            echo '</td>';

            // Адрес
            echo '<td>';
            echo '<input type="text" name="stores_address[]" value="' . esc_attr(isset($store['address']) ? $store['address'] : '') . '" class="regular-text" placeholder="Адрес">';
            echo '</td>';

            // Контакты: телефон / email / сайт
            $phone_val   = isset($store['phone']) ? $store['phone'] : '';
            $email_val   = isset($store['email']) ? $store['email'] : '';
            $website_val = isset($store['website']) ? $store['website'] : '';
            echo '<td>';
            echo '<input type="text" name="stores_phone[]" value="' . esc_attr($phone_val) . '" class="regular-text" placeholder="Телефон" style="margin-bottom: 5px;">';
            echo '<input type="email" name="stores_email[]" value="' . esc_attr($email_val) . '" class="regular-text" placeholder="Email" style="margin-bottom: 5px;">';
            echo '<input type="url" name="stores_website[]" value="' . esc_attr($website_val) . '" class="regular-text" placeholder="Сайт">';
            echo '</td>';

            // Координаты
            $lat_val = isset($store['lat']) ? $store['lat'] : '';
            $lng_val = isset($store['lng']) ? $store['lng'] : '';
            echo '<td>';
            echo '<input type="text" name="stores_lat[]" value="' . esc_attr($lat_val) . '" class="regular-text" placeholder="Широта" style="margin-bottom: 5px;">';
            echo '<input type="text" name="stores_lng[]" value="' . esc_attr($lng_val) . '" class="regular-text" placeholder="Долгота" style="margin-bottom: 5px;">';
            echo '<p class="description">Формат: 55.402727, 43.823609</p>';
            echo '</td>';

            // Описание и соцсети
            $desc_val   = isset($store['description']) ? $store['description'] : '';
            $socials_val = isset($store['socials']) ? $store['socials'] : '';
            echo '<td>';
            echo '<textarea name="stores_description[]" rows="2" class="large-text" placeholder="Описание магазина" style="margin-bottom: 5px;">' . esc_textarea($desc_val) . '</textarea>';
            echo '<textarea name="stores_socials[]" rows="2" class="large-text" placeholder="Соцсети (одна в строке)">' . esc_textarea($socials_val) . '</textarea>';
            echo '</td>';

            // Чекбокс удаления (не показываем для последней пустой строки)
            echo '<td class="store-delete">';
            if (!$is_last_empty) {
                echo '<label style="display: block; text-align: center;"><input type="checkbox" name="stores_delete[' . $idx . ']" value="1"> Удалить</label>';
            } else {
                echo '&mdash;';
            }
            echo '</td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';

        echo '<p class="submit" style="margin-top: 15px;">
                <input type="submit" name="update_user_stores" class="button button-primary" value="Сохранить магазины">
                <a href="' . admin_url('admin.php?page=user_moderation') . '" class="button">Назад к списку</a>
              </p>';

        echo '</form>';
    }
    
    echo '</div>';
}

// ======================================================================
// 5. Отображение избранного пользователя
// ======================================================================
function render_user_favorites($user) {
    $favorite_ids = get_user_meta($user->ID, 'favorite_models', true);
    if (!is_array($favorite_ids) || empty($favorite_ids)) {
        echo '<div style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
        echo '<h2>Избранное пользователя: ' . esc_html($user->user_email) . '</h2>';
        echo '<p>У пользователя нет избранных товаров.</p>';
        echo '<a href="' . admin_url('admin.php?page=user_moderation') . '" class="button">← Назад к списку</a>';
        echo '</div>';
        return;
    }
    
    $favorites = get_posts([
        'post__in' => $favorite_ids,
        'post_type' => 'post',
        'posts_per_page' => -1,
        'orderby' => 'post__in',
    ]);
    
    echo '<div style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
    echo '<h2>Избранное пользователя: ' . esc_html($user->user_email) . '</h2>';
    echo '<p style="color: #666; margin-bottom: 20px;">Всего избранных товаров: <strong>' . count($favorites) . '</strong></p>';
    echo '<a href="' . admin_url('admin.php?page=user_moderation') . '" class="button" style="margin-bottom: 20px;">← Назад к списку</a>';
    
    echo '<table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Изображение</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Параметры</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($favorites as $favorite) {
        // Получаем цену и параметры из ACF
        $slides = get_field('model_slides', $favorite->ID);
        $price = null;
        $parameters = array();
        
        if ($slides && is_array($slides) && !empty($slides) && isset($slides[0])) {
            $first_slide = $slides[0];
            if (isset($first_slide['price']) && !empty($first_slide['price'])) {
                $price = $first_slide['price'];
            }
            if (isset($first_slide['parameters']) && is_array($first_slide['parameters']) && !empty($first_slide['parameters'])) {
                $parameters = $first_slide['parameters'];
            }
        }
        
        // Fallback на старые поля
        if (!$price) {
            $price = get_post_meta($favorite->ID, '_model_price', true);
        }
        
        // Получаем изображение
        $thumbnail = get_template_directory_uri() . '/assets/images/c1.png';
        if ($slides && is_array($slides) && !empty($slides)) {
            if (isset($slides[0]['gallery']) && is_array($slides[0]['gallery']) && !empty($slides[0]['gallery'])) {
                $first_image = $slides[0]['gallery'][0];
                if (isset($first_image['url'])) {
                    $thumbnail = $first_image['url'];
                }
            }
        }
        if ($thumbnail === get_template_directory_uri() . '/assets/images/c1.png' && has_post_thumbnail($favorite->ID)) {
            $thumbnail = get_the_post_thumbnail_url($favorite->ID, 'thumbnail');
        }
        
        // Формируем строку параметров
        $params_html = '';
        if (!empty($parameters)) {
            $params_list = array();
            foreach ($parameters as $param) {
                $param_name = isset($param['name']) ? $param['name'] : '';
                $param_value = isset($param['value']) ? $param['value'] : '';
                if (!empty($param_name) && !empty($param_value)) {
                    $params_list[] = esc_html($param_name) . ': ' . esc_html($param_value);
                }
            }
            $params_html = !empty($params_list) ? implode('<br>', $params_list) : '-';
        } else {
            $params_html = '-';
        }
        
        echo "<tr>
                <td>{$favorite->ID}</td>
                <td><img src='" . esc_url($thumbnail) . "' style='width: 80px; height: 80px; object-fit: cover; border-radius: 4px;'></td>
                <td><strong><a href='" . esc_url(get_permalink($favorite->ID)) . "' target='_blank'>" . esc_html($favorite->post_title) . "</a></strong></td>
                <td>" . ($price ? '<strong>' . number_format($price, 0, ',', ' ') . ' ₽</strong>' : '-') . "</td>
                <td style='font-size: 12px; color: #666;'>" . $params_html . "</td>
                <td><a href='" . esc_url(get_permalink($favorite->ID)) . "' target='_blank' class='button button-small'>Просмотр</a></td>
               </tr>";
    }
    
    echo '</tbody></table>';
    echo '</div>';
}

// ======================================================================
// 6. Отображение заказов пользователя (для админа)
// ======================================================================
function render_user_orders($user) {
    // Обработка изменения статуса заказа
    if (isset($_POST['update_order_status']) && isset($_POST['order_id']) && isset($_POST['new_status']) && wp_verify_nonce($_POST['order_status_nonce'], 'update_order_status_' . sanitize_text_field($_POST['order_id']))) {
        $order_id = sanitize_text_field($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        
        $orders = get_user_meta($user->ID, 'user_orders', true);
        if (is_array($orders) && isset($orders[$order_id])) {
            $orders[$order_id]['status'] = $new_status;
            $orders[$order_id]['updated_at'] = current_time('mysql');
            update_user_meta($user->ID, 'user_orders', $orders);
            echo '<div class="notice notice-success"><p>Статус заказа обновлен.</p></div>';
        }
    }
    
    $orders = get_user_meta($user->ID, 'user_orders', true);
    if (!is_array($orders) || empty($orders)) {
        echo '<div style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
        echo '<h2>Заказы пользователя: ' . esc_html($user->user_email) . '</h2>';
        echo '<p>У пользователя нет заказов.</p>';
        echo '<a href="' . admin_url('admin.php?page=user_moderation') . '" class="button">← Назад к списку</a>';
        echo '</div>';
        return;
    }
    
    // Сортируем заказы по дате (новые первые)
    uasort($orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    echo '<div style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">';
    echo '<h2>Заказы пользователя: ' . esc_html($user->user_email) . '</h2>';
    echo '<p style="color: #666; margin-bottom: 20px;">Всего заказов: <strong>' . count($orders) . '</strong></p>';
    echo '<a href="' . admin_url('admin.php?page=user_moderation') . '" class="button" style="margin-bottom: 20px;">← Назад к списку</a>';
    
    echo '<table class="widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Номер заказа</th>
                    <th>Дата создания</th>
                    <th>Товары</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>';
    
    $status_labels = array(
        'pending' => 'Ожидает',
        'processing' => 'В работе',
        'completed' => 'Завершен',
        'cancelled' => 'Отменен'
    );
    
    $status_colors = array(
        'pending' => '#ff9800',
        'processing' => '#2196f3',
        'completed' => '#4caf50',
        'cancelled' => '#f44336'
    );
    
    foreach ($orders as $order_id => $order) {
        $status = isset($order['status']) ? $order['status'] : 'pending';
        $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
        $status_color = isset($status_colors[$status]) ? $status_colors[$status] : '#666';
        
        $date = new DateTime($order['created_at']);
        $formatted_date = $date->format('d.m.Y H:i');
        
        $items_count = isset($order['items']) ? count($order['items']) : 0;
        $items_list = '';
        if (isset($order['items']) && is_array($order['items'])) {
            $items_titles = array();
            foreach ($order['items'] as $item) {
                $items_titles[] = esc_html($item['title']);
            }
            $items_list = implode(', ', array_slice($items_titles, 0, 3));
            if (count($items_titles) > 3) {
                $items_list .= ' и еще ' . (count($items_titles) - 3);
            }
        }
        
        $total_price = isset($order['total_price']) ? floatval($order['total_price']) : 0;
        
        echo '<tr>';
        echo '<td><strong>' . esc_html($order_id) . '</strong></td>';
        echo '<td>' . esc_html($formatted_date) . '</td>';
        echo '<td>';
        echo '<div style="max-width: 300px;">';
        echo '<strong>' . $items_count . ' товар(ов)</strong><br>';
        if ($items_list) {
            echo '<span style="font-size: 12px; color: #666;">' . $items_list . '</span>';
        }
        echo '</div>';
        echo '</td>';
        echo '<td><strong>' . number_format($total_price, 0, ',', ' ') . ' ₽</strong></td>';
        echo '<td>';
        echo '<span style="padding: 4px 12px; border-radius: 4px; background: ' . $status_color . '; color: #fff; font-size: 12px; font-weight: 500;">' . esc_html($status_label) . '</span>';
        echo '</td>';
        echo '<td>';
        echo '<form method="post" style="display: inline-block; margin-right: 5px;">';
        wp_nonce_field('update_order_status_' . $order_id, 'order_status_nonce');
        echo '<input type="hidden" name="order_id" value="' . esc_attr($order_id) . '">';
        echo '<select name="new_status" style="margin-right: 5px;">';
        foreach ($status_labels as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($status, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<button type="submit" name="update_order_status" class="button button-small">Изменить</button>';
        echo '</form>';
        echo '<button type="button" class="button button-small view-order-details" data-order-id="' . esc_attr($order_id) . '" style="margin-left: 5px;">Детали</button>';
        echo '</td>';
        echo '</tr>';
        
        // Детали заказа (скрытый блок)
        echo '<tr class="order-details-row" id="order-details-' . esc_attr($order_id) . '" style="display: none;">';
        echo '<td colspan="6" style="background: #f9f9f9; padding: 20px;">';
        echo '<h3 style="margin-top: 0;">Детали заказа: ' . esc_html($order_id) . '</h3>';
        if (isset($order['items']) && is_array($order['items'])) {
            echo '<table class="widefat" style="margin-top: 10px;">';
            echo '<thead><tr><th>Название</th><th>Цена</th><th>Наличие</th><th>Параметры</th></tr></thead>';
            echo '<tbody>';
            foreach ($order['items'] as $item) {
                $item_in_stock = isset($item['in_stock']) ? $item['in_stock'] : true;
                echo '<tr>';
                echo '<td><strong>' . esc_html($item['title']) . '</strong></td>';
                echo '<td>' . number_format(floatval($item['price']), 0, ',', ' ') . ' ₽</td>';
                echo '<td>';
                echo '<span style="color: ' . ($item_in_stock ? '#4caf50' : '#f44336') . '; font-weight: 500;">';
                echo $item_in_stock ? '✓ В наличии' : '✗ Нет в наличии';
                echo '</span>';
                echo '</td>';
                echo '<td>';
                if (isset($item['parameters']) && is_array($item['parameters'])) {
                    $params = array();
                    foreach ($item['parameters'] as $param) {
                        $param_name = isset($param['name']) ? $param['name'] : '';
                        $param_value = isset($param['value']) ? $param['value'] : '';
                        if (!empty($param_name) && !empty($param_value)) {
                            $params[] = esc_html($param_name) . ': ' . esc_html($param_value);
                        }
                    }
                    echo !empty($params) ? implode('<br>', $params) : '-';
                } else {
                    echo '-';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
    
    // JavaScript для показа/скрытия деталей заказа
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const detailButtons = document.querySelectorAll(".view-order-details");
            detailButtons.forEach(function(btn) {
                btn.addEventListener("click", function() {
                    const orderId = this.getAttribute("data-order-id");
                    const detailsRow = document.getElementById("order-details-" + orderId);
                    if (detailsRow) {
                        if (detailsRow.style.display === "none") {
                            detailsRow.style.display = "table-row";
                            this.textContent = "Скрыть";
                        } else {
                            detailsRow.style.display = "none";
                            this.textContent = "Детали";
                        }
                    }
                });
            });
        });
    </script>';
}

// ======================================================================
// 7. ADMIN: страница всех заказов
// ======================================================================
add_action('admin_menu', function() {
    add_menu_page(
        'Все заказы',              // Название
        'Заказы',                  // Название в меню
        'manage_options',          // Права доступа
        'all-orders',              // Слаг страницы
        'render_all_orders_page',  // Функция вывода
        'dashicons-cart',          // Иконка
        61                         // Позиция (после Модерации)
    );
});

function render_all_orders_page() {
    // Обработка изменения статуса заказа
    if (isset($_POST['update_order_status']) && isset($_POST['order_id']) && isset($_POST['user_id']) && isset($_POST['new_status']) && wp_verify_nonce($_POST['order_status_nonce'], 'update_order_status_' . sanitize_text_field($_POST['order_id']))) {
        $order_id = sanitize_text_field($_POST['order_id']);
        $user_id = intval($_POST['user_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        
        $allowed_statuses = array('pending', 'processing', 'completed', 'cancelled');
        if (in_array($new_status, $allowed_statuses)) {
            $orders = get_user_meta($user_id, 'user_orders', true);
            if (is_array($orders) && isset($orders[$order_id])) {
                $orders[$order_id]['status'] = $new_status;
                $orders[$order_id]['updated_at'] = current_time('mysql');
                update_user_meta($user_id, 'user_orders', $orders);
                echo '<div class="notice notice-success"><p>Статус заказа обновлен.</p></div>';
            }
        }
    }
    
    // Собираем все заказы из всех пользователей
    $all_orders = array();
    $users = get_users(array('role__not_in' => array('administrator')));
    
    foreach ($users as $user) {
        $user_orders = get_user_meta($user->ID, 'user_orders', true);
        if (is_array($user_orders) && !empty($user_orders)) {
            foreach ($user_orders as $order_id => $order) {
                $order['user_id'] = $user->ID;
                $order['user_email'] = $user->user_email;
                $order['user_name'] = trim($user->first_name . ' ' . $user->last_name);
                $order['order_id'] = $order_id;
                $all_orders[] = $order;
            }
        }
    }
    
    // Сортируем по дате создания (новые первые)
    usort($all_orders, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Фильтрация по статусу
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    if ($status_filter && in_array($status_filter, array('pending', 'processing', 'completed', 'cancelled'))) {
        $all_orders = array_filter($all_orders, function($order) use ($status_filter) {
            return isset($order['status']) && $order['status'] === $status_filter;
        });
    }
    
    echo '<div class="wrap">';
    echo '<h1>Все заказы</h1>';
    
    // Статистика
    $stats = array(
        'total' => count($all_orders),
        'pending' => 0,
        'processing' => 0,
        'completed' => 0,
        'cancelled' => 0
    );
    
    $all_orders_for_stats = array();
    foreach ($users as $user) {
        $user_orders = get_user_meta($user->ID, 'user_orders', true);
        if (is_array($user_orders) && !empty($user_orders)) {
            foreach ($user_orders as $order) {
                $all_orders_for_stats[] = $order;
            }
        }
    }
    
    foreach ($all_orders_for_stats as $order) {
        $status = isset($order['status']) ? $order['status'] : 'pending';
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }
    
    echo '<div style="margin: 20px 0; display: flex; gap: 15px; flex-wrap: wrap;">';
    echo '<a href="' . admin_url('admin.php?page=all-orders') . '" class="button ' . ($status_filter === '' ? 'button-primary' : '') . '">Все (' . $stats['total'] . ')</a>';
    echo '<a href="' . admin_url('admin.php?page=all-orders&status=pending') . '" class="button ' . ($status_filter === 'pending' ? 'button-primary' : '') . '">Ожидает (' . $stats['pending'] . ')</a>';
    echo '<a href="' . admin_url('admin.php?page=all-orders&status=processing') . '" class="button ' . ($status_filter === 'processing' ? 'button-primary' : '') . '">В работе (' . $stats['processing'] . ')</a>';
    echo '<a href="' . admin_url('admin.php?page=all-orders&status=completed') . '" class="button ' . ($status_filter === 'completed' ? 'button-primary' : '') . '">Завершен (' . $stats['completed'] . ')</a>';
    echo '<a href="' . admin_url('admin.php?page=all-orders&status=cancelled') . '" class="button ' . ($status_filter === 'cancelled' ? 'button-primary' : '') . '">Отменен (' . $stats['cancelled'] . ')</a>';
    echo '</div>';
    
    if (empty($all_orders)) {
        echo '<p>Заказов не найдено.</p>';
        echo '</div>';
        return;
    }
    
    echo '<table class="widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>Номер заказа</th>
                    <th>Пользователь</th>
                    <th>Дата создания</th>
                    <th>Товары</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>';
    
    $status_labels = array(
        'pending' => 'Ожидает',
        'processing' => 'В работе',
        'completed' => 'Завершен',
        'cancelled' => 'Отменен'
    );
    
    $status_colors = array(
        'pending' => '#ff9800',
        'processing' => '#2196f3',
        'completed' => '#4caf50',
        'cancelled' => '#f44336'
    );
    
    foreach ($all_orders as $order) {
        $status = isset($order['status']) ? $order['status'] : 'pending';
        $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
        $status_color = isset($status_colors[$status]) ? $status_colors[$status] : '#666';
        
        $date = new DateTime($order['created_at']);
        $formatted_date = $date->format('d.m.Y H:i');
        
        $items_count = isset($order['items']) ? count($order['items']) : 0;
        $items_list = '';
        if (isset($order['items']) && is_array($order['items'])) {
            $items_titles = array();
            foreach ($order['items'] as $item) {
                $items_titles[] = esc_html($item['title']);
            }
            $items_list = implode(', ', array_slice($items_titles, 0, 2));
            if (count($items_titles) > 2) {
                $items_list .= ' и еще ' . (count($items_titles) - 2);
            }
        }
        
        $total_price = isset($order['total_price']) ? floatval($order['total_price']) : 0;
        $user_name = !empty($order['user_name']) ? $order['user_name'] : $order['user_email'];
        
        echo '<tr>';
        echo '<td><strong>' . esc_html($order['order_id']) . '</strong></td>';
        echo '<td>';
        echo '<div>';
        echo '<strong>' . esc_html($user_name) . '</strong><br>';
        echo '<span style="font-size: 12px; color: #666;">' . esc_html($order['user_email']) . '</span>';
        echo '</div>';
        echo '</td>';
        echo '<td>' . esc_html($formatted_date) . '</td>';
        echo '<td>';
        echo '<div style="max-width: 300px;">';
        echo '<strong>' . $items_count . ' товар(ов)</strong><br>';
        if ($items_list) {
            echo '<span style="font-size: 12px; color: #666;">' . $items_list . '</span>';
        }
        echo '</div>';
        echo '</td>';
        echo '<td><strong>' . number_format($total_price, 0, ',', ' ') . ' ₽</strong></td>';
        echo '<td>';
        echo '<span style="padding: 4px 12px; border-radius: 4px; background: ' . $status_color . '; color: #fff; font-size: 12px; font-weight: 500;">' . esc_html($status_label) . '</span>';
        echo '</td>';
        echo '<td>';
        echo '<form method="post" style="display: inline-block; margin-right: 5px;">';
        wp_nonce_field('update_order_status_' . $order['order_id'], 'order_status_nonce');
        echo '<input type="hidden" name="order_id" value="' . esc_attr($order['order_id']) . '">';
        echo '<input type="hidden" name="user_id" value="' . esc_attr($order['user_id']) . '">';
        echo '<select name="new_status" style="margin-right: 5px;">';
        foreach ($status_labels as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected($status, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<button type="submit" name="update_order_status" class="button button-small">Изменить</button>';
        echo '</form>';
        echo '<button type="button" class="button button-small view-order-details" data-order-id="' . esc_attr($order['order_id']) . '" style="margin-left: 5px;">Детали</button>';
        echo '</td>';
        echo '</tr>';
        
        // Детали заказа (скрытый блок)
        echo '<tr class="order-details-row" id="order-details-' . esc_attr($order['order_id']) . '" style="display: none;">';
        echo '<td colspan="7" style="background: #f9f9f9; padding: 20px;">';
        echo '<h3 style="margin-top: 0;">Детали заказа: ' . esc_html($order['order_id']) . '</h3>';
        echo '<div style="margin-bottom: 15px;">';
        echo '<strong>Пользователь:</strong> ' . esc_html($user_name) . ' (' . esc_html($order['user_email']) . ')<br>';
        echo '<strong>Дата создания:</strong> ' . esc_html($formatted_date) . '<br>';
        if (isset($order['updated_at'])) {
            $updated_date = new DateTime($order['updated_at']);
            echo '<strong>Последнее обновление:</strong> ' . esc_html($updated_date->format('d.m.Y H:i')) . '<br>';
        }
        echo '</div>';
        if (isset($order['items']) && is_array($order['items'])) {
            echo '<table class="widefat" style="margin-top: 10px;">';
            echo '<thead><tr><th>Название</th><th>Цена</th><th>Наличие</th><th>Параметры</th></tr></thead>';
            echo '<tbody>';
            foreach ($order['items'] as $item) {
                $item_in_stock = isset($item['in_stock']) ? $item['in_stock'] : true;
                echo '<tr>';
                echo '<td><strong>' . esc_html($item['title']) . '</strong></td>';
                echo '<td>' . number_format(floatval($item['price']), 0, ',', ' ') . ' ₽</td>';
                echo '<td>';
                echo '<span style="color: ' . ($item_in_stock ? '#4caf50' : '#f44336') . '; font-weight: 500;">';
                echo $item_in_stock ? '✓ В наличии' : '✗ Нет в наличии';
                echo '</span>';
                echo '</td>';
                echo '<td>';
                if (isset($item['parameters']) && is_array($item['parameters'])) {
                    $params = array();
                    foreach ($item['parameters'] as $param) {
                        $param_name = isset($param['name']) ? $param['name'] : '';
                        $param_value = isset($param['value']) ? $param['value'] : '';
                        if (!empty($param_name) && !empty($param_value)) {
                            $params[] = esc_html($param_name) . ': ' . esc_html($param_value);
                        }
                    }
                    echo !empty($params) ? implode('<br>', $params) : '-';
                } else {
                    echo '-';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '<tfoot>';
            echo '<tr><th colspan="3" style="text-align: right;">Итого:</th><th>' . number_format($total_price, 0, ',', ' ') . ' ₽</th></tr>';
            echo '</tfoot>';
            echo '</table>';
        }
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
    
    // JavaScript для показа/скрытия деталей заказа
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const detailButtons = document.querySelectorAll(".view-order-details");
            detailButtons.forEach(function(btn) {
                btn.addEventListener("click", function() {
                    const orderId = this.getAttribute("data-order-id");
                    const detailsRow = document.getElementById("order-details-" + orderId);
                    if (detailsRow) {
                        if (detailsRow.style.display === "none") {
                            detailsRow.style.display = "table-row";
                            this.textContent = "Скрыть";
                        } else {
                            detailsRow.style.display = "none";
                            this.textContent = "Детали";
                        }
                    }
                });
            });
        });
    </script>';
}

// ======================================================================
// 8. ADMIN: страница заявок на регистрацию
// ======================================================================
add_action('admin_menu', function() {
    add_menu_page(
            'Заявки',
            'Регистрация',
            'manage_options',
            'pending-users',
            'pending_users_page'
    );
});

function pending_users_page() {
    // Обработка одобрения пользователя
    if (isset($_GET['approve']) && isset($_GET['_wpnonce'])) {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'approve_user_' . intval($_GET['approve']))) {
            wp_die('Ошибка безопасности');
        }
        $uid = intval($_GET['approve']);
        if ($uid > 0) {
            update_user_meta($uid, 'account_status', 'approved');
            wp_redirect(admin_url('admin.php?page=pending-users&updated=approved'));
            exit;
        }
    }

    // Обработка отклонения пользователя
    if (isset($_GET['deny']) && isset($_GET['_wpnonce'])) {
        if (!wp_verify_nonce($_GET['_wpnonce'], 'deny_user_' . intval($_GET['deny']))) {
            wp_die('Ошибка безопасности');
        }
        $uid = intval($_GET['deny']);
        if ($uid > 0 && $uid != get_current_user_id()) {
            wp_delete_user($uid);
            wp_redirect(admin_url('admin.php?page=pending-users&updated=denied'));
            exit;
        }
    }

    $users = get_users([
            'meta_key' => 'account_status',
            'meta_value' => 'pending'
    ]);

    echo '<div class="wrap">';
    echo '<h1>Заявки на регистрацию</h1>';

    // Сообщения об успешных действиях
    if (isset($_GET['updated'])) {
        if ($_GET['updated'] === 'approved') {
            echo '<div class="notice notice-success is-dismissible"><p>Пользователь одобрен.</p></div>';
        } elseif ($_GET['updated'] === 'denied') {
            echo '<div class="notice notice-success is-dismissible"><p>Пользователь отклонен.</p></div>';
        }
    }

    if (!$users) {
        echo '<p>Нет заявок на регистрацию.</p>';
        echo '</div>';
        return;
    }

    echo '<table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Страна</th>
                    <th>Город</th>
                    <th>Название магазина</th>
                    <th>Адрес магазина</th>
                    <th>Сайт</th>
                    <th>Соцсети</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($users as $u) {
        $phone       = esc_html(get_user_meta($u->ID, 'phone', true));
        $country     = esc_html(get_user_meta($u->ID, 'country', true));
        $city        = esc_html(get_user_meta($u->ID, 'city', true));
        $address     = esc_html(get_user_meta($u->ID, 'shop_address', true));
        $website     = esc_url(get_user_meta($u->ID, 'website', true));
        $social      = esc_html(get_user_meta($u->ID, 'social', true));
        $shop_name   = esc_html(get_user_meta($u->ID, 'shop_name', true));
        $first_name  = esc_html($u->first_name);
        $last_name   = esc_html($u->last_name);
        $user_email  = esc_html($u->user_email);
        $approve_url = wp_nonce_url(admin_url('admin.php?page=pending-users&approve=' . $u->ID), 'approve_user_' . $u->ID);
        $deny_url    = wp_nonce_url(admin_url('admin.php?page=pending-users&deny=' . $u->ID), 'deny_user_' . $u->ID);

        echo "<tr>
                <td>{$user_email}</td>
                <td>{$first_name} {$last_name}</td>
                <td>{$phone}</td>
                <td>{$country}</td>
                <td>{$city}</td>
                <td>{$shop_name}</td>
                <td>{$address}</td>
                <td>" . ($website ? '<a href="' . $website . '" target="_blank">' . $website . '</a>' : '-') . "</td>
                <td>{$social}</td>
                <td>
                    <a href='" . esc_url($approve_url) . "' class='button button-primary'>Одобрить</a>
                    <a href='" . esc_url($deny_url) . "' class='button' onclick=\"return confirm('Вы уверены, что хотите отклонить этого пользователя?');\">Отклонить</a>
                </td>
               </tr>";
    }

    echo '</tbody></table>';
    echo '</div>';
}
