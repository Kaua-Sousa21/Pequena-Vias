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
        .saint-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 2rem;
        }
        .saint-card:hover {
            transform: translateY(-5px);
        }
        .saint-image {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navbar (mesmo do index.php) -->
    
    <!-- Header -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <h1>Santos Católicos</h1>
            <p class="lead">Conheça a vida e história dos santos da Igreja</p>
        </div>
    </div>

    <!-- Lista de Santos -->
    <div class="container my-5">
        <div class="row">
            <?php foreach ($santos as $santo): ?>
            <div class="col-md-4 mb-4">
                <div class="card saint-card">
                    <?php if ($santo['imagem']): ?>
                    <img src="<?= htmlspecialchars($santo['imagem']) ?>" 
                         class="card-img-top saint-image" 
                         alt="<?= htmlspecialchars($santo['nome']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($santo['nome']) ?></h5>
                        <?php if ($santo['resumo']): ?>
                        <p class="card-text"><?= htmlspecialchars(resumirTexto($santo['resumo'], 100)) ?></p>
                        <?php endif; ?>
                        <a href="santo.php?slug=<?= urlencode($santo['slug']) ?>" 
                           class="btn btn-primary">
                            Ler mais
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginação -->
        <?php if ($total_paginas > 1): ?>
        <nav aria-label="Navegação das páginas">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>

    <!-- Footer (mesmo do index.php) -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
