<?php
require_once '../config/init.php';
require_once 'functions/categoria_functions.php';

$titulo = 'Gerenciar Categorias';
$mensagem = null;

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'criar':
                $resultado = criarCategoria($_POST);
                break;
            case 'editar':
                $resultado = editarCategoria($_POST['id'], $_POST);
                break;
            case 'excluir':
                $resultado = excluirCategoria($_POST['id']);
                break;
        }
        
        $mensagem = [
            'tipo' => $resultado['sucesso'] ? 'success' : 'danger',
            'texto' => $resultado['mensagem']
        ];
    }
}

// Buscar categorias para listagem
$database = new Database();
$db = $database->getConnection();
$categoriaObj = new Categoria($db);
$categorias = $categoriaObj->listarTodas();

$conteudo = 'views/categorias_lista.php';
include 'layout.php';
