<?php
require_once '../config/init.php';
require_once 'check_auth.php';

// Verificar se é admin
requireAdmin();

$database = new Database();
$db = $database->getConnection();

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
                    'email' => $_POST['email'],
                    'nivel' => $_POST['nivel'],
                    'status' => $_POST['status']
                ];

                // Se for criação ou se a senha foi fornecida
                if ($_POST['action'] === 'create' || !empty($_POST['senha'])) {
                    $dados['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                }

                if ($_POST['action'] === 'create') {
                    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, nivel, status) VALUES (:nome, :email, :senha, :nivel, :status)");
                    $stmt->execute($dados);
                    $sucesso = 'Usuário criado com sucesso!';
                } else {
                    $dados['id'] = $_POST['id'];
                    $sql = "UPDATE usuarios SET nome = :nome, email = :email, nivel = :nivel, status = :status";
                    if (isset($dados['senha'])) {
                        $sql .= ", senha = :senha";
                    }
                    $sql .= " WHERE id = :id";
                    
                    $stmt = $db->prepare($sql);
                    $stmt->execute($dados);
                    $sucesso = 'Usuário atualizado com sucesso!';
                }
                break;

            case 'delete':
                // Não permitir excluir o próprio usuário
                if ($_POST['id'] == $_SESSION['usuario_id']) {
                    throw new Exception("Você não pode excluir seu próprio usuário.");
                }
                
                $stmt = $db->prepare("DELETE FROM usuarios WHERE id = :id");
                $stmt->execute(['id' => $_POST['id']]);
                $sucesso = 'Usuário excluído com sucesso!';
                break;
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar dados conforme a ação
switch ($action) {
    case 'new':
        $titulo = 'Novo Usuário';
        $usuario = [];
        break;
        
    case 'edit':
        $titulo = 'Editar Usuário';
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: usuarios.php');
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            header('Location: usuarios.php?erro=usuario_nao_encontrado');
            exit;
        }
        break;
        
    default:
        $titulo = 'Gerenciar Usuários';
        $stmt = $db->query("SELECT * FROM usuarios ORDER BY nome");
        $usuarios = $stmt->fetchAll();
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
                        <i class="fas fa-plus me-2"></i>Novo Usuário
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
                    <!-- Lista de Usuários -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Nível</th>
                                    <th>Status</th>
                                    <th>Último Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['nome']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $user['nivel'] === 'admin' ? 'danger' : 'info' ?>">
                                            <?= ucfirst($user['nivel']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $user['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($user['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $user['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($user['ultimo_acesso'])) : '-' ?>
                                    </td>
                                    <td>
                                        <a href="?action=edit&id=<?= $user['id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmarExclusao(<?= $user['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Formulário de Usuário -->
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
                        <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= $usuario['nome'] ?? '' ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= $usuario['email'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="senha" class="form-label">
                                    <?= $action === 'new' ? 'Senha' : 'Nova Senha (deixe em branco para manter a atual)' ?>
                                </label>
                                <input type="password" class="form-control" id="senha" name="senha" 
                                       <?= $action === 'new' ? 'required' : '' ?>>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="nivel" class="form-label">Nível</label>
                                <select class="form-select" id="nivel" name="nivel" required>
                                    <option value="editor" <?= ($usuario['nivel'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                                    <option value="admin" <?= ($usuario['nivel'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="ativo" <?= ($usuario['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= ($usuario['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
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
            if (confirm('Tem certeza que deseja excluir este usuário?')) {
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
