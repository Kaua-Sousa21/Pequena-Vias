<?php
class Categoria {
    private $conn;
    private $table_name = "categorias";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listarTodas() {
        try {
            $query = "SELECT c.*, COUNT(sc.santo_id) AS total_santos
                      FROM " . $this->table_name . " c
                      LEFT JOIN santo_categoria sc ON c.id = sc.categoria_id
                      LEFT JOIN santos s ON sc.santo_id = s.id AND s.status = 'ativo'
                      WHERE c.status = 'ativo'
                      GROUP BY c.id
                      ORDER BY c.nome ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar categorias: " . $e->getMessage());
            return [];
        }
    }
    public function criar($dados) {
    try {
        $sql = "INSERT INTO categorias (nome, slug, descricao, status) VALUES (:nome, :slug, :descricao, :status)";
        $stmt = $this->conn->prepare($sql);

        // Sanitize data
        $dados = $this->sanitizeData($dados);

        if ($stmt->execute($dados)) {
            return true;
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Erro ao criar categoria: " . $errorInfo[2]);
        }
    } catch (Exception $e) {
        error_log("Erro ao criar categoria: " . $e->getMessage());
        throw $e; // Re-throw the exception to be handled by the calling script
    }
}


private function sanitizeData($dados) {
    return [
        'nome' => sanitizeInput($dados['nome']),
        'slug' => criarSlug(sanitizeInput($dados['nome'])), // Certifique-se de sanitizar o nome antes de criar o slug
        'descricao' => sanitizeInput($dados['descricao']),
        'status' => sanitizeInput($dados['status'])
    ];
}

    public function buscarPorSlug($slug) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE slug = :slug AND status = 'ativo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Erro ao buscar categoria por slug: " . $e->getMessage());
            return false;
        }
    }
}
        
?>