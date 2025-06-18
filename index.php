<?php
require_once __DIR__ . '/config/init.php';

$site_config = [
    'title' => 'Pequenas Vias',
    'description' => 'Portal dedicado às vidas dos santos católicos',
    'logo' => 'Pequenas Vias',
    'menu_items' => [
        'Início' => 'index.php',
        'Santos' => 'santos.php',
        'Categorias' => 'categorias.php',
    ],
    'carousel_slides' => [
        [
            'image' => 'https://www.cnbb.org.br/wp-content/uploads/2021/12/Santa-Luzia.jpg',
            'title' => 'Santos Mártires',
            'description' => 'Conheça os primeiros testemunhas da fé cristã',
            'icon' => '✝️'
        ],
        [
            'image' => 'https://img.cancaonova.com/cnimages/canais/uploads/sites/2/2022/08/Santo-Agostinho-artigoCNBB.jpg',
            'title' => 'Santos Fundadores',
            'description' => 'Descubra os fundadores das grandes ordens religiosas',
            'icon' => '⛪'
        ],
        [
            'image' => 'https://historiasdocatolicismo.com/assets/images/blog/img-carloacutis.jpg',
            'title' => 'Santos Modernos',
            'description' => 'Inspire-se com os santos dos tempos atuais',
            'icon' => '🌟'
        ],
        [
            'image' => 'https://www.otaboanense.com.br/wp-content/uploads/2021/10/santa_terezinha.jpg',
            'title' => 'Santas Mulheres',
            'description' => 'Conheça as grandes santas da Igreja',
            'icon' => '🌹'
        ]
    ]
];

function renderHeader($config) {
    ob_start();
    ?>
    <header class="header">
        <div class="nav-container">
            <div class="logo"><?php echo $config['logo']; ?></div>
            <div style="position: relative;">
                <button class="menu-button" id="menuBtn">
                    <span>Menu</span>
                </button>
                <div class="menu-dropdown" id="menuDropdown">
                    <?php foreach($config['menu_items'] as $name => $link): ?>
                        <a href="<?php echo $link; ?>" class="menu-item"><?php echo $name; ?></a>
                    <?php endforeach; ?>

                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a href="admin/index.php" class="menu-item">Painel Admin</a>
                        <a href="admin/logout.php" class="menu-item">Sair</a>
                    <?php else: ?>
                        <a href="login.php" class="menu-item">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
    <?php
    return ob_get_clean();
}

function renderCarousel($slides) {
    ob_start();
    ?>
    <div class="carousel-wrapper">
        <div class="carousel-header">
            <h2>Explore Nossas Categorias</h2>
            <p>Descubra as diferentes faces da santidade</p>
        </div>
        
        <div class="carousel-container-3d">
            <button class="carousel-nav prev" id="prevBtn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            
            <div class="carousel-track" id="carouselTrack">
                <?php foreach($slides as $index => $slide): ?>
                <div class="carousel-card <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                    <div class="card-inner">
                        <div class="card-front">
                            <div class="card-image">
                                <img src="<?php echo $slide['image']; ?>" alt="<?php echo $slide['title']; ?>">
                                <div class="card-overlay">
                                    <div class="card-icon"><?php echo $slide['icon']; ?></div>
                                </div>
                            </div>
                            <div class="card-content">
                                <h3><?php echo $slide['title']; ?></h3>
                                <p><?php echo $slide['description']; ?></p>
                                <button class="card-button">Explorar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button class="carousel-nav next" id="nextBtn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <div class="carousel-dots">
            <?php foreach($slides as $index => $slide): ?>
                <button class="dot <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function renderContentBox($title, $content) {
    ob_start();
    ?>
    <div class="content-box">
        <h2><?php echo $title; ?></h2>
        <?php echo $content; ?>
    </div>
    <?php
    return ob_get_clean();
}

