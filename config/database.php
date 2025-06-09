<?php
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
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            return $this->conn;
        } catch(PDOException $e) {
            // Log do erro específico
            error_log("Erro de conexão com banco de dados: " . $e->getMessage());
            
            // Verifica o tipo de erro
            if ($e->getCode() == 1049) { // Banco não existe
                throw new Exception("Banco de dados não encontrado. Por favor, configure o banco de dados.");
            } else if ($e->getCode() == 1045) { // Erro de senha
                throw new Exception("Erro de autenticação no banco de dados. Verifique as credenciais.");
            } else if ($e->getCode() == 2002) { // Servidor não encontrado
                throw new Exception("Não foi possível conectar ao servidor de banco de dados.");
            }
            
            throw new Exception("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }
}
