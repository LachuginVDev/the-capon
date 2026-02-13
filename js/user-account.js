/**
 * Функционал личного кабинета
 */

(function() {
    'use strict';
    
    // Переключение табов
    function initTabs() {
        const tabs = document.querySelectorAll('.user-account-tab');
        const tabContents = document.querySelectorAll('.user-account-tab-content');
        
        if (tabs.length === 0) return;
        
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const targetTab = this.getAttribute('data-tab');
                
                if (!targetTab) return;
                
                // Убираем активный класс у всех табов
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                });
                tabContents.forEach(function(content) {
                    content.classList.remove('active');
                });
                
                // Добавляем активный класс к выбранному табу
                this.classList.add('active');
                const targetContent = document.querySelector('.user-account-tab-content[data-tab="' + targetTab + '"]');
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }
    
    // Удаление из избранного (с делегированием событий)
    function initRemoveFavorites() {
        const favoritesContainer = document.querySelector('.favorites-grid');
        
        if (!favoritesContainer) return;
        
        // Используем делегирование событий для динамически созданных элементов
        favoritesContainer.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.favorite-card-remove');
            if (!removeBtn) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const modelId = removeBtn.getAttribute('data-model-id');
            const card = removeBtn.closest('.favorite-card');
            
            if (!modelId || !card) return;
            
            // Показываем диалог подтверждения
            const showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : function(msg, type) { alert(msg); };
            const showConfirm = window.AuthNotifications && window.AuthNotifications.confirm ? window.AuthNotifications.confirm : function(msg, onConfirm) { if (confirm(msg)) onConfirm(); };
            
            showConfirm('Удалить модель из избранного?', function() {
                // Показываем индикатор загрузки
                removeBtn.style.opacity = '0.5';
                removeBtn.style.pointerEvents = 'none';
                
                // Проверяем наличие theCaponAjax
                if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
                    showNotification('Ошибка: AJAX не настроен', 'error');
                    removeBtn.style.opacity = '1';
                    removeBtn.style.pointerEvents = 'auto';
                    return;
                }
            
            fetch(theCaponAjax.ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'remove_favorite',
                    model_id: modelId
                })
            })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(function(res) {
                    if(res.success){
                        // Анимация удаления
                        card.style.transition = 'opacity 0.3s, transform 0.3s';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.9)';
                        
                        setTimeout(function() {
                            card.remove();
                            
                            // Если больше нет избранного, показываем сообщение
                            const grid = document.querySelector('.favorites-grid');
                            if (grid && grid.querySelectorAll('.favorite-card').length === 0) {
                                const tabContent = grid.closest('.user-account-tab-content');
                                if (tabContent) {
                                    grid.outerHTML = '<p style="text-align: center; padding: 40px 0; color: #666;">У вас пока нет избранных моделей.</p>';
                                }
                            }
                        }, 300);
                        showNotification('Модель удалена из избранного', 'success');
                    } else {
                        showNotification(res.data && res.data.message ? res.data.message : 'Ошибка удаления', 'error');
                        removeBtn.style.opacity = '1';
                        removeBtn.style.pointerEvents = 'auto';
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    showNotification('Ошибка соединения. Попробуйте позже.', 'error');
                    removeBtn.style.opacity = '1';
                    removeBtn.style.pointerEvents = 'auto';
                });
            });
        });
    }
    
    // Сохранение данных аккаунта
    function initAccountForm() {
        const accountForm = document.querySelector('.account-settings-form');
        if (!accountForm) return;
        
        accountForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : function(msg, type) { alert(msg); };
            
            // Проверяем наличие theCaponAjax
            if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
                showNotification('Ошибка: AJAX не настроен', 'error');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'update_user_account');
            if (theCaponAjax.account_nonce) {
                formData.append('nonce', theCaponAjax.account_nonce);
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Сохранение...';
            }
            
            // Скрываем предыдущие сообщения
            const messages = this.querySelectorAll('.account-settings-message');
            messages.forEach(function(msg) {
                msg.style.display = 'none';
            });
            
            fetch(theCaponAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
                .then(function(res) {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(function(res) {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                    
                    let messageEl = accountForm.querySelector('.account-settings-message');
                    if (!messageEl) {
                        messageEl = document.createElement('div');
                        messageEl.className = 'account-settings-message';
                        accountForm.insertBefore(messageEl, accountForm.firstChild);
                    }
                    
                    if (res.success) {
                        messageEl.className = 'account-settings-message success';
                        messageEl.textContent = (res.data && res.data.message) ? res.data.message : 'Данные успешно сохранены';
                        messageEl.style.display = 'block';
                        
                        // Прокручиваем к сообщению
                        messageEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        messageEl.className = 'account-settings-message error';
                        messageEl.textContent = (res.data && res.data.message) ? res.data.message : 'Ошибка сохранения данных';
                        messageEl.style.display = 'block';
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                    
                    let messageEl = accountForm.querySelector('.account-settings-message');
                    if (!messageEl) {
                        messageEl = document.createElement('div');
                        messageEl.className = 'account-settings-message';
                        accountForm.insertBefore(messageEl, accountForm.firstChild);
                    }
                    messageEl.className = 'account-settings-message error';
                    messageEl.textContent = 'Ошибка соединения. Попробуйте позже.';
                    messageEl.style.display = 'block';
                });
        });
    }
    
    // Создание заказа из избранного
    function initCreateOrder() {
        const createOrderBtn = document.getElementById('create-order-from-favorites');
        if (!createOrderBtn) return;
        
        createOrderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : function(msg, type) { alert(msg); };
            const showConfirm = window.AuthNotifications && window.AuthNotifications.confirm ? window.AuthNotifications.confirm : function(msg, onConfirm) { if (confirm(msg)) onConfirm(); };
            
            if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
                showNotification('Ошибка: AJAX не настроен', 'error');
                return;
            }
            
            showConfirm('Создать заказ из всех товаров в избранном?', function() {
                const originalText = createOrderBtn.textContent;
                createOrderBtn.disabled = true;
                createOrderBtn.textContent = 'Создание заказа...';
                
                const formData = new FormData();
                formData.append('action', 'create_order_from_favorites');
                if (theCaponAjax.create_order_nonce) {
                    formData.append('nonce', theCaponAjax.create_order_nonce);
                }
                
                fetch(theCaponAjax.ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                    .then(function(res) {
                        if (!res.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return res.json();
                    })
                    .then(function(res) {
                        if (res.success) {
                            showNotification(res.data && res.data.message ? res.data.message : 'Заказ успешно создан', 'success', 3000);
                            
                            // Удаляем все карточки из избранного
                            const favoritesGrid = document.querySelector('.favorites-grid');
                            if (favoritesGrid) {
                                const cards = favoritesGrid.querySelectorAll('.favorite-card');
                                cards.forEach(function(card) {
                                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                                    card.style.opacity = '0';
                                    card.style.transform = 'scale(0.9)';
                                    setTimeout(function() {
                                        card.remove();
                                    }, 300);
                                });
                                
                                // Если больше нет избранного, показываем сообщение
                                setTimeout(function() {
                                    if (favoritesGrid.querySelectorAll('.favorite-card').length === 0) {
                                        const tabContent = favoritesGrid.closest('.user-account-tab-content');
                                        if (tabContent) {
                                            favoritesGrid.outerHTML = '<p style="text-align: center; padding: 40px 0; color: #666;">У вас пока нет избранных моделей.</p>';
                                        }
                                    }
                                }, 350);
                            }
                            
                            // Переключаемся на вкладку заказов
                            const ordersTab = document.querySelector('.user-account-tab[data-tab="orders"]');
                            if (ordersTab) {
                                setTimeout(function() {
                                    ordersTab.click();
                                }, 500);
                            }
                            // Обновляем список заказов
                            setTimeout(function() {
                                loadOrders();
                            }, 600);
                        } else {
                            showNotification(res.data && res.data.message ? res.data.message : 'Ошибка создания заказа', 'error');
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        showNotification('Ошибка соединения. Попробуйте позже.', 'error');
                    })
                    .finally(function() {
                        createOrderBtn.disabled = false;
                        createOrderBtn.textContent = originalText;
                    });
            });
        });
    }
    
    // Загрузка и отображение заказов
    function loadOrders() {
        const ordersTableBody = document.getElementById('orders-table-body');
        if (!ordersTableBody) return;
        
        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
            ordersTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #d32f2f;">Ошибка: AJAX не настроен</td></tr>';
            return;
        }
        
        ordersTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px;"><span class="orders-loading">Загрузка заказов...</span></td></tr>';
        
        const formData = new FormData();
        formData.append('action', 'get_user_orders');
        
        fetch(theCaponAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(function(res) {
                if (res.success && res.data && res.data.orders) {
                    const orders = res.data.orders;
                    
                    if (Object.keys(orders).length === 0) {
                        ordersTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #666;">У вас пока нет заказов</td></tr>';
                        return;
                    }
                    
                    let html = '';
                    for (const orderId in orders) {
                        const order = orders[orderId];
                        const statusLabels = {
                            'pending': 'Ожидает',
                            'processing': 'В работе',
                            'completed': 'Завершен',
                            'cancelled': 'Отменен'
                        };
                        const statusColors = {
                            'pending': '#ff9800',
                            'processing': '#2196f3',
                            'completed': '#4caf50',
                            'cancelled': '#f44336'
                        };
                        
                        const status = order.status || 'pending';
                        const statusLabel = statusLabels[status] || status;
                        const statusColor = statusColors[status] || '#666';
                        
                        const date = new Date(order.created_at);
                        const formattedDate = date.toLocaleDateString('ru-RU', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        
                        const itemsCount = order.items ? order.items.length : 0;
                        const itemsList = order.items ? order.items.map(function(item) {
                            return item.title;
                        }).join(', ') : '';
                        
                        html += '<tr>';
                        html += '<td><strong>' + orderId + '</strong></td>';
                        html += '<td>' + formattedDate + '</td>';
                        html += '<td>';
                        html += '<div style="max-width: 300px;">';
                        html += '<strong>' + itemsCount + ' товар(ов)</strong>';
                        if (itemsList) {
                            html += '<div style="font-size: 12px; color: #666; margin-top: 5px;">' + itemsList.substring(0, 100) + (itemsList.length > 100 ? '...' : '') + '</div>';
                        }
                        html += '</div>';
                        html += '</td>';
                        html += '<td><strong>' + new Intl.NumberFormat('ru-RU').format(order.total_price || 0) + ' ₽</strong></td>';
                        html += '<td><span style="padding: 4px 12px; border-radius: 4px; background: ' + statusColor + '; color: #fff; font-size: 12px; font-weight: 500;">' + statusLabel + '</span></td>';
                        html += '</tr>';
                    }
                    
                    ordersTableBody.innerHTML = html;
                } else {
                    ordersTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #666;">У вас пока нет заказов</td></tr>';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                ordersTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #d32f2f;">Ошибка загрузки заказов. Попробуйте позже.</td></tr>';
            });
    }
    
    // Загружаем заказы при переключении на вкладку
    function initOrdersTab() {
        const ordersTab = document.querySelector('.user-account-tab[data-tab="orders"]');
        if (!ordersTab) return;
        
        ordersTab.addEventListener('click', function() {
            setTimeout(loadOrders, 100);
        });
    }

    // ===============================
    // Магазины пользователя (вкладка "Магазины")
    // ===============================
    function renderStores(stores) {
        const container = document.getElementById('lk-stores-container');
        if (!container) return;

        if (!Array.isArray(stores)) {
            stores = [];
        }

        if (stores.length === 0) {
            stores = [{
                name: '',
                country: '',
                city: '',
                address: '',
                phone: '',
                email: '',
                website: '',
                socials: '',
                description: '',
                lat: '',
                lng: ''
            }];
        }

        let html = '<div class="lk-stores-list-form">';

        stores.forEach(function(store, index) {
            html += '<div class="lk-store-item" data-index="' + index + '">';
            html += '<div class="lk-store-item-header">';
            html += '<strong>Магазин ' + (index + 1) + '</strong>';
            html += '<button type="button" class="lk-store-remove-btn" data-index="' + index + '">Удалить</button>';
            html += '</div>';

            html += '<div class="lk-store-fields">';

            html += '<div class="form-group">';
            html += '<label>Название магазина</label>';
            html += '<input type="text" name="store_name" value="' + (store.name || '') + '">';
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label>Адрес</label>';
            html += '<input type="text" name="store_address" value="' + (store.address || '').replace(/"/g, '&quot;') + '" class="lk-store-address-input" placeholder="Введите адрес — координаты подставятся автоматически">';
            html += '</div>';

            html += '<input type="hidden" name="store_lat" value="' + (store.lat || '') + '">';
            html += '<input type="hidden" name="store_lng" value="' + (store.lng || '') + '">';
            html += '<input type="hidden" name="store_country" value="' + (store.country || '').replace(/"/g, '&quot;') + '">';
            html += '<input type="hidden" name="store_city" value="' + (store.city || '').replace(/"/g, '&quot;') + '">';

            html += '<div class="form-group lk-store-map-wrap" data-index="' + index + '">';
            html += '<label>Карта</label>';
            html += '<div id="lk-store-map-' + index + '" class="lk-store-minimap" style="height: 200px; width: 100%; display: ' + (store.lat && store.lng ? 'block' : 'none') + ';"></div>';
            html += '<p class="lk-store-geocode-hint" style="font-size: 12px; color: #666; margin-top: 4px; display: ' + (store.lat && store.lng ? 'none' : 'block') + ';">Поиск координат по адресу...</p>';
            if (store.country || store.city) {
                html += '<p class="lk-store-location" style="font-size: 12px; color: #666; margin-top: 4px;">Страна: ' + (store.country || '—').replace(/</g, '&lt;') + ', город: ' + (store.city || '—').replace(/</g, '&lt;') + '</p>';
            }
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label>Телефон</label>';
            html += '<input type="text" name="store_phone" value="' + (store.phone || '') + '">';
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label>Email</label>';
            html += '<input type="email" name="store_email" value="' + (store.email || '') + '">';
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label>Сайт</label>';
            html += '<input type="url" name="store_website" value="' + (store.website || '') + '">';
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label>Соцсети</label>';
            html += '<textarea name="store_socials">' + (store.socials || '') + '</textarea>';
            html += '</div>';

            html += '<div class="form-group">';
            html += '<label>Описание</label>';
            html += '<textarea name="store_description">' + (store.description || '') + '</textarea>';
            html += '</div>';

            html += '</div>'; // .lk-store-fields

            html += '</div>'; // .lk-store-item
        });

        html += '<div class="lk-stores-actions">';
        html += '<button type="button" class="btn btn-secondary" id="lk-add-store">Добавить магазин</button>';
        html += '<button type="button" class="btn btn-primary" id="lk-save-stores">Сохранить магазины</button>';
        html += '</div>';

        html += '</div>';

        container.innerHTML = html;
        initLkStoreMaps();
        autoGeocodeStoresWithAddress();
    }

    function autoGeocodeStoresWithAddress() {
        var container = document.getElementById('lk-stores-container');
        if (!container) return;
        var items = container.querySelectorAll('.lk-store-item');
        var showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : null;
        var needGeocode = [];
        items.forEach(function(item) {
            var address = (item.querySelector('input[name="store_address"]') || {}).value;
            var lat = (item.querySelector('input[name="store_lat"]') || {}).value;
            var lng = (item.querySelector('input[name="store_lng"]') || {}).value;
            if (address && address.trim() && (!lat || !lng)) needGeocode.push(item);
        });
        needGeocode.forEach(function(item, i) {
            setTimeout(function() {
                handleStoreGeocode(item, showNotification, true);
            }, i * 1200);
        });
    }

    function handleStoreGeocode(item, showNotification, silent) {
        var addressInput = item.querySelector('input[name="store_address"]');
        var address = addressInput ? addressInput.value.trim() : '';
        if (!address) {
            if (!silent && showNotification) showNotification('Введите адрес', 'error');
            return;
        }
        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl || !theCaponAjax.stores_nonce) {
            if (!silent && showNotification) showNotification('Ошибка: AJAX не настроен', 'error');
            return;
        }

        var mapWrap = item.querySelector('.lk-store-map-wrap');
        var hint = mapWrap ? mapWrap.querySelector('.lk-store-geocode-hint') : null;
        if (hint) {
            hint.style.display = 'block';
            hint.textContent = 'Поиск координат...';
        }

        var formData = new FormData();
        formData.append('action', 'geocode_address');
        formData.append('nonce', theCaponAjax.stores_nonce);
        formData.append('address', address);

        fetch(theCaponAjax.ajaxurl, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (!res.success || !res.data) {
                    if (hint) { hint.textContent = 'Адрес не найден. Уточните адрес.'; hint.style.display = 'block'; }
                    if (!silent && showNotification) showNotification('Адрес не найден. Уточните адрес.', 'error');
                    return;
                }
                var lat = res.data.lat || '';
                var lng = res.data.lng || '';
                var country = res.data.country || '';
                var city = res.data.city || '';
                var latEl = item.querySelector('input[name="store_lat"]');
                var lngEl = item.querySelector('input[name="store_lng"]');
                var countryEl = item.querySelector('input[name="store_country"]');
                var cityEl = item.querySelector('input[name="store_city"]');
                if (latEl) latEl.value = lat;
                if (lngEl) lngEl.value = lng;
                if (countryEl) countryEl.value = country;
                if (cityEl) cityEl.value = city;

                var mapDiv = mapWrap ? mapWrap.querySelector('.lk-store-minimap') : null;
                var locationP = mapWrap ? mapWrap.querySelector('.lk-store-location') : null;
                if (mapWrap && mapDiv) {
                    mapDiv.style.display = (lat && lng) ? 'block' : 'none';
                    if (hint) {
                        hint.style.display = (lat && lng) ? 'none' : 'block';
                        if (lat && lng) hint.textContent = '';
                    }
                    if (locationP) {
                        locationP.textContent = 'Страна: ' + (country || '—') + ', город: ' + (city || '—');
                        locationP.style.display = (country || city) ? 'block' : 'none';
                    } else if (country || city) {
                        var p = document.createElement('p');
                        p.className = 'lk-store-location';
                        p.style.cssText = 'font-size: 12px; color: #666; margin-top: 4px;';
                        p.textContent = 'Страна: ' + (country || '—') + ', город: ' + (city || '—');
                        mapWrap.appendChild(p);
                    }
                }
                if (lat && lng) initOneLkStoreMap(item);
                if (!silent && showNotification && (lat && lng)) showNotification('Координаты определены', 'success');
            })
            .catch(function() {
                if (hint) { hint.textContent = 'Ошибка запроса. Попробуйте позже.'; hint.style.display = 'block'; }
                if (!silent && showNotification) showNotification('Ошибка запроса. Попробуйте позже.', 'error');
            });
    }

    function initOneLkStoreMap(item) {
        var latEl = item.querySelector('input[name="store_lat"]');
        var lngEl = item.querySelector('input[name="store_lng"]');
        var lat = latEl ? parseFloat(latEl.value, 10) : NaN;
        var lng = lngEl ? parseFloat(lngEl.value, 10) : NaN;
        if (isNaN(lat) || isNaN(lng)) return;
        var index = item.getAttribute('data-index');
        var mapId = 'lk-store-map-' + index;
        var mapDiv = document.getElementById(mapId);
        if (!mapDiv || typeof L === 'undefined') return;
        if (mapDiv._lkMap) return;
        try {
            var map = L.map(mapId).setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);
            L.marker([lat, lng]).addTo(map);
            mapDiv._lkMap = map;
        } catch (err) {
            console.error('LK store map init error:', err);
        }
    }

    function initLkStoreMaps() {
        var container = document.getElementById('lk-stores-container');
        if (!container || typeof L === 'undefined') return;
        var items = container.querySelectorAll('.lk-store-item');
        items.forEach(function(item) {
            var latEl = item.querySelector('input[name="store_lat"]');
            var lngEl = item.querySelector('input[name="store_lng"]');
            if (latEl && lngEl && latEl.value && lngEl.value) initOneLkStoreMap(item);
        });
    }

    function collectStoresFromForm() {
        const container = document.getElementById('lk-stores-container');
        if (!container) return [];

        const items = container.querySelectorAll('.lk-store-item');
        const stores = [];

        items.forEach(function(item) {
            const getVal = function(selector) {
                const el = item.querySelector(selector);
                return el ? el.value.trim() : '';
            };

            var lat = getVal('input[name="store_lat"]');
            var lng = getVal('input[name="store_lng"]');
            var country = getVal('input[name="store_country"]');
            var city = getVal('input[name="store_city"]');

            stores.push({
                name: getVal('input[name="store_name"]'),
                country: country,
                city: city,
                address: getVal('input[name="store_address"]'),
                phone: getVal('input[name="store_phone"]'),
                email: getVal('input[name="store_email"]'),
                website: getVal('input[name="store_website"]'),
                socials: getVal('textarea[name="store_socials"]'),
                description: getVal('textarea[name="store_description"]'),
                lat: lat,
                lng: lng
            });
        });

        return stores;
    }

    function loadStores() {
        const container = document.getElementById('lk-stores-container');
        if (!container) return;

        const showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : function(msg, type) { alert(msg); };

        if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
            showNotification('Ошибка: AJAX не настроен', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'get_user_stores');

        fetch(theCaponAjax.ajaxurl, {
            method: 'POST',
            body: formData
        })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(function(res) {
                if (res.success && res.data && Array.isArray(res.data.stores)) {
                    renderStores(res.data.stores);
                } else {
                    renderStores([]);
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                showNotification('Ошибка загрузки магазинов. Попробуйте позже.', 'error');
            });
    }

    function initStoresTab() {
        const storesTab = document.querySelector('.user-account-tab[data-tab="stores"]');
        const container = document.getElementById('lk-stores-container');
        if (!storesTab || !container) return;

        let loaded = false;

        storesTab.addEventListener('click', function() {
            if (!loaded) {
                loadStores();
                loaded = true;
            }
        });

        container.addEventListener('focusout', function(e) {
            if (e.target && e.target.matches && e.target.matches('input[name="store_address"]')) {
                var item = e.target.closest('.lk-store-item');
                var showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : null;
                if (item) handleStoreGeocode(item, showNotification, false);
            }
        });

        // Делегирование событий для добавления/удаления/сохранения
        container.addEventListener('click', function(e) {
            const addBtn = e.target.closest('#lk-add-store');
            const saveBtn = e.target.closest('#lk-save-stores');
            const removeBtn = e.target.closest('.lk-store-remove-btn');

            const showNotification = window.AuthNotifications && window.AuthNotifications.show ? window.AuthNotifications.show : function(msg, type) { alert(msg); };

            if (addBtn) {
                e.preventDefault();
                const currentStores = collectStoresFromForm();
                currentStores.push({
                    name: '',
                    country: '',
                    city: '',
                    address: '',
                    phone: '',
                    email: '',
                    website: '',
                    socials: '',
                    description: '',
                    lat: '',
                    lng: ''
                });
                renderStores(currentStores);
                return;
            }

            if (removeBtn) {
                e.preventDefault();
                const item = removeBtn.closest('.lk-store-item');
                if (item) {
                    item.remove();
                }
                return;
            }

            if (saveBtn) {
                e.preventDefault();

                if (typeof theCaponAjax === 'undefined' || !theCaponAjax.ajaxurl) {
                    showNotification('Ошибка: AJAX не настроен', 'error');
                    return;
                }

                const stores = collectStoresFromForm();
                const payload = new URLSearchParams();
                payload.append('action', 'save_user_stores');
                if (theCaponAjax.stores_nonce) {
                    payload.append('nonce', theCaponAjax.stores_nonce);
                }
                payload.append('stores', JSON.stringify(stores));

                saveBtn.disabled = true;
                saveBtn.textContent = 'Сохранение...';

                fetch(theCaponAjax.ajaxurl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                })
                    .then(function(res) {
                        if (!res.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return res.json();
                    })
                    .then(function(res) {
                        if (res.success) {
                            showNotification(res.data && res.data.message ? res.data.message : 'Магазины сохранены', 'success');
                            if (res.data && Array.isArray(res.data.stores)) {
                                renderStores(res.data.stores);
                            }
                        } else {
                            showNotification(res.data && res.data.message ? res.data.message : 'Ошибка сохранения магазинов', 'error');
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        showNotification('Ошибка соединения. Попробуйте позже.', 'error');
                    })
                    .finally(function() {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Сохранить магазины';
                    });
            }
        });
    }
    
    // Инициализация при загрузке DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initTabs();
            initRemoveFavorites();
            initAccountForm();
            initCreateOrder();
            initOrdersTab();
            initStoresTab();
        });
    } else {
        // DOM уже загружен
        initTabs();
        initRemoveFavorites();
        initAccountForm();
        initCreateOrder();
        initOrdersTab();
        initStoresTab();
    }
})();
