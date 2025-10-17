<?php
// Detecta página atual
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="alunoTurmas.php?idUsuario=<?= $_GET['idUsuario'] ?>" class="btn <?= $paginaAtual === 'alunoAcessos.php' ? 'btn-primary' : 'btn-outline-primary' ?>">
        <i class="bi bi-clock-history me-1"></i> Acessos
    </a>

    <a href="alunoTurmas.php?idUsuario=<?= $_GET['idUsuario'] ?>" class="btn <?= $paginaAtual === 'alunoTurmas.php' ? 'btn-success' : 'btn-outline-success' ?>">
        <i class="bi bi-people-fill me-1"></i> Turmas
    </a>

    <a href="alunoAtividades.php?idUsuario=<?= $_GET['idUsuario'] ?>" class="btn <?= $paginaAtual === 'alunoAtividades.php' ? 'btn-warning text-dark' : 'btn-outline-warning' ?>">
        <i class="bi bi-journal-text me-1"></i> Atividades
    </a>

    <a href="alunoPerfil.php?idUsuario=<?= $_GET['idUsuario'] ?>" class="btn <?= $paginaAtual === 'alunoPerfil.php' ? 'btn-info text-white' : 'btn-outline-info' ?>">
        <i class="bi bi-person-lines-fill me-1"></i> Perfil
    </a>

    <a href="alunoFinanceiro.php?idUsuario=<?= $_GET['idUsuario'] ?>" class="btn <?= $paginaAtual === 'alunoFinanceiro.php' ? 'btn-dark' : 'btn-outline-dark' ?>">
        <i class="bi bi-wallet2 me-1"></i> Financeiro
    </a>
</div>