<?php
/**
 * ACF поля для магазинов (страница "Где купить")
 *
 * @package The_Capon
 */

// Регистрируем ACF поля после инициализации ACF
add_action( 'acf/init', 'the_capon_register_stores_fields' );

function the_capon_register_stores_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_stores_page',
			'title'                 => 'Магазины (страница \"Где купить\")',
			'fields'                => array(
				array(
					'key'               => 'field_stores_repeater',
					'label'             => 'Магазины',
					'name'              => 'stores',
					'type'              => 'repeater',
					'instructions'      => 'Добавьте магазины-продавцов',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'collapsed'         => 'field_store_name',
					'min'               => 0,
					'max'               => 0,
					'layout'            => 'block',
					'button_label'      => 'Добавить магазин',
					'sub_fields'        => array(
						array(
							'key'               => 'field_store_name',
							'label'             => 'Название магазина',
							'name'              => 'name',
							'type'              => 'text',
							'required'          => 1,
							'wrapper'           => array(
								'width' => '50',
							),
						),
						array(
							'key'               => 'field_store_city',
							'label'             => 'Город',
							'name'              => 'city',
							'type'              => 'text',
							'required'          => 0,
							'wrapper'           => array(
								'width' => '50',
							),
						),
						array(
							'key'               => 'field_store_address',
							'label'             => 'Адрес',
							'name'              => 'address',
							'type'              => 'text',
							'required'          => 1,
							'wrapper'           => array(
								'width' => '100',
							),
						),
						array(
							'key'               => 'field_store_phone',
							'label'             => 'Телефон',
							'name'              => 'phone',
							'type'              => 'text',
							'required'          => 0,
							'wrapper'           => array(
								'width' => '33',
							),
						),
						array(
							'key'               => 'field_store_email',
							'label'             => 'Email',
							'name'              => 'email',
							'type'              => 'email',
							'required'          => 0,
							'wrapper'           => array(
								'width' => '33',
							),
						),
						array(
							'key'               => 'field_store_website',
							'label'             => 'Сайт',
							'name'              => 'website',
							'type'              => 'url',
							'required'          => 0,
							'wrapper'           => array(
								'width' => '34',
							),
						),
						array(
							'key'               => 'field_store_socials',
							'label'             => 'Соцсети',
							'name'              => 'socials',
							'type'              => 'textarea',
							'instructions'      => 'Например: Instagram, VK и т.п.',
							'required'          => 0,
							'wrapper'           => array(
								'width' => '100',
							),
							'rows'              => 2,
						),
						array(
							'key'               => 'field_store_lat',
							'label'             => 'Координаты (широта)',
							'name'              => 'lat',
							'type'              => 'text',
							'instructions'      => 'Например: 55.7558',
							'required'          => 1,
							'wrapper'           => array(
								'width' => '50',
							),
						),
						array(
							'key'               => 'field_store_lng',
							'label'             => 'Координаты (долгота)',
							'name'              => 'lng',
							'type'              => 'text',
							'instructions'      => 'Например: 37.6173',
							'required'          => 1,
							'wrapper'           => array(
								'width' => '50',
							),
						),
						array(
							'key'               => 'field_store_description',
							'label'             => 'Описание',
							'name'              => 'description',
							'type'              => 'textarea',
							'required'          => 0,
							'wrapper'           => array(
								'width' => '100',
							),
							'rows'              => 3,
						),
					),
				),
				array(
					'key'               => 'field_stores_map_center_lat',
					'label'             => 'Центр карты (широта)',
					'name'              => 'map_center_lat',
					'type'              => 'text',
					'instructions'      => 'Если не задано, будет использована первая точка',
					'required'          => 0,
					'wrapper'           => array(
						'width' => '33',
					),
				),
				array(
					'key'               => 'field_stores_map_center_lng',
					'label'             => 'Центр карты (долгота)',
					'name'              => 'map_center_lng',
					'type'              => 'text',
					'required'          => 0,
					'wrapper'           => array(
						'width' => '33',
					),
				),
				array(
					'key'               => 'field_stores_map_zoom',
					'label'             => 'Зум карты',
					'name'              => 'map_zoom',
					'type'              => 'number',
					'default_value'     => 10,
					'min'               => 1,
					'max'               => 18,
					'wrapper'           => array(
						'width' => '34',
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-where-buy.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);
}

