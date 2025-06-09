<?php
require_once '../config/init.php';
require_once 'check_auth.php';

$database = new Database();
$db = $database->getConnection();

$erro = '';
$sucesso = '';

// Buscar dados do usuário
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dados = [
            'nome' => $_POST['nome'],
            'email' => $_POST['email'],
            'id' => $_SESSION['usuario_id']
        ];

        // Verificar se o email já existe para outro usuário
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :email AND id != :id");
        $stmt->execute(['email' => $dados['email'], 'id' => $dados['id']]);
        if ($stmt->fetch()) {
            throw new Exception("Este email já está em uso por outro usuário.");
        }

        // Se a senha foi fornecida, atualizar
        if (!empty($_POST['senha_nova'])) {
            // Verificar senha atual
            if (!password_verify($_POST['senha_atual'], $usuario['senha'])) {
                throw new Exception("Senha atual incorreta.");
            }

            // Validar nova senha
            if (strlen($_POST['senha_nova']) < 6) {
                throw new Exception("A nova senha deve ter pelo menos 6 caracteres.");
            }

            $dados['senha'] = password_hash($_POST['senha_nova'], PASSWORD_DEFAULT);
        }

        // Atualizar dados
        $sql = "UPDATE usuarios SET nome = :nome, email = :email";
        if (isset($dados['senha'])) {
            $sql .= ", senha = :senha";
        }
        $sql .= " WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute($dados);

        // Atualizar nome na sessão
        $_SESSION['usuario_nome'] = $dados['nome'];

        $sucesso = 'Perfil atualizado com sucesso!';
        
        // Recarregar dados do usuário
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Painel Administrativo</title>
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
        .profile-header {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
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
                <!-- Header do Perfil -->
                <div class="profile-header rounded-3 mb-4">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-white rounded-circle p-3 mb-3">
                                    <i class="fas fa-user-circle fa-3x text-primary"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h1 class="h3 mb-2"><?= htmlspecialchars($usuario['nome']) ?></h1>
                                <p class="mb-0">
                                    <span class="badge bg-light text-dark me-2">
                                        <?= ucfirst($usuario['nivel']) ?>
                                    </span>
                                    <small>
                                        Último acesso: 
                                        <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca' ?>
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <!-- Formulário de Perfil -->
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Editar Perfil</h2>
                        
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($usuario['email']) ?>" required>
                            </div>

                            <hr class="my-4">

                            <h3 class="h5 mb-3">Alterar Senha</h3>

                            <div class="mb-3">
                                <label for="senha_atual" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="senha_atual" name="senha_atual">
                                <div class="form-text">Preencha apenas se desejar alterar sua senha</div>
                            </div>

                            <div class="mb-3">
                                <label for="senha_nova" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="senha_nova" name="senha_nova"
                                       minlength="6">
                            </div>

                            <div class="mb-3">
                                <label for="senha_confirma" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="senha_confirma" name="senha_confirma">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Salvar Alterações
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function () {
            'use strict'
            
            const form = document.querySelector('.needs-validation')
            const senhaNovaInput = document.getElementById('senha_nova')
            const senhaConfirmaInput = document.getElementById('senha_confirma')
            const senhaAtualInput = document.getElementById('senha_atual')

            form.addEventListener('submit', event => {
                let isValid = true

                // Validar confirmação de senha
                if (senhaNovaInput.value || senhaConfirmaInput.value) {
                    if (senhaNovaInput.value !== senhaConfirmaInput.value) {
                        alert('As senhas não conferem!')
                        isValid = false
                    }

                    if (!senhaAtualInput.value) {
                        alert('Para alterar a senha, informe a senha atual!')
                        isValid = false
                    }
                }

                if (!form.checkValidity() || !isValid) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })()
    </script>
</body>
</html>
