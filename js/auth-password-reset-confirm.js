/**
 * Обработка формы подтверждения сброса пароля
 */

(function() {
    'use strict';

    function initPasswordResetConfirmForm() {
        const form = document.getElementById('password-reset-confirm-form');
        if (!form) return;

        // Проверка наличия theCaponAjax
        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
            console.error('theCaponAjax не определен');
            return;
        }

        // Обработка переключения видимости пароля
        form.querySelectorAll('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input && input.type === 'password') {
                    input.type = 'text';
                    this.classList.add('is-visible');
                } else if (input) {
                    input.type = 'password';
                    this.classList.remove('is-visible');
                }
            });
        });

        const passwordInput = form.querySelector('[name="new_password"]');
        const confirmPasswordInput = form.querySelector('[name="confirm_password"]');

        // Валидация в реальном времени
        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                validatePassword(this);
                if (confirmPasswordInput && confirmPasswordInput.value) {
                    validatePasswordConfirm(confirmPasswordInput, this.value);
                }
            });
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('blur', function() {
                if (passwordInput) {
                    validatePasswordConfirm(this, passwordInput.value);
                }
            });
        }

        // Обработка отправки формы
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            let isValid = true;

            if (passwordInput && !validatePassword(passwordInput)) {
                isValid = false;
            }

            if (confirmPasswordInput && passwordInput && !validatePasswordConfirm(confirmPasswordInput, passwordInput.value)) {
                isValid = false;
            }

            if (!isValid) {
                if (window.AuthNotifications) {
                    window.AuthNotifications.show('Пожалуйста, исправьте ошибки в форме', 'error');
                }
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);
            formData.append('action', 'reset_password_confirm');

            // Показываем индикатор загрузки
            if (window.AuthNotifications) {
                window.AuthNotifications.setButtonLoading(submitBtn, true);
            }

            try {
                const res = await fetch(theCaponAjax.ajaxurl, {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }

                const json = await res.json();

                if (json.success) {
                    const message = json.data && json.data.message ? json.data.message : 'Пароль успешно изменен';
                    if (window.AuthNotifications) {
                        window.AuthNotifications.show(message, 'success', 3000);
                    }
                    // Перенаправление на страницу входа
                    setTimeout(function() {
                        window.location.href = theCaponAjax.loginUrl || '/войти/';
                    }, 1500);
                } else {
                    const message = json.data && json.data.message ? json.data.message : 'Ошибка сброса пароля';
                    if (window.AuthNotifications && typeof window.AuthNotifications.show === 'function') {
                        window.AuthNotifications.show(message, 'error');
                    } else {
                        alert(message);
                    }
                    if (window.AuthNotifications && typeof window.AuthNotifications.setButtonLoading === 'function') {
                        window.AuthNotifications.setButtonLoading(submitBtn, false);
                    } else if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = submitBtn.dataset.originalText || 'Изменить пароль';
                    }
                }
            } catch (error) {
                console.error('Password reset confirm error:', error);
                const errorMessage = 'Ошибка соединения. Попробуйте позже.';
                if (window.AuthNotifications && typeof window.AuthNotifications.show === 'function') {
                    window.AuthNotifications.show(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
                if (window.AuthNotifications && typeof window.AuthNotifications.setButtonLoading === 'function') {
                    window.AuthNotifications.setButtonLoading(submitBtn, false);
                } else if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || 'Изменить пароль';
                }
            }
        });
    }

    /**
     * Валидация пароля
     */
    function validatePassword(input) {
        const password = input.value;

        removeFieldError(input);

        if (!password) {
            showFieldError(input, 'Пароль обязателен для заполнения');
            return false;
        }

        if (password.length < 6) {
            showFieldError(input, 'Пароль должен содержать минимум 6 символов');
            return false;
        }

        return true;
    }

    /**
     * Валидация подтверждения пароля
     */
    function validatePasswordConfirm(input, password) {
        removeFieldError(input);

        if (!input.value) {
            showFieldError(input, 'Подтвердите пароль');
            return false;
        }

        if (input.value !== password) {
            showFieldError(input, 'Пароли не совпадают');
            return false;
        }

        return true;
    }

    /**
     * Показывает ошибку поля
     */
    function showFieldError(input, message) {
        removeFieldError(input);
        input.classList.add('has-error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        
        const field = input.closest('.form-field');
        if (field) {
            field.appendChild(errorDiv);
        }
    }

    /**
     * Убирает ошибку поля
     */
    function removeFieldError(input) {
        input.classList.remove('has-error');
        const field = input.closest('.form-field');
        if (field) {
            const errorDiv = field.querySelector('.field-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPasswordResetConfirmForm, 100);
        });
    } else {
        setTimeout(initPasswordResetConfirmForm, 100);
    }
})();
