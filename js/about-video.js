/**
 * Управление видео на странице "О компании"
 * Поведение как на детальной: по центру одна кнопка play/pause.
 */
(function() {
    'use strict';

    function initAboutVideo() {
        const video   = document.querySelector('#about-second-video');
        const playBtn = document.querySelector('.about-video-play-btn');

        if (!video || !playBtn) {
            return;
        }

        // Всегда запускаем с паузы и показываем кнопку
        video.muted = true;
        video.pause();
        playBtn.classList.remove('playing');

        function setPlayingState(isPlaying) {
            if (isPlaying) {
                playBtn.classList.add('playing');
            } else {
                playBtn.classList.remove('playing');
            }
        }

        function togglePlay() {
            if (video.paused) {
                video.play().then(function() {
                    setPlayingState(true);
                }).catch(function() {
                    // ignore
                });
            } else {
                video.pause();
                setPlayingState(false);
            }
        }

        // Клик по центральной кнопке
        playBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            togglePlay();
        });

        // Клик по самому видео
        video.addEventListener('click', function(e) {
            e.stopPropagation();
            togglePlay();
        });

        // Синхронизируем состояние по событиям видео
        video.addEventListener('play', function() {
            setPlayingState(true);
        });

        video.addEventListener('pause', function() {
            setPlayingState(false);
        });

        video.addEventListener('ended', function() {
            setPlayingState(false);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAboutVideo);
    } else {
        initAboutVideo();
    }
})();
