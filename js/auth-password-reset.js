/**
 * Обработка формы восстановления пароля
 */

(function() {
    'use strict';

    function initPasswordResetForm() {
        const form = document.getElementById('password-reset-form');
        if (!form) return;

        // Проверка наличия theCaponAjax
        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
            console.error('theCaponAjax не определен');
            return;
        }

        const emailInput = form.querySelector('[name="user_email"]');

        // Валидация email в реальном времени
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
        }

        // Обработка отправки формы
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            let isValid = true;
            if (emailInput && !validateEmail(emailInput)) {
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
            formData.append('action', 'request_password_reset');

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
                    const message = json.data && json.data.message ? json.data.message : 'Ссылка для восстановления пароля отправлена на ваш email';
                    if (window.AuthNotifications) {
                        window.AuthNotifications.show(message, 'success');
                    }
                    form.reset();
                } else {
                    const message = json.data && json.data.message ? json.data.message : 'Ошибка отправки запроса';
                    if (window.AuthNotifications && typeof window.AuthNotifications.show === 'function') {
                        window.AuthNotifications.show(message, 'error');
                    } else {
                        alert(message);
                    }
                }
            } catch (error) {
                console.error('Password reset error:', error);
                const errorMessage = 'Ошибка соединения. Попробуйте позже.';
                if (window.AuthNotifications && typeof window.AuthNotifications.show === 'function') {
                    window.AuthNotifications.show(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
            } finally {
                if (window.AuthNotifications && typeof window.AuthNotifications.setButtonLoading === 'function') {
                    window.AuthNotifications.setButtonLoading(submitBtn, false);
                } else if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitBtn.dataset.originalText || 'Отправить ссылку';
                }
            }
        });
    }

    /**
     * Валидация email
     */
    function validateEmail(input) {
        const email = input.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        removeFieldError(input);

        if (!email) {
            showFieldError(input, 'Email обязателен для заполнения');
            return false;
        }

        if (!emailRegex.test(email)) {
            showFieldError(input, 'Неверный формат email');
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
            setTimeout(initPasswordResetForm, 100);
        });
    } else {
        setTimeout(initPasswordResetForm, 100);
    }
})();
