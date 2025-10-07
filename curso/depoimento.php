<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require 'config_curso1.0/query_dados.php' ?>
    <?php require 'config_curso1.0/v2.0/query_publicacoes.php' ?>
    <?php if (empty($idTurma)) {
        echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
        exit();
    } ?>
    <?php require 'config_default1.0/query_turma.php' ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="config_default1.0/Css_config_redesocial.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="v2.0/Css_licoes.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_slideDown.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_slideDown.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_redesocial/Css_config_loader.css">
    <link rel="stylesheet" href="config_default1.0/Css_ofcanvas.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_default.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/Css_dropdownsModulos.css">
    <link rel="stylesheet" href="config_default1.0/CSS_linkAdmin.css?time=<?php echo time(); ?>">
    <link rel="stylesheet" href="../mycss/anuncio.css">


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>
</head>

<body>

    <?php if ($tipocurso == 0) :
        echo ('<meta http-equiv="refresh" content="0; url=../redesocial_turmas">');
        exit();
    endif; ?>
    <!-- Canvas 1 -->
    <?php require 'config_default1.0/ofcanvasCentraldoUsuario.php' ?>
    <script src="config_default1.0/JS_ofcanvasCentraldoUsuario.js"></script>
    </div>
    <!-- Navbar -->
    <?php require 'config_default1.0/config_navBar.php'; ?>
    <section id="listaaulas">
        <div class="container-fluid d-flex justify-content-center mt-4">
            <div class="row w-100">
                <!-- Conteúdo Principal da Aula -->

                <div class="col-md-12">
                    <?php require "config_Atividade1.0/body_depoimento.php" ?>
                </div>


            </div>

        </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <script>
        document.getElementById('btn-assistir').addEventListener('click', function() {
            const video = document.getElementById('video-aula');
            this.style.display = 'none';
            video.style.display = 'block';
            video.play(); // opcional: inicia o vídeo automaticamente
        });
    </script>

    <button id="btnTopo" class="btn btn-primary" onclick="voltarAoTopo()">&#8679;</button>
    <script src="config_default1.0/JS_scroolTop.js?<?= time(); ?>"></script>
    <script src="config_curso1.0/JS_slideDown.js?<?= time(); ?>"></script>
    <!-- Bootstrap JS e dependências -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Função para o botão Sair -->
    <script src="config_default1.0/JS_logoff.js"></script>
    <script>
        function abrirPagina(url) {
            window.open(url, '_self');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
    <?php require 'config_default1.0/link_adm.php'; ?>
</body>

</html>