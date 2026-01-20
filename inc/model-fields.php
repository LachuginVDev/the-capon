<?php
/**
 * Вспомогательные функции для работы с полями моделей
 *
 * @package The_Capon
 */

/**
 * Получение списка значений для фильтров
 */
function the_capon_get_filter_options( $field_name ) {
	$options = array(
		'silhouette' => array(
			'a-line'     => 'А-силуэт',
			'mermaid'    => 'Русалка',
			'ball-gown'  => 'Пышное',
			'straight'   => 'Прямое',
		),
		'neckline'   => array(
			'v-neck' => 'V-образный',
			'round'  => 'Круглый',
			'heart'  => 'Сердце',
			'boat'   => 'Лодочка',
		),
		'fabric'     => array(
			'silk'    => 'Шелк',
			'lace'    => 'Кружево',
			'satin'   => 'Атлас',
			'chiffon' => 'Шифон',
		),
		'color'      => array(
			'white'     => 'Белый',
			'ivory'     => 'Айвори',
			'champagne' => 'Шампань',
			'blush'     => 'Пудровый',
		),
	);

	return isset( $options[ $field_name ] ) ? $options[ $field_name ] : array();
}


