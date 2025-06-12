<?php
class Santo {
    private $conn;
    private $table_name = "santos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function contarSantosPorCategoria($categoria_slug) {
        try {
            $query = "SELECT COUNT(DISTINCT s.id) as total 
                      FROM " . $this->table_name . " s
                      JOIN santo_categoria sc ON s.id = sc.santo_id
                      JOIN categorias c ON sc.categoria_id = c.id
                      WHERE c.slug = :categoria_slug AND s.status = 'ativo'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_slug', $categoria_slug, PDO::PARAM_STR);
            $stmt->execute();
            
            $row = $stmt->fetch();
            return (int)$row['total'];
        } catch(PDOException $e) {
            error_log("Erro ao contar santos por categoria: " . $e->getMessage());
            return 0;
        }
    }
    public function listarSantos($limite = 20, $offset = 0, $busca = '') {
    try {
        $where = "WHERE 1=1"; // Começa com uma condição sempre verdadeira
        $params = [];

        if (!empty($busca)) {
            $where .= " AND (s.nome LIKE :busca OR s.biografia LIKE :busca OR s.resumo LIKE :busca)";
            $params[':busca'] = "%$busca%";
        }

        $query = "SELECT s.*, GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias
                  FROM " . $this->table_name . " s
                  LEFT JOIN santo_categoria sc ON s.id = sc.santo_id
                  LEFT JOIN categorias c ON sc.categoria_id = c.id
                  $where
                  GROUP BY s.id
                  ORDER BY s.nome ASC
                  LIMIT :limite OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind dos parâmetros de busca se existirem
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        // Bind dos parâmetros de paginação
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        error_log("Erro ao listar santos: " . $e->getMessage());
        return [];
    }
}

public function contarSantos($busca = '') {
    try {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($busca)) {
            $where .= " AND (nome LIKE :busca OR biografia LIKE :busca OR resumo LIKE :busca)";
            $params[':busca'] = "%$busca%";
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " $where";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $row = $stmt->fetch();
        
        return (int)$row['total'];
    } catch(PDOException $e) {
        error_log("Erro ao contar santos: " . $e->getMessage());
        return 0;
    }
}
        public function buscarPorSlug($slug) {
        try {
            $query = "SELECT s.*, GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias 
                      FROM " . $this->table_name . " s
                      LEFT JOIN santo_categoria sc ON s.id = sc.santo_id
                      LEFT JOIN categorias c ON sc.categoria_id = c.id
                      WHERE s.slug = :slug AND s.status = 'ativo'
                      GROUP BY s.id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();

            $santo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($santo) {
                // Formatar datas
                $santo['data_nascimento'] = $this->formatarData($santo['data_nascimento']);
                $santo['data_morte'] = $this->formatarData($santo['data_morte']);
                $santo['data_canonizacao'] = $this->formatarData($santo['data_canonizacao']);
                $santo['data_festa'] = $this->formatarDataLiturgica($santo['data_festa']);
            }
            
            return $santo;
        } catch (PDOException $e) {
            error_log("Erro ao buscar santo por slug: " . $e->getMessage());
            return false;
        }
    }


    private function formatarData($data) {
        return $data ? date('d/m/Y', strtotime($data)) : null;
    }

    private function formatarDataLiturgica($data) {
        if (!$data) return null;

        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
        ];

        $timestamp = strtotime($data);
        if (!$timestamp) return null;

        $dia = date('j', $timestamp);
        $mes_num = (int)date('n', $timestamp);
        $mes = $meses[$mes_num] ?? '';

        return "$dia de $mes";
    }

