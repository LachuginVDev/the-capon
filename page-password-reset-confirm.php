<?php
/*
Template Name: Сброс пароля
*/

get_header();

// Получаем параметры из URL
$key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
$login = isset($_GET['login']) ? sanitize_text_field($_GET['login']) : '';

// Проверяем валидность ключа
$user = check_password_reset_key($key, $login);

if (is_wp_error($user)) {
    $error_message = $user->get_error_message();
} else {
    $error_message = '';
}
?>

<div class="container">
    <?php if (is_wp_error($user)): ?>
        <div class="wp-login-form">
            <h2 class="form-title">Ошибка</h2>
            <p style="color: red; text-align: center;"><?php echo esc_html($error_message); ?></p>
            <div class="btn-registers">
                <a href="<?php echo esc_url( home_url( '/войти/' ) ); ?>" class="btn-register">Вернуться к входу</a>
            </div>
        </div>
    <?php else: ?>
        <form class="wp-login-form" id="password-reset-confirm-form">
            <h2 class="form-title">Введите новый пароль</h2>

            <input type="hidden" name="key" value="<?php echo esc_attr($key); ?>">
            <input type="hidden" name="login" value="<?php echo esc_attr($login); ?>">

            <div class="form-field password-wrap">
                <label>Новый пароль *</label>
                <input type="password" name="new_password" placeholder="новый пароль" required minlength="6">
                <button type="button" class="toggle-password">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.94336 4.22991C10.8845 3.99858 12.848 4.40893 14.5341 5.39829C16.2201 6.38764 17.536 7.90164 18.2809 9.70908C18.3503 9.89617 18.3503 10.102 18.2809 10.2891C17.9746 11.0316 17.5699 11.7295 17.0775 12.3641" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.7368 11.7983C11.2653 12.2537 10.6338 12.5057 9.97828 12.5C9.32278 12.4943 8.69575 12.2314 8.23223 11.7679C7.76871 11.3043 7.50579 10.6773 7.50009 10.0218C7.4944 9.36632 7.74638 8.73481 8.20178 8.26331" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M14.5656 14.5825C13.4602 15.2373 12.2268 15.6467 10.9492 15.7829C9.67165 15.919 8.37973 15.7787 7.16113 15.3716C5.94253 14.9644 4.82576 14.2999 3.88661 13.4232C2.94746 12.5464 2.2079 11.4778 1.7181 10.29C1.64865 10.1029 1.64865 9.89712 1.7181 9.71003C2.45696 7.91824 3.757 6.4144 5.4231 5.42419" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M1.66602 1.66675L18.3327 18.3334" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="form-field password-wrap">
                <label>Подтвердите пароль *</label>
                <input type="password" name="confirm_password" placeholder="подтвердите пароль" required minlength="6">
                <button type="button" class="toggle-password">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8.94336 4.22991C10.8845 3.99858 12.848 4.40893 14.5341 5.39829C16.2201 6.38764 17.536 7.90164 18.2809 9.70908C18.3503 9.89617 18.3503 10.102 18.2809 10.2891C17.9746 11.0316 17.5699 11.7295 17.0775 12.3641" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M11.7368 11.7983C11.2653 12.2537 10.6338 12.5057 9.97828 12.5C9.32278 12.4943 8.69575 12.2314 8.23223 11.7679C7.76871 11.3043 7.50579 10.6773 7.50009 10.0218C7.4944 9.36632 7.74638 8.73481 8.20178 8.26331" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M14.5656 14.5825C13.4602 15.2373 12.2268 15.6467 10.9492 15.7829C9.67165 15.919 8.37973 15.7787 7.16113 15.3716C5.94253 14.9644 4.82576 14.2999 3.88661 13.4232C2.94746 12.5464 2.2079 11.4778 1.7181 10.29C1.64865 10.1029 1.64865 9.89712 1.7181 9.71003C2.45696 7.91824 3.757 6.4144 5.4231 5.42419" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M1.66602 1.66675L18.3327 18.3334" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="btn-registers">
                <button type="submit" class="btn-register">Изменить пароль</button>
            </div>

            <?php wp_nonce_field( 'password_reset_confirm_nonce', 'reset_confirm_nonce' ); ?>
        </form>
    <?php endif; ?>
</div>

<?php get_footer(); ?>

