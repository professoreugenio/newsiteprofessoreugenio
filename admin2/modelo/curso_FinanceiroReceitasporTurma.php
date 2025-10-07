<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once APP_ROOT . '/admin2/v1.0/head.php'; ?>
    <link rel="stylesheet" href="/admin2/v1.0/CSS_config.css?<?= time(); ?>">
    <link rel="stylesheet" href="/admin2/financeiro1.0/CSS_financeiro.css?<?= time(); ?>">
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>

</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>

    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">
                <h3><i class="bi bi-journal-text me-2"></i> Lista de Cursos</h3>
                <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small>
                <h4>
                    <?php $tipo = "1"; ?>

                </h4>

            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/financeiro1.0/Subnav.php'; ?>

        </div>


        <?php
        $paginaAtual = basename($_SERVER['PHP_SELF']);
        ?>

        <?php require_once APP_ROOT . '/admin2/modelo/financeiro1.0/BodyFinanceiroReceitasporTurma.php'; ?>

    </div>

    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>



    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>