<?php
/* SUBNAV */
$pagina = basename($_SERVER['PHP_SELF']);
$status = $_GET['status'] ?? null;
$institucional = $_GET['institucional'] ?? null;
$matriz = $_GET['matriz'] ?? null;
$todos = $_GET['todos'] ?? null;
?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php if (temPermissao($niveladm, [1])): ?>
        <a href="pg_acessos.php?status=1"
           class="btn btn-flat btn-sm <?= $pagina == 'pg_acessos.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> CLIENTES
        </a>

        <a href="pg_acessos.php?institucional=1"
           class="btn btn-flat btn-sm <?= $pagina == 'pg_acessos.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-globe"></i> NOVO CLIENTE
        </a>
    <?php endif; ?>
</div>