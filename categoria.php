
<?php
require_once __DIR__ . '/config/init.php';

$database = new Database();
$db = $database->getConnection();
$santoObj = new Santo($db);
$categoriaObj = new Categoria($db);

// Verificar se o slug foi fornecido
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: index.php');
    exit();
}

$slug = $_GET['slug'];
$categoria = $categoriaObj->buscarPorSlug($slug);

// Se a categoria não foi encontrada, redirecionar
if (!$categoria) {
    header('Location: index.php?erro=categoria_nao_encontrada');
    exit();
}

// Parâmetros de paginação
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 12;
$offset = ($pagina - 1) * $por_pagina;

// Buscar santos da categoria
$santos = $santoObj->buscarPorCategoria($slug, $por_pagina, $offset);
$total_santos = $santoObj->contarSantosPorCategoria($slug);
$total_paginas = ceil($total_santos / $por_pagina);

// Meta tags para SEO
$titulo = htmlspecialchars($categoria['nome']) . ' - Santos Católicos';
$descricao = $categoria['descricao'] ? resumirTexto($categoria['descricao'], 160) : 'Santos da categoria ' . $categoria['nome'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <meta name="description" content="<?= htmlspecialchars($descricao) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($categoria['nome']) ?>, santos católicos, categoria">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($categoria['nome']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($descricao) ?>">
    <meta property="og:type" content="website">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .category-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 3rem 0;
        }
        .saint-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
        }
        .saint-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        .saint-image {
            height: 200px;
            object-fit: cover;
        }
        .pagination .page-link {
            color: #28a745;
        }
        .pagination .page-item.active .page-link {
            background-color: #28a745;
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cross me-2"></i>Santos Católicos
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="categorias.php">Categorias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calendario.php">Calendário</a>
                    </li>
                </ul>
                <form class="d-flex" method="GET" action="index.php">
                    <input class="form-control me-2" type="search" name="busca" placeholder="Buscar santos...">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Início</a></li>
                <li class="breadcrumb-item"><a href="categorias.php">Categorias</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($categoria['nome']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Header da Categoria -->
    <section class="category-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 mb-3"><?= htmlspecialchars($categoria['nome']) ?></h1>
                    <?php if ($categoria['descricao']): ?>
                    <p class="lead"><?= htmlspecialchars($categoria['descricao']) ?></p>
                    <?php endif; ?>
                    <div class="mb-3">
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-users me-1"></i>
                            <?= $total_santos ?> santos encontrados
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <?php if (empty($santos)): ?>
        <div class="alert alert-info text-center">
            <h4>Nenhum santo encontrado</h4>
            <p>Ainda não temos santos cadastrados nesta categoria.</p>
            <a href="index.php" class="btn btn-primary">Ver Todos os Santos</a>
        </div>
        <?php else: ?>
        <!-- Grid de Santos -->
        <div class="row">
            <?php foreach ($santos as $santo): ?>
            <div class="col-lg-4 col-md-6">
                <div class="card saint-card">
                    <?php if ($santo['imagem']): ?>
                    <img src="public/<?= htmlspecialchars($santo['imagem']) ?>" 
                         class="card-img-top saint-image" 
                         alt="<?= htmlspecialchars($santo['nome']) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($santo['nome']) ?></h5>
                        <?php if ($santo['resumo']): ?>
                        <p class="card-text"><?= htmlspecialchars(resumirTexto($santo['resumo'], 100)) ?></p>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-tag me-1"></i>
                                <?= ucfirst($santo['status']) ?>
                            </small>
                            <?php if ($santo['data_festa']): ?>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Festa: <?= formatarDataLiturgica($santo['data_festa']) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                        
                        <a href="santo.php?slug=<?= urlencode($santo['slug']) ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-book me-1"></i>
                            Ler Biografia
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginação -->
        <?php if ($total_paginas > 1): ?>
        <nav aria-label="Paginação dos santos" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($pagina > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?slug=<?= urlencode($slug) ?>&pagina=<?= $pagina - 1 ?>">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                </li>
                <?php endif; ?>

                <?php 
                $inicio = max(1, $pagina - 2);
                $fim = min($total_paginas, $pagina + 2);
                
                for ($i = $inicio; $i <= $fim; $i++): 
                ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link" href="?slug=<?= urlencode($slug) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($pagina < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?slug=<?= urlencode($slug) ?>&pagina=<?= $pagina + 1 ?>">
                        Próxima <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Santos Católicos</h5>
                    <p>Conheça a vida e história dos santos da Igreja Católica Apostólica Romana.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-cross me-2"></i>
                        Para maior glória de Deus
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>