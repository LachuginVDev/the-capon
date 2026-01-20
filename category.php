<?php
/**
 * Template for displaying Category archive (collections)
 * Шаблон для отображения рубрик (коллекций)
 *
 * @package The_Capon
 */

get_header();

// Получаем текущую категорию
$current_category = get_queried_object();
$parent_category = null;

// Если это дочерняя категория (коллекция)
if ( $current_category->parent != 0 ) {
	$parent_category = get_category( $current_category->parent );
}
?>

<!-- Заголовок коллекции/бренда -->
<section class="model-header">
	<div class="container">
		<h1 class="model-title"><?php single_cat_title(); ?></h1>
		
		<?php if ( category_description() ) : ?>
			<p class="model-description">
				<?php echo wp_strip_all_tags( category_description() ); ?>
			</p>
		<?php endif; ?>
	</div>
</section>

<?php
// Проверяем, это родительская или дочерняя категория
$child_categories = get_categories(
	array(
		'parent'     => $current_category->term_id,
		'hide_empty' => false,
	)
);

// Если есть дочерние категории - это бренд, показываем коллекции
if ( ! empty( $child_categories ) ) :
	?>
	<!-- Коллекции бренда -->
	<section class="collection-section">
		<div class="container">
			<div class="collection-grid">
				<?php foreach ( $child_categories as $child_cat ) : ?>
					<div class="collection-card">
						<?php
						// Получаем первую запись из коллекции для изображения
						$child_posts = get_posts(
							array(
								'category'       => $child_cat->term_id,
								'posts_per_page' => 1,
							)
						);

						$image_url = get_template_directory_uri() . '/assets/images/c1.png';
						if ( ! empty( $child_posts ) ) {
							$post_id = $child_posts[0]->ID;
							
							// Пытаемся получить изображение из ACF
							$slides = get_field( 'model_slides', $post_id );
							if ( $slides && is_array( $slides ) && ! empty( $slides ) ) {
								// Берем первое изображение из галереи первого слайда
								if ( isset( $slides[0]['gallery'] ) && is_array( $slides[0]['gallery'] ) && ! empty( $slides[0]['gallery'] ) ) {
									$first_image = $slides[0]['gallery'][0];
									if ( isset( $first_image['url'] ) ) {
										$image_url = $first_image['url'];
									}
								}
							}
							
							// Fallback на миниатюру поста
							if ( $image_url === get_template_directory_uri() . '/assets/images/c1.png' && has_post_thumbnail( $post_id ) ) {
								$image_url = get_the_post_thumbnail_url( $post_id, 'full' );
							}
						}
						?>
						<div class="collection-card-image" style="background-image: url('<?php echo esc_url( $image_url ); ?>');">
							<div class="collection-card-overlay">
								<h3 class="collection-card-title"><?php echo esc_html( $child_cat->name ); ?></h3>
								<a href="<?php echo esc_url( get_category_link( $child_cat->term_id ) ); ?>" class="collection-card-btn">Смотреть коллекцию</a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

