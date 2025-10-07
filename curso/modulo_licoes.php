<?php
define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php';
// Includes principais
require_once 'config_default1.0/query_dados.php';
require_once 'config_curso1.0/query_curso.php';
// Redireciona se não houver turma
if (empty($idTurma)) {
    header('Location: turmas.php');
    exit;
}
// Continua carregando dados
require_once 'config_default1.0/query_turma.php';
require_once 'config_curso1.0/query_publicacoes2.0.php';
require_once 'config_curso1.0/query_anexos.php';
// Redireciona se o tipo de curso for inválido
if (isset($tipocurso) && $tipocurso == 0) {
    header('Location: ../curso/modulos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $nmCurso ?> - Curso de <?= $nomeTurma; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
    <!-- CSS Customizado -->
    <link rel="stylesheet" href="v2.0/config_css/config.css?<?= time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?= time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        .box-licoes {
            display: none;
        }
    </style>
</head>

<body>
    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php
    // Supondo que você já tenha executado a consulta e possui $AvisoAniversariante (array)
    $temAviso = !empty($AvisoAniversariante);
    $qtd = $temAviso ? count($AvisoAniversariante) : 0;
    // Título dinâmico (singular/plural)
    $titulo = ($qtd === 1) ? "Hoje tem aniversariante na turma!" : "Hoje tem aniversariantes na turma!";
    $sub = ($qtd === 1) ? "Vamos dar os parabéns?" : "Vamos dar os parabéns?";
    ?>
    <?php require 'paginas/body_modulo_licoes.php' ?>
    <?php if ($idUser != '1') : ?>
        <?php require 'config_curso1.0/modais_atualizacao.php'; ?>
    <?php endif; ?>
    <?php require 'config_curso1.0/require_ModalAniversariante.php'; ?>
    <?php require 'config_curso1.0/require_ModalAniversarianteAviso.php'; ?>
</body>

</html>