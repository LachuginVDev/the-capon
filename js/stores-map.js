/**
 * Яндекс карта для страницы "Где купить"
 */
(function() {
    'use strict';

    function initStoresMap() {
        if (typeof ymaps === 'undefined') {
            return;
        }
        if (!window.theCaponStores || !theCaponStores.items || !theCaponStores.items.length) {
            return;
        }

        var mapContainer = document.getElementById('stores-map');
        if (!mapContainer) {
            return;
        }

        var center = theCaponStores.center || {};
        var centerLat = parseFloat(center.lat) || 55.7558;
        var centerLng = parseFloat(center.lng) || 37.6173;
        var zoom = parseInt(center.zoom, 10) || 10;

        ymaps.ready(function() {
            var map = new ymaps.Map('stores-map', {
                center: [centerLat, centerLng],
                zoom: zoom,
                controls: ['zoomControl']
            });

            var geoObjects = [];

            theCaponStores.items.forEach(function(store, index) {
                if (!store || !store.lat || !store.lng) return;

                var lat = parseFloat(store.lat);
                var lng = parseFloat(store.lng);
                if (isNaN(lat) || isNaN(lng)) return;

                var name = store.name || '';
                var address = store.address || '';

                var balloonContent = '<strong>' + (name || 'Магазин') + '</strong>';
                if (address) {
                    balloonContent += '<br>' + address;
                }

                var placemark = new ymaps.Placemark([lat, lng], {
                    hintContent: name,
                    balloonContent: balloonContent
                }, {
                    preset: 'islands#redIcon'
                });

                map.geoObjects.add(placemark);
                geoObjects[index] = placemark;
            });

            // Клик по магазину в списке
            var listContainer = document.getElementById('stores-list');
            if (listContainer) {
                listContainer.addEventListener('click', function(e) {
                    var item = e.target.closest('.store-item');
                    if (!item) return;

                    // Пропускаем магазины без координат
                    if (item.classList.contains('store-item--no-coords')) {
                        return;
                    }

                    var idx = parseInt(item.getAttribute('data-index'), 10);
                    if (isNaN(idx) || idx < 0 || !geoObjects[idx]) return;

                    var coords = geoObjects[idx].geometry.getCoordinates();
                    // Скролл к карте
                    if (mapContainer && typeof mapContainer.scrollIntoView === 'function') {
                        mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }

                    // Фокус на маркере
                    map.setCenter(coords, 15, {duration: 300});
                    geoObjects[idx].balloon.open();
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStoresMap);
    } else {
        initStoresMap();
    }
})();

