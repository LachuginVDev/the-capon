<?php
/**
 * The template for displaying all single posts (Models detail)
 * Шаблон для отображения детальной страницы модели
 *
 * @package The_Capon
 */

get_header();

while ( have_posts() ) :
    the_post();

    // Получаем категории
    $categories = get_the_category();
    $parent_category = null;
    $current_category = null;

    // Определяем родительскую и текущую категорию
    foreach ( $categories as $category ) {
        if ( $category->parent != 0 ) {
            $current_category = $category;
            $parent_category = get_category( $category->parent );
        }
    }

    // Получаем ACF поля через повторитель
    $slides = get_field( 'model_slides' );
    
    // Получаем цену для отображения
    $price = function_exists('get_field') ? get_field('model_price') : get_post_meta( get_the_ID(), '_model_price', true );
    ?>

    <!-- Детальная страница модели -->

    <section class="model-detail-content">
        <div class="model-detail-images">
            <?php if ( $slides && is_array( $slides ) && ! empty( $slides ) ) : ?>
                <div class="model-slides-container">
                    <div class="model-slides-wrapper">
                        <?php foreach ( $slides as $index => $slide ) : ?>
                            <?php
                            $slide_video = isset( $slide['video'] ) ? $slide['video'] : null;
                            $slide_gallery = isset( $slide['gallery'] ) ? $slide['gallery'] : array();
                            $is_first = $index === 0;
                            ?>
                            <div class="model-slide <?php echo $is_first ? 'active' : ''; ?>">
                                <?php if ( $is_first && $slide_video ) : ?>
                                    <!-- Первый слайд - видео -->
                                    <div class="model-slide-video">
                                        <video id="model-video-<?php echo get_the_ID(); ?>" muted loop playsinline width="100%" height="100%">
                                            <source src="<?php echo esc_url( $slide_video['url'] ); ?>" type="video/mp4">
                                            Ваш браузер не поддерживает видео.
                                        </video>
                                        <button class="model-video-play-btn" aria-label="Воспроизвести видео">
                                            <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="40" cy="40" r="40" fill="rgba(255, 255, 255, 0.8)"/>
                                                <path d="M32 24L56 40L32 56V24Z" fill="#000"/>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endif; ?>

                                <?php if ( ! empty( $slide_gallery ) ) : ?>
                                    <!-- Альбом фотографий - слайдер -->
                                    <div class="model-slide-gallery <?php echo ( $is_first && $slide_video ) ? 'model-slide-gallery-after-video' : ''; ?>">
                                        <?php foreach ( $slide_gallery as $gallery_index => $gallery_image ) : ?>
                                            <div class="model-slide-gallery-item <?php echo $gallery_index === 0 ? 'active' : ''; ?>" data-index="<?php echo $gallery_index; ?>">
                                                <img src="<?php echo esc_url( $gallery_image['url'] ); ?>" alt="<?php echo esc_attr( $gallery_image['alt'] ?: get_the_title() ); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if ( count( $slide_gallery ) > 1 ) : ?>
                                            <!-- Навигация для галереи -->
                                            <div class="model-gallery-nav">
                                                <button class="model-gallery-prev" aria-label="Предыдущее фото">‹</button>
                                                <button class="model-gallery-next" aria-label="Следующее фото">›</button>
                                            </div>
                                            <!-- Индикаторы для галереи -->
                                            <div class="model-gallery-indicators">
                                                <?php foreach ( $slide_gallery as $gallery_index => $gallery_image ) : ?>
                                                    <button class="model-gallery-indicator <?php echo $gallery_index === 0 ? 'active' : ''; ?>" data-index="<?php echo $gallery_index; ?>" aria-label="Фото <?php echo $gallery_index + 1; ?>"></button>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ( count( $slides ) > 1 ) : ?>
                        <!-- Навигация слайдера -->
                        <div class="model-slider-nav">
                            <button class="model-slider-prev" aria-label="Предыдущий слайд">‹</button>
                            <button class="model-slider-next" aria-label="Следующий слайд">›</button>
                        </div>

                        <!-- Индикаторы -->
                        <div class="model-slider-indicators">
                            <?php foreach ( $slides as $index => $slide ) : ?>
                                <button class="model-slider-indicator <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>" aria-label="Слайд <?php echo $index + 1; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <!-- Fallback если нет ACF полей -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="model-detail-main-image">
                        <?php the_post_thumbnail( 'full' ); ?>
                    </div>
                <?php else : ?>
                    <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/c1.png' ); ?>" alt="<?php the_title(); ?>">
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="model-detail-layout">
            <!-- Правая колонка - информация -->
            <div class="model-detail-info">
                <div class="model-detail-header">
                    <div class="model-detail-links">
                        <?php
                        $prev = get_previous_post(true);
                        $next = get_next_post(true);
                        ?>
                        <?php if ( $prev ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $prev->ID ) ); ?>">←</a>
                        <?php endif; ?>
                        <?php if ( $next ) : ?>
                            <a href="<?php echo esc_url( get_permalink( $next->ID ) ); ?>">следующее</a>
                        <?php endif; ?>
                    </div>

                    <h1 class="model-detail-title"><?php the_title(); ?></h1>
                    <div class="model-detail-breadcrumbs">
                        <?php
                        $category = get_the_category();
                        if ( !empty($category) ) {
                            $cat = $category[0];
                            echo '<a href="' . esc_url( get_category_link($cat->term_id) ) . '">' . esc_html( $cat->name ) . '</a>';
                            echo ' / ';
                        }
                        echo esc_html( get_the_title() );
                        ?>
                    </div>
                </div>

                <div class="model-detail-description">
                    <?php
                    // Выводим описание из первого слайда или контент поста
                    if ( $slides && is_array( $slides ) && ! empty( $slides ) && isset( $slides[0]['description'] ) && ! empty( $slides[0]['description'] ) ) {
                        echo wp_kses_post( wpautop( $slides[0]['description'] ) );
                    } else {
                        the_content();
                    }
                    ?>
                </div>

                <?php if ( $parent_category ) : ?>
                    <div class="model-detail-brand">
                        <strong>Бренд</strong>
                        <a href="<?php echo esc_url( get_category_link( $parent_category->term_id ) ); ?>"><?php echo esc_html( $parent_category->name ); ?></a>
                    </div>
                <?php endif; ?>

                <?php 
                // Выводим параметры из первого слайда (repeater)
                if ( $slides && is_array( $slides ) && ! empty( $slides ) && isset( $slides[0]['parameters'] ) && is_array( $slides[0]['parameters'] ) && ! empty( $slides[0]['parameters'] ) ) : 
                    $parameters = $slides[0]['parameters'];
                    ?>
                    <?php foreach ( $parameters as $param ) : 
                        $param_name = isset( $param['name'] ) ? $param['name'] : '';
                        $param_value = isset( $param['value'] ) ? $param['value'] : '';
                        if ( empty( $param_name ) && empty( $param_value ) ) continue;
                        ?>
                        <div class="model-detail-brand">
                            <?php if ( ! empty( $param_name ) ) : ?>
                                <strong><?php echo esc_html( $param_name ); ?></strong>
                                <span><?php echo esc_html( $param_value ); ?></span>
                            <?php else : ?>
                                <span><?php echo esc_html( $param_value ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ( $current_category ) : ?>
                    <div class="model-detail-collection">
                        <strong>Коллекция</strong>
                        <a href="<?php echo esc_url( get_category_link( $current_category->term_id ) ); ?>"><?php echo esc_html( $current_category->name ); ?></a>
                    </div>
                <?php endif; ?>

                <?php if ( has_excerpt() ) : ?>
                    <div class="model-detail-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>

                <?php 
                // Статус наличия товара
                $in_stock = get_field('model_in_stock');
                if ($in_stock === null) {
                    $in_stock = true; // По умолчанию в наличии
                }
                ?>
                <div class="model-detail-brand">
                    <strong>Наличие:</strong>
                    <span style="color: <?php echo $in_stock ? '#000' : '#000'; ?>; font-weight: 500; margin-left: 10px;">
                        <?php echo $in_stock ? 'В наличии' : 'Нет в наличии'; ?>
                    </span>
                </div>

                <?php 
                // Показываем блок действий только авторизованным пользователям
                if ( is_user_logged_in() ) : 
                    // Показываем цену из первого слайда или из старого поля
                    $display_price = '';
                    if ( $slides && is_array( $slides ) && ! empty( $slides ) && isset( $slides[0]['price'] ) && ! empty( $slides[0]['price'] ) ) {
                        $display_price = $slides[0]['price'];
                    } elseif ( $price ) {
                        $display_price = $price;
                    }
                    ?>
                    <div class="model-detail-actions">
                        <button class="btn btn-secondary make-order" data-model-id="<?php echo esc_attr(get_the_ID()); ?>">СДЕЛАТЬ ЗАКАЗ</button>
                        <?php if ( $display_price ) : ?>
                            <span><?php echo number_format( $display_price, 0, ',', ' ' ); ?> ₽</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </section>

    <!-- Похожие модели -->
    <?php
    $related_args = array(
            'posts_per_page' => 3,
            'post__not_in'   => array( get_the_ID() ),
            'orderby'        => 'rand',
    );

    if ( $current_category ) {
        $related_args['category__in'] = array( $current_category->term_id );
    }

    $related_query = new WP_Query( $related_args );

    if ( $related_query->have_posts() ) :
        ?>
        <section class="related-models">
            <div class="container">
                <h2 class="section-title">ВАМ также может понравиться</h2>
                <div class="model-cards-grid">
                    <?php
                    while ( $related_query->have_posts() ) :
                        $related_query->the_post();
                        ?>
                        <a href="<?php the_permalink(); ?>" class="model-card">
                            <div class="model-card-image" style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'full' ) ?: get_template_directory_uri() . '/assets/images/c1.png' ); ?>');"></div>
                            <h3 class="model-card-name"><?php the_title(); ?></h3>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php
        wp_reset_postdata();
    endif;

endwhile; // End of the loop.

get_footer();
