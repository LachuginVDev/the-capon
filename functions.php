<?php
/**
 * The Capon functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package The_Capon
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function the_capon_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on The Capon, use a find and replace
		* to change 'the-capon' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'the-capon', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Основное меню', 'the-capon' ),
			'top-bar' => esc_html__( 'Верхнее меню (топ-бар)', 'the-capon' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'the_capon_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'the_capon_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function the_capon_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'the_capon_content_width', 640 );
}
add_action( 'after_setup_theme', 'the_capon_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function the_capon_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'the-capon' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'the-capon' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'the_capon_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function the_capon_scripts() {
	// Подключение шрифтов Google
	wp_enqueue_style( 'the-capon-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Alice:wght@400&family=Montserrat:wght@300;400;500;600;700&display=swap', array(), null );
	
	// Основные стили из assets
	wp_enqueue_style( 'the-capon-main-style', get_template_directory_uri() . '/assets/css/style.css', array(), _S_VERSION );
	
	// Стандартный style.css темы
	wp_enqueue_style( 'the-capon-style', get_stylesheet_uri(), array( 'the-capon-main-style' ), _S_VERSION );
	wp_style_add_data( 'the-capon-style', 'rtl', 'replace' );

	// Скрипт из assets
	wp_enqueue_script( 'the-capon-main-script', get_template_directory_uri() . '/assets/js/script.js', array(), _S_VERSION, true );
	
	// Навигация
	wp_enqueue_script( 'the-capon-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	// Слайдер и видео для детальной страницы
	if ( is_single() ) {
		// Подключаем систему уведомлений для детальной страницы
		wp_enqueue_script( 'the-capon-auth-notifications', get_template_directory_uri() . '/js/auth-notifications.js', array(), _S_VERSION, false );
		wp_enqueue_script( 'the-capon-single-slider', get_template_directory_uri() . '/js/single-slider.js', array(), _S_VERSION, true );
		wp_enqueue_script( 'the-capon-single-video', get_template_directory_uri() . '/js/single-video.js', array(), _S_VERSION, true );
		wp_enqueue_script( 'the-capon-single-model', get_template_directory_uri() . '/js/single-model.js', array( 'the-capon-main-script', 'the-capon-auth-notifications' ), _S_VERSION, true );
	}

	// Кастомные стили и скрипты (для всех страниц)
	wp_enqueue_style( 'the-capon-custom', get_template_directory_uri() . '/assets/css/custom.css', array(), _S_VERSION );
	wp_enqueue_script( 'the-capon-custom', get_template_directory_uri() . '/js/custom.js', array( 'the-capon-main-script' ), _S_VERSION, true );

	// Страница "Где купить" - Яндекс карта и магазины
	if ( is_page_template( 'page-where-buy.php' ) ) {
		// Подключаем Яндекс.Карты (ключ можно добавить в URL при необходимости)
		wp_enqueue_script( 'the-capon-yandex-maps', 'https://api-maps.yandex.ru/2.1/?lang=ru_RU', array(), null, true );
		wp_enqueue_script( 'the-capon-stores-map', get_template_directory_uri() . '/js/stores-map.js', array( 'the-capon-yandex-maps' ), _S_VERSION, true );
	}

	// Видео для страницы "О компании"
	if ( is_page_template( 'page-about.php' ) ) {
		wp_enqueue_script( 'the-capon-about-video', get_template_directory_uri() . '/js/about-video.js', array(), _S_VERSION, true );
	}

	// Модальное окно поиска
	wp_enqueue_script( 'the-capon-search-modal', get_template_directory_uri() . '/js/search-modal.js', array(), _S_VERSION, true );

	// Скрипты для личного кабинета
	if ( is_page_template( 'page-lk.php' ) ) {
		// Подключаем систему уведомлений для ЛК
		wp_enqueue_script( 'the-capon-auth-notifications', get_template_directory_uri() . '/js/auth-notifications.js', array(), _S_VERSION, false );
		wp_enqueue_script( 'the-capon-user-account', get_template_directory_uri() . '/js/user-account.js', array( 'the-capon-main-script', 'the-capon-auth-notifications' ), _S_VERSION, true );
	}

	// Скрипты для авторизации
	// Система уведомлений (подключаем на всех страницах авторизации)
	if ( is_page_template( 'page-login.php' ) || 
		 is_page_template( 'page-register.php' ) || 
		 is_page_template( 'page-password-reset.php' ) || 
		 is_page_template( 'page-password-reset-confirm.php' ) ) {
		
		// Подключаем систему уведомлений первой (без зависимостей, чтобы загрузилась раньше)
		wp_enqueue_script( 'the-capon-auth-notifications', get_template_directory_uri() . '/js/auth-notifications.js', array(), _S_VERSION, false );
		
		// Подключаем скрипты для конкретных страниц
		if ( is_page_template( 'page-login.php' ) ) {
			wp_enqueue_script( 'the-capon-auth-login', get_template_directory_uri() . '/js/auth-login.js', array( 'the-capon-auth-notifications' ), _S_VERSION, true );
		}
		
		if ( is_page_template( 'page-register.php' ) ) {
			wp_enqueue_script( 'the-capon-auth-register', get_template_directory_uri() . '/js/auth-register.js', array( 'the-capon-auth-notifications' ), _S_VERSION, true );
		}
		
		if ( is_page_template( 'page-password-reset.php' ) ) {
			wp_enqueue_script( 'the-capon-auth-password-reset', get_template_directory_uri() . '/js/auth-password-reset.js', array( 'the-capon-auth-notifications' ), _S_VERSION, true );
		}
		
		if ( is_page_template( 'page-password-reset-confirm.php' ) ) {
			wp_enqueue_script( 'the-capon-auth-password-reset-confirm', get_template_directory_uri() . '/js/auth-password-reset-confirm.js', array( 'the-capon-auth-notifications' ), _S_VERSION, true );
		}
	}

	// Локализация для AJAX и nonce (только для основных скриптов, не для авторизации)
	wp_localize_script( 'the-capon-main-script', 'theCaponAjax', array(
		'ajaxurl'          => admin_url( 'admin-ajax.php' ),
		'account_nonce'    => wp_create_nonce( 'update_user_account_nonce' ),
		'create_order_nonce' => wp_create_nonce( 'create_order_nonce' ),
		'stores_nonce'     => wp_create_nonce( 'update_user_stores_nonce' ),
	) );

	// Локализация для детальной страницы товара
	if ( is_single() ) {
		wp_localize_script( 'the-capon-single-model', 'theCaponAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	// Локализация для скриптов авторизации
	if ( is_page_template( 'page-login.php' ) || 
		 is_page_template( 'page-register.php' ) || 
		 is_page_template( 'page-password-reset.php' ) || 
		 is_page_template( 'page-password-reset-confirm.php' ) ) {
		
		$auth_scripts = array();
		
		if ( is_page_template( 'page-login.php' ) ) {
			$auth_scripts[] = 'the-capon-auth-login';
		}
		if ( is_page_template( 'page-register.php' ) ) {
			$auth_scripts[] = 'the-capon-auth-register';
		}
		if ( is_page_template( 'page-password-reset.php' ) ) {
			$auth_scripts[] = 'the-capon-auth-password-reset';
		}
		if ( is_page_template( 'page-password-reset-confirm.php' ) ) {
			$auth_scripts[] = 'the-capon-auth-password-reset-confirm';
		}
		
		$auth_localize = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'login_nonce' => wp_create_nonce( 'custom_login_nonce' ),
		'register_nonce' => wp_create_nonce( 'custom_register_nonce' ),
			'reset_nonce' => wp_create_nonce( 'password_reset_nonce' ),
			'reset_confirm_nonce' => wp_create_nonce( 'password_reset_confirm_nonce' ),
			'loginUrl' => home_url( '/войти/' ),
			'loginRedirect' => home_url( '/lk/' ),
		);
		
		foreach ( $auth_scripts as $script_handle ) {
			wp_localize_script( $script_handle, 'theCaponAjax', $auth_localize );
		}
	}

	// Локализация для поиска
	wp_localize_script( 'the-capon-search-modal', 'theCaponSearch', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'the_capon_search_nonce' ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'the_capon_scripts' );

/**
 * Подключение медиа скриптов в админке для галереи
 */
