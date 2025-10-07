<?php
$pagina = basename($_SERVER['PHP_SELF']);

?>
<div class="d-flex gap-2 flex-wrap mb-3">

    <?php if (temPermissao($niveladm, [1])): ?>
        <a href="sistema_afiliadosProdutos.php?status=1"
            class="btn btn-flat btn-sm <?= $pagina == 'sistema_afiliadosProdutos.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-box-seam"></i> CAMPANHAS
        </a>
        <a href="sistema_afiliadosProdutosNovo.php?status=1"
            class="btn btn-flat btn-sm <?= $pagina == 'sistema_afiliadosProdutosNovo.php' ? 'btn-laranja' : 'btn-verde' ?>">
            <i class="bi bi-box-seam"></i> +
        </a>
        
        <a href="sistema_afiliadosUsuarios.php?status=1"
            class="btn btn-flat btn-sm <?= $pagina == 'sistema_afiliadosUsuarios.php' ? 'btn-laranja' : '' ?>">
            <i class="bi bi-person"></i> USUÁRIOS
        </a>



    <?php endif; ?>



</div>