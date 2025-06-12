<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Definir um conteúdo padrão se nenhum for especificado
if (!isset($conteudo) || empty($conteudo)) {
    $conteudo = 'views/dashboard.php';
}

// Verificar se o arquivo existe
if (!file_exists($conteudo)) {
    $conteudo = 'views/404.php';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - <?= $titulo ?? 'Admin' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }
        .sidebar a:hover {
            background: #34495e;
        }
        .content {
            padding: 20px;
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3">
                    <h3>Admin Panel</h3>
                </div>
                <nav>
                    <a href="index.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                    <a href="santos.php"><i class="fas fa-cross me-2"></i> Santos</a>
                    <a href="categorias.php"><i class="fas fa-tags me-2"></i> Categorias</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Sair</a>
                </nav>
            </div>

            <!-- Content -->
            <div class="col-md-9 col-lg-10 content">
                <?php if (isset($mensagem)): ?>
                    <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show">
                        <?= $mensagem['texto'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php include $conteudo; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
