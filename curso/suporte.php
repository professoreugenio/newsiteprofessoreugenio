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
    <?php if ($idCurso == '143'):
        echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas/">');
        exit();
    endif;  ?>
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
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
</head>

<body>

    <?php require 'paginas/body_suporte.php' ?>


    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php require 'afiliadosv1.0/require_ModalAfiliado.php'; ?>
    <?php if ($idUser != '1') : ?>

        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>

    <script src="config_turmas1.0/JS_accessturma.js?<?= time() ?>"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js?<?= time() ?>"></script>
</body>

</html>