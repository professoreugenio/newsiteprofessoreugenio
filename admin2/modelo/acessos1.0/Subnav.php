<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php if (temPermissao($niveladm, [1])): ?>
        <a href="alunosAcessos.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunosAcessos.php' && $status == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> USUÁRIOS
        </a>
        <a href="alunosAcessosAnonimos.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunosAcessosAnonimos.php' && $status == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> USUÁRIOS ANÔNIMOS
        </a>
        <a href="alunosAcessosGrafico.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunosAcessosGrafico.php' && $status == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> GRÁFICO
        </a>


    <?php endif; ?>


</div>