public function buscarPorId($id) {
    try {
        $query = "SELECT s.*, GROUP_CONCAT(c.id) as categoria_ids 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN santo_categoria sc ON s.id = sc.santo_id 
                  LEFT JOIN categorias c ON sc.categoria_id = c.id 
                  WHERE s.id = :id 
                  GROUP BY s.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    } catch(PDOException $e) {
        error_log("Erro ao buscar santo por ID: " . $e->getMessage());
        return false;
    }
}
        public function buscarPorCategoria($categoria_slug, $limite = 20, $offset = 0) {
        try {
            $query = "SELECT s.*, GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias
                      FROM santos s
                      JOIN santo_categoria sc ON s.id = sc.santo_id
                      JOIN categorias c ON sc.categoria_id = c.id
                      WHERE c.slug = :categoria_slug AND s.status = 'ativo'
                      GROUP BY s.id
                      ORDER BY s.nome ASC
                      LIMIT :limite OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':categoria_slug', $categoria_slug, PDO::PARAM_STR);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar santos por categoria: " . $e->getMessage());
            return []; // Retorna um array vazio em caso de erro.
        }
    }

    public function criar($dados) {
        try {
            // Iniciar transação
            $this->conn->beginTransaction();

            // Preparar a query
            $sql = "INSERT INTO " . $this->table_name . " 
                    (nome, nome_completo, slug, resumo, biografia, 
                    data_nascimento, local_nascimento, data_morte, local_morte, 
                    data_canonizacao, papa_canonizacao, data_festa, 
                    padroeiro_de, simbolos, imagem, milagres, oracao, status) 
                    VALUES 
                    (:nome, :nome_completo, :slug, :resumo, :biografia,
                    :data_nascimento, :local_nascimento, :data_morte, :local_morte,
                    :data_canonizacao, :papa_canonizacao, :data_festa,
                    :padroeiro_de, :simbolos, :imagem, :milagres, :oracao, :status)";

            $stmt = $this->conn->prepare($sql);

            // Sanitizar e preparar dados
            $dadosSanitizados = $this->sanitizeData($dados);
            $dadosSanitizados['status'] = $dados['status'] ?? 'ativo';

            // Executar a query
            if ($stmt->execute($dadosSanitizados)) {
                $santo_id = $this->conn->lastInsertId();

                // Se houver categorias, inserir relações
                if (!empty($dados['categorias'])) {
                    $this->atualizarCategoriasSanto($santo_id, $dados['categorias']);
                }

                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro ao criar santo: " . $e->getMessage());
            throw new Exception("Erro ao criar santo: " . $e->getMessage());
        }
    }

    public function atualizar($id, $dados) {
        try {
            $this->conn->beginTransaction();

            $campos = [];
            $valores = [];

            $campos_permitidos = [
                'nome', 'nome_completo', 'slug', 'resumo', 'biografia',
                'data_nascimento', 'local_nascimento', 'data_morte', 'local_morte',
                'data_canonizacao', 'papa_canonizacao', 'data_festa',
                'padroeiro_de', 'simbolos', 'imagem', 'milagres', 'oracao', 'status'
            ];

            foreach ($dados as $campo => $valor) {
                if (in_array($campo, $campos_permitidos)) {
                    $campos[] = "$campo = :$campo";
                    $valores[$campo] = $valor;
                }
            }

            if (empty($campos)) {
                throw new Exception("Nenhum campo válido para atualização");
            }

            $valores['id'] = $id;

            $query = "UPDATE " . $this->table_name . " 
                     SET " . implode(', ', $campos) . " 
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            
            if ($stmt->execute($valores)) {
                // Atualizar categorias se fornecidas
                if (isset($dados['categorias'])) {
                    $this->atualizarCategoriasSanto($id, $dados['categorias']);
                }

                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;

        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Erro ao atualizar santo: " . $e->getMessage());
            throw new Exception("Erro ao atualizar santo");
        }
    }

    public function excluir($id) {
        try {
            // Primeiro, buscar o santo para obter a imagem
            $query = "SELECT imagem FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $santo = $stmt->fetch();

            // Iniciar transação
            $this->conn->beginTransaction();

            // Excluir registros da tabela santo_categoria
            $query = "DELETE FROM santo_categoria WHERE santo_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            // Excluir o santo
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                // Se houver imagem, excluir o arquivo
                if ($santo && $santo['imagem']) {
                    $caminho_imagem = ROOT_PATH . '/public/' . $santo['imagem'];
                    if (file_exists($caminho_imagem)) {
                        unlink($caminho_imagem);
                    }
                }

                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Erro ao excluir santo: " . $e->getMessage());
            throw new Exception("Erro ao excluir santo");
        }
    }

    private function atualizarCategoriasSanto($santo_id, $categorias) {
        // Remover categorias antigas
        $stmt = $this->conn->prepare("DELETE FROM santo_categoria WHERE santo_id = :santo_id");
        $stmt->bindParam(':santo_id', $santo_id);
        $stmt->execute();

        // Inserir novas categorias
        if (!empty($categorias)) {
            $stmt = $this->conn->prepare("INSERT INTO santo_categoria (santo_id, categoria_id) VALUES (:santo_id, :categoria_id)");
            foreach ($categorias as $categoria_id) {
                $stmt->bindParam(':santo_id', $santo_id);
                $stmt->bindParam(':categoria_id', $categoria_id);
                $stmt->execute();
            }
        }
    }

    private function sanitizeData($dados) {
        return [
            'nome' => trim(strip_tags($dados['nome'])),
            'nome_completo' => isset($dados['nome_completo']) ? trim(strip_tags($dados['nome_completo'])) : null,
            'slug' => criarSlug($dados['nome']),
            'resumo' => isset($dados['resumo']) ? trim(strip_tags($dados['resumo'])) : null,
            'biografia' => isset($dados['biografia']) ? trim($dados['biografia']) : null,
            'data_nascimento' => !empty($dados['data_nascimento']) ? $dados['data_nascimento'] : null,
            'local_nascimento' => isset($dados['local_nascimento']) ? trim(strip_tags($dados['local_nascimento'])) : null,
            'data_morte' => !empty($dados['data_morte']) ? $dados['data_morte'] : null,
            'local_morte' => isset($dados['local_morte']) ? trim(strip_tags($dados['local_morte'])) : null,
            'data_canonizacao' => !empty($dados['data_canonizacao']) ? $dados['data_canonizacao'] : null,
            'papa_canonizacao' => isset($dados['papa_canonizacao']) ? trim(strip_tags($dados['papa_canonizacao'])) : null,
            'data_festa' => !empty($dados['data_festa']) ? $dados['data_festa'] : null,
            'padroeiro_de' => isset($dados['padroeiro_de']) ? trim(strip_tags($dados['padroeiro_de'])) : null,
            'simbolos' => isset($dados['simbolos']) ? trim(strip_tags($dados['simbolos'])) : null,
            'imagem' => isset($dados['imagem']) ? $dados['imagem'] : null,
            'milagres' => isset($dados['milagres']) ? trim($dados['milagres']) : null,
            'oracao' => isset($dados['oracao']) ? trim($dados['oracao']) : null
        ];
    }
}