function renderFooter() {
    ob_start();
    ?>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Sobre o Portal</h3>
                <p>Somos um portal dedicado a preservar e compartilhar as inspiradoras trajetórias de fé dos santos e santas da Igreja Católica.</p>
                <div class="social-links">
                    <a href="#" class="social-link">f</a>
                    <a href="#" class="social-link">t</a>
                    <a href="#" class="social-link">in</a>
                    <a href="#" class="social-link">ig</a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Recursos</h3>
                <ul>
                    <li><a href="#">Biografias dos Santos</a></li>
                    <li><a href="#">Calendário Litúrgico</a></li>
                    <li><a href="#">Orações e Novenas</a></li>
                    <li><a href="#">Arte Sacra</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contato</h3>
                <p>📧 contato@portalsantos.com</p>
                <p>📞 (11) 9999-8888</p>
                <p>📍 São Paulo, SP - Brasil</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?php echo date('Y'); ?> Portal dos Santos. Todos os direitos reservados.</p>
        </div>
    </footer>
    <?php
    return ob_get_clean();
}

// Conteúdo da página inicial
$page_content = '
    <p>
        Descubra as vidas extraordinárias dos santos e santas que iluminaram a história da Igreja Católica através dos séculos. Nosso site é um verdadeiro tesouro digital dedicado a preservar e compartilhar as inspiradoras trajetórias de fé, amor e dedicação daqueles que foram elevados aos altares pela Igreja.
    </p>
    <p>
        Explore centenas de biografias cuidadosamente pesquisadas e escritas, desde os primeiros mártires cristãos até os santos mais recentemente canonizados. Cada história é apresentada de forma envolvente e acessível, revelando não apenas os milagres e virtudes heroicas, mas também a humanidade e os desafios enfrentados por estes exemplos de santidade
    </p>
    <div class="highlight">
        <p>
            "Sede santos como vosso Pai celeste é santo" - Este portal foi criado com amor e reverência, tendo como objetivo inspirar fiéis de todas as idades a conhecer melhor estes exemplos luminosos de fé cristã. Cada santo nos ensina que a santidade é possível em qualquer época, lugar ou condição de vida.
        </p>
    </div>
    <p>
        Através destas páginas, esperamos que você encontre não apenas conhecimento histórico, mas verdadeira inspiração espiritual. Que as vidas dos santos sirvam como faróis de esperança, guiando-nos em nossa própria jornada de fé rumo à santidade.
    </p>
