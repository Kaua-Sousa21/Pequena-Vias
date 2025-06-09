<nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="px-3 mb-4">
            <a href="index.php" class="text-white text-decoration-none">
                <i class="fas fa-cross me-2"></i>
                <span class="fs-4">Pequenas Vias</span>
            </a>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" 
                   href="index.php">
                    <i class="fas fa-home me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'santos.php' ? 'active' : '' ?>" 
                   href="santos.php">
                    <i class="fas fa-user me-2"></i>
                    Santos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categorias.php' ? 'active' : '' ?>" 
                   href="categorias.php">
                    <i class="fas
    <a class="nav-link" href="logout.php">Sair</a>
</li>
