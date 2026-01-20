/**
 * Управление видео на детальной странице модели
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Управление видео
        const video = document.querySelector('.model-slide-video video');
        const playBtn = document.querySelector('.model-video-play-btn');
        
        if (video && playBtn) {
            // Устанавливаем видео без звука
            video.muted = true;
            
            // Пытаемся автозапустить видео
            const playPromise = video.play();
            
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    // Видео успешно запущено, скрываем кнопку
                    playBtn.classList.add('playing');
                }).catch(function(error) {
                    // Автозапуск не удался (политика браузера), показываем кнопку
                    playBtn.classList.remove('playing');
                });
            }
            
            // Скрываем кнопку плей когда видео играет
            video.addEventListener('play', function() {
                playBtn.classList.add('playing');
            });
            
            // Показываем кнопку плей когда видео на паузе
            video.addEventListener('pause', function() {
                playBtn.classList.remove('playing');
            });
            
            // Клик по кнопке плей
            playBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
            });
            
            // Клик по видео для паузы/воспроизведения
            video.addEventListener('click', function() {
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
            });
        }
    });
})();
