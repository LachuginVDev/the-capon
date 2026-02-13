<?php
/**
 * Template Name: Где купить
 *
 * @package The_Capon
 */

get_header();

// Собираем магазины из профилей пользователей (user_meta 'user_stores')
$stores = array();

$users = get_users(
	array(
		'fields' => array( 'ID', 'display_name' ),
	)
);

if ( ! empty( $users ) ) {
	foreach ( $users as $user ) {
		$user_stores = get_user_meta( $user->ID, 'user_stores', true );
		if ( ! is_array( $user_stores ) ) {
			continue;
		}

		foreach ( $user_stores as $store ) {
			if ( ! is_array( $store ) ) {
				continue;
			}

			$store['owner_id']   = $user->ID;
			$store['owner_name'] = $user->display_name;
			$stores[]            = $store;
		}
	}
}

// Для магазинов с координатами, но без страны/города — получаем через обратное геокодирование (кэш в transient)
if ( ! empty( $stores ) && function_exists( 'the_capon_reverse_geocode' ) ) {
	foreach ( $stores as $index => &$store ) {
		$has_coords   = ! empty( $store['lat'] ) && ! empty( $store['lng'] );
		$need_country = empty( $store['country'] ) || trim( (string) $store['country'] ) === '';
		$need_city    = empty( $store['city'] ) || trim( (string) $store['city'] ) === '';
		if ( $has_coords && ( $need_country || $need_city ) ) {
			$rev = the_capon_reverse_geocode( $store['lat'], $store['lng'] );
			if ( $need_country && $rev['country'] !== '' ) {
				$store['country'] = $rev['country'];
			}
			if ( $need_city && $rev['city'] !== '' ) {
				$store['city'] = $rev['city'];
			}
		}
	}
	unset( $store );
}

// Центр карты: ищем первый магазин с координатами или Москва по умолчанию
$map_center_lat = '55.7558';
$map_center_lng = '37.6173';
$map_zoom       = 10;

if ( ! empty( $stores ) ) {
	foreach ( $stores as $store ) {
		if ( ! empty( $store['lat'] ) && ! empty( $store['lng'] ) ) {
			$map_center_lat = $store['lat'];
			$map_center_lng = $store['lng'];
			break;
		}
	}
}

// Группируем магазины по стране, затем по городу (для вложенного аккордеона)
$grouped_by_country = array();

if ( ! empty( $stores ) ) {
	foreach ( $stores as $index => $store ) {
		$country = isset( $store['country'] ) ? trim( (string) $store['country'] ) : '';
		$city    = isset( $store['city'] ) ? trim( (string) $store['city'] ) : '';
		if ( $country === '' ) {
			$country = 'Другие страны';
		}
		if ( $city === '' ) {
			$city = 'Другие города';
		}

		if ( ! isset( $grouped_by_country[ $country ] ) ) {
			$grouped_by_country[ $country ] = array();
		}
		if ( ! isset( $grouped_by_country[ $country ][ $city ] ) ) {
			$grouped_by_country[ $country ][ $city ] = array();
		}

		$grouped_by_country[ $country ][ $city ][] = array(
			'index' => $index,
			'store' => $store,
		);
	}

	// Сортировка: страны по алфавиту, «Другие страны» в конец; города по алфавиту, «Другие города» в конец
	$other_country = 'Другие страны';
	$other_city    = 'Другие города';

	uksort( $grouped_by_country, function( $a, $b ) use ( $other_country ) {
		if ( $a === $other_country ) return 1;
		if ( $b === $other_country ) return -1;
		return strcasecmp( $a, $b );
	} );

	foreach ( $grouped_by_country as $country_name => &$cities ) {
		uksort( $cities, function( $a, $b ) use ( $other_city ) {
			if ( $a === $other_city ) return 1;
			if ( $b === $other_city ) return -1;
			return strcasecmp( $a, $b );
		} );
	}
	unset( $cities );
}

?>

