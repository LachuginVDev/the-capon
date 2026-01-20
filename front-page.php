<?php
/**
 * Template Name: Front Page
 * Главная страница сайта
 *
 * @package The_Capon
 */

get_header();
?>

<!-- Главная секция -->
    <section id="home" class="hero">

        <?php
        $upload = wp_upload_dir();
        $video_url = $upload['baseurl'] . '/v1.webm';
        ?>

        <video class="hero-bg" autoplay muted loop playsinline>
            <source src="<?php echo esc_url( $video_url ); ?>" type="video/webm">
        </video>


            <div class="hero-content">
                <button class="hero-btn">New Collection</button>
            </div>

    </section>


<!-- Секция 1: Картинка слева, текст справа -->
<section class="content-section section-1">
	<div class="section-half section-image" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/1.png' ); ?>');">
		<div class="image-content">
			<h2 class="image-title">Kaplun</h2>
			<div class="image-overlay">
				<a href="/category/tatiana-kaplun/" class="image-btn">Смотреть коллекцию</a>
			</div>
		</div>
	</div>
	<div class="section-half section-text">
		<div class="text-content">
			<h2 class="text-title">стиль<br>
                и элегантность<br>
                для самых требовательных</h2>
		</div>
	</div>
</section>

<!-- Секция 2: Текст слева, картинка справа -->
<section class="content-section section-2">
	<div class="section-half section-text">
		<div class="text-content">
			<h2 class="text-title">легкая
                женственность
                для самых
                нежных</h2>
		</div>
	</div>
	<div class="section-half section-image" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/2.png' ); ?>');">
		<div class="image-content">
			<h2 class="image-title">kookla</h2>
			<div class="image-overlay">
				<a href="/category/kookla/" class="image-btn">посмотреть</a>
			</div>
		</div>
	</div>
</section>

<!-- Секция 3: Картинка слева, текст справа -->
<section class="content-section section-3">
	<div class="section-half section-image" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/3.png' ); ?>');">
		<div class="image-content">
			<h2 class="image-title">marry mark</h2>
			<div class="image-overlay">
				<button class="image-btn">посмотреть</button>
			</div>
		</div>
	</div>
	<div class="section-half section-text">
		<div class="text-content">
			<h2 class="text-title">вечерний
                стиль для тех
                кто любит
                красоту</h2>
		</div>
	</div>
</section>

<!-- Секция 4: Текст слева, картинка справа -->
<section class="content-section section-4">
	<div class="section-half section-text">
		<div class="text-content">
			<h2 class="text-title">стильный
                размер для самых
                свободных</h2>
		</div>
	</div>
	<div class="section-half section-image" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/4.png' ); ?>');">
		<div class="image-content">
			<h2 class="image-title">plus size</h2>
			<div class="image-overlay">
				<button class="image-btn">посмотреть</button>
			</div>
		</div>
	</div>
</section>

<!-- Секция О нас -->
<section class="about-section" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/bg2.png' ); ?>');">
	<div class="about-content">
		<h2 class="about-title">О нас</h2>
	</div>
</section>

<!-- Секция Новости -->
<section class="news-section">
	<div class="container">
		<div class="news-content">
			<!-- Левый блок -->
			<div class="news-sidebar">
				<h3 class="sidebar-title">мечта дизайнера</h3>
				<p class="sidebar-text">Сердце и душа, Стоящие за дизайном Татьяны Каплум</p>
			</div>
			
			<!-- Основной блок -->
			<div class="news-main">
				<h2 class="news-title">Модный Дом Татьяны Каплун - это наша большая и дружная семья, где каждый выполняет свою важную роль. Это семейное дело 
					с глубокой историей, которая началась в Нижнем Новгороде 
					в 1995 году.</h2>
				<ul class="news-list">
					<li>Сегодня Татьяна Каплун - самый известный дизайнер свадебных платьев в России.</li>
					<li>Ежегодно более 2000 невест в мегаполисах и региональных городах выбирают платье нашего бренда.</li>
					<li>Что объединяет столько девушек, незнакомых друг с другом? Любовь к продуманному дизайну, качеству и внимание к деталям.</li>
					<li>Модный дом Татьяны Каплун ежегодно выпускает две коллекции платьев под брендами Tatiana Kaplun и Kookla.</li>
					<li>Коллекция Tatiana Kaplun - сложная, нетривиальная, элегантная, сочетает в себе модные тенденции и классические линии.</li>
				</ul>
				<button class="news-btn">
					читать дальше
					<svg width="94" height="8" viewBox="0 0 94 8" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M93.3536 4.3534C93.5488 4.15814 93.5488 3.84156 93.3536 3.6463L90.1716 0.464318C89.9763 0.269056 89.6597 0.269056 89.4645 0.464318C89.2692 0.65958 89.2692 0.976163 89.4645 1.17142L92.2929 3.99985L89.4645 6.82828C89.2692 7.02354 89.2692 7.34012 89.4645 7.53538C89.6597 7.73065 89.9763 7.73065 90.1716 7.53538L93.3536 4.3534ZM0 4.49985H93V3.49985H0V4.49985Z" fill="black" />
					</svg>
				</button>
			</div>
		</div>
	</div>
</section>

<!-- Секция Trunk Show -->
<section class="trunk-show-section">
	<div class="container">
		<h2 class="trunk-show-title">trunk show</h2>
		<div class="trunk-show-cards">
			<!-- Левая карточка -->
			<div class="trunk-card-wrapper trunk-card-left">
				<div class="card-date">
					<span class="date-number">15-19</span>
					<span class="date-month">апрель</span>
				</div>
				<div class="trunk-card" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/n1.png' ); ?>');"></div>
				<div class="card-title">Название события</div>
			</div>
			
			<!-- Центральная карточка (самая большая) -->
			<div class="trunk-card-wrapper trunk-card-center">
				<div class="card-date">
					<span class="date-number">22-26</span>
					<span class="date-month">май</span>
				</div>
				<div class="trunk-card" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/n2.png' ); ?>');"></div>
				<div class="card-title">Главное событие</div>
			</div>
			
			<!-- Правая карточка -->
			<div class="trunk-card-wrapper trunk-card-right">
				<div class="card-date">
					<span class="date-number">01-05</span>
					<span class="date-month">июнь</span>
				</div>
				<div class="trunk-card" style="background-image: url('<?php echo esc_url( get_template_directory_uri() . '/assets/images/n3.png' ); ?>');"></div>
				<div class="card-title">Специальный показ</div>
			</div>
		</div>
		
		<button class="trunk-show-btn">
			показать больше
			<svg width="94" height="8" viewBox="0 0 94 8" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M93.3536 4.3534C93.5488 4.15814 93.5488 3.84156 93.3536 3.6463L90.1716 0.464318C89.9763 0.269056 89.6597 0.269056 89.4645 0.464318C89.2692 0.65958 89.2692 0.976163 89.4645 1.17142L92.2929 3.99985L89.4645 6.82828C89.2692 7.02354 89.2692 7.34012 89.4645 7.53538C89.6597 7.73065 89.9763 7.73065 90.1716 7.53538L93.3536 4.3534ZM0 4.49985H93V3.49985H0V4.49985Z" fill="black" />
			</svg>
		</button>
	</div>
</section>

<?php
get_footer();

