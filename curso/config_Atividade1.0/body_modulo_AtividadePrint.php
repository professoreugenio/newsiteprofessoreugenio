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
<?php require_once 'config_default1.0/sidebarLateral.php'; ?>
<?php require_once 'config_default1.0/sidebarLateralLicaoatual.php'; ?>
<!-- Conteúdo -->
<main class="container ">
    <div class="row w-100">
        <!-- Conteúdo Principal da Aula -->
        <div class="col-md-11">
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
                        <a class="btn btn-warning btn-sm mt-2 mt-md-0" href="./">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>MÓDULOS
                        </a>
                        <a class="btn btn-success btn-sm mt-2 mt-md-0" onclick="window.location.href='modulo_status.php';">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i>LIÇÕES
                        </a>
                        <?php if ($quantAtv >= 1): ?>
                            <a class="btn btn-roxo btn-sm mt-2 mt-md-0" onclick="window.location.href='actionCurso.php?Atv=<?php echo $QuestInicial; ?>';">
                                <i class="bi bi-eye me-1"></i>ATIVIDADE
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
                <!-- Título da Aula Atual -->
                <div class="text-center border-top pt-2">
                    <h6 class="text-light-50 mb-1">📌 Voltar para:</h6>
                    <h5 class="fw-medium text-light mb-0">
                        <p class="mb-0 fw-medium text-info" style="cursor: pointer;" onclick="window.location.href='modulo_licao.php?<?= time(); ?>';">
                            <i class="bi bi-arrow-return-right me-1"></i> <?= $ordem; ?>. <?= $titulo; ?>
                        </p>
                    </h5>
                </div>
            </div>
            <?php require 'config_Atividade1.0/body_modulo_AtividadeFormPrint.php' ?>
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
<!-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btn = document.getElementById("btliberaLicao");
            const toastEl = document.getElementById("customToast");
            const toastMsg = document.getElementById("toastMsg");
            const toast = new bootstrap.Toast(toastEl);
            function showToast(message, success = true) {
                toastEl.classList.remove("bg-success", "bg-danger");
                toastEl.classList.add(success ? "bg-success" : "bg-danger");
                toastMsg.textContent = message;
                toast.show();
            }
            btn.addEventListener("click", function() {
                const idLicao = this.getAttribute("data-id");
                if (!idLicao) {
                    showToast("ID da lição não encontrado!", false);
                    return;
                }
                fetch("config_curso1.0/liberaLicao.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "id=" + encodeURIComponent(idLicao)
                    })
                    .then(response => response.text())
                    .then(result => {
                        if (result.trim() === "1") {
                            showToast("✅ Lição liberada com sucesso!");
                            setTimeout(() => location.reload(), 2000); // Aguarda 2s e recarrega
                        } else {
                            showToast("❌ Não foi possível liberar a lição.", false);
                        }
                    })
                    .catch(error => {
                        console.error("Erro AJAX:", error);
                        showToast("⚠️ Falha de comunicação com o servidor.", false);
                    });
            });
        });
    </script> -->
<script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script>
<script src="config_curso1.0/JS_liberaLicao.js?<? time(); ?>"></script>
<script src="config_curso1.0/JS_copiarPre.js?<? time(); ?>"></script>
<div id="copyTooltip">✅ Texto copiado!</div>