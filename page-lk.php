<?php
/*
Template Name: Личный кабинет
*/

if (!is_user_logged_in()) {
    wp_redirect(home_url('/войти/'));
    exit;
}

get_header();

$user = wp_get_current_user();

// Получаем избранное
$favorite_ids = get_user_meta($user->ID, 'favorite_models', true);
if (!is_array($favorite_ids)) {
    $favorite_ids = array();
}

// Получаем модели из избранного
$favorites = array();
if (!empty($favorite_ids)) {
    $favorites = get_posts([
        'post__in' => $favorite_ids,
        'post_type' => 'post',
        'posts_per_page' => -1,
        'orderby' => 'post__in', // Сохраняем порядок добавления
    ]);
}
?>

<div class="container">
    <h2 class="form-title">Личный кабинет</h2>

    <?php 
    $account_status = get_user_meta($user->ID, 'account_status', true);
    if ($account_status === 'pending'): 
    ?>
        <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <p style="margin: 0; color: #856404;">Ваш аккаунт ожидает одобрения администратором. После одобрения вам станут доступны все функции.</p>
        </div>
    <?php endif; ?>

    <!-- Табы -->
    <div class="user-account-tabs">
        <button class="user-account-tab active" data-tab="general">Общие</button>
        <button class="user-account-tab" data-tab="orders">Заказы</button>
        <button class="user-account-tab" data-tab="stores">Магазины</button>
        <button class="user-account-tab" data-tab="settings">Настройки аккаунта</button>
    </div>

    <!-- Таб: Общие -->
    <div class="user-account-tab-content active" data-tab="general">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 style="margin: 0;">Избранное</h3>
            <?php if (!empty($favorites)): ?>
                <button class="btn btn-primary create-order-btn" id="create-order-from-favorites">
                    Создать заказ из избранного
                </button>
            <?php endif; ?>
        </div>

    <?php if (!empty($favorites)): ?>
        <div class="favorites-grid">
            <?php foreach ($favorites as $favorite): 
                // Получаем ACF поля из model_slides
                $slides = get_field('model_slides', $favorite->ID);
                
                // Получаем цену из первого слайда
                $price = null;
                $parameters = array();
                
                if ($slides && is_array($slides) && !empty($slides) && isset($slides[0])) {
                    $first_slide = $slides[0];
                    
                    // Цена из первого слайда
                    if (isset($first_slide['price']) && !empty($first_slide['price'])) {
                        $price = $first_slide['price'];
                    }
                    
                    // Параметры из первого слайда
                    if (isset($first_slide['parameters']) && is_array($first_slide['parameters']) && !empty($first_slide['parameters'])) {
                        $parameters = $first_slide['parameters'];
                    }
                }
                
                // Fallback на старые поля для совместимости
                if (!$price) {
                    $price = get_post_meta($favorite->ID, '_model_price', true);
                }
                
                // Получаем изображение из ACF или fallback
                $image_url = get_template_directory_uri() . '/assets/images/c1.png';
                
                if ($slides && is_array($slides) && !empty($slides)) {
                    // Берем первое изображение из галереи первого слайда
                    if (isset($slides[0]['gallery']) && is_array($slides[0]['gallery']) && !empty($slides[0]['gallery'])) {
                        $first_image = $slides[0]['gallery'][0];
                        if (isset($first_image['url'])) {
                            $image_url = $first_image['url'];
                        }
                    }
                }
                
                // Fallback на миниатюру поста
                if ($image_url === get_template_directory_uri() . '/assets/images/c1.png' && has_post_thumbnail($favorite->ID)) {
                    $image_url = get_the_post_thumbnail_url($favorite->ID, 'full');
                }
                
                // Получаем статус наличия
                $in_stock = get_field('model_in_stock', $favorite->ID);
                if ($in_stock === null) {
                    $in_stock = true; // По умолчанию в наличии
                }
            ?>
                <div class="favorite-card" data-model-id="<?php echo esc_attr($favorite->ID); ?>">
                    <a href="<?php echo esc_url(get_permalink($favorite->ID)); ?>" class="favorite-card-link">
                        <div class="favorite-card-image" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
                        <div class="favorite-card-content">
                            <h4 class="favorite-card-title"><?php echo esc_html($favorite->post_title); ?></h4>
                            
                            <div class="favorite-card-stock" style="margin-bottom: 10px;">
                                <span style="color: <?php echo $in_stock ? '#4caf50' : '#f44336'; ?>; font-size: 13px; font-weight: 500;">
                                    <?php echo $in_stock ? '✓ В наличии' : '✗ Нет в наличии'; ?>
                                </span>
                            </div>
                            
                            <?php if ($price): ?>
                                <div class="favorite-card-price">
                                    <?php echo number_format($price, 0, ',', ' '); ?> ₽
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($parameters)): ?>
                                <div class="favorite-card-parameters">
                                    <?php foreach ($parameters as $param): 
                                        $param_name = isset($param['name']) ? $param['name'] : '';
                                        $param_value = isset($param['value']) ? $param['value'] : '';
                                        if (empty($param_name) && empty($param_value)) continue;
                                        ?>
                                        <div class="favorite-card-param">
                                            <?php if (!empty($param_name)): ?>
                                                <strong><?php echo esc_html($param_name); ?>:</strong>
                                            <?php endif; ?>
                                            <span><?php echo esc_html($param_value); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <button class="favorite-card-remove" data-model-id="<?php echo esc_attr($favorite->ID); ?>" title="Удалить из избранного">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center; padding: 40px 0; color: #666;">У вас пока нет избранных моделей.</p>
    <?php endif; ?>
    </div>

    <!-- Таб: Магазины -->
    <div class="user-account-tab-content" data-tab="stores">
        <h3 style="margin-bottom: 20px;">Магазины</h3>
        <p style="margin-bottom: 20px; color: #666; max-width: 600px;">
            Добавьте свои магазины (адрес, контакты). Эти данные будут использоваться на странице «Где купить» и на карте.
        </p>
        <div id="lk-stores-container"></div>
    </div>

    <!-- Таб: Заказы -->
    <div class="user-account-tab-content" data-tab="orders">
        <h3 style="margin-bottom: 30px;">Мои заказы</h3>
        
        <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Номер заказа</th>
                        <th>Дата создания</th>
                        <th>Товары</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody id="orders-table-body">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            <span class="orders-loading">Загрузка заказов...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Таб: Настройки аккаунта -->
    <div class="user-account-tab-content" data-tab="settings">
        <h3 style="margin-bottom: 30px;">Настройки аккаунта</h3>
        
        <form class="account-settings-form" method="post">
            <div class="account-settings-message"></div>
            
            <div class="form-group">
                <label for="user_email">Email *</label>
                <input type="email" id="user_email" name="user_email" value="<?php echo esc_attr($user->user_email); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="first_name">Имя</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>">
            </div>
            
            <div class="form-group">
                <label for="last_name">Фамилия</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>">
            </div>
            
            <div class="form-group">
                <label for="phone">Телефон</label>
                <input type="tel" id="phone" name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>">
            </div>
            
            <div class="form-group">
                <label for="shop_name">Название магазина</label>
                <input type="text" id="shop_name" name="shop_name" value="<?php echo esc_attr(get_user_meta($user->ID, 'shop_name', true)); ?>">
            </div>
            
            <div class="form-group">
                <label for="country">Страна</label>
                <input type="text" id="country" name="country" value="<?php echo esc_attr(get_user_meta($user->ID, 'country', true)); ?>">
            </div>
            
            <div class="form-group">
                <label for="city">Город</label>
                <input type="text" id="city" name="city" value="<?php echo esc_attr(get_user_meta($user->ID, 'city', true)); ?>">
            </div>
            
            <div class="form-group">
                <label for="shop_address">Адрес магазина</label>
                <input type="text" id="shop_address" name="shop_address" value="<?php echo esc_attr(get_user_meta($user->ID, 'shop_address', true)); ?>">
            </div>
            
            <div class="form-group">
                <label for="website">Сайт</label>
                <input type="url" id="website" name="website" value="<?php echo esc_attr(get_user_meta($user->ID, 'website', true)); ?>" placeholder="https://">
            </div>
            
            <div class="form-group">
                <label for="social">Соцсети</label>
                <input type="text" id="social" name="social" value="<?php echo esc_attr(get_user_meta($user->ID, 'social', true)); ?>" placeholder="Instagram, Facebook и т.д.">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="btn btn-logout">Выйти</a>
            </div>
        </form>
    </div>
</div>

<?php get_footer(); ?>
