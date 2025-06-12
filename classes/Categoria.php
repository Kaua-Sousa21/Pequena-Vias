<?php
class Categoria {
    private $conn;
    private $table_name = "categorias";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function criar($dados) {
        try {
            // Validar dados
            if (empty($dados['nome'])) {
                throw new Exception("O nome da categoria é obrigatório");
            }

            $query = "INSERT INTO " . $this->table_name . " 
                    (nome, slug, descricao, status) 
                    VALUES 
                    (:nome, :slug, :descricao, :status)";

            $stmt = $this->conn->prepare($query);

            // Sanitizar e preparar dados
            $dados = $this->sanitizarDados($dados);

            if ($stmt->execute($dados)) {
                return true;
            }
            return false;

        } catch(PDOException $e) {
            error_log("Erro ao criar categoria: " . $e->getMessage());
            throw new Exception("Erro ao criar categoria");
        }
    }

    public function atualizar($id, $dados) {
        try {
            // Validações
            if (empty($id)) {
                throw new Exception("ID da categoria é obrigatório");
            }
            if (empty($dados['nome'])) {
                throw new Exception("O nome da categoria é obrigatório");
            }

            // Preparar campos para atualização
            $campos = [];
            $valores = [];

            // Campos permitidos
            $campos_permitidos = ['nome', 'descricao', 'status'];

            foreach ($dados as $campo => $valor) {
                if (in_array($campo, $campos_permitidos)) {
                    $campos[] = "$campo = :$campo";
                    $valores[$campo] = $valor;
                }
            }

            // Adicionar slug
            $campos[] = "slug = :slug";
            $valores['slug'] = criarSlug($dados['nome']);

            // Adicionar ID
            $valores['id'] = $id;

            $query = "UPDATE " . $this->table_name . " 
                     SET " . implode(', ', $campos) . " 
                     WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            return $stmt->execute($valores);

        } catch(PDOException $e) {
            error_log("Erro ao atualizar categoria: " . $e->getMessage());
            throw new Exception("Erro ao atualizar categoria");
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

    public function excluir($id) {
        try {
            // Verificar se existem santos vinculados
            $query = "SELECT COUNT(*) as total FROM santo_categoria WHERE categoria_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch();

            if ($result['total'] > 0) {
                throw new Exception("Não é possível excluir a categoria pois existem santos vinculados a ela");
            }

            // Excluir categoria
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();

        } catch(PDOException $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            throw new Exception("Erro ao excluir categoria");
        }
    }

    public function listarTodas() {
        try {
            $query = "SELECT c.*, COUNT(sc.santo_id) as total_santos 
                      FROM " . $this->table_name . " c
                      LEFT JOIN santo_categoria sc ON c.id = sc.categoria_id
                      LEFT JOIN santos s ON sc.santo_id = s.id AND s.status = 'ativo'
                      WHERE c.status = 'ativo'
                      GROUP BY c.id
                      ORDER BY c.nome ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Erro ao listar categorias: " . $e->getMessage());
            return [];
        }
    }

    public function buscarPorSlug($slug) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                     WHERE slug = :slug AND status = 'ativo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar categoria por slug: " . $e->getMessage());
            return false;
        }
    }

    public function buscarPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar categoria por ID: " . $e->getMessage());
            return false;
        }
    }

    private function sanitizarDados($dados) {
        return [
            'nome' => trim(strip_tags($dados['nome'])),
            'slug' => $dados['slug'],
            'descricao' => isset($dados['descricao']) ? trim(strip_tags($dados['descricao'])) : null,
            'status' => in_array($dados['status'], ['ativo', 'inativo']) ? $dados['status'] : 'ativo'
        ];
    }
}
