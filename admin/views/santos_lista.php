<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciar Santos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCriarSanto">
        <i class="fas fa-plus me-2"></i>Novo Santo
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagem</th>
                        <th>Nome</th>
                        <th>Data Festa</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($santos as $santo): ?>
                    <tr>
                        <td><?= $santo['id'] ?></td>
                        <td>
                            <?php if ($santo['imagem']): ?>
                                <img src="public/<?= htmlspecialchars($santo['imagem']) ?>" 
                                     alt="<?= htmlspecialchars($santo['nome']) ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($santo['nome']) ?></td>
                        <td><?= $santo['data_festa'] ? date('d/m', strtotime($santo['data_festa'])) : '-' ?></td>
                        <td>
                            <span class="badge bg-<?= $santo['status'] === 'ativo' ? 'success' : 'danger' ?>">
                                <?= ucfirst($santo['status']) ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="editarSanto(<?= $santo['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="confirmarExclusao(<?= $santo['id'] ?>, '<?= htmlspecialchars($santo['nome']) ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Criar Santo -->
<div class="modal fade" id="modalCriarSanto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Novo Santo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="santos.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="acao" value="criar">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="nome_completo" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Resumo</label>
                        <textarea name="resumo" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Biografia</label>
                        <textarea name="biografia" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Local de Nascimento</label>
                            <input type="text" name="local_nascimento" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data da Festa</label>
                            <input type="date" name="data_festa" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Imagem</label>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categorias</label>
                        <select name="categorias[]" class="form-select" multiple>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>">
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Santo -->
<div class="modal fade" id="modalEditarSanto" tabindex="-1">
    <!-- Modal Editar Santo -->
<div class="modal fade" id="modalEditarSanto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Santo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="santos.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="editId">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nome</label>
                            <input type="text" name="nome" id="editNome" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" name="nome_completo" id="editNomeCompleto" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Resumo</label>
                        <textarea name="resumo" id="editResumo" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Biografia</label>
                        <textarea name="biografia" id="editBiografia" class="form-control" rows="5"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="data_nascimento" id="editDataNascimento" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Local de Nascimento</label>
                            <input type="text" name="local_nascimento" id="editLocalNascimento" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data da Morte</label>
                            <input type="date" name="data_morte" id="editDataMorte" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Local da Morte</label>
                            <input type="text" name="local_morte" id="editLocalMorte" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data da Festa</label>
                            <input type="date" name="data_festa" id="editDataFesta" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Data de Canonização</label>
                            <input type="date" name="data_canonizacao" id="editDataCanonizacao" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Papa da Canonização</label>
                        <input type="text" name="papa_canonizacao" id="editPapaCanonizacao" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Padroeiro de</label>
                        <input type="text" name="padroeiro_de" id="editPadroeiroDE" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Símbolos</label>
                        <input type="text" name="simbolos" id="editSimbolos" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Milagres</label>
                        <textarea name="milagres" id="editMilagres" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Oração</label>
                        <textarea name="oracao" id="editOracao" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Imagem</label>
                            <input type="file" name="imagem" class="form-control" accept="image/*">
                            <div id="imagemAtual" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Categorias</label>
                        <select name="categorias[]" id="editCategorias" class="form-select" multiple>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>">
                                    <?= htmlspecialchars($categoria['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="modalConfirmarExclusao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o santo <strong id="nomeSantoExclusao"></strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <form action="santos.php" method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" id="idSantoExclusao">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>   

function editarSanto(id) {
    fetch(`ajax/buscar_santo.php?id=${id}`)
        .then(response => response.json())
        .then(santo => {
            // Preencher campos do formulário
            document.getElementById('editId').value = santo.id;
            document.getElementById('editNome').value = santo.nome || '';
            document.getElementById('editNomeCompleto').value = santo.nome_completo || '';
            document.getElementById('editResumo').value = santo.resumo || '';
            document.getElementById('editBiografia').value = santo.biografia || '';
            document.getElementById('editDataNascimento').value = santo.data_nascimento || '';
            document.getElementById('editLocalNascimento').value = santo.local_nascimento || '';
            document.getElementById('editDataMorte').value = santo.data_morte || '';
            document.getElementById('editLocalMorte').value = santo.local_morte || '';
            document.getElementById('editDataFesta').value = santo.data_festa || '';
            document.getElementById('editDataCanonizacao').value = santo.data_canonizacao || '';
            document.getElementById('editPapaCanonizacao').value = santo.papa_canonizacao || '';
            document.getElementById('editPadroeiroDE').value = santo.padroeiro_de || '';
            document.getElementById('editSimbolos').value = santo.simbolos || '';
            document.getElementById('editMilagres').value = santo.milagres || '';
            document.getElementById('editOracao').value = santo.oracao || '';
            document.getElementById('editStatus').value = santo.status || 'ativo';

            // Mostrar imagem atual se existir
            const imagemAtualDiv = document.getElementById('imagemAtual');
            if (santo.imagem) {
                imagemAtualDiv.innerHTML = `
                    <img src="../public/${santo.imagem}" alt="${santo.nome}" 
                         style="max-width: 100px; max-height: 100px; object-fit: cover;">
                    <p class="small text-muted mt-1">Imagem atual</p>
                `;
            } else {
                imagemAtualDiv.innerHTML = '<p class="small text-muted">Sem imagem</p>';
            }

            // Selecionar categorias
            if (santo.categoria_ids) {
                const categoriaIds = santo.categoria_ids.split(',');
                const selectCategorias = document.getElementById('editCategorias');
                Array.from(selectCategorias.options).forEach(option => {
                    option.selected = categoriaIds.includes(option.value);
                });
            }

            // Abrir modal
            new bootstrap.Modal(document.getElementById('modalEditarSanto')).show();
        })
        .catch(error => {
            console.error('Erro ao buscar dados do santo:', error);
            alert('Erro ao carregar dados do santo');
        });
}

function editarSanto(id) {
    // Fazer requisição AJAX para buscar dados do santo
    fetch(`ajax/buscar_santo.php?id=${id}`)
        .then(response => response.json())
        .then(santo => {
            // Preencher formulário de edição
            document.getElementById('editId').value = santo.id;
            document.getElementById('editNome').value = santo.nome;
            // ... preencher outros campos
            
            // Abrir modal
            new bootstrap.Modal(document.getElementById('modalEditarSanto')).show();
        });
}

function confirmarExclusao(id, nome) {
    document.getElementById('idSantoExclusao').value = id;
    document.getElementById('nomeSantoExclusao').textContent = nome;
    new bootstrap.Modal(document.getElementById('modalConfirmarExclusao')).show();
}
</script>
