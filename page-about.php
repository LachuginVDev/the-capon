<?php
/**
 * Template Name: О компании
 *
 * @package The_Capon
 */

get_header(); ?>

    <?php 
    // Получаем данные из ACF
    $first_title = get_field('about_first_title') ?: 'О нас';
    $first_bg = get_field('about_first_bg');
    $first_bg_url = $first_bg ? esc_url($first_bg) : esc_url(get_template_directory_uri() . '/assets/images/abautbg.png');
    
    $content_blocks = get_field('about_content_blocks');
    
    // Фон второй секции (как было)
    $second_bg = get_field('about_second_bg');
    $second_bg_url = $second_bg ? esc_url( $second_bg ) : esc_url( get_template_directory_uri() . '/assets/images/abautbg2.jpg' );
    
    // Видео второй секции - делаем по аналогии с детальной страницей (проверяем только наличие поля)
    $second_video = get_field( 'about_second_video' );
    
    $final_text = get_field( 'about_final_text' );
    ?>
    
    <!-- Секция О нас -->
    <section class="about-section" style="background-image: url('<?php echo $first_bg_url; ?>');">
        <div class="about-content">
            <h2 class="about-title"><?php echo esc_html($first_title); ?></h2>
        </div>
    </section>
    
    <!-- Секция Новости -->
    <?php if ( $content_blocks && is_array( $content_blocks ) ) : ?>
        <section class="news-section">
            <div class="container">
                <?php foreach ( $content_blocks as $block ) : 
                    $sidebar_title = isset( $block['sidebar_title'] ) ? $block['sidebar_title'] : '';
                    $sidebar_text = isset( $block['sidebar_text'] ) ? $block['sidebar_text'] : '';
                    $main_text = isset( $block['main_text'] ) ? $block['main_text'] : '';
                    $images = isset( $block['images'] ) ? $block['images'] : array();
                    ?>
                    <div class="news-content">
                        <!-- Левый блок -->
                        <div class="news-sidebar">
                            <?php if ( ! empty( $sidebar_title ) ) : ?>
                                <h3 class="sidebar-title"><?php echo esc_html( $sidebar_title ); ?></h3>
                            <?php endif; ?>
                            <?php if ( ! empty( $sidebar_text ) ) : ?>
                                <p class="sidebar-text"><?php echo esc_html( $sidebar_text ); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Основной блок -->
                        <div class="news-main">
                            <?php if ( ! empty( $main_text ) ) : ?>
                                <p class="news-title"><?php echo esc_html( $main_text ); ?></p>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $images ) && is_array( $images ) ) : ?>
                                <div class="news-main-images">
                                    <?php foreach ( $images as $image ) : 
                                        $image_url = isset( $image['url'] ) ? $image['url'] : '';
                                        $image_alt = isset( $image['alt'] ) ? $image['alt'] : '';
                                        if ( ! empty( $image_url ) ) :
                                        ?>
                                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
                                    <?php endif; endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Вторая секция с фоновым изображением или видео -->
    <?php if ( $second_video && is_array( $second_video ) && ! empty( $second_video['url'] ) ) : ?>
        <section class="about-section about-section-video">
            <video id="about-second-video" muted loop playsinline preload="auto">
                <source src="<?php echo esc_url( $second_video['url'] ); ?>" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>
            <button class="about-video-play-btn" aria-label="Воспроизвести видео" type="button">
                <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="40" fill="rgba(255, 255, 255, 0.8)"/>
                    <path d="M32 24L56 40L32 56V24Z" fill="#000"/>
                </svg>
            </button>
        </section>
    <?php else : ?>
        <section class="about-section" style="background-image: url('<?php echo $second_bg_url; ?>');">
            <div class="about-content">
                <h2 class="about-title">
                    <svg width="58" height="67" viewBox="0 0 58 67" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M57.75 33.342L-3.14031e-06 66.684L-2.25464e-07 6.23255e-05L57.75 33.342Z" fill="white" />
                    </svg>
                </h2>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Финальный блок контента -->
    <?php if ( ! empty( $final_text ) ) : ?>
        <section class="news-section">
            <div class="container">
                <div class="news-content">
                    <!-- Левый блок -->
                    <div class="news-sidebar"></div>

                    <!-- Основной блок -->
                    <div class="news-main">
                        <p class="news-title"><?php echo esc_html( $final_text ); ?></p>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

<?php get_footer(); ?>
