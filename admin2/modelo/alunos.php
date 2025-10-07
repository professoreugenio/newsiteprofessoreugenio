<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
$pagina = basename($_SERVER['PHP_SELF']);
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
                <h3><i class="bi bi-journal-text me-2"></i> Lista de Cursos</h3>
                <small><?= $saudacao; ?></small><br>
                <small><?= diadasemana($data, 2); ?></small>
                <div>
                    <a href="alunos.php?status=1"
                        class="btn btn-flat btn-sm <?= ($pagina == 'alunos.php') ? 'btn-laranja' : '' ?>">
                        <i class="bi bi-shop"></i> Vencidos
                    </a>
                    <a href="alunos_Geral.php?status=1"
                        class="btn btn-flat btn-sm <?= ($pagina == 'alunos_Geral.php') ? 'btn-laranja' : '' ?>">
                        <i class="bi bi-shop"></i> Todos Alunos
                    </a>

                </div>
            </div>
            <?php if (temPermissao($niveladm, [1])): ?>
                <?php require_once APP_ROOT . '/admin2/modelo/cursosv1.0/Subnav.php'; ?>
            <?php endif; ?>
        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/usuariosv1.0/BodyAlunosLista1.0.php'; ?>

    </div>

    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>



    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>