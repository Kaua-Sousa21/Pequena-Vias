<?php
require_once '../config/init.php';
require_once 'functions/santo_functions.php';

$titulo = 'Gerenciar Santos';
$mensagem = null;


// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'criar':
                $resultado = criarSanto($_POST, $_FILES['imagem'] ?? null);
                break;
            case 'editar':
                $resultado = editarSanto($_POST['id'], $_POST, $_FILES['imagem'] ?? null);
                break;
            case 'excluir':
                $resultado = excluirSanto($_POST['id']);
                break;
        }
        
        $mensagem = [
            'tipo' => $resultado['sucesso'] ? 'success' : 'danger',
            'texto' => $resultado['mensagem']
        ];
    }
}

// Buscar santos para listagem
$database = new Database();
$db = $database->getConnection();
$santoObj = new Santo($db);
$santos = $santoObj->listarSantos();
$categoriaObj = new Categoria($db);
$categorias = $categoriaObj->listarTodas();

$conteudo = 'views/santos_lista.php';
include 'layout.php';
