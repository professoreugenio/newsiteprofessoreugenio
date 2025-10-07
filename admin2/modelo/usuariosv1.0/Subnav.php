<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php if (temPermissao($niveladm, [1])): ?>
        <a href="cursos_TurmasAlunos.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?? '' ?>&id=<?= $_GET['id'] ?? '' ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'vendas.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> Alunos
        </a>
        <a href="alunoAcessos.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoAcessos.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> Acessos
        </a>

        <a href="alunoTurmas.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoTurmas.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> Turmas
        </a>
        <a href="alunoAtendimento.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoAtendimento.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> Atendimento
        </a>

        <a href="alunoPerfil.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoPerfil.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> Perfil
        </a>
        <a href="alunoFinanceiro.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-flat btn-sm <?= ($pagina == 'alunoFinanceiro.php') ? 'btn-laranja' : '' ?>">
            <i class="bi bi-shop"></i> Financeiro
        </a>



    <?php endif; ?>
</div>