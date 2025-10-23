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
        <a href="cursos.php?status=1"
            class="btn btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= ($pagina == 'cursos.php' && $status == 1) ? 'btn-laranja text-white' : 'btn-outline-warning text-dark' ?>">
            <i class="bi bi-bag-fill me-1"></i> Comercial
        </a>

        <!-- Institucional -->
        <a href="cursos.php?institucional=1"
            class="btn btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= ($pagina == 'cursos.php' && $institucional == 1) ? 'btn-info text-white' : 'btn-outline-info text-dark' ?>">
            <i class="bi bi-building me-1"></i> Institucional
        </a>

        <!-- Publicações -->
        <a href="conteudoCategorias.php?matriz=1"
            class="btn btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= ($pagina == 'conteudoCategorias.php' && $matriz == 1) ? 'btn-primary text-white' : 'btn-outline-primary text-dark' ?>">
            <i class="bi bi-collection me-1"></i> Publicações
        </a>

        <!-- Todos -->
        <a href="cursos.php?todos=1"
            class="btn btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= ($pagina == 'cursos.php' && $todos == 1) ? 'btn-secondary text-white' : 'btn-outline-secondary text-dark' ?>">
            <i class="bi bi-list-ul me-1"></i> Todos
        </a>
    <?php endif; ?>


    <?php if (temPermissao($niveladm, [1, 3])): ?>
        <!-- Novo curso -->
        <a href="cursos_novo.php?id=<?= time() ?>"
            class="btn btn-success btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= $pagina == 'cursos_novo.php' ? 'active' : '' ?>">
            <i class="bi bi-plus-circle-fill me-1"></i> Novo Curso
        </a>
    <?php endif; ?>


    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Relatórios -->
        <a href="relatorio_cursos.php"
            class="btn btn-sm px-3 py-2 rounded-3 shadow-sm border-0 <?= $pagina == 'relatorio_cursos.php' ? 'btn-laranja text-white' : 'btn-outline-dark text-dark' ?>"
            title="Relatórios e Estatísticas">
            <i class="bi bi-bar-chart-line-fill me-1"></i> Relatórios
        </a>
    <?php endif; ?>

</div>