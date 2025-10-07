<!-- Toast Bootstrap - Notificação -->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1100;">
    <div id="customToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">
                <!-- Mensagem será inserida via JS -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>
<!-- Navbar -->
<?php include 'v2.0/nav.php'; ?>
<?php $quantAnexo = "0"; ?>

<?php require_once 'config_default1.0/sidebarLateralLicaoatual.php'; ?>
<!-- Conteúdo -->
<main class="container ">
    <div class="row w-100">
        <!-- Conteúdo Principal da Aula -->
        <div class="col-md-9">
            <div id="cabecalhoAulas" class="p-3 bg-dark text-light rounded-3 shadow-sm mb-3 border border-secondary">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                    <!-- Informações da Turma e Módulo -->
                    <div>
                        <h5 class="fw-semibold text-white mb-1">
                            <?= $nmmodulo; ?>
                            <span class="badge <?= $corBarra ?> ms-2"><?= $perc; ?>%</span>
                        </h5>
                    </div>
                    <!-- Botão de voltar para Módulos -->
                    <div>
                        <a class="btn btn-warning btn-sm mt-2 mt-md-0" href="./modulos.php">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>MÓDULOS
                        </a>
                        <a class="btn btn-success btn-sm mt-2 mt-md-0" onclick="window.location.href='modulo_status.php';">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>LIÇÕES
                        </a>
                        <?php if ($quantAtv >= 1): ?>
                            <a class="btn btn-roxo btn-sm mt-2 mt-md-0" onclick="window.location.href='actionCurso.php?Atv=<?php echo $QuestInicial; ?>';">
                                <i class="bi bi-eye me-1"></i>QUESTIONÁRIO
                            </a>
                        <?php endif; ?>
                        <a class="btn btn-roxo btn-sm mt-2 mt-md-0" onclick="window.location.href='modulo_AtividadePrint.php';">
                            <i class="bi bi-camera me-1"></i> PRINT
                        </a>
                        <?php if ($codigoUser == 1): ?>
                        <?php endif; ?>
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
                    <h4 class="fw-medium text-light mb-0">
                        <?= $ordemAtual ?? ''; ?>. <?= $titulo; ?>
                    </h4>
                    <?php require 'config_curso1.0/require_CountAulas.php'; ?>
                </div>
            </div>
            <?php if (!empty($codigoUser) && $codigoUser == 1): ?>
                <h5 class="text-center">
                    Comercial <?= $comercialDados; ?> { Curso:<?= $nmCurso; ?> }{ IdTurma:<?= $idTurma; ?> }{ IdCurso: <?= $codigocurso; ?> }{ IdModulo: <?= $codigomodulo; ?> }{ IdAula: <?= $codigoaula; ?> }{ Liberada: <?= $aulaLiberada; ?> }
                </h5>
            <?php endif; ?>
            <?php require_once 'config_aulas1.0/LicaoVideo4.0.php'; ?>
            <p class="dicas">
                👨‍🏫 ANTES DE PRINTAR A ATIVIDADE:
                AJUSTE O ZOOM PARA 30%. USE CTRL + SHIFT + F1. USE WINDOWS + PRINT SCREEN
            </p>
            <div id="curso-corpotexto">
                <?php echo $texto;  ?>
            </div>
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
<!-- <script src="regixv2.0/registraacessos.js?<?= time(); ?>"></script> -->
<script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
<script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
<script src="config_aulas1.0/JS_listatopicosView2.js?<?= time(); ?>"></script>
<script src="config_default1.0/JS_tooltips.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Seleciona todos os links de ancoragem
        const anchorLinks = document.querySelectorAll('a.link-anchor[href^="#"]');
        const headerOffset = 80; // ajuste conforme a altura do seu cabeçalho (navbar)
        // Função para rolar suavemente para o tópico
        anchorLinks.forEach(link => {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                const targetId = this.getAttribute("href").substring(1);
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.scrollY - headerOffset;
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });
                }
            });
        });
    });
</script>


<div class="box-botoes-licao-fixoRodape  gap-2 px-3 py-2">
    <!-- Botões de navegação -->
    <!-- Botões de navegação centralizados lado a lado -->
    <!-- Container para centralizar os dois botões na mesma linha -->
    <div class="d-flex justify-content-center mt-2">
        <div id="botoesNavegacao" class="d-flex gap-3">
            <?php if ($codigoAnterior): ?>
                <a class="btn btn-warning px-2" href="actionCurso.php?lc=<?php echo $encAnt; ?>">
                    <i class="bi bi-arrow-left-circle"></i> ANTERIOR
                </a>
            <?php endif; ?>
            <?php if ($codigoProxima): ?>
                <a title="Conclua a atividade desta lição"
                    class="btn btn-success px-2"
                    href="actionCurso.php?lc=<?php echo $encProx; ?>">
                    PRÓXIMA <i class="bi bi-arrow-right-circle"></i>
                </a>
            <?php endif; ?>
        </div>
        <a style="margin-left: 10px" href="#" class="btn btn-light px-2 ml-2" data-bs-toggle="modal" data-bs-target="#modalCaderno">
            <i class="bi bi-journal-text me-2"></i>
        </a>
        <a style="margin-left: 10px" href="#" class="btn btn-light px-2 ml-2" data-bs-toggle="modal" data-bs-target="#modalDepoimento">
            Depoimento
        </a>
    </div>

    <!-- Loader JS -->
    <script>
        document.querySelectorAll('#botoesNavegacao a').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const originalText = this.innerHTML;
                this.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Carregando...`;
                this.classList.add("disabled");
                window.location.href = this.href;
            });
        });
    </script>
</div>

<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_curso1.0/JS_liberaLicao.js?<? time(); ?>"></script>
<script src="config_curso1.0/JS_copiarPre.js?<? time(); ?>"></script>
<div id="copyTooltip">✅ Texto copiado!</div>