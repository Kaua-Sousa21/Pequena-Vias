<?php
require_once '../config/init.php';
require_once 'check_auth.php';

$database = new Database();
$db = $database->getConnection();
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
                    'slug' => criarSlug($_POST['nome']),
                    'descricao' => $_POST['descricao'],
                    'status' => $_POST['status']
                ];

                if ($_POST['action'] === 'create') {
                    $categoriaObj->criar($dados);
                    $sucesso = 'Categoria criada com sucesso!';
                } else {
                    $categoriaObj->atualizar($_POST['id'], $dados);
                    $sucesso = 'Categoria atualizada com sucesso!';
                }
                break;

            case 'delete':
                $categoriaObj->deletar($_POST['id']);
                $sucesso = 'Categoria excluída com sucesso!';
                break;
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar dados conforme a ação
switch ($action) {
    case 'new':
        $titulo = 'Nova Categoria';
        $categoria = [];
        break;
        
    case 'edit':
        $titulo = 'Editar Categoria';
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: categorias.php');
            exit;
        }
        $categoria = $categoriaObj->buscarPorId($id);
        if (!$categoria) {
            header('Location: categorias.php?erro=categoria_nao_encontrada');
            exit;
        }
        break;
        
    default:
        $titulo = 'Gerenciar Categorias';
        $categorias = $categoriaObj->listarTodas();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2196f3;
            --primary-dark: #2196f3;
            --secondary-color: #1976d2;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --border-color: #e5e7eb;
            --text-color: #374151;
            --text-muted: #6b7280;
            --sidebar-bg: linear-gradient(90deg, #1976d2, #2196f3);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            
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
            color: var(--text-color);
        }

        .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            width: 280px;
            box-shadow: var(--card-shadow);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            color: white;
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            background: none;
            margin: 0.25rem 0;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.15);
            transform: translateX(10px);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .container-fluid {
            padding: 0;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .page-header {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 15px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            border: none;
            border-radius: 15px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 10px;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        .table-responsive {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
        }

        .table {
            margin: 0;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border: none;
            padding: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            position: relative;
        }

        .table tbody td {
            padding: 1.5rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-weight: 500;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            transform: scale(1.01);
        }

        .badge {
            padding: 0.6rem 1rem;
            border-radius: 25px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .bg-success {
            background: linear-gradient(135deg, var(--success-color), #059669) !important;
            color: white !important;
        }

        .bg-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563) !important;
            color: white !important;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
            transform: translateY(-2px);
        }

        .form-select {
            border: 2px solid var(--border-color);
            border-radius: 15px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-weight: 500;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }

        .mb-3 {
            margin-bottom: 2rem !important;
        }

        .alert {
            border: none;
            border-radius: 15px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            font-weight: 600;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .alert-success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border-left: 4px solid var(--success-color);
        }

        .alert-success::before {
            background: var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border-left: 4px solid var(--danger-color);
        }

        .alert-danger::before {
            background: var(--danger-color);
        }

        .form-container {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .table-responsive, .form-container, .page-header, .alert {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.8rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }
        }

        /* Efeitos especiais */
        .btn:active {
            transform: translateY(0) !important;
        }

        .form-control:invalid {
            border-color: var(--danger-color);
        }

        .form-control:valid {
            border-color: var(--success-color);
        }

        /* Melhorias na tabela */
        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table thead th:first-child {
            border-top-left-radius: 20px;
        }

        .table thead th:last-child {
            border-top-right-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Conteúdo Principal -->
            <div class="main-content">
                <div class="page-header">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <h1 class="page-title"><?= $titulo ?></h1>
                        <?php if ($action === 'list'): ?>
                        <a href="?action=new" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Nova Categoria
                        </a>
                        <?php endif; ?>
                    </div>
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
                    <!-- Lista de Categorias -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-tag me-2"></i>Nome</th>
                                    <th><i class="fas fa-link me-2"></i>Slug</th>
                                    <th><i class="fas fa-praying-hands me-2"></i>Total de Santos</th>
                                    <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                                    <th><i class="fas fa-cogs me-2"></i>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($cat['nome']) ?></strong>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($cat['slug']) ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= $cat['total_santos'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $cat['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                            <i class="fas fa-<?= $cat['status'] === 'ativo' ? 'check' : 'times' ?> me-1"></i>
                                            <?= ucfirst($cat['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="?action=edit&id=<?= $cat['id'] ?>" 
                                               class="btn btn-sm btn-primary" 
                                               title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="confirmarExclusao(<?= $cat['id'] ?>)"
                                                    title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Formulário de Categoria -->
                    <div class="form-container">
                        <form method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
                            <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nome" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Nome da Categoria
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="nome" 
                                               name="nome" 
                                               value="<?= htmlspecialchars($categoria['nome'] ?? '') ?>" 
                                               required
                                               placeholder="Digite o nome da categoria">
                                        <div class="invalid-feedback">
                                            Por favor, informe o nome da categoria.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-toggle-on me-2"></i>Status
                                        </label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="">Selecione...</option>
                                            <option value="ativo" <?= ($categoria['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>
                                                <i class="fas fa-check"></i> Ativo
                                            </option>
                                            <option value="inativo" <?= ($categoria['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>
                                                <i class="fas fa-times"></i> Inativo
                                            </option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Por favor, selecione o status.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="descricao" class="form-label">
                                    <i class="fas fa-align-left me-2"></i>Descrição
                                </label>
                                <textarea class="form-control" 
                                          id="descricao" 
                                          name="descricao" 
                                          rows="4"
                                          placeholder="Descreva a categoria (opcional)"><?= htmlspecialchars($categoria['descricao'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex gap-3 pt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    <?= $action === 'new' ? 'Criar Categoria' : 'Atualizar Categoria' ?>
                                </button>
                                <a href="categorias.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Voltar
                                </a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmação de exclusão com estilo
        function confirmarExclusao(id) {
            if (confirm('⚠️ Tem certeza que deseja excluir esta categoria?\n\nEsta ação não pode ser desfeita!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Validação do formulário com Bootstrap
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Efeitos visuais
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });

            // Smooth scroll para formulários
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'new' || urlParams.get('action') === 'edit') {
                setTimeout(() => {
                    document.querySelector('.form-container').scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
        });

        // Melhorar UX dos botões
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (!this.disabled) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                }
            });
        });
    </script>
</body>
</html>