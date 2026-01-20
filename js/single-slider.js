/**
 * Слайдер для детальной страницы модели
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Слайдер основных слайдов (видео + альбомы)
        const slider = document.querySelector('.model-slides-container');
        if (slider) {
            const slides = slider.querySelectorAll('.model-slide');
            const prevBtn = slider.querySelector('.model-slider-prev');
            const nextBtn = slider.querySelector('.model-slider-next');
            const indicators = slider.querySelectorAll('.model-slider-indicator');
            
            let currentSlide = 0;
            const totalSlides = slides.length;

            // Если слайдов меньше 2, скрываем навигацию
            if (totalSlides < 2) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                if (indicators.length > 0) {
                    indicators.forEach(ind => ind.style.display = 'none');
                }
            } else {
                // Функция показа слайда
                function showSlide(index) {
                    slides.forEach(slide => slide.classList.remove('active'));
                    indicators.forEach(ind => ind.classList.remove('active'));

                    if (slides[index]) {
                        slides[index].classList.add('active');
                    }
                    if (indicators[index]) {
                        indicators[index].classList.add('active');
                    }

                    currentSlide = index;
                }

                // Следующий слайд
                function nextSlide() {
                    const next = (currentSlide + 1) % totalSlides;
                    showSlide(next);
                }

                // Предыдущий слайд
                function prevSlide() {
                    const prev = (currentSlide - 1 + totalSlides) % totalSlides;
                    showSlide(prev);
                }

                // Обработчики кнопок
                if (nextBtn) {
                    nextBtn.addEventListener('click', nextSlide);
                }
                if (prevBtn) {
                    prevBtn.addEventListener('click', prevSlide);
                }

                // Обработчики индикаторов
                indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', function() {
                        showSlide(index);
                    });
                });
            }
        }

        // Слайдер для галереи внутри каждого слайда
        const galleries = document.querySelectorAll('.model-slide-gallery');
        galleries.forEach(function(gallery) {
            const items = gallery.querySelectorAll('.model-slide-gallery-item');
            const prevBtn = gallery.querySelector('.model-gallery-prev');
            const nextBtn = gallery.querySelector('.model-gallery-next');
            const indicators = gallery.querySelectorAll('.model-gallery-indicator');
            
            if (items.length < 2) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                if (indicators.length > 0) {
                    indicators.forEach(ind => ind.style.display = 'none');
                }
                return;
            }

            let currentIndex = 0;

            function showGalleryItem(index) {
                items.forEach(item => item.classList.remove('active'));
                indicators.forEach(ind => ind.classList.remove('active'));

                if (items[index]) {
                    items[index].classList.add('active');
                }
                if (indicators[index]) {
                    indicators[index].classList.add('active');
                }

                currentIndex = index;
            }

            function nextGalleryItem() {
                const next = (currentIndex + 1) % items.length;
                showGalleryItem(next);
            }

            function prevGalleryItem() {
                const prev = (currentIndex - 1 + items.length) % items.length;
                showGalleryItem(prev);
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', nextGalleryItem);
            }
            if (prevBtn) {
                prevBtn.addEventListener('click', prevGalleryItem);
            }

            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', function() {
                    showGalleryItem(index);
                });
            });
        });
    });
})();
