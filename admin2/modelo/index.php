<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2)); // sobe 2 níveis
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>

    <!-- Painel Lateral -->
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container-fluid px-4 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
            <div class="mt-4">
                <h3>Painel Admin</h3>
                <!-- <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small> -->
                <h4 class="mt-2">
                    <small class="text-muted">Bem-vindo de volta, <strong><?= $nomeadm ?? 'Administrador'; ?></strong>!</small>
                </h4>

                <?php
                require 'alunos1.0/require_UltimosInscritos.php';
                ?>
                <!-- ÚLTIMAS INSCRIÇÕES REALIZADAS -->
            </div>
            <div>
                <?php if (temPermissao($niveladm, [1])): ?>
                    <div class="d-flex justify-content-end mb-3">
                        <button id="toggleValores" class="btn btn-outline-secondary btn-sm">
                            <i id="iconToggle" class="bi bi-eye-slash"></i> Exibir valores
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php require_once APP_ROOT . '/admin2/v1.0/CardsResumo.php'; ?>
        <?php require_once APP_ROOT . '/admin2/v1.0/GraficoLinhasAcessos.php'; ?>


    </div>

    <!-- SCRIPTS -->
    <script src="../v1.0/PainelLateral.js"></script>

    <!-- LIBS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>