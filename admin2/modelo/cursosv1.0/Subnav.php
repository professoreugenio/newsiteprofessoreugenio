<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex gap-2 flex-wrap mb-3 mt-4">
    <?php if (temPermissao($niveladm, [1,2])): ?>
        <a href="vendas.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $status == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> COMERCIAL
        </a>

        <a href="vendas.php?institucional=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $institucional == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-globe"></i> INSTITUCIONAL
        </a>

        <a href="conteudoCategorias.php?matriz=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'conteudoCategorias.php' && $matriz == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-geo-alt"></i> PUBLICAÇÕES
        </a>
        <a href="cursos.php?todos=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $todos == 1) ? 'btn-laranja' : '' ?>">
            <i class="bi bi-geo-alt"></i> TODOS
        </a>
    <?php endif; ?>

    <?php if (temPermissao($niveladm, [1, 3])): ?>
        <a href="cursos_novo.php"
            class="btn btn-flat btn-sm <?= $pagina == 'cursos_novo.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-plus-circle"></i> Novo Curso
        </a>

    <?php endif; ?>

    <?php if (temPermissao($niveladm, [1])): ?>

        <a href="relatorio_vendas.php"
            class="btn btn-flat btn-sm <?= $pagina == 'relatorio_vendas.php' ? 'btn-laranja' : '' ?>"
            title="Relatórios">
            <i class="bi bi-bar-chart-line"></i> Relatórios
        </a>

    <?php endif; ?>
</div>