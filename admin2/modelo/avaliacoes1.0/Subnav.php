<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : '0';
?>
<div class="d-flex gap-2 flex-wrap mb-3">

    <a href="vendas.php?status=1"
        class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $status == 1) ? 'btn-laranja' : '' ?>">
        <i class="bi bi-shop"></i> COMERCIAL
    </a>

    <a href="vendas.php?institucional=1"
        class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $institucional == 1) ? 'btn-laranja' : '' ?>">
        <i class="bi bi-globe"></i> INSTITUCIONAL
    </a>

    <a href="vendas.php?matriz=1"
        class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $matriz == 1) ? 'btn-laranja' : '' ?>">
        <i class="bi bi-geo-alt"></i> MATRIZ
    </a>
    <a href="cursos.php?todos=1"
        class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php' && $todos == 1) ? 'btn-laranja' : '' ?>">
        <i class="bi bi-geo-alt"></i> TODOS
    </a>

    <a href="cursos_novo.php"
        class="btn btn-flat btn-sm <?= $pagina == 'cursos_novo.php' ? 'btn-laranja' : '' ?>">
        <i class="bi bi-plus-circle"></i> Novo Curso
    </a>

    <a href="relatorio_vendas.php"
        class="btn btn-flat btn-sm <?= $pagina == 'relatorio_vendas.php' ? 'btn-laranja' : '' ?>"
        title="Relatórios">
        <i class="bi bi-bar-chart-line"></i> Relatórios
    </a>
</div>