function the_capon_admin_scripts( $hook ) {
	global $post_type;
	if ( $post_type == 'post' && ( $hook == 'post.php' || $hook == 'post-new.php' ) ) {
		wp_enqueue_media();
	}
	
	// Стили для страницы модерации
	if ( $hook == 'toplevel_page_user_moderation' ) {
		echo '<style>
			.status-warning { color: #d63638; font-weight: bold; }
			.status-success { color: #00a32a; font-weight: bold; }
			.widefat th, .widefat td { padding: 10px; }
		</style>';
	}
}
add_action( 'admin_enqueue_scripts', 'the_capon_admin_scripts' );


/**
 * Fallback для основного меню
 */
function the_capon_primary_menu_fallback() {
	?>
	<ul class="nav-menu">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Главная</a></li>
		<?php
		// Показываем родительские категории
		$categories = get_categories(
			array(
				'parent'     => 0,
				'hide_empty' => false,
				'exclude'    => array( 1 ), // Исключаем "Без рубрики"
			)
		);

		foreach ( $categories as $category ) :
			?>
			<li><a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php
}

/**
 * Установка постоянных ссылок при активации темы
 */
function the_capon_setup_permalinks() {
	$permalink_structure = get_option( 'permalink_structure' );
	
	if ( empty( $permalink_structure ) || $permalink_structure === '/?p=%post_id%' ) {
		update_option( 'permalink_structure', '/%category%/%postname%/' );
		flush_rewrite_rules();
	}
}
add_action( 'after_switch_theme', 'the_capon_setup_permalinks' );

/**
 * Хлебные крошки для сайта
 */
function the_capon_breadcrumbs() {
	// Не показываем на главной
	if ( is_front_page() ) {
		return;
	}

	$delimiter = ' / ';
	$home_title = 'Главная';

	echo '<div class="breadcrumbs">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $home_title ) . '</a>';

	if ( is_category() ) {
		$current_category = get_queried_object();
		
		// Если есть родительская категория
		if ( $current_category->parent != 0 ) {
			$parent_category = get_category( $current_category->parent );
			$ancestors = get_ancestors( $current_category->term_id, 'category' );
			$ancestors = array_reverse( $ancestors );
			
			foreach ( $ancestors as $ancestor_id ) {
				$ancestor = get_category( $ancestor_id );
				echo $delimiter;
				echo '<a href="' . esc_url( get_category_link( $ancestor->term_id ) ) . '">' . esc_html( $ancestor->name ) . '</a>';
			}
		}
		
		echo $delimiter . '<span class="breadcrumb-current">' . single_cat_title( '', false ) . '</span>';
		
	} elseif ( is_single() && ! is_attachment() ) {
		$categories = get_the_category();
		
		if ( ! empty( $categories ) ) {
			// Берем первую категорию
			$category = $categories[0];
			
			// Если у категории есть родитель
			if ( $category->parent != 0 ) {
				$ancestors = get_ancestors( $category->term_id, 'category' );
				$ancestors = array_reverse( $ancestors );
				
				foreach ( $ancestors as $ancestor_id ) {
					$ancestor = get_category( $ancestor_id );
					echo $delimiter;
					echo '<a href="' . esc_url( get_category_link( $ancestor->term_id ) ) . '">' . esc_html( $ancestor->name ) . '</a>';
				}
			}
			
			echo $delimiter;
			echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>';
		}
		
		echo $delimiter . '<span class="breadcrumb-current">' . get_the_title() . '</span>';
		
	} elseif ( is_page() ) {
		if ( $post->post_parent ) {
			$ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
			foreach ( $ancestors as $ancestor ) {
				echo $delimiter;
				echo '<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . get_the_title( $ancestor ) . '</a>';
			}
		}
		echo $delimiter . '<span class="breadcrumb-current">' . get_the_title() . '</span>';
		
	} elseif ( is_tag() ) {
		echo $delimiter . '<span class="breadcrumb-current">Метка: ' . single_tag_title( '', false ) . '</span>';
		
	} elseif ( is_author() ) {
		global $author;
		$userdata = get_userdata( $author );
		echo $delimiter . '<span class="breadcrumb-current">Автор: ' . esc_html( $userdata->display_name ) . '</span>';
		
	} elseif ( is_404() ) {
		echo $delimiter . '<span class="breadcrumb-current">Ошибка 404</span>';
		
	} elseif ( is_search() ) {
		echo $delimiter . '<span class="breadcrumb-current">Результаты поиска: ' . get_search_query() . '</span>';
		
	} elseif ( is_day() ) {
		echo $delimiter . '<span class="breadcrumb-current">Архив за ' . get_the_date() . '</span>';
		
	} elseif ( is_month() ) {
		echo $delimiter . '<span class="breadcrumb-current">Архив за ' . get_the_date( 'F Y' ) . '</span>';
		
	} elseif ( is_year() ) {
		echo $delimiter . '<span class="breadcrumb-current">Архив за ' . get_the_date( 'Y' ) . '</span>';
	}

	echo '</div>';
}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Model custom fields - только функция фильтров.
 */
require get_template_directory() . '/inc/model-fields.php';

/**
 * ACF fields for single page (детальная страница модели).
 */
require get_template_directory() . '/inc/acf-fields-single.php';

/**
 * ACF fields for model attributes (силуэт, вырез, ткань, цвет, цена).
 * Отключено - теперь используются параметры из повторителя в acf-fields-single.php
 */
// require get_template_directory() . '/inc/acf-fields-model.php';

/**
 * ACF fields for contacts page.
 */
require get_template_directory() . '/inc/acf-fields-contacts.php';

/**
 * ACF fields for stores ("Где купить").
 */
require get_template_directory() . '/inc/acf-fields-stores.php';

/**
 * ACF fields for about page.
 */
require get_template_directory() . '/inc/acf-fields-about.php';

/**
 * Авторизация, регистрация и восстановление пароля.
 */
require get_template_directory() . '/inc/auth.php';

/**
 * Личный кабинет и избранное.
 */
require get_template_directory() . '/inc/user-account.php';

/**
 * Админ страницы управления пользователями.
 */
require get_template_directory() . '/inc/admin-users.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * AJAX обработчик для live search
 */
function the_capon_live_search() {
	// Проверка nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'the_capon_search_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Ошибка безопасности' ) );
        return;
    }

	$query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';

	if ( empty( $query ) || strlen( $query ) < 2 ) {
		wp_send_json_error( array( 'message' => 'Запрос слишком короткий' ) );
		return;
	}

	// Выполняем поиск
	$search_query = new WP_Query(
		array(
			's'              => $query,
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		)
	);

	if ( $search_query->have_posts() ) {
		$html = '<ul class="search-results__list">';
		while ( $search_query->have_posts() ) {
			$search_query->the_post();
			$image_url = get_template_directory_uri() . '/assets/images/c1.png';

			// Пытаемся получить изображение из ACF
			$slides = get_field( 'model_slides', get_the_ID() );
			if ( $slides && is_array( $slides ) && ! empty( $slides ) ) {
				if ( isset( $slides[0]['gallery'] ) && is_array( $slides[0]['gallery'] ) && ! empty( $slides[0]['gallery'] ) ) {
					$first_image = $slides[0]['gallery'][0];
					if ( isset( $first_image['url'] ) ) {
						$image_url = $first_image['url'];
					}
				}
			}

			// Fallback на миниатюру поста
			if ( $image_url === get_template_directory_uri() . '/assets/images/c1.png' && has_post_thumbnail() ) {
				$image_url = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
			}

			$html .= '<a href="' . esc_url( get_permalink() ) . '" class="search-result__item">';
			$html .= '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( get_the_title() ) . '" class="search-result__image">';
			$html .= '<div class="search-result__content">';
			$html .= '<h3 class="search-result__title">' . esc_html( get_the_title() ) . '</h3>';
			$html .= '<p class="search-result__excerpt">' . esc_html( wp_trim_words( get_the_excerpt(), 15 ) ) . '</p>';
			$html .= '</div>';
			$html .= '</a>';
		}
		$html .= '</ul>';
		wp_reset_postdata();
		wp_send_json_success( array( 'html' => $html ) );
    } else {
		wp_send_json_success( array( 'html' => '<div class="search-results__empty"><p>Ничего не найдено. Попробуйте изменить запрос.</p></div>' ) );
	}
}

add_action( 'wp_ajax_the_capon_live_search', 'the_capon_live_search' );
add_action( 'wp_ajax_nopriv_the_capon_live_search', 'the_capon_live_search' );
