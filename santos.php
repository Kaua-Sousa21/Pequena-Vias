<?php
require_once __DIR__ . '/config/init.php';

$database = new Database();
$db = $database->getConnection();
$santoObj = new Santo($db);

// Parâmetros de paginação
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 12;
$offset = ($pagina - 1) * $por_pagina;

// Buscar santos
$santos = $santoObj->listarSantos($por_pagina, $offset);
$total_santos = $santoObj->contarSantos();
$total_paginas = ceil($total_santos / $por_pagina);

// Meta tags para SEO
$titulo = "Santos Católicos - Lista Completa";
$descricao = "Conheça a vida e história dos santos da Igreja Católica";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <meta name="description" content="<?= htmlspecialchars($descricao) ?>">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .hero-section {
             background: linear-gradient(90deg, #1976d2, #2196f3);
            color: white;
            padding: 60px 0;
        }
        
        .saint-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            overflow: hidden;
            background: white;
            height: 100%;
        }
        
        .saint-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .saint-image {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        
        .saint-image-placeholder {
            height: 250px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        
        .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .card-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .card-text {
            color: #6c757d;
            margin-bottom: 1.5rem;
            flex-grow: 1;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            color: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .stats-info {
            text-align: center;
            margin-bottom: 3rem;
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #3498db;
        }
        
        .pagination .page-link {
            border: none;
            border-radius: 25px;
            margin: 0 5px;
            padding: 10px 15px;
            color: #2c3e50;
            transition: all 0.3s ease;
        }
        
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .pagination .page-link:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 40px 0;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .saint-image,
            .saint-image-placeholder {
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="display-4 fw-bold mb-3">Santos Católicos</h1>
                    <p class="lead">Conheça a vida e história dos santos da Igreja Católica</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Lista de Santos -->
    <div class="container my-5">
        <div class="section-title">
            <h2>Nossos Santos</h2>
        </div>
        
        <div class="stats-info">
            <div class="stats-number"><?= $total_santos ?></div>
            <p class="mb-0">Santos catalogados em nossa base</p>
        </div>

        <div class="row">
            <?php foreach ($santos as $santo): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card saint-card">
                    <?php if (!empty($santo['imagem'])): ?>
                        <img src="public/<?= htmlspecialchars($santo['imagem']) ?>" 
                             class="saint-image" 
                             alt="<?= htmlspecialchars($santo['nome']) ?>">
                    <?php else: ?>
                        <div class="saint-image-placeholder">
                            <i class="fas fa-user-circle fa-4x"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($santo['nome']) ?></h5>
                        
                        <?php if (!empty($santo['resumo'])): ?>
                            <p class="card-text"><?= htmlspecialchars(resumirTexto($santo['resumo'], 120)) ?></p>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <a href="santo.php?slug=<?= urlencode($santo['slug']) ?>" 
                               class="btn btn-custom w-100">
                                <i class="fas fa-book-open me-2"></i>
                                Ler História
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginação -->
        <?php if ($total_paginas > 1): ?>
        <nav aria-label="Navegação das páginas" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($pagina > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                
                <?php if ($pagina < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>