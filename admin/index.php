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
    
    // Últimos santos adicionados
    $stmt = $db->query("SELECT nome, data_criacao FROM santos ORDER BY data_criacao DESC LIMIT 5");
    $ultimos_santos = $stmt->fetchAll();
    
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --card-shadow: 0 2px 15px rgba(0,0,0,0.08);
            --border-radius: 12px;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
            padding: 12px 16px;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            box-shadow: 0 2px 10px rgba(255,255,255,0.1);
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        .logo-section {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .logo-section h4 {
            font-weight: 600;
            margin: 0;
        }

        .stats-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .stats-card .card-body {
            padding: 24px;
        }

        .stats-card .stats-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            opacity: 0.2;
        }

        .stats-card.primary {
            background: linear-gradient(135deg, var(--accent-color), #5dade2);
            color: white;
        }

        .stats-card.success {
            background: linear-gradient(135deg, var(--success-color), #58d68d);
            color: white;
        }

        .stats-card.warning {
            background: linear-gradient(135deg, var(--warning-color), #f7dc6f);
            color: white;
        }

        .stats-card.danger {
            background: linear-gradient(135deg, var(--danger-color), #ec7063);
            color: white;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .main-content {
            padding: 24px;
        }

        .page-header {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 24px;
            margin-bottom: 24px;
        }

        .page-title {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
        }

        .action-buttons {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 24px;
            margin-top: 24px;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            margin-right: 12px;
            margin-bottom: 12px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .recent-activity {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 24px;
            margin-top: 24px;
        }

        .activity-item {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light-bg);
            color: var(--primary-color);
        }

        .user-dropdown {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 20px;
        }

        .user-dropdown .dropdown-toggle {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            padding: 12px 16px;
            width: 100%;
            text-align: left;
        }

        .user-dropdown .dropdown-toggle:focus {
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                padding: 16px;
            }
            
            .stats-card .card-body {
                padding: 16px;
            }
            
            .btn-action {
                width: 100%;
                margin-right: 0;
                justify-content: center;
            }
        }

        /* Correções para tabelas */
        .table-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-top: 24px;
        }

        .table-header {
            background: var(--primary-color);
            color: white;
            padding: 16px 24px;
            margin: 0;
            font-weight: 600;
        }

        .table-responsive {
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .table {
            margin: 0;
        }

        .table th {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: var(--primary-color);
        }

        .table td {
            vertical-align: middle;
        }

        /* Correções para formulários */
        .form-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 24px;
            margin-top: 24px;
        }

        .form-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3">
                <div class="sidebar">
                    <div class="p-3">
                        <div class="logo-section text-center">
                            <i class="fas fa-cross fa-2x mb-2" style="color: #f1c40f;"></i>
                            <h4>Pequenas Vias</h4>
                            <small class="text-muted">Painel Administrativo</small>
                        </div>
                        
                        <nav class="nav flex-column">
                            <a href="index.php" class="nav-link active">
                                <i class="fas fa-home"></i>
                                <span class="ms-2">Dashboard</span>
                            </a>
                            <a href="santos.php" class="nav-link">
                                <i class="fas fa-user"></i>
                                <span class="ms-2">Santos</span>
                            </a>
                            <a href="categorias.php" class="nav-link">
                                <i class="fas fa-tags"></i>
                                <span class="ms-2">Categorias</span>
                            </a>
                            <?php if ($_SESSION['usuario_nivel'] === 'admin'): ?>
                            <a href="usuarios.php" class="nav-link">
                                <i class="fas fa-users"></i>
                                <span class="ms-2">Usuários</span>
                            </a>
                            <?php endif; ?>
                        </nav>

                        <div class="user-dropdown">
                            <div class="dropdown">
                                <button class="btn dropdown-toggle text-white w-100" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-2"></i>
                                    <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-dark w-100">
                                    <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                    <li><a class="dropdown-item" href="configuracoes.php"><i class="fas fa-cog me-2"></i>Configurações</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="col-lg-10 col-md-9">
                <div class="main-content">
                    <!-- Header da página -->
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="page-title">Dashboard</h1>
                                <p class="text-muted mb-0">Bem-vindo ao painel administrativo do Pequenas Vias</p>
                            </div>
                            <div class="col-auto">
                                <span class="badge bg-success fs-6">Online</span>
                            </div>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card primary">
                                <div class="card-body">
                                    <h3 class="stats-number"><?= $stats['total_santos'] ?></h3>
                                    <p class="stats-label">Total de Santos</p>
                                    <i class="fas fa-user stats-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card success">
                                <div class="card-body">
                                    <h3 class="stats-number"><?= $stats['total_categorias'] ?></h3>
                                    <p class="stats-label">Categorias</p>
                                    <i class="fas fa-tags stats-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card warning">
                                <div class="card-body">
                                    <h3 class="stats-number"><?= $stats['santos_ativos'] ?></h3>
                                    <p class="stats-label">Santos Ativos</p>
                                    <i class="fas fa-check-circle stats-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card danger">
                                <div class="card-body">
                                    <h3 class="stats-number"><?= $stats['acessos_hoje'] ?></h3>
                                    <p class="stats-label">Acessos Hoje</p>
                                    <i class="fas fa-eye stats-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Ações Rápidas -->
                        <div class="col-lg-8">
                            <div class="action-buttons">
                                <h4 class="mb-3">
                                    <i class="fas fa-bolt text-warning me-2"></i>
                                    Ações Rápidas
                                </h4>
                                <a href="santos.php?action=new" class="btn btn-primary btn-action">
                                    <i class="fas fa-plus"></i>
                                    Novo Santo
                                </a>
                                <a href="categorias.php?action=new" class="btn btn-success btn-action">
                                    <i class="fas fa-plus"></i>
                                    Nova Categoria
                                </a>
                                <a href="santos.php" class="btn btn-info btn-action">
                                    <i class="fas fa-list"></i>
                                    Ver Santos
                                </a>
                                <a href="categorias.php" class="btn btn-warning btn-action">
                                    <i class="fas fa-tags"></i>
                                    Ver Categorias
                                </a>
                                <?php if ($_SESSION['usuario_nivel'] === 'admin'): ?>
                                <a href="usuarios.php" class="btn btn-secondary btn-action">
                                    <i class="fas fa-users"></i>
                                    Gerenciar Usuários
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Atividade Recente -->
                        <div class="col-lg-4">
                            <div class="recent-activity">
                                <h4 class="mb-3">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    Atividade Recente
                                </h4>
                                <?php if (!empty($ultimos_santos)): ?>
                                    <?php foreach ($ultimos_santos as $santo): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-plus"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($santo['nome']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                Adicionado em <?= date('d/m/Y', strtotime($santo['data_criacao'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Nenhuma atividade recente</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Adicionar funcionalidade de menu responsivo
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-ajustar altura da sidebar em dispositivos móveis
            function adjustSidebar() {
                const sidebar = document.querySelector('.sidebar');
                if (window.innerWidth <= 768) {
                    sidebar.style.minHeight = 'auto';
                } else {
                    sidebar.style.minHeight = '100vh';
                }
            }
            
            window.addEventListener('resize', adjustSidebar);
            adjustSidebar();
            
            // Destacar link ativo baseado na URL atual
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>