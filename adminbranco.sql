CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    ultimo_acesso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir um usuário admin inicial
INSERT INTO usuarios (nome, email, senha, nivel) VALUES 
('Administrador', 'admin@pequenasvias.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- A senha é 'password' - você deve alterar isso depois
