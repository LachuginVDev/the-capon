/**
 * Обработка формы регистрации
 */

(function() {
    'use strict';

    function initRegisterForm() {
        const form = document.querySelector('.wp-register-form');
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

        // Получаем все поля для валидации
        const emailInput = form.querySelector('[name="email"]');
        const firstNameInput = form.querySelector('[name="first_name"]');
        const lastNameInput = form.querySelector('[name="last_name"]');
        const phoneInput = form.querySelector('[name="phone"]');
        const passwordInput = form.querySelector('[name="password"]');
        const passwordConfirmInput = form.querySelector('[name="password_confirm"]');
        const shopNameInput = form.querySelector('[name="shop_name"]');
        const countryInput = form.querySelector('[name="country"]');
        const cityInput = form.querySelector('[name="city"]');
        const shopAddressInput = form.querySelector('[name="shop_address"]');
        const websiteInput = form.querySelector('[name="website"]');
        const socialInput = form.querySelector('[name="social"]');

        // Валидация в реальном времени
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
        }

        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                validatePhone(this);
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                validatePassword(this);
                // Проверяем совпадение паролей
                if (passwordConfirmInput && passwordConfirmInput.value) {
                    validatePasswordConfirm(passwordConfirmInput, passwordInput.value);
                }
            });
        }

        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('blur', function() {
                if (passwordInput) {
                    validatePasswordConfirm(this, passwordInput.value);
                }
            });
        }

        if (websiteInput) {
            websiteInput.addEventListener('blur', function() {
                if (this.value) {
                    validateUrl(this);
                }
            });
        }

        // Обработка отправки формы
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Валидация всех полей
            let isValid = true;

            if (emailInput && !validateEmail(emailInput)) isValid = false;
            if (firstNameInput && !validateRequired(firstNameInput, 'Имя')) isValid = false;
            if (lastNameInput && !validateRequired(lastNameInput, 'Фамилия')) isValid = false;
            if (phoneInput && !validatePhone(phoneInput)) isValid = false;
            if (passwordInput && !validatePassword(passwordInput)) isValid = false;
            if (passwordConfirmInput && passwordInput && !validatePasswordConfirm(passwordConfirmInput, passwordInput.value)) isValid = false;
            if (shopNameInput && !validateRequired(shopNameInput, 'Название магазина')) isValid = false;
            if (countryInput && !validateRequired(countryInput, 'Страна')) isValid = false;
            if (cityInput && !validateRequired(cityInput, 'Город')) isValid = false;
            if (shopAddressInput && !validateRequired(shopAddressInput, 'Адрес магазина')) isValid = false;
            if (websiteInput && websiteInput.value && !validateUrl(websiteInput)) isValid = false;

            if (!isValid) {
                if (window.AuthNotifications) {
                    window.AuthNotifications.show('Пожалуйста, исправьте ошибки в форме', 'error');
                }
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            const formData = new FormData(form);
            formData.append('action', 'custom_register');

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
                    const message = json.data && json.data.message ? json.data.message : 'Регистрация прошла успешно';
                    if (window.AuthNotifications) {
                        window.AuthNotifications.show(message, 'success');
                    }
                    form.reset();
                    // Очищаем все ошибки
                    form.querySelectorAll('.has-error').forEach(function(input) {
                        removeFieldError(input);
                    });
                } else {
                    const message = json.data && json.data.message ? json.data.message : 'Ошибка регистрации';
                    if (window.AuthNotifications && typeof window.AuthNotifications.show === 'function') {
                        window.AuthNotifications.show(message, 'error');
                    } else {
                        alert(message);
                    }
                }
            } catch (error) {
                console.error('Register error:', error);
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
                    submitBtn.textContent = submitBtn.dataset.originalText || 'Создать аккаунт';
                }
            }
        });
    }

    /**
     * Валидация обязательного поля
     */
    function validateRequired(input, fieldName) {
        removeFieldError(input);
        if (!input.value.trim()) {
            showFieldError(input, fieldName + ' обязателен для заполнения');
            return false;
        }
        return true;
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
     * Валидация телефона
     */
    function validatePhone(input) {
        const phone = input.value.trim();
        // Простая валидация: минимум 10 цифр
        const phoneRegex = /^[\d\s\-\+\(\)]+$/;
        const digitsOnly = phone.replace(/\D/g, '');

        removeFieldError(input);

        if (!phone) {
            showFieldError(input, 'Телефон обязателен для заполнения');
            return false;
        }

        if (!phoneRegex.test(phone)) {
            showFieldError(input, 'Телефон содержит недопустимые символы');
            return false;
        }

        if (digitsOnly.length < 10) {
            showFieldError(input, 'Телефон должен содержать минимум 10 цифр');
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
     * Валидация URL
     */
    function validateUrl(input) {
        const url = input.value.trim();
        removeFieldError(input);

        if (!url) {
            return true; // Необязательное поле
        }

        try {
            // Пробуем добавить протокол, если его нет
            let testUrl = url;
            if (!/^https?:\/\//i.test(url)) {
                testUrl = 'http://' + url;
            }
            new URL(testUrl);
            return true;
        } catch (e) {
            showFieldError(input, 'Неверный формат URL');
            return false;
        }
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
            setTimeout(initRegisterForm, 100);
        });
    } else {
        setTimeout(initRegisterForm, 100);
    }
})();
