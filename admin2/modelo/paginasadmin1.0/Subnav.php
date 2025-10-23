<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex flex-wrap gap-2 mb-3 mt-4">

    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <!-- Comercial -->
        <a href="paginasAdmin.php?status=1"
            class="btn btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= ($pagina == 'paginasAdmin.php' && $status == 1) ? 'btn-laranja text-white' : 'btn-outline-warning text-dark' ?>">
            <i class="bi bi-bag-fill me-1"></i> Páginas Admin
        </a>
    <?php endif; ?>



    <?php if (temPermissao($niveladm, [1, 3])): ?>

    <?php endif; ?>


    <?php if (temPermissao($niveladm, [1])): ?>

    <?php endif; ?>

</div>