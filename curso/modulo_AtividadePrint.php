<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once 'config_default1.0/query_dados.php' ?>
    <?php require_once 'config_curso1.0/query_curso.php' ?>
    <?php require_once 'config_curso1.0/query_publicacoes2.0.php' ?>
    <?php require_once 'config_curso1.0/query_anexos.php' ?>
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
    <link rel="stylesheet" href="config_curso1.0/CSS_config.css?<?= time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/Css_licoesv2.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_default.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_Atividade1.0/CSS_atividadesanexos.css?<?php echo time(); ?>">
    <style>
        .box-licoes {
            display: none;
        }

        /* Estiliza a faixa de legendas
        video::cue {
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        font-size: 1.8rem;
        font-weight: 600;
        padding: 1em 1.6em;
        margin-bottom: 5em;
        border-radius: 8px;
        text-shadow: 1px 1px 2px #000;
        }
        */
        /* Ajustes opcionais para responsividade em mobile */
        @media (max-width: 576px) {
            video::cue {
                font-size: 0.95rem;
            }
        }
    </style>
    <!-- Lightbox2 (via CDN jsDelivr) -->
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2/dist/js/lightbox-plus-jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500&display=swap" rel="stylesheet">

</head>

<body>
    <?php require 'config_Atividade1.0/body_modulo_AtividadePrint.php' ?>
    <?php if ($idUser != '1') : ?>
        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>
</body>

</html>