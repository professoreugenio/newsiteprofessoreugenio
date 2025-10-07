<?php
$pagina = basename($_SERVER['PHP_SELF']);

?>
<div class="d-flex gap-2 flex-wrap mb-3">

    <?php if (temPermissao($niveladm, [1])): ?>
        <a href="bancodeImagens.php?status=1"
            class="btn btn-flat btn-sm <?= $pagina == 'bancodeImagens.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> GALERIA
        </a>

        <a href="#" id="btnNovaGaleria" data-bs-toggle="modal" data-bs-target="#modalNovaGaleria"
            class="btn btn-flat btn-sm <?= $pagina == 'teste.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-globe"></i> NOVA GALERIA
        </a>

    <?php endif; ?>



</div>