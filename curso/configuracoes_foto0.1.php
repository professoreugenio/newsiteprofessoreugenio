<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once 'config_default1.0/query_dados.php' ?>
    <?php require 'config_curso1.0/query_curso.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_curso1.0/v2.0/query_publicacoes.php' ?>
    <?php require 'config_curso1.0/query_anexos.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_default1.0/query_turma.php' ?>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Curso de <?= $nomeTurma; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="v2.0/config_css/config.css?<?= time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>
    <link rel="stylesheet" href="v2.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <style>
        .box-licoes {
            display: none;
        }
    </style>

    <link rel="stylesheet" href="perfilv2.0/perfil.css">
</head>

<body style="min-height: 600px;">
    <?php if ($tipocurso == 0) :
        echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas">');
        exit();
    endif; ?>

    <?php $nav = "2";
    require 'perfilv2.0/nav_configuracoes.php' ?>
    <!-- Navbar -->
    <?php include 'v2.0/nav.php'; ?>
    <?php $quantAnexo = "0"; ?>
    <?php require 'v2.0/sidebarLateral.php'; ?>
    <?php require 'v2.0/sidebarLateralLicoes.php'; ?>
    <!-- Conteúdo -->
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 mx-auto">
                <div class="info-curso">
                    <?php require 'perfilv2.0/form_foto.php' ?>
                </div>
            </div>
        </div>
    </main>


    <!-- Rodapé -->
    <?php require 'v2.0/footer.php'; ?>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS JS -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
    <script>
        function abrirPagina(url) {
            window.open(url, '_self');
        }
    </script>
    <script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>


    <script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
    <script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
</body>

</html>