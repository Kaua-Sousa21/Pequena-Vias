<?php
require_once __DIR__ . '/../config/init.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

$titulo = 'Dashboard';
$conteudo = 'views/dashboard.php';

// Buscar estatísticas
try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Instanciar objetos
    $santoObj = new Santo($db);
    $categoriaObj = new Categoria($db);

    // Buscar estatísticas
    $total_santos = $santoObj->contarSantos();
    $santos_recentes = $santoObj->listarSantos(5, 0);
    $categorias = $categoriaObj->listarTodas();
    $total_categorias = count($categorias);
    $santos_do_dia = $santoObj->santosDodia();
    
} catch (Exception $e) {
    error_log($e->getMessage());
    $mensagem = [
        'tipo' => 'danger',
        'texto' => 'Erro ao carregar dados do dashboard'
    ];
}

include 'layout.php';
