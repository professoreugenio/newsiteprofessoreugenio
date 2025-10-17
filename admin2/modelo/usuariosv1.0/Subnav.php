<?php
$pagina = basename($_SERVER['PHP_SELF']);
$status = isset($_GET['status']) ? $_GET['status'] : null;
$institucional = isset($_GET['institucional']) ? $_GET['institucional'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$todos = isset($_GET['todos']) ? $_GET['todos'] : null;
?>
<div class="d-flex gap-2 flex-wrap mb-3">

    <?php if (temPermissao($niveladm, [1])): ?>
        <!-- Alunos -->
        <a href="alunos_Geral.php?status=1"
            class="btn btn-outline-primary btn-sm fw-semibold px-3 d-flex align-items-center gap-1 shadow-sm <?= ($pagina == 'alunos_Geral.php') ? 'active-link' : '' ?>">
            <i class="bi bi-people-fill"></i> Alunos
        </a>

        <!-- Acessos -->
        <a href="alunoAcessos.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-outline-info btn-sm fw-semibold px-3 d-flex align-items-center gap-1 shadow-sm <?= ($pagina == 'alunoAcessos.php') ? 'active-link' : '' ?>">
            <i class="bi bi-door-open-fill"></i> Acessos
        </a>

        <!-- Turmas -->
        <a href="alunoTurmas.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-outline-success btn-sm fw-semibold px-3 d-flex align-items-center gap-1 shadow-sm <?= ($pagina == 'alunoTurmas.php') ? 'active-link' : '' ?>">
            <i class="bi bi-journal-bookmark-fill"></i> Turmas
        </a>

        <!-- Perfil -->
        <a href="alunoPerfil.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-outline-warning btn-sm fw-semibold px-3 d-flex align-items-center gap-1 shadow-sm <?= ($pagina == 'alunoPerfil.php') ? 'active-link' : '' ?>">
            <i class="bi bi-person-badge-fill"></i> Perfil
        </a>

        <!-- Financeiro -->
        <a href="alunoFinanceiro.php?idUsuario=<?= $_GET['idUsuario'] ?? '' ?>&id=<?= $_GET['id'] ?? '' ?>&tm=<?= $_GET['tm'] ?? '' ?>"
            class="btn btn-outline-danger btn-sm fw-semibold px-3 d-flex align-items-center gap-1 shadow-sm <?= ($pagina == 'alunoFinanceiro.php') ? 'active-link' : '' ?>">
            <i class="bi bi-cash-stack"></i> Financeiro
        </a>
    <?php endif; ?>

</div>

<style>
    /* Estilo ativo */
    .active-link {
        background: linear-gradient(135deg, #00bb9c, #009176);
        color: #fff !important;
        border-color: #00bb9c !important;
        box-shadow: 0 0 8px rgba(0, 187, 156, 0.4);
    }

    /* Efeito hover suave */
    .btn-outline-primary:hover,
    .btn-outline-info:hover,
    .btn-outline-success:hover,
    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
        color: #fff !important;
        transform: translateY(-2px);
        transition: all 0.2s ease-in-out;
    }

    /* Ícones e espaçamento */
    .btn i {
        font-size: 1rem;
    }

    /* Sombra e estilo geral */
    .btn {
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
</style>