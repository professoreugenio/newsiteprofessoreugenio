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
    <?php require_once APP_ROOT . '/admin2/modelo/usuariosv1.0/QueryUsuario.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <style>
        .note-editor.note-frame {
            border-radius: 1rem;
        }

        .note-editable {
            min-height: 220px;
        }
    </style>
</head>

<body id="adminLayout">
    <?php require_once APP_ROOT . '/admin2/v1.0/nav.php'; ?>
    <?php require_once APP_ROOT . '/admin2/v1.0/PainelLateral.php'; ?>
    <div class="container-fluid px-4 mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center">
            <div class="mt-2">
                <?php require 'usuariosv1.0/cabecalhoPage.php'; ?>
            </div>
            <?php if (temPermissao($niveladm, [1])): ?>
            <?php require_once APP_ROOT . '/admin2/modelo/usuariosv1.0/Subnav.php'; ?>
            <?php endif; ?>
        </div>
        <?php require_once APP_ROOT . '/admin2/modelo/usuariosv1.0/BodyAlunosFinanceiro.php'; ?>
    </div>
    <!-- Scripts -->
    <script src="../v1.0/PainelLateral.js"></script>
    <?php require_once APP_ROOT . '/admin2/v1.0/footer.php'; ?>
</body>

</html>