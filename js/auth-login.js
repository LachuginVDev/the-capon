/**
 * Обработка формы входа
 */

(function() {
    'use strict';

    function initLoginForm() {
        const form = document.querySelector('.wp-login-form');
        if (!form) {
            return;
        }

        // Проверка наличия theCaponAjax
        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
            console.error('theCaponAjax не определен');
            return;
        }

        // Обработка переключения видимости пароля
        const togglePassword = form.querySelector('.toggle-password');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const input = this.previousElementSibling;
                if (input && input.type === 'password') {
                    input.type = 'text';
                    this.classList.add('is-visible');
                } else if (input) {
                    input.type = 'password';
                    this.classList.remove('is-visible');
                }
            });
        }

        // Валидация в реальном времени
        const emailInput = form.querySelector('[name="log"]');
        const passwordInput = form.querySelector('[name="pwd"]');

        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                validatePassword(this);
            });
        }

        // Обработка отправки формы
        form.setAttribute('novalidate', 'novalidate');
        
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Валидация перед отправкой
            let isValid = true;

            if (emailInput && !validateEmail(emailInput)) {
                isValid = false;
            }

            if (passwordInput && !validatePassword(passwordInput)) {
                isValid = false;
            }

            if (!isValid) {
                showNotification('Пожалуйста, исправьте ошибки в форме', 'error');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);
            formData.append('action', 'login_user');

            // Показываем индикатор загрузки
            setButtonLoading(submitBtn, true);

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
                    showNotification('Вход выполнен успешно', 'success', 2000);
                    setTimeout(function() {
                        window.location.href = theCaponAjax.loginRedirect || '/lk/';
                    }, 500);
                } else {
                    const message = json.data && json.data.message ? json.data.message : 'Ошибка входа';
                    showNotification(message, 'error');
                    setButtonLoading(submitBtn, false);
                }
            } catch (error) {
                console.error('Login error:', error);
                showNotification('Ошибка соединения. Попробуйте позже.', 'error');
                setButtonLoading(submitBtn, false);
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

    /**
     * Показывает уведомление
     */
    function showNotification(message, type, duration) {
        if (window.AuthNotifications && typeof window.AuthNotifications.show === 'function') {
            window.AuthNotifications.show(message, type, duration);
        } else {
            alert(message);
        }
    }

    /**
     * Устанавливает состояние загрузки кнопки
     */
    function setButtonLoading(button, loading) {
        if (window.AuthNotifications && typeof window.AuthNotifications.setButtonLoading === 'function') {
            window.AuthNotifications.setButtonLoading(button, loading);
        } else if (button) {
            button.disabled = loading;
        }
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initLoginForm, 100);
        });
    } else {
        setTimeout(initLoginForm, 100);
    }
})();
