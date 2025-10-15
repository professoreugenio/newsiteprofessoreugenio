<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/QueryCursos.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <style>
        /* --- Correção para dropdown de mensagens WhatsApp --- */
        .venda-item {
            position: relative;
            /* importante para escopo do dropdown */
            overflow: visible !important;
            /* garante que o dropdown não fique cortado */
        }

        .dropdown-menu {
            z-index: 9000 !important;
            position: absolute !important;
            /* acima dos outros cards */
        }

        /* Pequeno ajuste visual */
        .dropdown-menu.show {
            transform: translate3d(0, 0, 10px) !important;
            margin-top: 4px;
            z-index: 9000 !important;
            position: absolute !important;
        }
    </style>

</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h3><i class="bi bi-journal-text me-2"></i> TURMA <?= $Nomecurso ?></h3>
            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/turmas1.0/Subnav.php'; ?>
        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/turmas1.0/BodyFormNovaTurma.php'; ?>
    </div>
    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>