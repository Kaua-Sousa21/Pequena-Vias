<?php
require_once 'config/database.php';
require_once 'classes/Santo.php';
require_once 'utils/functions.php';

$database = new Database();
$db = $database->getConnection();
$santoObj = new Santo($db);

// Verificar se o slug foi fornecido
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: index.php');
    exit();
}

$slug = $_GET['slug'];
$santo = $santoObj->buscarPorSlug($slug);

// Se o santo não foi encontrado, redirecionar
if (!$santo) {
    header('Location: index.php?erro=santo_nao_encontrado');
    exit();
}

// Meta tags para SEO
$titulo = htmlspecialchars($santo['nome']) . ' - Santos Católicos';
$descricao = resumirTexto($santo['resumo'], 160);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?></title>
    <meta name="description" content="<?= htmlspecialchars($descricao) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($santo['nome']) ?>, santo católico, biografia, vida">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($santo['nome']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($descricao) ?>">
    <meta property="og:type" content="article">
    <?php if ($santo['imagem']): ?>
    <meta property="og:image" content="<?= htmlspecialchars($santo['imagem']) ?>">
    <?php endif; ?>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Schema.org structured data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "<?= htmlspecialchars($santo['nome']) ?>",
        "description": "<?= htmlspecialchars($descricao) ?>",
        <?php if ($santo['data_nascimento']): ?>
        "birthDate": "<?= $santo['data_nascimento'] ?>",
        <?php endif; ?>
        <?php if ($santo['data_morte']): ?>
        "deathDate": "<?= $santo['data_morte'] ?>",
        <?php endif; ?>
        <?php if ($santo['local_nascimento']): ?>
        "birthPlace": "<?= htmlspecialchars($santo['local_nascimento']) ?>",
        <?php endif; ?>
        "additionalType": "https://schema.org/Religious"
    }
    </script>
    
    <style>
        .saint-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 0;
        }
        .saint-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .info-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .prayer-section {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 1.5rem;
            border-radius: 5px;
        }
        .timeline-item {
            border-left: 2px solid #007bff;
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        .share-buttons a {
            margin-right: 10px;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }
        .breadcrumb {
            background: transparent;
            padding: 0;
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
                        <a class="nav-link" href="categorias.php">Categorias</a>
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
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($santo['nome']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Header do Santo -->
    <section class="saint-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 mb-3"><?= htmlspecialchars($santo['nome']) ?></h1>
                    <?php if ($santo['nome_completo'] && $santo['nome_completo'] != $santo['nome']): ?>
                    <p class="lead mb-3">
                        <em><?= htmlspecialchars($santo['nome_completo']) ?></em>
                    </p>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <span class="badge bg-light text-dark fs-6 me-2">
                            <?= ucfirst($santo['status']) ?>
                        </span>
                        <?php if ($santo['data_festa']): ?>
                        <span class="badge bg-warning text-dark fs-6 me-2">
                            <i class="fas fa-calendar me-1"></i>
                            Festa: <?= formatarDataLiturgica($santo['data_festa']) ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($santo['categorias']): ?>
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-tags me-1"></i>
                            <?= htmlspecialchars($santo['categorias']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($santo['resumo']): ?>
                    <p class="lead"><?= htmlspecialchars($santo['resumo']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <div class="row">
            <!-- Conteúdo Principal -->
            <div class="col-lg-8">
                <!-- Imagem Principal -->
                <?php if ($santo['imagem']): ?>
                <div class="text-center mb-4">
                    <img src="public/<?= htmlspecialchars($santo['imagem']) ?>" 
                         alt="<?= htmlspecialchars($santo['nome']) ?>" 
                         class="img-fluid saint-image">
                </div>
                <?php endif; ?>

                <!-- Biografia -->
                <?php if ($santo['biografia']): ?>
                <div class="card info-card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-book me-2"></i>Biografia
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($santo['biografia'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Milagres -->
                <?php if ($santo['milagres']): ?>
                <div class="card info-card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0">
                            <i class="fas fa-star me-2"></i>Milagres e Sinais
                        </h3>
                    </div>
                    <div class="card-body">
                        <div style="line-height: 1.7;">
                            <?= nl2br(htmlspecialchars($santo['milagres'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Oração -->
                <?php if ($santo['oracao']): ?>
                <div class="prayer-section mb-4">
                    <h3 class="mb-3">
                        <i class="fas fa-praying-hands me-2"></i>Oração a <?= htmlspecialchars($santo['nome']) ?>
                    </h3>
                    <div style="font-style: italic; line-height: 1.7;">
                        <?= nl2br(htmlspecialchars($santo['oracao'])) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Compartilhar -->
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="fas fa-share me-2"></i>Compartilhar
                        </h5>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="btn btn-primary">
                                <i class="fab fa-facebook-f me-1"></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($santo['nome'] . ' - ' . $santo['resumo']) ?>&url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="btn btn-info">
                                <i class="fab fa-twitter me-1"></i>Twitter
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode($santo['nome'] . ' - ' . $santo['resumo'] . ' http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="btn btn-success">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Informações Básicas -->
                <div class="card info-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informações
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($santo['data_nascimento']): ?>
                        <div class="timeline-item">
                            <h6><i class="fas fa-baby text-primary me-2"></i>Nascimento</h6>
                            <p class="mb-1"><?= formatarData($santo['data_nascimento']) ?></p>
                            <?php if ($santo['local_nascimento']): ?>
                            <small class="text-muted"><?= htmlspecialchars($santo['local_nascimento']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($santo['data_morte']): ?>
                        <div class="timeline-item">
                            <h6><i class="fas fa-cross text-danger me-2"></i>Morte</h6>
                            <p class="mb-1"><?= formatarData($santo['data_morte']) ?></p>
                            <?php if ($santo['local_morte']): ?>
                            <small class="text-muted"><?= htmlspecialchars($santo['local_morte']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($santo['data_canonizacao']): ?>
                        <div class="timeline-item">
                            <h6><i class="fas fa-award text-warning me-2"></i>Canonização</h6>
                            <p class="mb-1"><?= formatarData($santo['data_canonizacao']) ?></p>
                            <?php if ($santo['papa_canonizacao']): ?>
                            <small class="text-muted">Papa <?= htmlspecialchars($santo['papa_canonizacao']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($santo['data_festa']): ?>
                        <div class="timeline-item">
                            <h6><i class="fas fa-calendar-star text-success me-2"></i>Festa Litúrgica</h6>
                            <p class="mb-0"><?= formatarDataLiturgica($santo['data_festa']) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Padroeiro -->
                <?php if ($santo['padroeiro_de']): ?>
                <div class="card info-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt me-2"></i>Padroeiro(a) de
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><?= htmlspecialchars($santo['padroeiro_de']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Símbolos -->
                <?php if ($santo['simbolos']): ?>
                <div class="card info-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-icons me-2"></i>Símbolos
                        </h5>
                    </div>
                    <div class="card-body">
                        <p><?= htmlspecialchars($santo['simbolos']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Navegação -->
                <div class="card info-card">
                    <div class="card-body text-center">
                        <a href="index.php" class="btn btn-outline-primary mb-2 w-100">
                            <i class="fas fa-list me-2"></i>Ver Todos os Santos
                        </a>
                        <a href="calendario.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-calendar me-2"></i>Calendário Litúrgico
                        </a>
                    </div>
                </div>
            </div>
        </div>
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