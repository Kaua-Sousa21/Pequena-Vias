<?php
require_once __DIR__ . '/config/init.php';

$site_config = [
    'title' => 'Pequenas Vias',
    'description' => 'Portal dedicado às vidas dos santos católicos',
    'logo' => 'Pequenas Vias',
    'menu_items' => [
    'Início' => 'index.php',
    'Santos' => 'santos.php',  // Alterado de santo.php para santos.php
    'Categorias' => 'categorias.php',
    'Calendário' => 'calendario.php'
],

    'carousel_slides' => [
        [
            'image' => 'https://www.cnbb.org.br/wp-content/uploads/2021/12/Santa-Luzia.jpg',
            'title' => 'Santos Mártires',
            'description' => 'Conheça os primeiros testemunhas da fé cristã'
        ],
        [
            'image' => 'https://img.cancaonova.com/cnimages/canais/uploads/sites/2/2022/08/Santo-Agostinho-artigoCNBB.jpg',
            'title' => 'Santos Fundadores',
            'description' => 'Descubra os fundadores das grandes ordens religiosas'
        ],
        [
            'image' => 'https://historiasdocatolicismo.com/assets/images/blog/img-carloacutis.jpg',
            'title' => 'Santos Modernos',
            'description' => 'Inspire-se com os santos dos tempos atuais'
        ],
        [
            'image' => 'https://www.otaboanense.com.br/wp-content/uploads/2021/10/santa_terezinha.jpg',
            'title' => 'Santas Mulheres',
            'description' => 'Conheça as grandes santas da Igreja'
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
    <div class="carousel-container">
        <div class="carousel" id="carousel">
            <?php foreach($slides as $index => $slide): ?>
            <div class="carousel-slide">
                <img src="<?php echo $slide['image']; ?>" alt="<?php echo $slide['title']; ?>">
                <div class="carousel-overlay">
                    <h3><?php echo $slide['title']; ?></h3>
                    <p><?php echo $slide['description']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="carousel-indicators">
            <?php foreach($slides as $index => $slide): ?>
                <div class="indicator <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>"></div>
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
            <p>&copy; <?php echo date('Y'); ?> Portal dos Santos. Todos os direitos reservados.</p>
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
            background: linear-gradient(135deg, #ffffff 0%, #e3f2fd 100%);
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(90deg, #1976d2, #2196f3);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(25, 118, 210, 0.3);
            position: relative;
            overflow: visible;
            z-index: 1000;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1001;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 2px;
        }

        .menu-button {
            background: rgba(255, 255, 255, 0.3);
            border: 2px solid white;
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
            z-index: 10;
            display: inline-block;
        }

        .menu-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: left 0.3s ease;
        }

        .menu-button:hover::before {
            left: 0;
        }

        .menu-button:hover {
            background: white;
            color: #1976d2;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .menu-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1002;
            border: 2px solid #e3f2fd;
        }

        .menu-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(10px);
        }

        .menu-item {
            display: block;
            padding: 15px 20px;
            color: #1976d2;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid #e3f2fd;
        }

        .menu-item:last-child {
            border-bottom: none;
            border-radius: 0 0 15px 15px;
        }

        .menu-item:first-child {
            border-radius: 15px 15px 0 0;
        }

        .menu-item:hover {
            background: #e3f2fd;
            padding-left: 30px;
        }

        /* Main Content */
        .main-content {
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .carousel-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(25, 118, 210, 0.3);
            background: white;
            z-index: 1;
        }

        .carousel {
            display: flex;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .carousel-slide {
            min-width: 100%;
            position: relative;
        }

        .carousel-slide img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
        }

        .carousel-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(25, 118, 210, 0.8));
            color: white;
            padding: 2rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .carousel-slide:hover .carousel-overlay {
            transform: translateY(0);
        }

        .carousel-indicators {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            background: white;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e3f2fd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .indicator.active {
            background: #1976d2;
            transform: scale(1.3);
        }

        .indicator:hover {
            background: #2196f3;
        }

        /* Content Box */
        .content-box {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin: 4rem auto 0;
            max-width: 800px;
            box-shadow: 0 10px 30px rgba(25, 118, 210, 0.15);
            border: 1px solid #e3f2fd;
            position: relative;
            overflow: hidden;
        }

        .content-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #1976d2, #2196f3);
        }

        .content-box h2 {
            color: #1976d2;
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
            background: linear-gradient(120deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid #2196f3;
            margin: 2rem 0;
        }

        .content-box .highlight p {
            margin: 0;
            font-style: italic;
            color: #1976d2;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            margin-top: 4rem;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
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
            text-decoration: underline;
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

        /* Decorative Elements */
        .decoration {
            position: fixed;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(45deg, #2196f3, #1976d2);
            opacity: 0.1;
            z-index: -1;
            animation: float 6s ease-in-out infinite;
        }

        .decoration:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .decoration:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .decoration:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 1rem;
            }
            
            .logo {
                font-size: 1.5rem;
            }
            
            .main-content {
                padding: 2rem 1rem;
            }
            
            .carousel-slide img {
                height: 250px;
            }

            .content-box {
                padding: 2rem;
                margin: 2rem 1rem 0;
            }

            .content-box h2 {
                font-size: 1.8rem;
            }

            .footer {
                padding: 2rem 1rem 1rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="decoration"></div>
    <div class="decoration"></div>
    <div class="decoration"></div>
    
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
            e.stopPropagation();
            menuDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function() {
            menuDropdown.classList.remove('active');
        });

        menuDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Carousel functionality
        const carousel = document.getElementById('carousel');
        const indicators = document.querySelectorAll('.indicator');
        let currentSlide = 0;
        const totalSlides = <?php echo count($site_config['carousel_slides']); ?>;

        function goToSlide(slideIndex) {
            currentSlide = slideIndex;
            const translateX = -slideIndex * 100;
            carousel.style.transform = `translateX(${translateX}%)`;
            
            // Update indicators
            indicators.forEach((indicator, index) => {
                indicator.classList.toggle('active', index === slideIndex);
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            goToSlide(currentSlide);
        }

        // Auto-advance carousel every 2 seconds
        setInterval(nextSlide, 5000);

        // Manual navigation
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                goToSlide(index);
            });
        });

        // Add loading animation for images
        const images = document.querySelectorAll('.carousel-slide img');
        images.forEach(img => {
            img.addEventListener('load', function() {
                this.style.opacity = '1';
            });
        });
    </script>
</body>
</html>