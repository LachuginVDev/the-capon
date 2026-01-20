/**
 * Функционал детальной страницы товара
 */

(function() {
    'use strict';

    function initSingleModel() {
        // Проверяем наличие theCaponAjax
        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
            console.warn('theCaponAjax не определен');
            return;
        }

        // Обработка кнопки "СДЕЛАТЬ ЗАКАЗ" на детальной странице
        const makeOrderBtn = document.querySelector('.make-order');
        if (!makeOrderBtn) return;

        makeOrderBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const modelId = this.getAttribute('data-model-id');
            if (!modelId) {
                console.error('ID модели не найден');
                return;
            }

            // Проверяем наличие системы уведомлений
            if (!window.AuthNotifications || typeof window.AuthNotifications.show !== 'function') {
                console.error('Система уведомлений не загружена');
                return;
            }

            const showNotification = window.AuthNotifications.show;
            const setButtonLoading = window.AuthNotifications.setButtonLoading || function(btn, loading) {
                if (btn) {
                    btn.disabled = loading;
                }
            };

            // Показываем индикатор загрузки
            setButtonLoading(this, true);

            const formData = new FormData();
            formData.append('action', 'make_order');
            formData.append('model_id', modelId);

            try {
                const res = await fetch(theCaponAjax.ajaxurl, {
                    method: 'POST',
                    body: formData
                });

                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await res.json();

                if (data.success) {
                    showNotification(data.data.message || 'Модель добавлена в избранное', 'success', 2000);
                    setTimeout(function() {
                        if (data.data && data.data.url) {
                            window.location.href = data.data.url;
                        }
                    }, 500);
                } else {
                    if (data.data && data.data.login) {
                        showNotification('Необходима авторизация', 'warning', 3000);
                        setTimeout(function() {
                            window.location.href = data.data.login;
                        }, 1500);
                    } else {
                        showNotification(data.data && data.data.message ? data.data.message : 'Ошибка', 'error');
                    }
                }
            } catch(err) {
                console.error('Error:', err);
                showNotification('Ошибка соединения. Попробуйте позже.', 'error');
            } finally {
                setButtonLoading(this, false);
            }
        });
    }

    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initSingleModel, 100);
        });
    } else {
        setTimeout(initSingleModel, 100);
    }
})();
