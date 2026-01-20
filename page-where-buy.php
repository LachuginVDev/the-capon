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
				<?php 
				foreach ( $stores as $index => $store ) :
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

