<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gerenciar Categorias</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCriarCategoria">
        <i class="fas fa-plus me-2"></i>Nova Categoria
    </button>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Total Santos</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?= $categoria['id'] ?></td>
                        <td><?= htmlspecialchars($categoria['nome']) ?></td>
                        <td><?= htmlspecialchars($categoria['slug']) ?></td>
                        <td><?= $categoria['total_santos'] ?></td>
                        <td>
                            <span class="badge bg-<?= $categoria['status'] === 'ativo' ? 'success' : 'danger' ?>">
                                <?= ucfirst($categoria['status']) ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="editarCategoria(<?= $categoria['id'] ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" 
                                    onclick="confirmarExclusao(<?= $categoria['id'] ?>, '<?= htmlspecialchars($categoria['nome']) ?>')">
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

<!-- Modal Criar Categoria -->
<div class="modal fade" id="modalCriarCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Categoria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="categorias.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="acao" value="criar">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
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

<!-- Modal Editar Categoria -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1">
    <!-- Similar ao modal de criar, mas com campos preenchidos -->
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
                <p>Tem certeza que deseja excluir a categoria <strong id="nomeCategoriaExclusao"></strong>?</p>
                <p class="text-danger">Esta ação não pode ser desfeita!</p>
            </div>
            <div class="modal-footer">
                <form action="categorias.php" method="POST">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" id="idCategoriaExclusao">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editarCategoria(id) {
    fetch(`ajax/buscar_categoria.php?id=${id}`)
        .then(response => response.json())
        .then(categoria => {
            document.getElementById('editId').value = categoria.id;
            document.getElementById('editNome').value = categoria.nome;
            document.getElementById('editDescricao').value = categoria.descricao;
            document.getElementById('editStatus').value = categoria.status;
            
            new bootstrap.Modal(document.getElementById('modalEditarCategoria')).show();
        });
}

function confirmarExclusao(id, nome) {
    document.getElementById('idCategoriaExclusao').value = id;
    document.getElementById('nomeCategoriaExclusao').textContent = nome;
    new bootstrap.Modal(document.getElementById('modalConfirmarExclusao')).show();
}
</script>
