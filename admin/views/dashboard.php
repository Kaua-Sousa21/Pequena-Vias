<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard</h1>
        <div>
            <a href="santos.php" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Novo Santo
            </a>
            <a href="categorias.php" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nova Categoria
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Santos</h5>
                    <p class="card-text display-4"><?= $total_santos ?? 0 ?></p>
                    <a href="santos.php" class="text-white">Ver todos <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total de Categorias</h5>
                    <p class="card-text display-4"><?= $total_categorias ?? 0 ?></p>
                    <a href="categorias.php" class="text-white">Ver todas <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Santos do Dia</h5>
                    <p class="card-text display-4"><?= count($santos_do_dia ?? []) ?></p>
                    <span class="text-white"><?= date('d/m/Y') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo adicional do dashboard aqui -->
</div>
