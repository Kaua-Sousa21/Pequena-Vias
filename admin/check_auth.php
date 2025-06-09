<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

// Verifica se o usuário tem permissão de admin para certas operações
function requireAdmin() {
    if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin') {
        header('Location: index.php?erro=sem_permissao');
        exit;
    }
}
