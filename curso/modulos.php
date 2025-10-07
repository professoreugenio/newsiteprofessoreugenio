<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<!-- https://professoreugenio.com/curso/actionCurso.php?mdl=bUFIazN0SnI1TkFKVzRPSlBlZjRRUT09&pub=5207 -->

<head>
    <?php require_once 'config_default1.0/query_dados.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_default1.0/query_turma.php' ?>
    <?php
    // if ($idCurso == '143'):
    //     echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas/">');
    //     exit();
    // endif;

    ?>
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
    <link rel="stylesheet" href="config_default1.0/Css_config_redesocial.css?<?php echo time(); ?>">

    <link rel="stylesheet" href="config_default1.0/Css_dropdownsModulos.css">
    <link rel="stylesheet" href="config_default1.0/CSS_linkAdmin.css?time=<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_cardsCurso.css?time=<?php echo time(); ?>">
    <!-- <link rel="stylesheet" href="v2.0/CSS_sidebarLateral.css?<?= time(); ?>"> -->
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">

    <link rel="stylesheet" href="config_default1.0/CSS_default.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_modalCaderno.css?<?php echo time(); ?>">
    <style>
        .corsegund {
            color: #ff7f2aff;
        }

        .corprima {
            color: #050b22;
        }
    </style>

    <style>
        body {
            background-color: #0F1D36;
            color: #ffffff;
        }

        /* Botões de módulos */
        .mod-btns .btn {
            margin-right: .5rem;
            margin-bottom: .5rem;
            border-color: #00BB9C;
            color: #00BB9C;
            background-color: transparent;
        }

        .mod-btns .btn:hover {
            background-color: #00BB9C;
            color: #ffffff;
        }

        /* Card de módulo */
        .card {
            background-color: #112240;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .card-header {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        /* Lista de lições */
        .list-group-item {
            background-color: #0F1D36;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        /* Ícones */
        .lesson-item .bi {
            font-size: 1rem;
        }

        /* Progress bar */
        .progress {
            background-color: rgba(255, 255, 255, 0.15);
        }

        .progress-bar {
            background-color: #00BB9C;
        }

        /* Badges personalizadas */
        .badge.text-bg-success {
            background-color: #00BB9C !important;
        }

        .badge.text-bg-primary {
            background-color: #FF9C00 !important;
            color: #000 !important;
        }

        .badge.text-bg-info {
            background-color: #2196F3 !important;
        }

        .badge.text-bg-secondary {
            background-color: #6c757d !important;
        }

        /* Scroll dos botões */
        .mod-scroll-x {
            overflow-x: auto;
            white-space: nowrap;
        }

        .anchor-offset {
            scroll-margin-top: 90px;
        }
    </style>
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
</head>

<body>

    <?php require 'paginas/body_index.php' ?>


    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php require 'afiliadosv1.0/require_ModalAfiliado.php'; ?>
    <?php if ($idUser != '1') : ?>
        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>




    <script src="acessosv1.0/ajax_registraAcesso.js?<?= time() ?>"></script>
    <script src="config_turmas1.0/JS_accessturma.js?<?= time() ?>"></script>

</body>

</html>