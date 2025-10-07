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
    <link rel="stylesheet" href="config_default1.0/Css_ofcanvas.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_turmas1.0/CSS_turmas.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_turmas1.0/CSS_turmas2.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/Css_dropdownsModulos.css">
    <link rel="stylesheet" href="config_default1.0/CSS_linkAdmin.css?time=<?php echo time(); ?>">
    <link rel="stylesheet" href="../mycss/anuncio.css">
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <?php if (!empty($_COOKIE['startusuario'])) {
        $dec = encrypt($_COOKIE['startusuario'], $action = 'd');
        $expuser = explode("&", $dec);
        $iduser = $expuser[0] ?? '';
        $nome = $expuser[1];
        $idturma = $expuser[4];
    }
    if (!empty($_COOKIE['adminstart'])) {
        $dec = encrypt($_COOKIE['adminstart'], $action = 'd');
        $expuser = explode("&", $dec);
        $iduser = $expuser[0] ?? '';
        $nome = $expuser[1] ?? '';

        echo $codigoUser;
    } ?>

    <?php

    $queryUltimoAcesso = $con->prepare("SELECT * FROM a_site_registraacessos WHERE idusuariora = :idusuario  ORDER BY datara DESC, horara DESC LIMIT 1 ");
    $queryUltimoAcesso->bindParam(":idusuario", $codigoUser);
    // Executa a consulta
    $queryUltimoAcesso->execute();
    $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
    $ultimadata   = isset($rwUltAcesso['datara'])    ? databr($rwUltAcesso['datara'])    : 'Sem registro';
    $ultihorai   = isset($rwUltAcesso['horara'])    ? horabr($rwUltAcesso['horara'])    : 'Sem registro';
    $ultihoraf   = isset($rwUltAcesso['horafinalra'])    ? horabr($rwUltAcesso['horafinalra'])    : 'Sem registro';
    ?>
</head>

<body style="margin-top: 0px;">
    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <!-- Navbar -->
    <?php include 'v2.0/nav.php'; ?>
    <div style="height: 75px;"></div>
    <!-- Cabeçalho: Saudação + Último Acesso -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center mx-auto">
                <header class="mb-4" data-aos="fade-up" aria-labelledby="titulo-saudacao">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8 text-center">
                            <h1 id="titulo-saudacao" class="h4 mb-1">
                                <?= $saudacao ?>, <span class="fw-bold"><?= htmlspecialchars($nmUser ?: 'Aluno') ?></span>! 👋
                            </h1>
                            <p class="mb-0 text-white-50">
                                Aqui estão seus cursos inscritos. Selecione um card abaixo para continuar.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end text-center mt-3 mt-lg-0">
                            <span class="badge px-3 py-2">
                                <i class="bi bi-clock-history me-1"></i>
                                Último acesso ao sistema:
                                <strong id="ultimoAcessoSistema">
                                    <?= $ultimadata ?> <?= $ultihorai ?>
                                </strong>
                            </span>
                        </div>
                    </div>
                </header>
            </div>
        </div>
    </div>


    <section id="Corpo" class="bg-dark">
        <div class="container text-center mt-4">
            <!-- Saudação do Usuário -->



            <!-- Listagem de Turmas -->
            <div class="container">
                <?php if ($codigoUser == 1): ?>
                    <?php require 'config_curso1.0/Lista_turmas1.0.php'; ?>
                <?php else: ?>
                    <?php require 'config_curso1.0/Lista_turmas3.0.php'; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal Boas-Vindas -->


    </section>
    <!-- Modal Boas-Vindas em Tela Cheia -->
    <div class="modal fade modal-hello" id="modalBoasVindas" tabindex="-1" aria-labelledby="modalBoasVindasLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content shadow-lg border-0 rounded-0 overflow-hidden">

                <!-- Cabeçalho -->
                <div class="modal-header border-0 p-4 position-relative bg-dark text-white">
                    <div class="d-flex align-items-center gap-2 w-100 justify-content-center" data-aos="fade-down">
                        <h4 class="modal-title m-0" id="modalBoasVindasLabel">
                            👋 Seja bem-vindo, <strong><?php echo htmlspecialchars($nmUser); ?></strong>!
                        </h4>
                    </div>
                </div>
                <!-- Corpo com vídeo centralizado -->
                <div class="modal-body bg-black d-flex flex-column justify-content-center align-items-center vh-100 p-4">
                    <p>Dicas de como utilizar o sistema </p>

                    <!-- Vídeo responsivo ocupando 50% da altura style="height:50vh; width:auto; max-width:90vw;" -->
                    <div class="ratio ratio-16x9 w-100" style="max-height:70vh;max-width:50vw;" data-aos="zoom-in">
                        <iframe width="100%" height="100%"
                            src="https://www.youtube.com/embed/g3PgAThaCQA?autoplay=0&rel=0"
                            title="Vídeo de Boas Vindas" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>

                    <!-- Botão fechar -->
                    <div class="text-center mt-4" data-aos="fade-up">
                        <button type="button" class="btn btn-light btn-sm px-5 rounded-pill" data-bs-dismiss="modal" aria-label="Fechar">Fechar ✖
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <!-- (garanta que já existam os includes de Bootstrap JS e AOS CSS/JS) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.AOS) AOS.init({
                duration: 700,
                once: true
            });

            // Abrir automaticamente ao carregar (remova se não quiser auto-show)
            const el = document.getElementById('modalBoasVindas');
            if (el && window.bootstrap) {
                const modal = new bootstrap.Modal(el);
                modal.show();
            }
        });
    </script>


    <script src="config_turmas1.0/JS_accessturma.js"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>
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