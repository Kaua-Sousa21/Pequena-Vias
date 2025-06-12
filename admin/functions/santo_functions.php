<?php
function criarSanto($dados, $imagem = null) {
    try {
        // Validações
        if (empty($dados['nome'])) {
            throw new Exception("O nome do santo é obrigatório");
        }

        $database = new Database();
        $db = $database->getConnection();
        $santoObj = new Santo($db);

        // Processar upload de imagem se houver
        if ($imagem && $imagem['error'] === UPLOAD_ERR_OK) {
            $dados['imagem'] = processarUploadImagem($imagem);
        }

        // Criar slug baseado no nome
        $dados['slug'] = criarSlug($dados['nome']);

        if ($santoObj->criar($dados)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Santo criado com sucesso!'
            ];
        }
        
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao criar santo.'
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao criar santo: ' . $e->getMessage()
        ];
    }
}

function editarSanto($id, $dados, $imagem = null) {
    try {
        // Validações
        if (empty($id)) {
            throw new Exception("ID do santo é obrigatório");
        }
        if (empty($dados['nome'])) {
            throw new Exception("O nome do santo é obrigatório");
        }

        $database = new Database();
        $db = $database->getConnection();
        $santoObj = new Santo($db);

        // Processar upload de imagem se houver
        if ($imagem && $imagem['error'] === UPLOAD_ERR_OK) {
            $dados['imagem'] = processarUploadImagem($imagem);
        }

        // Garantir que todos os campos existam, mesmo que vazios
        $campos_padrao = [
            'nome_completo' => null,
            'resumo' => null,
            'biografia' => null,
            'data_nascimento' => null,
            'local_nascimento' => null,
            'data_morte' => null,
            'local_morte' => null,
            'data_canonizacao' => null,
            'papa_canonizacao' => null,
            'data_festa' => null,
            'padroeiro_de' => null,
            'simbolos' => null,
            'milagres' => null,
            'oracao' => null,
            'status' => 'ativo'
        ];

        // Mesclar dados recebidos com os padrões
        $dados = array_merge($campos_padrao, array_filter($dados, function($value) {
            return $value !== null && $value !== '';
        }));

        if ($santoObj->atualizar($id, $dados)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Santo atualizado com sucesso!'
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar santo.'
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao atualizar santo: ' . $e->getMessage()
        ];
    }
}

function excluirSanto($id) {
    try {
        if (empty($id)) {
            throw new Exception("ID do santo é obrigatório");
        }

        $database = new Database();
        $db = $database->getConnection();
        $santoObj = new Santo($db);

        if ($santoObj->excluir($id)) {
            return [
                'sucesso' => true,
                'mensagem' => 'Santo excluído com sucesso!'
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao excluir santo.'
        ];
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [
            'sucesso' => false,
            'mensagem' => 'Erro ao excluir santo: ' . $e->getMessage()
        ];
    }
}

function buscarSanto($id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $santoObj = new Santo($db);

        return $santoObj->buscarPorId($id);
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
