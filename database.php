<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'santos_catolicos';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $exception) {
            error_log("Erro de conexão com banco: " . $exception->getMessage());
            throw new Exception("Erro interno do servidor. Tente novamente mais tarde.");
        }
        
        return $this->conn;
    }
}

// classes/Santo.php
class Santo {
    private $conn;
    private $table_name = "santos";
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

    public function __construct($db) {
        $this->conn = $db;
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

// classes/Categoria.php
class Categoria {
    private $conn;
    private $table_name = "categorias";

    public function __construct($db) {
        $this->conn = $db;
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

// utils/functions.php
function formatarData($data) {
    if (!$data) return '';
    
    $timestamp = strtotime($data);
    if ($timestamp === false) return '';
    
    return date('d/m/Y', $timestamp);
}

function formatarDataLiturgica($data) {
    if (!$data) return '';
    
    $meses = [
        1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
        5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
        9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro'
    ];
    
    $timestamp = strtotime($data);
    if ($timestamp === false) return '';
    
    $dia = date('j', $timestamp);
    $mes_num = (int)date('n', $timestamp);
    $mes = isset($meses[$mes_num]) ? $meses[$mes_num] : '';
    
    return "$dia de $mes";
}

function criarSlug($texto) {
    if (empty($texto)) return '';
    
    // Remove acentos
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    
    // Converte para minúsculas
    $texto = strtolower($texto);
    
    // Remove caracteres especiais
    $texto = preg_replace('/[^a-z0-9\s-]/', '', $texto);
    
    // Substitui espaços e hífens múltiplos por um hífen
    $texto = preg_replace('/[\s-]+/', '-', $texto);
    
    // Remove hífens do início e fim
    $texto = trim($texto, '-');
    
    return $texto;
}

function resumirTexto($texto, $limite = 150) {
    if (empty($texto)) return '';
    
    // Remove tags HTML se houver
    $texto = strip_tags($texto);
    
    if (strlen($texto) <= $limite) return $texto;
    
    $texto = substr($texto, 0, $limite);
    $ultima_posicao = strrpos($texto, ' ');
    
    if ($ultima_posicao !== false) {
        $texto = substr($texto, 0, $ultima_posicao);
    }
    
    return $texto . '...';
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validarData($data) {
    if (empty($data)) return false;
    
    $timestamp = strtotime($data);
    return $timestamp !== false;
}

function gerarPaginacao($pagina_atual, $total_paginas, $url_base = '') {
    if ($total_paginas <= 1) return '';
    
    $html = '<div class="paginacao">';
    
    // Botão Anterior
    if ($pagina_atual > 1) {
        $pagina_anterior = $pagina_atual - 1;
        $html .= '<a href="' . $url_base . '?pagina=' . $pagina_anterior . '" class="btn-paginacao">« Anterior</a>';
    }
    
    // Números das páginas
    $inicio = max(1, $pagina_atual - 2);
    $fim = min($total_paginas, $pagina_atual + 2);
    
    for ($i = $inicio; $i <= $fim; $i++) {
        if ($i == $pagina_atual) {
            $html .= '<span class="pagina-atual">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $url_base . '?pagina=' . $i . '" class="numero-pagina">' . $i . '</a>';
        }
    }
    
    // Botão Próximo
    if ($pagina_atual < $total_paginas) {
        $proxima_pagina = $pagina_atual + 1;
        $html .= '<a href="' . $url_base . '?pagina=' . $proxima_pagina . '" class="btn-paginacao">Próximo »</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}
?>