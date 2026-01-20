/**
 * Система уведомлений для форм авторизации
 */

(function() {
    'use strict';

    /**
     * Создает и показывает уведомление
     * @param {string} message - Текст сообщения
     * @param {string} type - Тип: 'success', 'error', 'info', 'warning'
     * @param {number} duration - Время показа в мс (0 = не скрывать автоматически)
     */
    function showNotification(message, type, duration) {
        type = type || 'info';
        duration = duration !== undefined ? duration : 5000;

        // Удаляем существующие уведомления
        const existing = document.querySelector('.auth-notification');
        if (existing) {
            existing.remove();
        }

        // Создаем контейнер уведомления
        const notification = document.createElement('div');
        notification.className = 'auth-notification auth-notification--' + type;
        notification.setAttribute('role', 'alert');
        notification.setAttribute('aria-live', 'polite');

        // Иконка в зависимости от типа
        let icon = '';
        if (type === 'success') {
            icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M16.667 5L7.5 14.167 3.333 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        } else if (type === 'error') {
            icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        } else if (type === 'warning') {
            icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 6.667V10M10 13.333H10.008M18.333 10C18.333 14.602 14.602 18.333 10 18.333C5.398 18.333 1.667 14.602 1.667 10C1.667 5.398 5.398 1.667 10 1.667C14.602 1.667 18.333 5.398 18.333 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        } else {
            icon = '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 18.333C14.602 18.333 18.333 14.602 18.333 10C18.333 5.398 14.602 1.667 10 1.667C5.398 1.667 1.667 5.398 1.667 10C1.667 14.602 5.398 18.333 10 18.333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 6.667V10M10 13.333H10.008" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        }

        notification.innerHTML = `
            <div class="auth-notification__icon">${icon}</div>
            <div class="auth-notification__message">${message}</div>
            <button class="auth-notification__close" aria-label="Закрыть">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        `;

        // Добавляем в DOM
        // Ищем контейнер формы
        let container = document.querySelector('.wp-login-form, .wp-register-form, #password-reset-form, #password-reset-confirm-form');
        if (!container) {
            container = document.querySelector('.container');
        }
        if (!container) {
            container = document.body;
        }
        
        // Вставляем уведомление перед контейнером формы
        if (container && container !== document.body) {
            container.parentNode.insertBefore(notification, container);
        } else {
            // Если не нашли контейнер, вставляем в начало body
            document.body.insertBefore(notification, document.body.firstChild);
        }

        // Анимация появления
        setTimeout(function() {
            notification.classList.add('auth-notification--show');
        }, 10);

        // Обработчик закрытия
        const closeBtn = notification.querySelector('.auth-notification__close');
        closeBtn.addEventListener('click', function() {
            hideNotification(notification);
        });

        // Автоматическое скрытие
        if (duration > 0) {
            setTimeout(function() {
                hideNotification(notification);
            }, duration);
        }

        // Прокрутка к уведомлению
        notification.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Скрывает уведомление с анимацией
     */
    function hideNotification(notification) {
        notification.classList.remove('auth-notification--show');
        setTimeout(function() {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }

    /**
     * Показывает индикатор загрузки на кнопке
     */
    function setButtonLoading(button, isLoading) {
        if (!button) return;

        if (isLoading) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.innerHTML = '<span class="button-spinner"></span> Загрузка...';
            button.classList.add('is-loading');
        } else {
            button.disabled = false;
            button.textContent = button.dataset.originalText || button.textContent;
            button.classList.remove('is-loading');
        }
    }

    /**
     * Показывает диалог подтверждения
     * @param {string} message - Текст сообщения
     * @param {function} onConfirm - Функция при подтверждении
     * @param {function} onCancel - Функция при отмене (опционально)
     */
    function showConfirm(message, onConfirm, onCancel) {
        // Удаляем существующие диалоги
        const existing = document.querySelector('.auth-confirm-dialog');
        if (existing) {
            existing.remove();
        }

        // Создаем overlay
        const overlay = document.createElement('div');
        overlay.className = 'auth-confirm-overlay';
        
        // Создаем диалог
        const dialog = document.createElement('div');
        dialog.className = 'auth-confirm-dialog';
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        dialog.setAttribute('aria-labelledby', 'auth-confirm-title');

        dialog.innerHTML = `
            <div class="auth-confirm-dialog__icon">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                    <circle cx="24" cy="24" r="24" fill="#ff9800" fill-opacity="0.1"/>
                    <path d="M24 16V24M24 32H24.01M34 24C34 29.523 29.523 34 24 34C18.477 34 14 29.523 14 24C14 18.477 18.477 14 24 14C29.523 14 34 18.477 34 24Z" stroke="#ff9800" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="auth-confirm-dialog__title" id="auth-confirm-title">Подтверждение</h3>
            <p class="auth-confirm-dialog__message">${message}</p>
            <div class="auth-confirm-dialog__actions">
                <button class="auth-confirm-dialog__btn auth-confirm-dialog__btn--cancel" type="button">Отмена</button>
                <button class="auth-confirm-dialog__btn auth-confirm-dialog__btn--confirm" type="button">Подтвердить</button>
            </div>
        `;

        overlay.appendChild(dialog);
        document.body.appendChild(overlay);

        // Анимация появления
        setTimeout(function() {
            overlay.classList.add('auth-confirm-overlay--show');
            dialog.classList.add('auth-confirm-dialog--show');
        }, 10);

        // Обработчики
        const confirmBtn = dialog.querySelector('.auth-confirm-dialog__btn--confirm');
        const cancelBtn = dialog.querySelector('.auth-confirm-dialog__btn--cancel');

        function closeDialog() {
            overlay.classList.remove('auth-confirm-overlay--show');
            dialog.classList.remove('auth-confirm-dialog--show');
            setTimeout(function() {
                if (overlay.parentNode) {
                    overlay.remove();
                }
            }, 300);
        }

        confirmBtn.addEventListener('click', function() {
            closeDialog();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        });

        cancelBtn.addEventListener('click', function() {
            closeDialog();
            if (typeof onCancel === 'function') {
                onCancel();
            }
        });

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeDialog();
                if (typeof onCancel === 'function') {
                    onCancel();
                }
            }
        });

        // Закрытие по Escape
        const escapeHandler = function(e) {
            if (e.key === 'Escape') {
                closeDialog();
                if (typeof onCancel === 'function') {
                    onCancel();
                }
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }

    // Экспортируем функции в глобальную область
    // Делаем доступным сразу, даже если DOM еще не загружен
    window.AuthNotifications = {
        show: showNotification,
        hide: hideNotification,
        setButtonLoading: setButtonLoading,
        confirm: showConfirm
    };
    
    // Для отладки
    if (typeof console !== 'undefined') {
        console.log('AuthNotifications initialized');
    }
})();
