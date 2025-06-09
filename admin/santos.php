<?php
require_once '../config/init.php';
require_once 'check_auth.php';

$database = new Database();
$db = $database->getConnection();
$santoObj = new Santo($db);
$categoriaObj = new Categoria($db);

$action = $_GET['action'] ?? 'list';
$erro = '';
$sucesso = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'create':
            case 'update':
                $dados = [
                    'nome' => $_POST['nome'],
                    'nome_completo' => $_POST['nome_completo'],
                    'slug' => criarSlug($_POST['nome']),
                    'resumo' => $_POST['resumo'],
                    'biografia' => $_POST['biografia'],
                    'data_nascimento' => $_POST['data_nascimento'],
                    'local_nascimento' => $_POST['local_nascimento'],
                    'data_morte' => $_POST['data_morte'],
                    'local_morte' => $_POST['local_morte'],
                    'data_canonizacao' => $_POST['data_canonizacao'],
                    'papa_canonizacao' => $_POST['papa_canonizacao'],
                    'data_festa' => $_POST['data_festa'],
                    'padroeiro_de' => $_POST['padroeiro_de'],
                    'simbolos' => $_POST['simbolos'],
                    'milagres' => $_POST['milagres'],
                    'oracao' => $_POST['oracao'],
                    'status' => $_POST['status'],
                    'categorias' => $_POST['categorias'] ?? []
                ];

                // Upload de imagem
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $imagem = processarUploadImagem($_FILES['imagem']);
                    if ($imagem) {
                        $dados['imagem'] = $imagem;
                    }
                }

                if ($_POST['action'] === 'create') {
                    $santoObj->criar($dados);
                    $sucesso = 'Santo criado com sucesso!';
                } else {
                    $santoObj->atualizar($_POST['id'], $dados);
                    $sucesso = 'Santo atualizado com sucesso!';
                }
                break;

            case 'delete':
                $santoObj->deletar($_POST['id']);
                $sucesso = 'Santo excluído com sucesso!';
                break;
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar categorias para o formulário
$categorias = $categoriaObj->listarTodas();

// Buscar dados conforme a ação
switch ($action) {
    case 'new':
        $titulo = 'Novo Santo';
        $santo = [];
        break;
        
    case 'edit':
        $titulo = 'Editar Santo';
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            header('Location: santos.php');
            exit;
        }
        $santo = $santoObj->buscarPorId($id);
        if (!$santo) {
            header('Location: santos.php?erro=santo_nao_encontrado');
            exit;
        }
        break;
        
    default:
        $titulo = 'Gerenciar Santos';
        $pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;
        $por_pagina = 20;
        $offset = ($pagina - 1) * $por_pagina;
        
        $santos = $santoObj->listarSantos($por_pagina, $offset);
        $total_santos = $santoObj->contarSantos();
        $total_paginas = ceil($total_santos / $por_pagina);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?> - Painel Administrativo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: white;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Conteúdo Principal -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= $titulo ?></h1>
                    <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Novo Santo
                    </a>
                    <?php endif; ?>
                </div>

                <?php if ($erro): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                <?php endif; ?>

                <?php if ($action === 'list'): ?>
                    <!-- Lista de Santos -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Data Festa</th>
                                    <th>Categorias</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($santos as $santo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($santo['nome']) ?></td>
                                    <td><?= formatarDataLiturgica($santo['data_festa']) ?></td>
                                    <td><?= htmlspecialchars($santo['categorias']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $santo['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($santo['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?action=edit&id=<?= $santo['id'] ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmarExclusao(<?= $santo['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <?php if ($total_paginas > 1): ?>
                    <nav aria-label="Navegação da lista de santos">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Formulário de Santo -->
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="<?= $action === 'new' ? 'create' : 'update' ?>">
                        <?php if ($action === 'edit'): ?>
                        <input type="hidden" name="id" value="<?= $santo['id'] ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?= $santo['nome'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nome_completo" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="nome_completo" name="nome_completo"
                                       value="<?= $santo['nome_completo'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="resumo" class="form-label">Resumo</label>
                            <textarea class="form-control" id="resumo" name="resumo" rows="3"><?= $santo['resumo'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="biografia" class="form-label">Biografia</label>
                            <textarea class="form-control summernote" id="biografia" name="biografia"><?= $santo['biografia'] ?? '' ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                       value="<?= $santo['data_nascimento'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="local_nascimento" class="form-label">Local de Nascimento</label>
                                <input type="text" class="form-control" id="local_nascimento" name="local_nascimento"
                                       value="<?= $santo['local_nascimento'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_morte" class="form-label">Data de Morte</label>
                                <input type="date" class="form-control" id="data_morte" name="data_morte"
                                       value="<?= $santo['data_morte'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="local_morte" class="form-label">Local de Morte</label>
                                <input type="text" class="form-control" id="local_morte" name="local_morte"
                                       value="<?= $santo['local_morte'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="data_canonizacao" class="form-label">Data de Canonização</label>
                                <input type="date" class="form-control" id="data_canonizacao" name="data_canonizacao"
                                       value="<?= $santo['data_canonizacao'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="papa_canonizacao" class="form-label">Papa da Canonização</label>
                                <input type="text" class="form-control" id="papa_canonizacao" name="papa_canonizacao"
                                       value="<?= $santo['papa_canonizacao'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="data_festa" class="form-label">Data da Festa</label>
                            <input type="date" class="form-control" id="data_festa" name="data_festa"
                                   value="<?= $santo['data_festa'] ?? '' ?>">
                        </div>

                        <div class="mb-3">
                            <label for="categorias" class="form-label">Categorias</label>
                            <select class="form-select" id="categorias" name="categorias[]" multiple>
                                <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"
                                        <?= in_array($categoria['id'], $santo['categorias'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="imagem" class="form-label">Imagem</label>
                            <?php if (!empty($santo['imagem'])): ?>
                            <div class="mb-2">
                                <img src="<?= htmlspecialchars($santo['imagem']) ?>" 
                                     alt="Imagem atual" style="max-height: 100px;">
                            </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="imagem" name="imagem">
                        </div>

                        <div class="mb-3">
                            <label for="padroeiro_de" class="form-label">Padroeiro de</label>
                            <textarea class="form-control" id="padroeiro_de" name="padroeiro_de"><?= $santo['padroeiro_de'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="simbolos" class="form-label">Símbolos</label>
                            <textarea class="form-control" id="simbolos" name="simbolos"><?= $santo['simbolos'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="milagres" class="form-label">Milagres</label>
                            <textarea class="form-control summernote" id="milagres" name="milagres"><?= $santo['milagres'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="oracao" class="form-label">Oração</label>
                            <textarea class="form-control" id="oracao" name="oracao"><?= $santo['oracao'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="ativo" <?= ($santo['status'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="inativo" <?= ($santo['status'] ?? '') === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="santos.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        // Inicializar Summernote
        $('.summernote').summernote({
            height: 300,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Confirmação de exclusão
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este santo?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Validação do formulário
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>
