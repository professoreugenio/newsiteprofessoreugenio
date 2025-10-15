<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex gap-2 flex-wrap mb-3">

    <?php if (temPermissao($niveladm, [1])): ?>
        <a href="anuncios.php?status=1"
            class="btn btn-flat btn-sm <?= $pagina == 'anuncios.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> CLIENTES
        </a>

        <a href="anuncios_clientesNovo.php?institucional=1"
            class="btn btn-flat btn-sm <?= $pagina == 'anuncios_clientesNovo.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-globe"></i> NOVO CLIENTE
        </a>

    <?php endif; ?>



</div>