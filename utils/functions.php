<?php
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
    function processarUploadImagem($imagem) {
    $targetDir = "../uploads/"; // Diretório para salvar as imagens
    $targetFile = $targetDir . basename($imagem["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

    // Verificar se é uma imagem
    $check = getimagesize($imagem["tmp_name"]);
    if($check === false) {
        throw new Exception("O arquivo não é uma imagem.");
    }

    // Verificar se o arquivo já existe
    if (file_exists($targetFile)) {
        // Renomear o arquivo para evitar conflitos
        $targetFile = $targetDir . uniqid() . "." . $imageFileType;
    }

    // Verificar tamanho do arquivo (5MB)
    if ($imagem["size"] > 5000000) {
        throw new Exception("Desculpe, o arquivo é muito grande.");
    }

    // Permitir certos formatos de arquivo
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        throw new Exception("Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.");
    }

    // Tentar fazer o upload
    if (move_uploaded_file($imagem["tmp_name"], $targetFile)) {
        return str_replace("../", "", $targetFile); // Retorna o caminho da imagem sem ../
    } else {
        throw new Exception("Desculpe, houve um erro ao fazer o upload do arquivo.");
    }
}
?>