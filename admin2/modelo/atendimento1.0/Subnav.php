<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex gap-2 flex-wrap mb-3 mt-4">
    <?php if (temPermissao($niveladm, [1, 2])): ?>
        <a href="alunoAtendimento.php?idUsuario=<?= $_GET['idUsuario']?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoAtendimento.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> ATENDIMENTO
        </a>

        <a href="alunoTurmas.php?idUsuario=<?= $_GET['idUsuario']?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoPerfil.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> TURMAS
        </a>

        <a href="alunoAtendimentoNovo.php?idUsuario=<?= $_GET['idUsuario']?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-globe"></i> NOVO
        </a>



    <?php endif; ?>
</div>