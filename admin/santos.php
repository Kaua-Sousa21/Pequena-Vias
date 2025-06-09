<?php
require_once '../config/init.php';
require_once 'check_auth.php';

$database = new Database();
$db = $database->getConnection();
$santoObj = new Santo($db);
$categoriaObj = new Categoria($db);

$action = $_GET['action'] ?? 'list';
$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'create':
            case 'update':
                $dados = [
                    'nome' => $_POST['nome'],
                    'nome_completo' => $_POST['nome_completo'],
                    'slug' => criarSlug($_POST['nome']),
                    'resumo' => $_POST['resumo'],
                    'biografia' => $_POST['biografia'],
                    'data_nascimento' => $_POST['data_nascimento'],
                    'local_nascimento' => $_POST['local_nascimento'],
                    'data_morte' => $_POST['data_morte'],
                    'local_morte' => $_POST['local_morte'],
                    'data_canonizacao' => $_POST['data_canonizacao'],
                    'papa_canonizacao' => $_POST['papa_canonizacao'],
                    'data_festa' => $_POST['data_festa'],
                    'padroeiro_de' => $_POST['padroeiro_de'],
                    'simbolos' => $_POST['simbolos'],
                    'milagres' => $_POST['milagres'],
                    'oracao' => $_POST['oracao'],
                    'status' => $_POST['status'],
                    'categorias' => $_POST['categorias'] ?? []
                ];

                // Upload de imagem
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $imagem = processarUploadImagem($_FILES['imagem']);
                    if ($imagem) {
                        $dados['imagem'] = $imagem;
                    }
                }

                if ($_POST['action'] === 'create') {
                    $santoObj->criar($dados);
                    $sucesso = 'Santo criado com sucesso!';
                } else {
                    $santoObj->atualizar($_POST['id'], $dados);
                    $sucesso = 'Santo atualizado com sucesso!';
                }
                break;

            case 'delete':
                $santoObj->deletar($_POST['id']);
                $sucesso = 'Santo excluído com sucesso!';
                break;
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar categorias para o formulário
$categorias = $categoriaObj->listarTodas();

