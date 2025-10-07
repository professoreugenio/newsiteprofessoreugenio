<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<?php require_once 'config_default1.0/query_dados.php' ?>
<?php require_once 'config_curso1.0/query_curso.php' ?>
<?php require_once 'config_curso1.0/query_publicacoes2.0.php' ?>
<?php require_once 'config_curso1.0/query_anexos.php' ?>
<?php if (empty($idTurma)) {
    echo ('<meta http-equiv="refresh" content="0; url=turmas.php">');
    exit();
} ?>
<?php require 'config_default1.0/query_turma.php' ?>

<?php
// Helper de escape (se não existir ainda)
if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}

// Busca do vídeo do YouTube vinculado à aula (fallback seguro)
$fetchVideo = [];
$quantVideo = 0;
try {
    if (isset($codigoaula) && $codigoaula) {
        $queryVideo = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic LIMIT 1");
        $queryVideo->bindParam(":idpublic", $codigoaula);
        $queryVideo->execute();
        $fetchVideo = $queryVideo->fetchAll(PDO::FETCH_ASSOC);
        $quantVideo = count($fetchVideo);
    }
} catch (Throwable $e) { /* silencioso */
}

// Dados dinâmicos com fallback
$nmModuloSafe       = isset($nmmodulo) ? h($nmmodulo) : 'Módulo';
$percSafe           = isset($perc) ? (int)$perc : 0;
$totalAssistidas    = isset($totalAssistidas) ? (int)$totalAssistidas : (isset($concluidas) ? (int)$concluidas : 0);
$totalLicoes        = isset($totalLicoes) ? (int)$totalLicoes : (isset($total) ? (int)$total : 0);
$ordemAtualSafe     = isset($ordemAtual) ? h($ordemAtual) : '—';
$tituloSafe         = isset($titulo) ? h($titulo) : 'Título da Lição';
$ytKey              = ($quantVideo >= 1 && !empty($fetchVideo[0]['chavetube_sy'])) ? h($fetchVideo[0]['chavetube_sy']) : 'dQw4w9WgXcQ';
?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lição — Professor Eugênio</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- AOS -->
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <!-- Summernote (lite) -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-h1: #00BB9C;
            --brand-h2: #FF9C00;
            --brand-bg: #112240;
            --brand-text: #ffffff;
            --indigo: #4F46E5;
            --amber: #FF9C00;
            --emerald: #00BB9C;
        }

        * {
            scroll-margin-top: 96px
        }

        body {
            background: var(--brand-bg);
            color: var(--brand-text)
        }

        .navbar {
            background: #0f1d36
        }

        .navbar .nav-link,
        .navbar .navbar-brand {
            color: #fff !important
        }

        .navbar .nav-link:hover {
            opacity: .85
        }

        .navbar-dark .navbar-toggler {
            border-color: rgba(255, 255, 255, .2)
        }

        .navbar-dark .navbar-toggler-icon {
            filter: invert(1)
        }

        h1,
        .h1 {
            color: var(--brand-h1)
        }

        h2,
        .h2 {
            color: var(--brand-h2)
        }

        .badge-soft {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            color: #e2e8f0;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .35rem .6rem;
            border-radius: 999px;
            font-size: .8rem;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(0, 0, 0, .2);
        }

        .progress {
            background: rgba(255, 255, 255, .08);
            height: 10px
        }

        .progress-bar {
            background: var(--brand-h1)
        }

        .section-gap {
            padding-top: 24px;
            padding-bottom: 24px
        }

        .text-muted-2 {
            color: #cbd5e1 !important
        }

        .btn-emerald {
            background: var(--emerald);
            color: #0b1c17;
            border: 0
        }

        .btn-emerald:hover {
            filter: brightness(.95)
        }

        .btn-amber {
            background: var(--amber);
            color: #111;
            border: 0
        }

        .btn-amber:hover {
            filter: brightness(.95)
        }

        .btn-indigo {
            background: var(--indigo);
            color: #fff;
            border: 0
        }

        .btn-indigo:hover {
            filter: brightness(1.05)
        }

        .btn-outline-light {
            --bs-btn-color: #e5e7eb;
            --bs-btn-border-color: #e5e7eb;
            --bs-btn-hover-bg: #e5e7eb;
            --bs-btn-hover-color: #0f172a;
            --bs-btn-hover-border-color: #e5e7eb;
        }

        .card {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .08);
            backdrop-filter: blur(2px);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
            border-color: rgba(255, 255, 255, .16);
        }

        /* Player 16:9 */
        .ratio-16x9 {
            position: relative;
            width: 100%;
            padding-top: 56.25%;
            border-radius: 1rem;
            overflow: hidden
        }

        .ratio-16x9 iframe,
        .ratio-16x9 .yt-thumb {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0
        }

        /* Barra fixa inferior (65%) */
        .bottom-bar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(17, 34, 64, .65);
            backdrop-filter: blur(6px);
            border-top: 1px solid rgba(255, 255, 255, .08);
            z-index: 1030;
        }

        /* Voltar ao topo */
        #btnTop {
            position: fixed;
            right: 16px;
            bottom: 88px;
            opacity: 0;
            visibility: hidden;
            transition: .25s ease;
            z-index: 1030;
        }

        #btnTop.show {
            opacity: 1;
            visibility: visible
        }

        /* Print */
        @media print {

            .navbar,
            .bottom-bar,
            #btnTop {
                display: none !important
            }

            a[href]:after {
                content: "" !important
            }

            body {
                background: #fff;
                color: #000
            }
        }
    </style>
