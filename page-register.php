<?php
/*
Template Name: Регистрация
*/

get_header();

// --- Разрешаем кириллицу и латиницу в логине ---

?>

<form class="wp-register-form">

    <h2 class="form-title">Создайте Свою учетную запись</h2>

    <div class="form-field">
        <label>Email *</label>
        <input type="email" name="email" placeholder="email" required>
    </div>

    <div class="form-field">
        <label>First Name *</label>
        <input type="text" name="first_name" placeholder="имя" required>
    </div>

    <div class="form-field">
        <label>Last Name *</label>
        <input type="text" name="last_name" placeholder="фамилия" required>
    </div>

    <div class="form-field">
        <label>Phone *</label>
        <input type="text" name="phone" placeholder="телефон" required>
    </div>

    <!-- Пароль -->
    <div class="form-field password-wrap">
        <label>Password *</label>
        <input type="password" name="password" placeholder="пароль" required>
        <button type="button" class="toggle-password">
            <!-- SVG глаз спрятан -->
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.94336 4.22991C10.8845 3.99858 12.848 4.40893 14.5341 5.39829C16.2201 6.38764 17.536 7.90164 18.2809 9.70908C18.3503 9.89617 18.3503 10.102 18.2809 10.2891C17.9746 11.0316 17.5699 11.7295 17.0775 12.3641" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M11.7368 11.7983C11.2653 12.2537 10.6338 12.5057 9.97828 12.5C9.32278 12.4943 8.69575 12.2314 8.23223 11.7679C7.76871 11.3043 7.50579 10.6773 7.50009 10.0218C7.4944 9.36632 7.74638 8.73481 8.20178 8.26331" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M14.5656 14.5825C13.4602 15.2373 12.2268 15.6467 10.9492 15.7829C9.67165 15.919 8.37973 15.7787 7.16113 15.3716C5.94253 14.9644 4.82576 14.2999 3.88661 13.4232C2.94746 12.5464 2.2079 11.4778 1.7181 10.29C1.64865 10.1029 1.64865 9.89712 1.7181 9.71003C2.45696 7.91824 3.757 6.4144 5.4231 5.42419" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M1.66602 1.66675L18.3327 18.3334" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    <!-- Подтверждение пароля -->
    <div class="form-field password-wrap">
        <label>Confirm Password *</label>
        <input type="password" name="password_confirm" placeholder="подтвердите пароль" required>
        <button type="button" class="toggle-password">
            <!-- тот же SVG -->
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.94336 4.22991C10.8845 3.99858 12.848 4.40893 14.5341 5.39829C16.2201 6.38764 17.536 7.90164 18.2809 9.70908C18.3503 9.89617 18.3503 10.102 18.2809 10.2891C17.9746 11.0316 17.5699 11.7295 17.0775 12.3641" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M11.7368 11.7983C11.2653 12.2537 10.6338 12.5057 9.97828 12.5C9.32278 12.4943 8.69575 12.2314 8.23223 11.7679C7.76871 11.3043 7.50579 10.6773 7.50009 10.0218C7.4944 9.36632 7.74638 8.73481 8.20178 8.26331" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M14.5656 14.5825C13.4602 15.2373 12.2268 15.6467 10.9492 15.7829C9.67165 15.919 8.37973 15.7787 7.16113 15.3716C5.94253 14.9644 4.82576 14.2999 3.88661 13.4232C2.94746 12.5464 2.2079 11.4778 1.7181 10.29C1.64865 10.1029 1.64865 9.89712 1.7181 9.71003C2.45696 7.91824 3.757 6.4144 5.4231 5.42419" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M1.66602 1.66675L18.3327 18.3334" stroke="#99A1AF" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>

    <div class="form-field">
        <label>Shop name *</label>
        <input type="text" name="shop_name" placeholder="название магазина" required>
    </div>

    <!-- Страна + город -->
    <div class="form-row">
        <div class="form-field">
            <label>Country *</label>
            <input type="text" name="country" placeholder="страна" required>
        </div>

        <div class="form-field">
            <label>City *</label>
            <input type="text" name="city" placeholder="город" required>
        </div>
    </div>

    <div class="form-field">
        <label>Shop address *</label>
        <input type="text" name="shop_address" placeholder="адрес магазина" required>
    </div>

    <!-- Сайт + соц сети -->
    <div class="form-row">
        <div class="form-field">
            <label>Website</label>
            <input type="text" name="website" placeholder="сайт">
        </div>

        <div class="form-field">
            <label>Social Media</label>
            <input type="text" name="social" placeholder="соц. сети">
        </div>
    </div>

    <div class="btn-registers">
        <button type="submit" class="btn-register">создать аккаунт</button>

        <p class="has-account">
            У вас уже есть учетная запись?
            <a href="<?php echo esc_url( home_url( '/войти/' ) ); ?>">Войти здесь</a>
        </p>
    </div>

    <?php wp_nonce_field( 'custom_register_nonce', 'register_nonce' ); ?>

</form>



<?php get_footer(); ?>
