<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require 'config_curso1.0/query_dados.php' ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso on-line Master Class</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="config_default1.0/Css_config_redesocial.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="v2.0/Css_licoes.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_licoes/CSS_slideDown.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_redesocial/Css_config_loader.css">
    <link rel="stylesheet" href="config_default1.0/Css_ofcanvas.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_turmas1.0/CSS_turmas.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_turmas1.0/CSS_turmas2.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/Css_dropdownsModulos.css">
    <link rel="stylesheet" href="config_default1.0/CSS_linkAdmin.css?time=<?php echo time(); ?>">
    <link rel="stylesheet" href="../mycss/anuncio.css">
    <style>
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <style>
        @media (max-width: 767.98px) {
            .col-lg-3 {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }
        }
    </style>
    <style>
        .blinking-text {
            animation: blink 1s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.2;
            }
        }
    </style>
   
</head>

<body style="margin-top: 0px;">
    
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">Minhas Turmas</a>
        </div>
    </nav>
    <section id="Corpo" class="bg-dark">
        <div class="container text-center">
            <!-- Saudação do Usuário -->
            <div>
                <!-- Texto de acolhimento -->
                <div class="texto-acolhimento">
                    <h3><?php echo $saudacao; ?> <?php echo $nmUser; ?>, seja bem-vindo de volta!</h3>
                    <p>Escolha abaixo uma das suas turmas para continuar seus estudos.</p>
                </div>
                <p>
                    <?php if (!empty($_COOKIE['startusuario'])) {
                        $dec = encrypt($_COOKIE['startusuario'], $action = 'd');
                        $expuser = explode("&", $dec);
                        $nome = $expuser[1];
                        $idturma = $expuser[4];
                    }
                    if (!empty($_COOKIE['adminstart'])) {
                        $dec = encrypt($_COOKIE['adminstart'], $action = 'd');
                        $expuser = explode("&", $dec);
                        $nome = $expuser[1];
                        $idturma = $expuser[4];
                    } ?>
                </p>
            </div>
            <!-- Listagem de Turmas -->
            <div class="container">
                <div class="cards-container">
                    <?php if ($codigoUser == 1): ?>
                        <?php require 'config_curso1.0/Lista_turmas3.0.php'; ?>
                    <?php else: ?>
                        <?php require 'config_curso1.0/Lista_turmas3.0.php'; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <script src="config_turmas1.0/JS_accessturma.js"></script>
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
    <script>
        // Abrir canvas
        const buttons = document.querySelectorAll('.btn-canvas');
        const canvases = document.querySelectorAll('.canvas');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-target');
                document.getElementById(target).classList.add('active');
            });
        });
        // Fechar canvas
        const closeButtons = document.querySelectorAll('.close-btn-canvas');
        closeButtons.forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                const target = closeBtn.getAttribute('data-close');
                document.getElementById(target).classList.remove('active');
            });
        });
    </script>
</body>

</html>