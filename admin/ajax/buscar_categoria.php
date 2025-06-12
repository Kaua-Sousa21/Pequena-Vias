<?php
require_once '../../config/init.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID não fornecido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $categoriaObj = new Categoria($db);
    
    $categoria = $categoriaObj->buscarPorId($_GET['id']);
    
    if (!$categoria) {
        http_response_code(404);
        echo json_encode(['erro' => 'Categoria não encontrada']);
        exit;
    }
    
    echo json_encode($categoria);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar categoria']);
}
