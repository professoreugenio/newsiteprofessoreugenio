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
    <?php require_once APP_ROOT . '/admin2/v1.0/dadosuser.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>

    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="mt-4">

                <small><?= diadasemana($data, 2); ?></small>
                <h4>
                    MENSAGENS RECEBIDAS
                    <i class="bi bi-envelope-fill ms-2"></i>
                </h4>


            </div>
            <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/Subnav.php'; ?>

        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/conteudos1.0/BodyConteudoModulos.php'; ?>

    </div>

    <!-- Scripts -->
    <script src=" ../v1.0/PainelLateral.js"></script>



    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>