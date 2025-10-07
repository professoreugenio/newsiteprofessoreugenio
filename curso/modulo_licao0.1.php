<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<?php require_once 'config_default1.0/query_dados.php' ?>
<?php require_once 'config_curso1.0/query_curso.php' ?>
<?php require_once 'config_curso1.0/query_publicacoes2.0.php' ?>
<?php require_once 'config_curso1.0/query_anexos.php' ?>
<?php if (empty($idTurma)) {
    echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
    exit();
} ?>
<?php require 'config_default1.0/query_turma.php' ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $nomeTurma; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <link rel="stylesheet" href="config_curso1.0/CSS_config.css?<?= time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>

    <!-- Bootstrap 5 (se já carrega, mantenha o seu) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (se já carrega, ignore) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- jQuery (necessário para Summernote) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Popper (necessário para tooltips/menus do Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <?php if ($idUser == 1): ?>
        <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
        <!-- <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv1.0.css?<?php echo time(); ?>"> -->
    <?php endif; ?>

    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/Css_licoesv2.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_default.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_modalCaderno.css?<?php echo time(); ?>">


</head>

<body>

    <!-- Botão ATIVIDADE embaixo, centralizado e com largura total -->
    <?php require 'config_aulas1.0/require_ModalDepoimento.php'; ?>
    <?php if ($idUser == '1') : ?>
        <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php else: ?>
        <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
        <?php // require_once 'config_default1.0/sidebarLateral0.1.php'; 
        ?>
    <?php endif; ?>
    <a href="modulo_licaonovo.php">Novo</a>
    <?php require 'paginas/body_modulo_licao.php' ?>
    <?php if ($idUser != '1') : ?>
        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>

</body>

</html>