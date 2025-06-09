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
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $titulo ?></h1>
                    <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Nova Categoria
                    </a>
                    <?php endif; ?>
                </div>

                <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <!-- Lista de Categorias -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Slug</th>
                                    <th>Total de Santos</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['nome']) ?></td>
                                    <td><?= htmlspecialchars($cat['slug']) ?></td>
                                    <td><?= $cat['total_santos'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $cat['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($cat['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?action=edit&id=<?= $cat['id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmarExclusao(<?= $cat['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Formulário de Categoria -->
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
                        <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $categoria['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" 
                                   value="<?= $categoria['nome'] ?? '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?= $categoria['descricao'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="ativo" <?= ($categoria['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= ($categoria['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmação de exclusão
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir esta categoria?')) {
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

        // Validação do formulário
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
    </script>
</body>
</html>
