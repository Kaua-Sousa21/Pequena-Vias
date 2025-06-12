<?php
function criarCategoria($dados) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $categoriaObj = new Categoria($db);

        // Criar slug baseado no nome
        $dados['slug'] = criarSlug($dados['nome']);

        if ($categoriaObj->criar($dados)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Categoria criada com sucesso!'
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao criar categoria.'
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao criar categoria: ' . $e->getMessage()
        ];
    }
}

function editarCategoria($id, $dados) {
    try {
        // Validações
        if (empty($id)) {
            throw new Exception("ID da categoria é obrigatório");
        }
        if (empty($dados['nome'])) {
            throw new Exception("O nome da categoria é obrigatório");
        }

        $database = new Database();
        $db = $database->getConnection();
        $categoriaObj = new Categoria($db);

        // Criar slug baseado no nome
        $dados['slug'] = criarSlug($dados['nome']);

        if ($categoriaObj->atualizar($id, $dados)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Categoria atualizada com sucesso!'
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar categoria.'
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar categoria: ' . $e->getMessage()
        ];
    }
}

function excluirCategoria($id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $categoriaObj = new Categoria($db);

        if ($categoriaObj->excluir($id)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Categoria excluída com sucesso!'
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao excluir categoria.'
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao excluir categoria: ' . $e->getMessage()
        ];
    }
}