// Buscar dados conforme a ação
switch ($action) {
    case 'new':
        $titulo = 'Novo Santo';
        $santo = [];
        break;
        
    case 'edit':
        $titulo = 'Editar Santo';
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: santos.php');
            exit;
        }
        $santo = $santoObj->buscarPorId($id);
        if (!$santo) {
            header('Location: santos.php?erro=santo_nao_encontrado');
            exit;
        }
        break;
        
    default:
        $titulo = 'Gerenciar Santos';
        $pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
        $por_pagina = 20;
        $offset = ($pagina - 1) * $por_pagina;
        
        $santos = $santoObj->listarSantos($por_pagina, $offset);
        $total_santos = $santoObj->contarSantos();
        $total_paginas = ceil($total_santos / $por_pagina);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - Painel Administrativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #06b6d4;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
            --text-muted: #6b7280;
            --sidebar-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--dark-color);
            overflow-x: hidden;
        }

        /* Layout Container */
        .admin-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
            backdrop-filter: blur(10px);
        }

        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            width: calc(100% - var(--sidebar-width));
        }

        .main-content.expanded {
            margin-left: 0;
            width: 100%;
        }

        /* Mobile Toggle Button */
        .sidebar-toggle {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background: var(--primary-dark);
            transform: scale(1.1);
        }

        /* Header */
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-header h1 {
            color: var(--dark-color);
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
            flex: 1;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border: none;
            padding: 20px 25px;
            font-weight: 600;
        }

        /* Buttons */
        .btn {
            border-radius: 12px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border: none;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.75rem;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 16px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            transform: translateY(-1px);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
        }

        /* Table Container - CORRIGIDO */
        .table-container {
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-responsive {
            border-radius: 0;
            box-shadow: none;
            border: none;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            margin: 0;
            width: 100%;
            min-width: 800px; /* Largura mínima para evitar cortes */
        }

        .table thead th {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: none;
            padding: 1.5rem 1rem;
            font-weight: 700;
            color: var(--dark-color);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 1.5rem 1rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-weight: 500;
        }

        .table tbody td:first-child {
            font-weight: 600;
            color: var(--dark-color);
        }

        .table-hover tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            transition: all 0.3s ease;
        }

        /* Responsive table adjustments */
        @media (max-width: 1200px) {
            .table {
                min-width: 1000px;
            }
        }

        @media (max-width: 992px) {
            .table {
                min-width: 900px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 1rem 0.75rem;
            }
        }

        /* Badges */
        .badge {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .bg-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%) !important;
        }

        .bg-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 20px 25px;
            font-weight: 500;
            box-shadow: var(--card-shadow);
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }

        /* Pagination */
        .pagination {
            gap: 5px;
            justify-content: center;
            margin-top: 30px;
        }

        .page-link {
            border: none;
            border-radius: 10px;
            padding: 10px 15px;
            color: var(--primary-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-color: var(--primary-color);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        /* Image Preview */
        .image-preview {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            max-width: 200px;
        }

        /* Form Sections */
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 25px;
        }

        .form-section h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        /* Summernote */
        .note-editor {
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        /* Action Buttons Container */
        .action-buttons {
            display: flex;
            gap: 5px;
            align-items: center;
            justify-content: flex-start;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-toggle {
                display: flex;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
            
            .page-header {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 2px;
            }
            
            .btn-sm {
                padding: 4px 8px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 576px) {
            .table {
                min-width: 600px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }
            
            .page-header .btn {
                width: 100%;
                margin-top: 15px;
            }
        }

        /* Loading Animation */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Overlay para mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            backdrop-filter: blur(5px);
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Melhorias para campos de ação */
        .actions-cell {
            min-width: 120px;
        }

        /* Status badge improvements */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-width: 80px;
            justify-content: center;
        }

        /* Categoria tags */
        .categoria-tags {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Data formatting */
        .data-festa {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="sidebar">
                    <?php include 'includes/sidebar.php'; ?>
                </div>
            </div>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10">
                <div class="main-content">
                    <!-- Header -->
                    <div class="page-header d-flex justify-content-between align-items-center">
                        <h1><?= $titulo ?></h1>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=new" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Novo Santo
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($erro): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($sucesso) ?>
                    </div>
                    <?php endif; ?>

                    <?php if ($action === 'list'): ?>
                        <!-- Lista de Santos -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-pray me-2"></i>Nome</th>
                                        <th><i class="fas fa-calendar me-2"></i>Data Festa</th>
                                        <th><i class="fas fa-tags me-2"></i>Categorias</th>
                                        <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                                        <th><i class="fas fa-cogs me-2"></i>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($santos as $santo): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($santo['nome']) ?></strong>
                                        </td>
                                        <td><?= formatarDataLiturgica($santo['data_festa']) ?></td>
                                        <td><?= htmlspecialchars($santo['categorias']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $santo['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                                <i class="fas fa-<?= $santo['status'] === 'ativo' ? 'check' : 'times' ?> me-1"></i>
                                                <?= ucfirst($santo['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?action=edit&id=<?= $santo['id'] ?>" 
                                               class="btn btn-sm btn-primary me-2" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="confirmarExclusao(<?= $santo['id'] ?>)" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        <?php if ($total_paginas > 1): ?>
                        <nav aria-label="Navegação da lista de santos" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Formulário de Santo -->
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
                            <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $santo['id'] ?>">
                            <?php endif; ?>

                            <!-- Informações Básicas -->
                            <div class="form-section">
                                <h5><i class="fas fa-info-circle text-primary me-2"></i>Informações Básicas</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nome" class="form-label">Nome *</label>
                                        <input type="text" class="form-control" id="nome" name="nome" 
                                               value="<?= $santo['nome'] ?? '' ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="nome_completo" class="form-label">Nome Completo</label>
                                        <input type="text" class="form-control" id="nome_completo" name="nome_completo"
                                               value="<?= $santo['nome_completo'] ?? '' ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="resumo" class="form-label">Resumo</label>
                                    <textarea class="form-control" id="resumo" name="resumo" rows="3"><?= $santo['resumo'] ?? '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="biografia" class="form-label">Biografia</label>
                                    <textarea class="form-control summernote" id="biografia" name="biografia"><?= $santo['biografia'] ?? '' ?></textarea>
                                </div>
                            </div>

                            <!-- Datas Importantes -->
                            <div class="form-section">
                                <h5><i class="fas fa-calendar-alt text-primary me-2"></i>Datas Importantes</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                        <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                               value="<?= $santo['data_nascimento'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="local_nascimento" class="form-label">Local de Nascimento</label>
                                        <input type="text" class="form-control" id="local_nascimento" name="local_nascimento"
                                               value="<?= $santo['local_nascimento'] ?? '' ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="data_morte" class="form-label">Data de Morte</label>
                                        <input type="date" class="form-control" id="data_morte" name="data_morte"
                                               value="<?= $santo['data_morte'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="local_morte" class="form-label">Local de Morte</label>
                                        <input type="text" class="form-control" id="local_morte" name="local_morte"
                                               value="<?= $santo['local_morte'] ?? '' ?>">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="data_canonizacao" class="form-label">Data de Canonização</label>
                                        <input type="date" class="form-control" id="data_canonizacao" name="data_canonizacao"
                                               value="<?= $santo['data_canonizacao'] ?? '' ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="papa_canonizacao" class="form-label">Papa da Canonização</label>
                                        <input type="text" class="form-control" id="papa_canonizacao" name="papa_canonizacao"
                                               value="<?= $santo['papa_canonizacao'] ?? '' ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="data_festa" class="form-label">Data da Festa</label>
                                    <input type="date" class="form-control" id="data_festa" name="data_festa"
                                           value="<?= $santo['data_festa'] ?? '' ?>">
                                </div>
                            </div>

                            <!-- Classificação e Imagem -->
                            <div class="form-section">
                                <h5><i class="fas fa-image text-primary me-2"></i>Classificação e Imagem</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="categorias" class="form-label">Categorias</label>
                                        <select class="form-select" id="categorias" name="categorias[]" multiple>
                                            <?php foreach ($categorias as $categoria): ?>
                                            <option value="<?= $categoria['id'] ?>"
                                                    <?= in_array($categoria['id'], $santo['categorias'] ?? []) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($categoria['nome']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="ativo" <?= ($santo['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                            <option value="inativo" <?= ($santo['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="imagem" class="form-label">Imagem</label>
                                    <?php if (!empty($santo['imagem'])): ?>
                                    <div class="mb-3">
                                        <div class="image-preview">
                                            <img src="<?= htmlspecialchars($santo['imagem']) ?>" 
                                                 alt="Imagem atual" class="img-fluid">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="imagem" name="imagem">
                                </div>
                            </div>

                            <!-- Informações Espirituais -->
                            <div class="form-section">
                                <h5><i class="fas fa-pray text-primary me-2"></i>Informações Espirituais</h5>
                                <div class="mb-3">
                                    <label for="padroeiro_de" class="form-label">Padroeiro de</label>
                                    <textarea class="form-control" id="padroeiro_de" name="padroeiro_de" rows="3"><?= $santo['padroeiro_de'] ?? '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="simbolos" class="form-label">Símbolos</label>
                                    <textarea class="form-control" id="simbolos" name="simbolos" rows="3"><?= $santo['simbolos'] ?? '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="milagres" class="form-label">Milagres</label>
                                    <textarea class="form-control summernote" id="milagres" name="milagres"><?= $santo['milagres'] ?? '' ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="oracao" class="form-label">Oração</label>
                                    <textarea class="form-control" id="oracao" name="oracao" rows="4"><?= $santo['oracao'] ?? '' ?></textarea>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="form-section text-center">
                                <button type="submit" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-save me-2"></i>Salvar Santo
                                </button>
                                <a href="santos.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-2"></i>Cancelar
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        // Inicializar Summernote
        $('.summernote').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            styleTags: [
                'p',
                { title: 'Blockquote', tag: 'blockquote', className: 'blockquote', value: 'blockquote' },
                'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
            ]
        });

        // Confirmação de exclusão com SweetAlert style
        function confirmarExclusao(id) {
            // Criar modal de confirmação personalizado
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: var(--card-shadow-hover);">
                        <div class="modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; border: none; border-radius: 20px 20px 0 0;">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão
                            </h5>
                        </div>
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                            <h6 class="mb-3">Tem certeza que deseja excluir este santo?</h6>
                            <p class="text-muted">Esta ação não pode ser desfeita.</p>
                        </div>
                        <div class="modal-footer border-0 justify-content-center">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </button>
                            <button type="button" class="btn btn-danger" onclick="executarExclusao(${id})">
                                <i class="fas fa-trash me-2"></i>Sim, Excluir
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const modalInstance = new bootstrap.Modal(modal);
            modalInstance.show();
            
            modal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(modal);
            });
        }

        function executarExclusao(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Validação do formulário com animações
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                        
                        // Animar campos inválidos
                        const invalidFields = form.querySelectorAll(':invalid');
                        invalidFields.forEach(field => {
                            field.style.animation = 'shake 0.5s ease-in-out';
                            setTimeout(() => {
                                field.style.animation = '';
                            }, 500);
                        });
                    } else {
                        // Mostrar loading no botão de submit
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Salvando...';
                            submitBtn.disabled = true;
                        }
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Animação de shake para campos inválidos
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
                20%, 40%, 60%, 80% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);

        // Preview de imagem
        document.getElementById('imagem')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Remover preview anterior se existir
                    const existingPreview = document.querySelector('.image-preview-new');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    // Criar novo preview
                    const preview = document.createElement('div');
                    preview.className = 'image-preview image-preview-new mt-3';
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid">`;
                    e.target.parentElement.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });

        // Smooth scroll para seções do formulário
        document.querySelectorAll('.form-section h5').forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                this.parentElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            });
        });

        // Auto-save draft (opcional - pode ser implementado)
        let autoSaveTimeout;
        document.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Aqui você pode implementar auto-save
                    console.log('Auto-save triggered');
                }, 2000);
            });
        });

        // Tooltip para botões
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Contador de caracteres para textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            if (!textarea.classList.contains('summernote')) {
                const counter = document.createElement('small');
                counter.className = 'text-muted mt-1 d-block';
                counter.innerHTML = `<i class="fas fa-keyboard me-1"></i>0 caracteres`;
                textarea.parentElement.appendChild(counter);
                
                textarea.addEventListener('input', function() {
                    const count = this.value.length;
                    counter.innerHTML = `<i class="fas fa-keyboard me-1"></i>${count} caracteres`;
                });
            }
        });

        // Animação de entrada para elementos
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Aplicar animação de entrada para cards e seções
        document.querySelectorAll('.form-section, .card, .table-responsive').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });

        // Melhorar experiência com select múltiplo
        const multiSelect = document.getElementById('categorias');
        if (multiSelect) {
            // Adicionar feedback visual
            multiSelect.addEventListener('change', function() {
                const selectedCount = this.selectedOptions.length;
                let feedback = this.parentElement.querySelector('.select-feedback');
                
                if (!feedback) {
                    feedback = document.createElement('small');
                    feedback.className = 'text-muted mt-1 d-block select-feedback';
                    this.parentElement.appendChild(feedback);
                }
                
                if (selectedCount > 0) {
                    feedback.innerHTML = `<i class="fas fa-tags me-1"></i>${selectedCount} categoria${selectedCount > 1 ? 's' : ''} selecionada${selectedCount > 1 ? 's' : ''}`;
                } else {
                    feedback.innerHTML = '<i class="fas fa-info-circle me-1"></i>Nenhuma categoria selecionada';
                }
            });
        }
    </script>
</body>
</html>