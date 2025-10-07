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


    <style>
        /* Aparência geral da seção */
        .af-toolbar {
            gap: .5rem;
        }

        .af-toolbar .meta {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .af-toolbar .meta .label {
            color: #6b7280;
            font-size: .875rem;
            line-height: 1.1;
        }

        /* Tabela compacta e com header sticky */
        .af-table-wrap {
            max-height: 420px;
            /* lista rolável sem estourar o card */
            overflow: auto;
            border-radius: .75rem;
        }

        .af-table {
            --bs-table-bg: #fff;
        }

        .af-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: var(--bs-body-bg, #f8f9fa);
            border-bottom: 1px solid var(--bs-border-color);
        }

        .af-table td,
        .af-table th {
            padding-top: .5rem;
            padding-bottom: .5rem;
            vertical-align: middle;
        }

        .af-table .td-num {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* Badges menores e elegantes */
        .badge-soft {
            border: 1px solid transparent;
            font-weight: 600;
        }

        .badge-soft-warning {
            background: var(--bs-warning-bg-subtle);
            color: #9a6700;
            border-color: #f7e0a3;
        }

        .badge-soft-success {
            background: var(--bs-success-bg-subtle);
            color: #0a6d2a;
            border-color: #bde5c8;
        }

        .badge-soft-info {
            background: var(--bs-info-bg-subtle);
            color: #055160;
            border-color: #b6e3f0;
        }

        /* Linhas com sutil destaque por status */
        .tr-pendente td {
            background: rgba(255, 193, 7, .06);
        }

        /* amarelo sutil */
        .tr-aprovado td {
            background: rgba(25, 135, 84, .04);
        }

        /* verde sutil */

        /* Títulos internos */
        .cell-title {
            font-weight: 600;
        }

        .cell-sub {
            color: #6b7280;
            font-size: .8rem;
        }

        /* Responsividade suave */
        @media (max-width: 576px) {
            .af-toolbar {
                flex-direction: column;
                align-items: flex-start !important;
                gap: .25rem;
            }
        }
    </style>

    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateral.css?<?php echo time(); ?>">
</head>

<body>

    <?php require 'paginas/body_afiliado_cash.php' ?>


    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php if ($idUser != '1') : ?>

        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>
    <?php require 'afiliadosv1.0/require_ModalAfiliado.php'; ?>
    <script src="config_turmas1.0/JS_accessturma.js?<?= time() ?>"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js?<?= time() ?>"></script>


</body>

</html>