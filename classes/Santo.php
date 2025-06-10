<?php
// classes/Santo.php
class Santo {
    private $conn;
    private $table_name = "santos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function contarSantosPorCategoria($categoria_slug) {
        try {
            $query = "SELECT COUNT(DISTINCT s.id) AS total 
                      FROM " . $this->table_name . " s
                      JOIN santo_categoria sc ON s.id = sc.santo_id
                      JOIN categorias c ON sc.categoria_id = c.id
                      WHERE c.slug = :categoria_slug AND s.status = 'ativo'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_slug', $categoria_slug, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch();
            return (int)($row['total'] ?? 0); // Tratamento para caso não haja resultados
        } catch (PDOException $e) {
            error_log("Erro ao contar santos por categoria: " . $e->getMessage());
            return 0;
        }
    }

    public function criar($dados) {
        try {
            $sql = "INSERT INTO santos (nome, nome_completo, slug, resumo, biografia, data_nascimento, local_nascimento, data_morte, local_morte, data_canonizacao, papa_canonizacao, data_festa, padroeiro_de, simbolos, imagem, milagres, oracao, status) 
                    VALUES (:nome, :nome_completo, :slug, :resumo, :biografia, :data_nascimento, :local_nascimento, :data_morte, :local_morte, :data_canonizacao, :papa_canonizacao, :data_festa, :padroeiro_de, :simbolos, :imagem, :milagres, :oracao, :status)";
            $stmt = $this->conn->prepare($sql);

            // Sanitize data and handle dates
            $dados = $this->sanitizeData($dados);

            if ($stmt->execute($dados)) {
                $santoId = $this->conn->lastInsertId();
                $this->atualizarCategoriasSanto($santoId, $dados['categorias'] ?? []); // Handle missing categories
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                throw new Exception("Erro ao criar santo: " . $errorInfo[2]);
            }
        } catch (Exception $e) {
            error_log("Erro ao criar santo: " . $e->getMessage());
            throw $e; // Re-throw the exception for handling
        }
    }
        private function sanitizeData($dados) {
        $sanitizedData = [];
        foreach ($dados as $key => $value) {
            if (is_array($value)) { // Se for um array (como categorias), sanitize cada elemento
                $sanitizedData[$key] = array_map('sanitizeInput', $value);
            } else {
                $sanitizedData[$key] = sanitizeInput($value);
            }
        }

        // Tratar datas. Converter para o formato Y-m-d ou definir como NULL se estiverem vazias
        foreach (['data_nascimento', 'data_morte', 'data_canonizacao', 'data_festa'] as $dataField) {
            if (isset($sanitizedData[$dataField]) && !empty($sanitizedData[$dataField])) {
                $sanitizedData[$dataField] = date('Y-m-d', strtotime($sanitizedData[$dataField]));
            } else {
                $sanitizedData[$dataField] = null;
            }
        }
        return $sanitizedData;
    }





    // Buscar santo por slug
    public function buscarPorSlug($slug) {
        try {
            $query = "SELECT s.*, GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias 
                      FROM " . $this->table_name . " s
                      LEFT JOIN santo_categoria sc ON s.id = sc.santo_id
                      LEFT JOIN categorias c ON sc.categoria_id = c.id
                      WHERE s.slug = :slug AND s.status = 'ativo'
                      GROUP BY s.id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar santo por slug: " . $e->getMessage());
            return false;
        }
    }

    // Buscar santos com paginação
    public function listarSantos($limite = 20, $offset = 0, $busca = '') {
        try {
            $where = "WHERE s.status = 'ativo'";
            if (!empty($busca)) {
                $where .= " AND (s.nome LIKE :busca OR s.biografia LIKE :busca OR s.resumo LIKE :busca)";
            }

            $query = "SELECT s.id, s.nome, s.slug, s.resumo, s.data_festa, s.imagem, s.status,
                             GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias
                      FROM " . $this->table_name . " s
                      LEFT JOIN santo_categoria sc ON s.id = sc.santo_id
                      LEFT JOIN categorias c ON sc.categoria_id = c.id
                      $where
                      GROUP BY s.id
                      ORDER BY s.nome ASC
                      LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($busca)) {
                $busca_param = "%$busca%";
                $stmt->bindParam(':busca', $busca_param, PDO::PARAM_STR);
            }
            
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar santos: " . $e->getMessage());
            return [];
        }
    }

    // Santos do dia
    public function santosDodia($data = null) {
        try {
            if (!$data) {
                $data = date('m-d');
            } else {
                $timestamp = strtotime($data);
                if ($timestamp === false) {
                    $data = date('m-d');
                } else {
                    $data = date('m-d', $timestamp);
                }
            }

            $query = "SELECT s.*, GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias
                      FROM " . $this->table_name . " s
                      LEFT JOIN santo_categoria sc ON s.id = sc.santo_id
                      LEFT JOIN categorias c ON sc.categoria_id = c.id
                      WHERE DATE_FORMAT(s.data_festa, '%m-%d') = :data 
                      AND s.status = 'ativo'
                      GROUP BY s.id
                      ORDER BY s.nome";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':data', $data, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar santos do dia: " . $e->getMessage());
            return [];
        }
    }

    // Contar total de santos
    public function contarSantos($busca = '') {
        try {
            $where = "WHERE status = 'ativo'";
            if (!empty($busca)) {
                $where .= " AND (nome LIKE :busca OR biografia LIKE :busca OR resumo LIKE :busca)";
            }

            $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " $where";
            $stmt = $this->conn->prepare($query);
            
            if (!empty($busca)) {
                $busca_param = "%$busca%";
                $stmt->bindParam(':busca', $busca_param, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $row = $stmt->fetch();
            
            return (int)$row['total'];
        } catch(PDOException $e) {
            error_log("Erro ao contar santos: " . $e->getMessage());
            return 0;
        }
    }

    // Buscar por categoria
    public function buscarPorCategoria($categoria_slug, $limite = 20, $offset = 0) {
        try {
            $query = "SELECT s.*, c.nome as categoria_nome,
                             GROUP_CONCAT(DISTINCT c2.nome ORDER BY c2.nome SEPARATOR ', ') as todas_categorias
                      FROM " . $this->table_name . " s
                      JOIN santo_categoria sc ON s.id = sc.santo_id
                      JOIN categorias c ON sc.categoria_id = c.id
                      LEFT JOIN santo_categoria sc2 ON s.id = sc2.santo_id
                      LEFT JOIN categorias c2 ON sc2.categoria_id = c2.id
                      WHERE c.slug = :categoria_slug AND s.status = 'ativo'
                      GROUP BY s.id
                      ORDER BY s.nome ASC
                      LIMIT :limite OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria_slug', $categoria_slug, PDO::PARAM_STR);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar santos por categoria: " . $e->getMessage());
            return [];
        }
    }

    // Buscar santos aleatórios para destaque
    public function santosDestaque($limite = 3) {
        try {
            $query = "SELECT s.id, s.nome, s.slug, s.resumo, s.data_festa, s.imagem,
                             GROUP_CONCAT(DISTINCT c.nome ORDER BY c.nome SEPARATOR ', ') as categorias
                      FROM " . $this->table_name . " s
                      LEFT JOIN santo_categoria sc ON s.id = sc.santo_id
                      LEFT JOIN categorias c ON sc.categoria_id = c.id
                      WHERE s.status = 'ativo' AND s.imagem IS NOT NULL AND s.imagem != ''
                      GROUP BY s.id
                      ORDER BY RAND()
                      LIMIT :limite";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao buscar santos em destaque: " . $e->getMessage());
            return [];
        }
    }
}


?>