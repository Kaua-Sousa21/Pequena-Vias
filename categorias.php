<?php
require_once __DIR__ . '/config/init.php';

$database = new Database();
$db = $database->getConnection();
$categoriaObj = new Categoria($db);

// Buscar todas as categorias
$categorias = $categoriaObj->listarTodas();

// Meta tags para SEO
$titulo = "Categorias - Santos Católicos";
$descricao = "Explore as diferentes categorias de santos da Igreja Católica";
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
        .category-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .category-card:hover {
            transform: translateY(-5px);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 3rem;
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #1976d2;
        }
        
        .category-count {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-4">Categorias de Santos</h1>
            <p class="lead">Explore as diferentes categorias de santos da Igreja Católica</p>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <?php foreach ($categorias as $categoria): ?>
            <div class="col-md-4 mb-4">
                <div class="card category-card">
                    <div class="card-body text-center">
                        <div class="category-icon">
                            <i class="fas fa-cross"></i>
                        </div>
                        <h3 class="card-title"><?= htmlspecialchars($categoria['nome']) ?></h3>
                        <?php if ($categoria['descricao']): ?>
                        <p class="card-text"><?= htmlspecialchars(resumirTexto($categoria['descricao'], 100)) ?></p>
                        <?php endif; ?>
                        <div class="category-count mb-3">
                            <?= $categoria['total_santos'] ?> santos
                        </div>
                        <a href="categoria.php?slug=<?= urlencode($categoria['slug']) ?>" 
                           class="btn btn-primary">
                            Ver Santos
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
