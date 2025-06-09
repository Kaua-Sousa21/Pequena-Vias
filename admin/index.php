<?php
require_once '../config/init.php';
require_once 'check_auth.php';

$database = new Database();
$db = $database->getConnection();

// Buscar estatísticas
$stats = [
    'total_santos' => 0,
    'total_categorias' => 0,
    'santos_ativos' => 0,
    'acessos_hoje' => 0
];



try {
    // Total de santos
    $stmt = $db->query("SELECT COUNT(*) as total FROM santos");
    $stats['total_santos'] = $stmt->fetch()['total'];
    
    // Total de categorias
    $stmt = $db->query("SELECT COUNT(*) as total FROM categorias");
    $stats['total_categorias'] = $stmt->fetch()['total'];
    
    // Santos ativos
    $stmt = $db->query("SELECT COUNT(*) as total FROM santos WHERE status = 'ativo'");
    $stats['santos_ativos'] = $stmt->fetch()['total'];
} catch (Exception $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Pequenas Vias</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .stats-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                        <i class="fas fa-cross me-2"></i>
                        <span class="fs-4">Pequenas Vias</span>
                    </a>
                    <hr>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="index.php" class="nav-link active">
                                <i class="fas fa-home me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="santos.php" class="nav-link">
                                <i class="fas fa-user me-2"></i>
                                Santos
                            </a>
                        </li>
                        <li>
                            <a href="categorias.php" class="nav-link">
                                <i class="fas fa-tags me-2"></i>
                                Categorias
                            </a>
                        </li>
                        <?php if ($_SESSION['usuario_nivel'] === 'admin'): ?>
                        <li>
                            <a href="usuarios.php" class="nav-link">
                                <i class="fas fa-users me-2"></i>
                                Usuários
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <hr>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <strong><?= htmlspecialchars($_SESSION['usuario_nome']) ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="perfil.php">Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                <h1 class="h2 mb-4">Dashboard</h1>

                <!-- Estatísticas -->
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title text-muted">Total de Santos</h5>
                                <h2 class="mb-0"><?= $stats['total_santos'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title text-muted">Categorias</h5>
                                <h2 class="mb-0"><?= $stats['total_categorias'] ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stats-card">
                            <div class="card-body">
                                <h5 class="card-title text-muted">Santos Ativos</h5>
                                <h2 class="mb-0"><?= $stats['santos_ativos'] ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h3 class="h4 mb-3">Ações Rápidas</h3>
                        <div class="d-flex gap-2">
                            <a href="santos.php?action=new" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Novo Santo
                            </a>
                            <a href="categorias.php?action=new" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Nova Categoria
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
