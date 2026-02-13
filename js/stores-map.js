/**
 * Карта магазинов (Leaflet + OpenStreetMap) для страницы "Где купить"
 */
(function() {
    'use strict';

    function initStoresMap() {
        if (typeof L === 'undefined') {
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

        var map = L.map('stores-map').setView([centerLat, centerLng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var markers = [];

        theCaponStores.items.forEach(function(store, index) {
            if (!store || !store.lat || !store.lng) return;

            var lat = parseFloat(store.lat);
            var lng = parseFloat(store.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            var name = store.name || '';
            var address = store.address || '';
            var popupContent = '<strong>' + (name || 'Магазин') + '</strong>';
            if (address) {
                popupContent += '<br>' + address;
            }

            var marker = L.marker([lat, lng]).addTo(map).bindPopup(popupContent);
            markers[index] = marker;
        });

        // Клик по магазину в списке — центрируем карту и открываем попап
        var listContainer = document.getElementById('stores-list');
        if (listContainer) {
            listContainer.addEventListener('click', function(e) {
                var item = e.target.closest('.store-item');
                if (!item) return;

                if (item.classList.contains('store-item--no-coords')) {
                    return;
                }

                var idx = parseInt(item.getAttribute('data-index'), 10);
                if (isNaN(idx) || idx < 0 || !markers[idx]) return;

                var marker = markers[idx];
                var coords = marker.getLatLng();
                if (mapContainer && typeof mapContainer.scrollIntoView === 'function') {
                    mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                map.setView([coords.lat, coords.lng], 15);
                marker.openPopup();
            });
        }

        // Вложенный аккордеон: страна → город → магазины
        var countryItems = document.querySelectorAll('.stores-accordion-item--country');
        if (countryItems.length > 0) {
            var firstCountryBody = countryItems[0].querySelector('.stores-accordion-body');
            if (firstCountryBody) {
                firstCountryBody.style.display = 'block';
                countryItems[0].classList.add('is-open');
                var firstCity = countryItems[0].querySelector('.stores-accordion-item--city');
                if (firstCity) {
                    var firstCityBody = firstCity.querySelector('.stores-accordion-body');
                    if (firstCityBody) {
                        firstCityBody.style.display = 'block';
                        firstCity.classList.add('is-open');
                    }
                }
            }

            countryItems.forEach(function(countryItem) {
                var countryHeader = countryItem.querySelector('.stores-accordion-header');
                var countryBody = countryItem.querySelector('.stores-accordion-body');
                if (!countryHeader || !countryBody) return;

                countryHeader.addEventListener('click', function(e) {
                    if (e.target.closest('.stores-accordion-item--city')) return;
                    var isOpen = countryItem.classList.contains('is-open');
                    countryItems.forEach(function(other) {
                        var ob = other.querySelector('.stores-accordion-body');
                        if (ob) ob.style.display = 'none';
                        other.classList.remove('is-open');
                    });
                    if (!isOpen) {
                        countryBody.style.display = 'block';
                        countryItem.classList.add('is-open');
                    }
                });
            });

            var cityItems = document.querySelectorAll('.stores-accordion-item--city');
            cityItems.forEach(function(cityItem) {
                var cityHeader = cityItem.querySelector('.stores-accordion-header--city');
                var cityBody = cityItem.querySelector('.stores-accordion-body');
                if (!cityHeader || !cityBody) return;

                cityHeader.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var isOpen = cityItem.classList.contains('is-open');
                    cityBody.style.display = isOpen ? 'none' : 'block';
                    cityItem.classList.toggle('is-open', !isOpen);
                });
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initStoresMap);
    } else {
        initStoresMap();
    }
})();
