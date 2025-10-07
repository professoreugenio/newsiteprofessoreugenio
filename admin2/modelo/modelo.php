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
                <small><?= $saudacao; ?></small><br>

            </div>
            <div>
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-gear-fill me-1"></i> Configurações
                </button>
            </div>
        </div>
        <!-- AQUI CONTEÚDO DA PÁGINA -->
    </div>
    <!-- SCRIPTS -->
    <script src="../v1.0/PainelLateral.js"></script>
    <!-- LIBS -->
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>