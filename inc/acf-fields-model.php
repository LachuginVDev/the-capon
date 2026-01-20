<?php
/**
 * ACF поля для моделей (старые поля: силуэт, вырез, ткань, цвет, цена)
 *
 * @package The_Capon
 */

// Регистрируем ACF поля после инициализации ACF
add_action( 'acf/init', 'the_capon_register_model_attributes_fields' );

function the_capon_register_model_attributes_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_model_attributes',
			'title'                 => 'Параметры модели',
			'fields'                => array(
				array(
					'key'               => 'field_model_silhouette',
					'label'             => 'Силуэт',
					'name'              => 'model_silhouette',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'a-line'    => 'А-силуэт',
						'mermaid'   => 'Русалка',
						'ball-gown' => 'Пышное',
						'straight'  => 'Прямое',
					),
					'default_value'     => '',
					'allow_null'         => 1,
					'multiple'           => 0,
					'ui'                 => 0,
					'return_format'      => 'value',
					'ajax'               => 0,
					'placeholder'        => '',
				),
				array(
					'key'               => 'field_model_neckline',
					'label'             => 'Вырез',
					'name'              => 'model_neckline',
					'type'              => 'select',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'v-neck' => 'V-образный',
						'round'  => 'Круглый',
						'heart'  => 'Сердце',
						'boat'   => 'Лодочка',
					),
					'default_value'     => '',
					'allow_null'         => 1,
					'multiple'           => 0,
					'ui'                 => 0,
					'return_format'      => 'value',
					'ajax'               => 0,
					'placeholder'        => '',
				),
				array(
					'key'               => 'field_model_fabric',
					'label'             => 'Ткань',
					'name'              => 'model_fabric',
					'type'              => 'checkbox',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'silk'    => 'Шелк',
						'lace'    => 'Кружево',
						'satin'   => 'Атлас',
						'chiffon' => 'Шифон',
					),
					'allow_custom'      => 0,
					'default_value'     => array(),
					'layout'            => 'vertical',
					'toggle'            => 0,
					'return_format'      => 'value',
				),
				array(
					'key'               => 'field_model_color',
					'label'             => 'Цвет',
					'name'              => 'model_color',
					'type'              => 'checkbox',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'choices'           => array(
						'white'     => 'Белый',
						'ivory'     => 'Айвори',
						'champagne' => 'Шампань',
						'beige'     => 'Бежевый',
						'blush'     => 'Пудровый',
					),
					'allow_custom'      => 0,
					'default_value'     => array(),
					'layout'            => 'vertical',
					'toggle'            => 0,
					'return_format'      => 'value',
				),
				array(
					'key'               => 'field_model_price',
					'label'             => 'Цена',
					'name'              => 'model_price',
					'type'              => 'number',
					'instructions'      => '',
					'required'          => 0,
					'conditional_logic' => 0,
					'wrapper'           => array(
						'width' => '',
						'class' => '',
						'id'    => '',
					),
					'default_value'     => '',
					'placeholder'       => '',
					'prepend'           => '',
					'append'            => '',
					'min'               => 0,
					'max'               => '',
					'step'              => '',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
			),
			'menu_order'            => 1,
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
