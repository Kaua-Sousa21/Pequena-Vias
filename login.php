<?php
require_once __DIR__ . '/config/init.php';
session_start();

// Se já estiver logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: admin/index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, nome, email, senha, nivel FROM usuarios 
                  WHERE email = :email AND status = 'ativo'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($usuario = $stmt->fetch()) {
            if (password_verify($senha, $usuario['senha'])) {
                // Login bem-sucedido
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_nivel'] = $usuario['nivel'];
                
                // Atualiza último acesso
                $stmt = $db->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id");
                $stmt->bindParam(':id', $usuario['id']);
                $stmt->execute();
                
                header('Location: admin/index.php');
                exit;
            }
        }
        
        $erro = 'Email ou senha inválidos';
        
    } catch (Exception $e) {
        $erro = 'Erro ao tentar fazer login';
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1976d2 0%, #2196f3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header img {
            width: 80px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-cross fa-3x text-primary mb-3"></i>
            <h1 class="h4">Painel Administrativo</h1>
            <p class="text-muted">Pequenas Vias</p>
        </div>

        <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="index.php" class="text-muted">
                <small>Voltar para o site</small>
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
