<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>
<?php $quantAnexo = "0"; ?>
<?php require 'config_default1.0/sidebarLateral.php'; ?>
<?php require 'v2.0/sidebarLateralLicoes.php'; ?>
<!-- Conteúdo -->
<main class="container ">
    <div class="row w-100">
        <!-- Conteúdo Principal da Aula -->
        <div class="col-md-12">
            <div id="cabecalhoAulas" class="p-3 bg-dark text-light rounded-3 shadow-sm mb-3 border border-secondary">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                    <!-- Informações da Turma e Módulo -->
                    <div>
                        <h5 class="fw-semibold text-white mb-1">
                            <?= $nmmodulo; ?>
                            <span class="badge <?= $corBarra ?> ms-2"><?= $perc; ?>%</span>
                            <?= $codigocurso ?>
                        </h5>
                    </div>
                    <!-- Botão de voltar para Módulos -->
                    <div>
                        <a class="btn btn-warning btn-sm mt-2 mt-md-0" href="./">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> + MÓDULOS
                        </a>
                        <a class="btn btn-success btn-sm mt-2 mt-md-0" onclick="window.location.href='modulo_status.php';">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> + LIÇÕES
                        </a>
                    </div>
                </div>
                <!-- Título da Aula Atual -->
                <?php
                $queryVideo = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic ");
                $queryVideo->bindParam(":idpublic", $codigoaula);
                $queryVideo->execute();
                $fetchVideo = $queryVideo->fetchALL();
                $quantVideo = count($fetchVideo);
                ?>
                <div class="text-center border-top pt-2">
                    <h6 class="text-light-50 mb-1">📌 Voltar para:</h6>
                    <h5 class="fw-medium text-light mb-0">
                        <p class="mb-0 fw-medium text-info" style="cursor: pointer;" onclick="window.location.href='modulo_licao.php?<?= time(); ?>';">
                            <i class="bi bi-arrow-return-right me-1"></i> <?= $ordem; ?>. <?= $titulo; ?>
                        </p>
                    </h5>
                    <h5>Seguem abaixo as atividades desta lição! <?= $codigomodulo ?></h5>
                </div>
            </div>

            <?php $dec = encrypt($_COOKIE['nav'], $action = 'd'); ?>
            <?php

            ?>
            <?php if ($codigoUser == 1):
                require 'config_Atividade1.0/curso_ModuloListaAlunosAtividades0.3.php';

            else:
                if ($quantQenviadas < $quantAtv) {
                    require 'config_Atividade1.0/questionario.php';
                } else {
                    require 'config_Atividade1.0/curso_modulo_ViewAtividadesRespondidas4.0.php';
                }
            endif; ?>
        </div>
    </div>
</main>
<!-- Rodapé -->
<?php require 'v2.0/footer.php'; ?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<script>
    function abrirPagina(url) {
        window.open(url, '_self');
    }
</script>
<button id="btnTopo" class="btn btn-primary" onclick="voltarAoTopo()">&#8679;</button>
<script src="config_default1.0/JS_scroolTop.js?<?= time(); ?>"></script>
<script src="regixv3.0/registraacessos.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
<script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
<script src="config_aulas1.0/JS_listatopicosView2.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_tooltips.js"></script>
<script>
    function enviarResposta() {
        // Botões e Loading
        var $btnEnviar = $('#btnEnviar');
        var originalHtml = $btnEnviar.html();
        $btnEnviar.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
        $btnEnviar.prop('disabled', true);
        // Coleta de dados
        var ordem = $('#ordem').val();
        var respostaTexto = $('#respostaAluno').val(); // textarea
        var respostaRadio = $('input[name="quest"]:checked').val(); // radio
        // Prioriza radio se existir, senão textarea
        var resposta = respostaRadio ? respostaRadio : respostaTexto;
        $.ajax({
            type: 'POST',
            url: 'config_Atividades/AtividadeInsert.php',
            data: {
                resposta: resposta,
                ordem: ordem
            },
            success: function(response) {
                showToast('✅ Sua resposta foi enviada para avaliação do professor.');
                $('#atividade').load('config_Atividades/AtividadeLoad.php');
                // Oculta o botão enviar e mostra o botão próxima
                $btnEnviar.hide();
                $('#btnProxima').show();
            },
            error: function() {
                showToast('❌ Erro ao enviar a atividade. Tente novamente.', true);
            },
            complete: function() {
                $btnEnviar.prop('disabled', false).html(originalHtml);
            }
        });
    }

    function showToast(mensagem, erro = false) {
        const toastClass = erro ? 'bg-danger' : 'bg-success';
        const toast = $(`
            <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true"
                style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        ${mensagem}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        // Remove toast após 5 segundos
        setTimeout(() => toast.remove(), 5000);
    }
</script>
<!-- AOS Animate On Scroll -->
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();
</script>
<div class="position-fixed top-0 start-50 translate-middle-x mt-3 p-3" style="z-index: 9999; width: 100%; max-width: 500px;">
    <div id="toastQuestionario" class="toast text-white bg-danger border-0 w-100 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">Mensagem</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>
<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>