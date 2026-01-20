<?php
/*
Template Name: Восстановление пароля
*/

get_header();
?>

<div class="container">
    <form class="wp-login-form" id="password-reset-form">
        <h2 class="form-title">Восстановление пароля</h2>

        <div class="form-field">
            <label>Email *</label>
            <input type="email" name="user_email" placeholder="email" required>
        </div>

        <div class="btn-registers">
            <button type="submit" class="btn-register">Отправить ссылку</button>

            <p class="has-account">
                Вспомнили пароль?
                <a href="<?php echo esc_url( home_url( '/войти/' ) ); ?>">Войти</a>
            </p>
        </div>

        <?php wp_nonce_field( 'password_reset_nonce', 'reset_nonce' ); ?>
    </form>
</div>

<?php get_footer(); ?>