<?php else : ?>
	<!-- Фильтры (только для страниц с моделями) -->
	<section class="model-filters">
		<div class="container">
			<form method="GET" action="<?php echo esc_url( get_category_link( $current_category->term_id ) ); ?>" class="filters-wrapper">
				<div class="filter-item">
					<select name="silhouette" class="filter-select" onchange="this.form.submit()">
						<option value="">Силуэт</option>
						<?php
						$silhouette_options = the_capon_get_filter_options( 'silhouette' );
						foreach ( $silhouette_options as $value => $label ) :
							$selected = isset( $_GET['silhouette'] ) && $_GET['silhouette'] === $value ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-item">
					<select name="neckline" class="filter-select" onchange="this.form.submit()">
						<option value="">Вырез</option>
						<?php
						$neckline_options = the_capon_get_filter_options( 'neckline' );
						foreach ( $neckline_options as $value => $label ) :
							$selected = isset( $_GET['neckline'] ) && $_GET['neckline'] === $value ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-item">
					<select name="fabric" class="filter-select" onchange="this.form.submit()">
						<option value="">Ткань</option>
						<?php
						$fabric_options = the_capon_get_filter_options( 'fabric' );
						foreach ( $fabric_options as $value => $label ) :
							$selected = isset( $_GET['fabric'] ) && $_GET['fabric'] === $value ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="filter-item">
					<select name="color" class="filter-select" onchange="this.form.submit()">
						<option value="">Цвет</option>
						<?php
						$color_options = the_capon_get_filter_options( 'color' );
						foreach ( $color_options as $value => $label ) :
							$selected = isset( $_GET['color'] ) && $_GET['color'] === $value ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php echo $selected; ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
		</div>
	</section>

	<!-- Модели в коллекции (на основе model-page.html) -->
	<section class="model-cards-section">
		<div class="container">
			<?php
			// Получаем активные фильтры
			$meta_query = array( 'relation' => 'AND' );

			if ( ! empty( $_GET['silhouette'] ) ) {
				$meta_query[] = array(
					'key'     => 'model_silhouette',
					'value'   => sanitize_text_field( $_GET['silhouette'] ),
					'compare' => '=',
				);
			}

			if ( ! empty( $_GET['neckline'] ) ) {
				$meta_query[] = array(
					'key'     => 'model_neckline',
					'value'   => sanitize_text_field( $_GET['neckline'] ),
					'compare' => '=',
				);
			}

			if ( ! empty( $_GET['fabric'] ) ) {
				$meta_query[] = array(
					'key'     => 'model_fabric',
					'value'   => sanitize_text_field( $_GET['fabric'] ),
					'compare' => 'LIKE',
				);
			}

			if ( ! empty( $_GET['color'] ) ) {
				$meta_query[] = array(
					'key'     => 'model_color',
					'value'   => sanitize_text_field( $_GET['color'] ),
					'compare' => 'LIKE',
				);
			}

			// Создаем новый запрос с фильтрами
			$query_args = array(
				'cat'            => $current_category->term_id,
				'posts_per_page' => get_option( 'posts_per_page' ),
				'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
			);
			
			// Добавляем meta_query только если есть фильтры
			if ( count( $meta_query ) > 1 ) {
				$query_args['meta_query'] = $meta_query;
			}
			
			$filtered_query = new WP_Query( $query_args );

			if ( $filtered_query->have_posts() ) :
				?>
				<div class="model-cards-grid">
					<?php
					while ( $filtered_query->have_posts() ) :
						$filtered_query->the_post();
						
						// Получаем изображение из ACF или fallback
						$image_url = get_template_directory_uri() . '/assets/images/c1.png';
						$slides = get_field( 'model_slides' );
						
						// Пытаемся получить изображение из ACF
						if ( $slides && is_array( $slides ) && ! empty( $slides ) ) {
							// Берем первое изображение из галереи первого слайда
							if ( isset( $slides[0]['gallery'] ) && is_array( $slides[0]['gallery'] ) && ! empty( $slides[0]['gallery'] ) ) {
								$first_image = $slides[0]['gallery'][0];
								if ( isset( $first_image['url'] ) ) {
									$image_url = $first_image['url'];
								}
							}
						}
						
						// Fallback на миниатюру поста
						if ( $image_url === get_template_directory_uri() . '/assets/images/c1.png' && has_post_thumbnail() ) {
							$image_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );
						}
						?>
						<a href="<?php the_permalink(); ?>" class="model-card">
							<div class="model-card-image" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
							<h3 class="model-card-name"><?php the_title(); ?></h3>
						</a>
					<?php endwhile; ?>
				</div>

				<?php
				// Пагинация
				echo paginate_links(
					array(
						'total'     => $filtered_query->max_num_pages,
						'current'   => max( 1, get_query_var( 'paged' ) ),
						'mid_size'  => 2,
						'prev_text' => '← Назад',
						'next_text' => 'Вперед →',
						'type'      => 'list',
					)
				);
				wp_reset_postdata();
				?>

			<?php else : ?>
				<p>В этой коллекции пока нет моделей с выбранными параметрами.</p>
			<?php
			endif;
			wp_reset_postdata();
			?>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();

