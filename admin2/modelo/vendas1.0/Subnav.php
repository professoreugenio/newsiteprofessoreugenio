<?php
$pagina = basename($_SERVER['PHP_SELF']);

?>
<div class="d-flex gap-2 flex-wrap mb-3 mt-4">
    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <a href="cursos.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'cursos.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> CURSOS
        </a>
        <a href="vendas.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> VENDAS
        </a>
        <a href="sistema_afiliadosUsuarios.php?status=1"
            class="btn btn-flat btn-sm <?= ($pagina == 'sistema_afiliadosUsuarios.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> AFILIADOS
        </a>

    <?php endif; ?>


</div>