</head>

<body>

    <!-- Toast Bootstrap (reutilizável) -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1100;">
        <div id="customToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <!-- NAVBAR (padrão dark) -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="https://professoreugenio.com/fotos/usuarios/usuario.png" alt="Foto do aluno" width="36" height="36" class="rounded-circle border border-2 border-opacity-25">
                <span class="fw-semibold"><?= isset($nomeAluno) ? h($nomeAluno) : 'Aluno' ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nv"><span class="navbar-toggler-icon"></span></button>
            <div id="nv" class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-mortarboard me-1"></i>Curso</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="bi bi-chat-dots me-1"></i>Contato</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="#"><i class="bi bi-x-circle me-1"></i>Fechar</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HEADER: Módulo + Progresso -->
    <header class="container section-gap">
        <div class="row align-items-center g-3">
            <div class="col-lg-8" data-aos="fade-right">
                <h1 class="h3 mb-2"><?= $nmModuloSafe ?></h1>
                <div class="d-flex align-items-center gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Progresso do curso</span>
                            <span><strong id="pctLabel"><?= $percSafe ?>%</strong></span>
                        </div>
                        <div class="progress rounded-pill">
                            <div class="progress-bar" id="pctBar" style="width:<?= $percSafe ?>%" aria-valuenow="<?= $percSafe ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <span class="chip"><i class="bi bi-collection-play"></i> <?= $totalAssistidas ?> de <?= $totalLicoes ?> lições</span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <div class="btn-group">
                    <a href="./modulos.php" class="btn btn-emerald"><i class="bi bi-grid-3x3-gap me-1"></i>Módulos</a>
                    <a href="#texto-aula" class="btn btn-outline-light link-anchor"><i class="bi bi-card-text me-1"></i>Ver texto da aula</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Título da Lição + Ações -->
    <section class="container section-gap pt-0">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge badge-soft px-3 py-2 fs-6">Lição <strong><?= $ordemAtualSafe ?></strong></span>
                    <h2 class="h4 m-0"><?= $tituloSafe ?></h2>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-up" data-aos-delay="50">
                <div class="btn-group" id="grupoAcoes">
                    <a href="modulo_status.php" class="btn btn-amber"><i class="bi bi-list-ol me-1"></i>Lições</a>
                    <?php if (!empty($quantAtv) && $quantAtv >= 1): ?>
                        <a href="actionCurso.php?Atv=<?= urlencode($QuestInicial ?? '') ?>" class="btn btn-indigo"><i class="bi bi-clipboard-check me-1"></i>Questionário</a>
                    <?php endif; ?>
                    <button class="btn btn-outline-light" id="btnPrint"><i class="bi bi-printer me-1"></i>Print</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Vídeo (YouTube) -->
    <section class="container section-gap">
        <div class="card rounded-4 p-3" data-aos="zoom-in">
            <div class="ratio-16x9">
                <iframe
                    src="https://www.youtube.com/embed/<?= $ytKey ?>"
                    title="Aula — YouTube player"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen></iframe>
            </div>
        </div>
    </section>

    <!-- Texto da Aula -->
    <section class="container section-gap" id="texto-aula">
        <div class="card rounded-4 p-4" data-aos="fade-up">
            <h3 class="h5 mb-3">Conteúdo da aula</h3>
            <div class="text-muted-2">
                <?php
                // Render do texto salvo (permitindo HTML controlado)
                if (!empty($texto)) {
                    echo $texto;
                } else {
                    echo '<p>O conteúdo desta aula será disponibilizado em breve.</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Voltar ao Topo -->
    <button id="btnTop" class="btn btn-outline-light rounded-circle p-3" aria-label="Voltar ao topo" title="Voltar ao topo">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bottom Bar fixa -->
    <div class="bottom-bar py-2">
        <div class="container d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2" id="botoesNavegacao">
                <?php if (!empty($codigoAnterior)): ?>
                    <a class="btn btn-outline-light px-3" href="actionCurso.php?lc=<?= urlencode($encAnt ?? '') ?>">
                        <i class="bi bi-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                <?php if (!empty($codigoProxima)): ?>
                    <a class="btn btn-emerald px-3" href="actionCurso.php?lc=<?= urlencode($encProx ?? '') ?>">
                        Próxima <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-amber" data-bs-toggle="modal" data-bs-target="#modalCaderno">
                    <i class="bi bi-journal-text me-1"></i>Caderno
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Caderno (Summernote) -->
    <div class="modal fade" id="modalCaderno" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-journal-text me-2"></i>Meu caderno da aula</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <textarea id="cadernoEditor"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-light" data-bs-dismiss="modal">Fechar</button>
                    <button class="btn btn-emerald" id="btnSalvarCaderno"><i class="bi bi-save me-1"></i>Salvar anotações</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="container section-gap" style="padding-bottom:96px">
        <div class="text-center small text-muted-2">
            © <span id="ano"></span> Professor Eugênio — Todos os direitos reservados.
        </div>
    </footer>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
        AOS.init({
            once: true,
            duration: 700,
            easing: 'ease-out-cubic'
        });
        document.getElementById('ano').textContent = new Date().getFullYear();

        // Utilitário de Toast
        const toastEl = document.getElementById('customToast');
        const toastMsg = document.getElementById('toastMsg');
        const toastBS = bootstrap.Toast.getOrCreateInstance(toastEl, {
            delay: 2200
        });

        function showToast(message, success = true) {
            toastEl.classList.remove('bg-success', 'bg-danger');
            toastEl.classList.add(success ? 'bg-success' : 'bg-danger');
            toastMsg.textContent = message;
            toastBS.show();
        }

        // Print
        document.getElementById('btnPrint')?.addEventListener('click', () => window.print());

        // Voltar ao topo
        const btnTop = document.getElementById('btnTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 280) btnTop.classList.add('show');
            else btnTop.classList.remove('show');
        });
        btnTop.addEventListener('click', () => window.scrollTo({
            top: 0,
            behavior: 'smooth'
        }));

        // Smooth scroll âncoras (.link-anchor)
        document.addEventListener("DOMContentLoaded", function() {
            const anchorLinks = document.querySelectorAll('a.link-anchor[href^="#"]');
            const headerOffset = 80;
            anchorLinks.forEach(link => {
                link.addEventListener("click", function(e) {
                    e.preventDefault();
                    const id = this.getAttribute("href").substring(1);
                    const t = document.getElementById(id);
                    if (!t) return;
                    const y = t.getBoundingClientRect().top + window.scrollY - headerOffset;
                    window.scrollTo({
                        top: y,
                        behavior: "smooth"
                    });
                });
            });
        });

        // Summernote (init ao abrir modal p/ render correto)
        const modalCaderno = document.getElementById('modalCaderno');

        function initSummernote() {
            const el = document.getElementById('cadernoEditor');
            if (!el) return;
            $(el).summernote({
                placeholder: 'Escreva suas anotações da aula aqui...',
                tabsize: 2,
                height: 280,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['undo', 'redo', 'fullscreen']]
                ]
            });
        }
        modalCaderno.addEventListener('shown.bs.modal', initSummernote);

        // Salvar Caderno (AJAX stub) — ajuste URL e payload conforme seu backend
        document.getElementById('btnSalvarCaderno')?.addEventListener('click', () => {
            const html = $('#cadernoEditor').summernote('code');
            // Exemplo de envio:
            // fetch('licoesv1.0/ajax_salvarCaderno.php', {
            //   method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            //   body: new URLSearchParams({ idusuario:'<?= (int)($codigoUser ?? 0) ?>', idlicao:'<?= (int)($codigoaula ?? 0) ?>', texto: html })
            // }).then(r=>r.text()).then(tx=>{
            //   if(tx.trim()==='1') showToast('✅ Anotações salvas!');
            //   else showToast('❌ Não foi possível salvar.', false);
            // }).catch(()=> showToast('⚠️ Falha de comunicação.', false));
            showToast('✅ Anotações salvas!'); // temporário
        });

        // Botões de navegação com spinner até carregar
        document.querySelectorAll('#botoesNavegacao a, #grupoAcoes a.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Apenas links reais (ignora âncoras internas)
                const href = this.getAttribute('href') || '';
                if (href.startsWith('#')) return;
                e.preventDefault();
                const original = this.innerHTML;
                this.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Carregando...`;
                this.classList.add('disabled');
                window.location.href = href;
            });
        });
    </script>

    <!--
    INTEGRAÇÃO (PHP):
    - Cabeçalho:
      • $nmmodulo, $perc, $totalAssistidas, $totalLicoes
    - Lição:
      • $ordemAtual, $titulo
    - Vídeo:
      • new_sistema_youtube_PJA (chave em $ytKey)
    - Texto:
      • $texto (HTML seguro)
    - Navegação:
      • $codigoAnterior/$encAnt | $codigoProxima/$encProx
    - Caderno:
      • Ajustar endpoint de salvamento via AJAX
  -->
</body>

</html>