<?php
require_once '../../config/init.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID não fornecido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $santoObj = new Santo($db);
    
    $santo = $santoObj->buscarPorId($_GET['id']);
    
    if (!$santo) {
        http_response_code(404);
        echo json_encode(['erro' => 'Santo não encontrado']);
        exit;
    }
    
    // Converter datas para o formato HTML5
    if ($santo['data_nascimento']) {
        $santo['data_nascimento'] = date('Y-m-d', strtotime($santo['data_nascimento']));
    }
    if ($santo['data_morte']) {
        $santo['data_morte'] = date('Y-m-d', strtotime($santo['data_morte']));
    }
    if ($santo['data_festa']) {
        $santo['data_festa'] = date('Y-m-d', strtotime($santo['data_festa']));
    }
    if ($santo['data_canonizacao']) {
        $santo['data_canonizacao'] = date('Y-m-d', strtotime($santo['data_canonizacao']));
    }
    
    echo json_encode($santo);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar santo']);
}