';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $site_config['title']; ?></title>
    <meta name="description" content="<?php echo $site_config['description']; ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 1rem 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 2px;
        }

        .menu-button {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .menu-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .menu-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1002;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .menu-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(10px);
        }

        .menu-item {
            display: block;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .menu-item:last-child {
            border-bottom: none;
            border-radius: 0 0 15px 15px;
        }

        .menu-item:first-child {
            border-radius: 15px 15px 0 0;
        }

        .menu-item:hover {
            background: rgba(102, 126, 234, 0.1);
            padding-left: 30px;
        }

        /* Main Content */
        .main-content {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Novo Carrossel 3D */
        .carousel-wrapper {
            margin: 2rem 0;
        }

        .carousel-header {
            text-align: center;
            margin-bottom: 3rem;
            color: white;
        }

        .carousel-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .carousel-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .carousel-container-3d {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            perspective: 1000px;
        }

        .carousel-track {
            display: flex;
            gap: 2rem;
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
        }

        .carousel-card {
            min-width: 320px;
            height: 450px;
            position: relative;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            transform-style: preserve-3d;
            cursor: pointer;
        }

        .carousel-card:not(.active) {
            transform: scale(0.8) rotateY(15deg);
            opacity: 0.6;
        }

        .carousel-card.active {
            transform: scale(1) rotateY(0deg);
            opacity: 1;
            z-index: 10;
        }

        .card-inner {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .carousel-card:hover .card-inner {
            transform: rotateY(5deg) rotateX(5deg);
        }

        .card-front {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .card-image {
            position: relative;
            height: 60%;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .carousel-card:hover .card-image img {
            transform: scale(1.1);
        }

        .card-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carousel-card:hover .card-overlay {
            opacity: 1;
        }

        .card-icon {
            font-size: 3rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        .card-content {
            padding: 2rem;
            height: 40%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-content h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .card-content p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .card-button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            align-self: flex-start;
        }

        .card-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .carousel-nav:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-nav.prev {
            left: -25px;
        }

        .carousel-nav.next {
            right: -25px;
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: white;
            transform: scale(1.3);
        }

        .dot:hover {
            background: rgba(255, 255, 255, 0.7);
        }

        /* Content Box */
        .content-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            margin: 4rem auto 0;
            max-width: 800px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .content-box h2 {
            color: #333;
            font-size: 2.2rem;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 700;
        }

        .content-box p {
            color: #555;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
            text-align: justify;
        }

        .content-box .highlight {
            background: linear-gradient(120deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid #667eea;
            margin: 2rem 0;
        }

        .content-box .highlight p {
            margin: 0;
            font-style: italic;
            color: #333;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            color: white;
            padding: 3rem 2rem 2rem;
            margin-top: 4rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .footer-section h3 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            line-height: 1.6;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: white;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .social-link {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .carousel-container-3d {
                padding: 1rem 0;
            }

            .carousel-track {
                gap: 1rem;
            }

            .carousel-card {
                min-width: 280px;
                height: 400px;
            }

            .carousel-nav {
                display: none;
            }

            .carousel-header h2 {
                font-size: 2rem;
            }

            .content-box {
                padding: 2rem;
                margin: 2rem 1rem 0;
            }

            .main-content {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <?php echo renderHeader($site_config); ?>

    <main class="main-content">
        <?php echo renderCarousel($site_config['carousel_slides']); ?>
        <?php echo renderContentBox('Bem-vindos a Pequenas Vias', $page_content); ?>
    </main>

    <?php echo renderFooter(); ?>

    <script>
        // Menu functionality
        const menuBtn = document.getElementById('menuBtn');
        const menuDropdown = document.getElementById('menuDropdown');

        menuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            menuDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function() {
            menuDropdown.classList.remove('active');
        });

        menuDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Novo Carrossel 3D
        class Carousel3D {
            constructor() {
                this.track = document.getElementById('carouselTrack');
                this.cards = document.querySelectorAll('.carousel-card');
                this.dots = document.querySelectorAll('.dot');
                this.prevBtn = document.getElementById('prevBtn');
                this.nextBtn = document.getElementById('nextBtn');
                
                this.currentIndex = 0;
                this.totalCards = this.cards.length;
                this.autoPlayInterval = null;
                
                this.init();
            }
            
            init() {
                this.updateCarousel();
                this.bindEvents();
                this.startAutoPlay();
            }
            
            bindEvents() {
                this.prevBtn.addEventListener('click', () => this.prev());
                this.nextBtn.addEventListener('click', () => this.next());
                
                this.dots.forEach((dot, index) => {
                    dot.addEventListener('click', () => this.goTo(index));
                });
                
                // Pause on hover
                this.track.addEventListener('mouseenter', () => this.stopAutoPlay());
                this.track.addEventListener('mouseleave', () => this.startAutoPlay());
            }
            
            updateCarousel() {
                this.cards.forEach((card, index) => {
                    card.classList.toggle('active', index === this.currentIndex);
                });
                
                this.dots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === this.currentIndex);
                });
                
                // Center the active card
                const offset = this.currentIndex * -340; // card width + gap
                this.track.style.transform = `translateX(${offset}px)`;
            }
            
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.totalCards;
                this.updateCarousel();
            }
            
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.totalCards) % this.totalCards;
                this.updateCarousel();
            }
            
            goTo(index) {
                this.currentIndex = index;
                this.updateCarousel();
            }
            
            startAutoPlay() {
                this.stopAutoPlay();
                this.autoPlayInterval = setInterval(() => this.next(), 4000);
            }
            
            stopAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                    this.autoPlayInterval = null;
                }
            }
        }

        // Initialize carousel when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            new Carousel3D();
        });
    </script>
</body>
</html>