<div class="container">
	<h2 class="form-title">Где купить</h2>

	<?php if ( ! empty( $stores ) ) : ?>
		<section class="stores-map-section">
			<div id="stores-map" class="stores-map"></div>
		</section>

		<section class="stores-list-section">
			<h3 class="stores-list-title">Наши магазины</h3>
			<div class="stores-list" id="stores-list">
				<?php if ( ! empty( $grouped_by_country ) ) : ?>
					<div class="stores-accordion stores-accordion--country">
						<?php foreach ( $grouped_by_country as $country_name => $cities ) : ?>
							<?php
							$country_stores_count = 0;
							foreach ( $cities as $city_stores ) {
								$country_stores_count += count( $city_stores );
							}
							?>
							<div class="stores-accordion-item stores-accordion-item--country">
								<button class="stores-accordion-header" type="button">
									<span><?php echo esc_html( $country_name ); ?></span>
									<span class="stores-accordion-count"><?php echo (int) $country_stores_count; ?></span>
								</button>
								<div class="stores-accordion-body">
									<div class="stores-accordion stores-accordion--city">
										<?php foreach ( $cities as $city_name => $city_stores ) : ?>
											<div class="stores-accordion-item stores-accordion-item--city">
												<button class="stores-accordion-header stores-accordion-header--city" type="button">
													<span><?php echo esc_html( $city_name ); ?></span>
													<span class="stores-accordion-count"><?php echo (int) count( $city_stores ); ?></span>
												</button>
												<div class="stores-accordion-body">
													<?php
													foreach ( $city_stores as $item ) :
														$index       = $item['index'];
														$store       = $item['store'];
														$name        = isset( $store['name'] ) ? $store['name'] : '';
														$address     = isset( $store['address'] ) ? $store['address'] : '';
														$phone       = isset( $store['phone'] ) ? $store['phone'] : '';
														$email       = isset( $store['email'] ) ? $store['email'] : '';
														$website     = isset( $store['website'] ) ? $store['website'] : '';
														$socials     = isset( $store['socials'] ) ? $store['socials'] : '';
														$lat         = isset( $store['lat'] ) ? $store['lat'] : '';
														$lng         = isset( $store['lng'] ) ? $store['lng'] : '';
														$description = isset( $store['description'] ) ? $store['description'] : '';
														$has_coords  = ! empty( $lat ) && ! empty( $lng );
														?>
														<div class="store-item<?php echo $has_coords ? '' : ' store-item--no-coords'; ?>"
															data-index="<?php echo esc_attr( $index ); ?>"
															data-lat="<?php echo esc_attr( $lat ); ?>"
															data-lng="<?php echo esc_attr( $lng ); ?>">
															<h4 class="store-item__name">
																<?php echo esc_html( $name ); ?>
															</h4>
															<?php if ( $address ) : ?>
																<p class="store-item__address"><?php echo esc_html( $address ); ?></p>
															<?php endif; ?>
															<div class="store-item__contacts">
																<?php if ( $phone ) : ?>
																	<div><strong>Телефон:</strong> <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></div>
																<?php endif; ?>
																<?php if ( $email ) : ?>
																	<div><strong>Email:</strong> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
																<?php endif; ?>
																<?php if ( $website ) : ?>
																	<div><strong>Сайт:</strong> <a href="<?php echo esc_url( $website ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $website ); ?></a></div>
																<?php endif; ?>
																<?php if ( $socials ) : ?>
																	<div><strong>Соцсети:</strong> <?php echo nl2br( esc_html( $socials ) ); ?></div>
																<?php endif; ?>
															</div>
															<?php if ( $description ) : ?>
																<p class="store-item__description"><?php echo esc_html( $description ); ?></p>
															<?php endif; ?>
														</div>
													<?php endforeach; ?>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>

		<script>
			window.theCaponStores = {
				center: {
					lat: '<?php echo esc_js( $map_center_lat ); ?>',
					lng: '<?php echo esc_js( $map_center_lng ); ?>',
					zoom: <?php echo (int) $map_zoom; ?>
				},
					items: <?php echo wp_json_encode( $stores ); ?>
			};
		</script>
	<?php else : ?>
		<p style="text-align: center; padding: 40px 0; color: #666;">Магазины пока не добавлены.</p>
	<?php endif; ?>
</div>

<?php
get_footer();

