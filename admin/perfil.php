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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0d6efd;
            --secondary-blue: #0056b3;
            --light-blue: #e3f2fd;
            --dark-blue: #003d82;
            --gradient-blue: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: var(--gradient-blue);
            box-shadow: 2px 0 15px rgba(13, 110, 253, 0.1);
        }
        
        .profile-header {
            background: var(--gradient-blue);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border: 3px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.3);
        }
        
        .profile-avatar i {
            font-size: 2.5rem;
            color: white;
        }
        
        .user-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .main-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(13, 110, 253, 0.1);
            overflow: hidden;
            background: white;
        }
        
        .card-header-custom {
            background: var(--gradient-blue);
            color: white;
            padding: 1.5rem 2rem;
            border: none;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
            transform: translateY(-1px);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-blue);
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: var(--gradient-blue);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.4);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.5rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1e7dd 0%, #a3d5ba 100%);
            color: #0f5132;
            border-left: 4px solid #198754;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1aeb5 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .section-divider {
            border: none;
            height: 2px;
            background: var(--gradient-blue);
            border-radius: 2px;
            margin: 2rem 0;
        }
        
        .password-section {
            background: var(--light-blue);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 1.5rem;
        }
        
        .form-text {
            color: var(--secondary-blue);
            font-size: 0.875rem;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-blue);
            z-index: 10;
        }
        
        .input-with-icon {
            padding-left: 3rem;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.1);
            border-left: 4px solid var(--primary-blue);
        }
        
        .page-title {
            color: var(--dark-blue);
            font-weight: 700;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                
                <!-- Título da Página -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title">
                        <i class="fas fa-user-circle me-2"></i>
                        Meu Perfil
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Perfil</li>
                        </ol>
                    </nav>
                </div>

                <!-- Header do Perfil -->
                <div class="profile-header mb-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="profile-avatar rounded-circle">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h2 class="h3 mb-2 fw-bold"><?= htmlspecialchars($usuario['nome']) ?></h2>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <span class="user-badge badge rounded-pill px-3 py-2">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    <?= ucfirst($usuario['nivel']) ?>
                                </span>
                                <small class="opacity-75">
                                    <i class="fas fa-clock me-1"></i>
                                    Último acesso: 
                                    <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alertas -->
                <?php if ($erro): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div><?= htmlspecialchars($erro) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                <div class="alert alert-success d-flex align-items-center mb-4">
                    <i class="fas fa-check-circle me-2"></i>
                    <div><?= htmlspecialchars($sucesso) ?></div>
                </div>
                <?php endif; ?>

                <!-- Formulário de Perfil -->
                <div class="main-card card">
                    <div class="card-header-custom">
                        <h3 class="h4 mb-0 fw-bold">
                            <i class="fas fa-edit me-2"></i>
                            Editar Perfil
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" class="needs-validation" novalidate>
                            
                            <!-- Informações Pessoais -->
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="nome" class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Nome Completo
                                    </label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control input-with-icon" id="nome" name="nome" 
                                               value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>
                                        Email
                                    </label>
                                    <div class="input-group">
                                        <span class="input-icon">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control input-with-icon" id="email" name="email" 
                                               value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <hr class="section-divider">

                            <!-- Seção de Alteração de Senha -->
                            <div class="password-section">
                                <h4 class="h5 mb-4 text-primary fw-bold">
                                    <i class="fas fa-lock me-2"></i>
                                    Alterar Senha
                                </h4>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="senha_atual" class="form-label">Senha Atual</label>
                                        <div class="input-group">
                                            <span class="input-icon">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" class="form-control input-with-icon" 
                                                   id="senha_atual" name="senha_atual">
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Preencha apenas se desejar alterar sua senha
                                        </div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="senha_nova" class="form-label">Nova Senha</label>
                                        <div class="input-group">
                                            <span class="input-icon">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control input-with-icon" 
                                                   id="senha_nova" name="senha_nova" minlength="6">
                                        </div>
                                        <div class="form-text">Mínimo de 6 caracteres</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="senha_confirma" class="form-label">Confirmar Nova Senha</label>
                                        <div class="input-group">
                                            <span class="input-icon">
                                                <i class="fas fa-check-circle"></i>
                                            </span>
                                            <input type="password" class="form-control input-with-icon" 
                                                   id="senha_confirma" name="senha_confirma">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botões de Ação -->
                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">
                                    <i class="fas fa-undo me-2"></i>
                                    Cancelar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Informações Adicionais -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Conta Criada</h6>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($usuario['data_criacao'] ?? 'now')) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user-shield fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Nível de Acesso</h6>
                                    <small class="text-muted"><?= ucfirst($usuario['nivel']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-shield-check fa-2x text-success"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Status</h6>
                                    <small class="text-success">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                        Ativo
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação do formulário
        (function () {
            'use strict'
            
            const form = document.querySelector('.needs-validation')
            const senhaNovaInput = document.getElementById('senha_nova')
            const senhaConfirmaInput = document.getElementById('senha_confirma')
            const senhaAtualInput = document.getElementById('senha_atual')

            // Validação em tempo real para confirmação de senha
            senhaConfirmaInput.addEventListener('input', function() {
                if (senhaNovaInput.value && senhaConfirmaInput.value) {
                    if (senhaNovaInput.value === senhaConfirmaInput.value) {
                        senhaConfirmaInput.classList.remove('is-invalid')
                        senhaConfirmaInput.classList.add('is-valid')
                    } else {
                        senhaConfirmaInput.classList.remove('is-valid')
                        senhaConfirmaInput.classList.add('is-invalid')
                    }
                }
            })

            form.addEventListener('submit', event => {
                let isValid = true

                // Validar confirmação de senha
                if (senhaNovaInput.value || senhaConfirmaInput.value) {
                    if (senhaNovaInput.value !== senhaConfirmaInput.value) {
                        showAlert('As senhas não conferem!', 'danger')
                        isValid = false
                    }

                    if (!senhaAtualInput.value) {
                        showAlert('Para alterar a senha, informe a senha atual!', 'warning')
                        isValid = false
                    }
                }

                if (!form.checkValidity() || !isValid) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)

            // Função para mostrar alertas
            function showAlert(message, type) {
                const alertContainer = document.createElement('div')
                alertContainer.className = `alert alert-${type} alert-dismissible fade show`
                alertContainer.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `
                
                const firstCard = document.querySelector('.main-card')
                firstCard.parentNode.insertBefore(alertContainer, firstCard)
                
                // Auto-dismiss após 5 segundos
                setTimeout(() => {
                    if (alertContainer.parentNode) {
                        alertContainer.remove()
                    }
                }, 5000)
            }
        })()

        // Adicionar efeitos visuais
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)'
            })
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)'
            })
        })
    </script>
</body>
</html>