-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS santos_catolicos
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE santos_catolicos;

-- Tabela de santos
CREATE TABLE IF NOT EXISTS santos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    nome_completo VARCHAR(255),
    slug VARCHAR(255) NOT NULL UNIQUE,
    resumo TEXT,
    biografia TEXT,
    data_nascimento DATE,
    local_nascimento VARCHAR(255),
    data_morte DATE,
    local_morte VARCHAR(255),
    data_canonizacao DATE,
    papa_canonizacao VARCHAR(255),
    data_festa DATE,
    padroeiro_de TEXT,
    simbolos TEXT,
    imagem VARCHAR(255),
    milagres TEXT,
    oracao TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    descricao TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de relacionamento entre santos e categorias
CREATE TABLE IF NOT EXISTS santo_categoria (
    santo_id INT,
    categoria_id INT,
    PRIMARY KEY (santo_id, categoria_id),
    FOREIGN KEY (santo_id) REFERENCES santos(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir categorias iniciais
INSERT INTO categorias (nome, slug, descricao) VALUES
('Mártires', 'martires', 'Santos que deram a vida em testemunho da fé'),
('Doutores da Igreja', 'doutores-da-igreja', 'Santos reconhecidos por sua contribuição teológica e doutrinária'),
('Fundadores', 'fundadores', 'Santos que fundaram ordens ou congregações religiosas'),
('Místicos', 'misticos', 'Santos conhecidos por suas experiências místicas e contemplativas'),
('Papas', 'papas', 'Santos que foram Papas da Igreja Católica'),
('Virgens', 'virgens', 'Santas que consagraram sua virgindade a Deus'),
('Confessores', 'confessores', 'Santos que testemunharam a fé através de suas vidas'),
('Religiosos', 'religiosos', 'Santos que viveram em ordens ou congregações religiosas'),
('Leigos', 'leigos', 'Santos que viveram sua santidade na vida secular'),
('Crianças e Jovens', 'criancas-e-jovens', 'Santos que alcançaram a santidade em tenra idade'),
('Padres e Bispos', 'padres-e-bispos', 'Santos que serviram como sacerdotes ou bispos'),
('Missionários', 'missionarios', 'Santos que se dedicaram à evangelização'),
('Taumaturgos', 'taumaturgos', 'Santos conhecidos por seus milagres'),
('Padroeiros', 'padroeiros', 'Santos invocados como protetores especiais'),
('Santos Modernos', 'santos-modernos', 'Santos dos séculos XIX, XX e XXI');

-- Inserir alguns santos de exemplo
INSERT INTO santos (nome, slug, resumo, data_festa, status) VALUES
('São Francisco de Assis', 'sao-francisco-de-assis', 'Fundador da Ordem Franciscana e conhecido por seu amor à natureza', '1004-04', 'ativo'),
('Santa Teresinha do Menino Jesus', 'santa-teresinha', 'Carmelita conhecida por sua "pequena via" para a santidade', '1001-10', 'ativo'),
('Santo Antônio de Pádua', 'santo-antonio', 'Franciscano conhecido por seus milagres e pregações', '0613-06', 'ativo'),
('São João Paulo II', 'sao-joao-paulo-ii', 'Papa que liderou a Igreja Católica por 27 anos', '1022-10', 'ativo'),
('Santa Rita de Cássia', 'santa-rita', 'Conhecida como a "Santa das Causas Impossíveis"', '0522-05', 'ativo');

-- Relacionar santos com categorias
INSERT INTO santo_categoria (santo_id, categoria_id) VALUES
(1, 3), -- São Francisco - Fundadores
(1, 8), -- São Francisco - Religiosos
(2, 6), -- Santa Teresinha - Virgens
(2, 8), -- Santa Teresinha - Religiosos
(3, 8), -- Santo Antônio - Religiosos
(3, 13), -- Santo Antônio - Taumaturgos
(4, 5), -- João Paulo II - Papas
(4, 15), -- João Paulo II - Santos Modernos
(5, 8), -- Santa Rita - Religiosos
(5, 14); -- Santa Rita - Padroeiros

-- Adicionar índices para melhor performance
ALTER TABLE santos ADD INDEX idx_slug (slug);
ALTER TABLE santos ADD INDEX idx_status (status);
ALTER TABLE santos ADD INDEX idx_data_festa (data_festa);
ALTER TABLE categorias ADD INDEX idx_slug (slug);
ALTER TABLE categorias ADD INDEX idx_status (status);
