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

<?php require 'config_curso1.0/require_contlicoes.php'; ?>

<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lição — Professor Eugênio</title>

    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js">
    </script>

    <!-- Bootstrap 5 (se já carrega, mantenha o seu) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons (se já carrega, ignore) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">



    <!-- Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

    <link rel="stylesheet" href="config_curso1.0/CSS_config.css?<?= time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_curso.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/Css_config_redesocial.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/CSS_sidebarLateralv2.0.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_curso1.0/Css_licoes.css?<?php echo time(); ?>">
    <link rel="stylesheet" href="config_default1.0/CSS_modalCaderno.css?<?php echo time(); ?>">

    <style>
        #textoAula h5 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            font-size: 1.25em;
            font-weight: 300;
            border: 2px solid #ff9100ff;
            padding: 0.3em 0.5em;
            color: #ffc107;
            display: inline-block;
            border-radius: 8px;
            /* Cor dourada */
        }


        #textoAula pre {
            border: 2px solid #ff9100ff;
            background-color: #1e1e1e;
            padding: 1em;
            border-radius: 8px;
            overflow-x: auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: larger;
            /* 🔑 Ajustes para quebra de linha automática */
            white-space: pre-wrap;
            /* mantém espaços e permite quebra */
            word-wrap: break-word;
            /* quebra palavras longas */
            overflow-x: auto;
            /* rolagem apenas se for realmente necessário */
        }
    </style>

    <!-- Lightbox2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/css/lightbox.min.css" rel="stylesheet" />

    <!-- Lightbox2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.4/dist/js/lightbox.min.js"></script>


    <style>
        .lightboxOverlay {
            background: rgba(0, 0, 0, 0.95) !important;
        }

        .lightbox .lb-data {
            color: #fff !important;
        }
    </style>
</head>

<body>

    <?php $quantAnexo = "0"; ?>
    <?php require_once 'config_default1.0/sidebarLateral.php'; ?>
    <?php include 'v2.0/nav.php'; ?>
    <?php require_once 'config_default1.0/sidebarLateralLicaoatual.php'; ?>


    <!-- Toast Bootstrap (reutilizável) -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1100;">
        <div id="customToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
            </div>
        </div>
    </div>




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
                    <span class="chip"><i class="bi bi-collection-play"></i> <?= $totalAssistidas ?> de <?= (int)$totalLicoesModulo; ?> lições</span>
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
                    <?php $encIdCurso = $enc = encrypt($codigocurso, $action = 'e'); ?>
                    <?php $encIdModulo = $enc = encrypt($codigomodulo, $action = 'e'); ?>
                    <?php $encIdMTurma = $enc = encrypt($idTurma, $action = 'e'); ?>
                    <?php $encIdAula = $enc = encrypt($codigoaula, $action = 'e'); ?>
                    <?php if ($codigoUser == 1):

                        $url = "../admin2/modelo/cursos_publicacaoEditarTexto.php?";
                        $url2 = "../admin2/modelo/cursos_TurmasAlunos.php?";

                    ?>
                        <a style="color:#ffff00" target="_blank" href="<?= $url ?>id=<?= $encIdCurso ?>&md=<?= $encIdModulo ?>&pub=<?= $encIdAula ?>">
                            <i class="bi bi-pencil-square me-1"></i>
                        </a>
                        <a style="color:#ff8040" target="_blank" href="<?= $url2 ?>id=<?= $encIdCurso ?>&tm=<?= $encIdMTurma ?>"> Turma <i class="bi bi-pencil-square me-1"></i></a>

                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-up" data-aos-delay="50">
                <div class="btn-group" id="grupoAcoes">
                    <a href="modulo_status.php" class="btn btn-amber"><i class="bi bi-list-ol me-1"></i>Lições</a>
                    <?php if (!empty($quantAtv) && $quantAtv >= 1): ?>
                        <a href="actionCurso.php?Atv=<?= urlencode($QuestInicial ?? '') ?>" class="btn btn-indigo"><i class="bi bi-clipboard-check me-1"></i>Questionário</a>
                    <?php endif; ?>
                    <button class="btn btn-roxo btn-sm" onclick="window.location.href='modulo_AtividadePrint.php';"><i class="bi bi-printer me-1"></i>Print</button>
                </div>
            </div>
        </div>
    </section>

    <?php require_once 'config_aulas1.0/LicaoVideo5.0.php'; ?>

    <!-- Texto da Aula -->
    <section class="container section-gap" id="texto-aula">
        <div class="card rounded-4 p-4" data-aos="fade-up">
            <h3 class="h5 mb-3">Conteúdo da aula</h3>
            <div class="text-muted-2" id="textoAula">
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
    <?php require 'config_aulas1.0/require_bottombar.php' ?>

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
    <?php require 'config_aulas1.0/require_ModalDepoimento.php'; ?>
    <!-- Rodapé -->
    <footer class="container section-gap">
        <div class="text-center small text-muted-2">
            © <span id="ano"></span> Professor Eugênio — Todos os direitos reservados.
        </div>
    </footer>
    <div style="height: 100px;">&nbsp</div>

    <!-- MÓDULO BUSCA -->

    <!-- MODAL DE BUSCA -->
    <div class="modal fade" id="modalBuscaLicao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content busca-elegante rounded-4 shadow-lg border-0 overflow-hidden">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title m-0">Buscar conteúdo</h5>
                        <small class="text-muted">Título, olho, tag ou texto</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body pt-3">
                    <form id="formBuscaLicao" class="position-relative" action="modulos_buscalicao.php" method="get">
                        <label for="modal_q" class="visually-hidden">Buscar</label>
                        <input
                            id="modal_q"
                            type="search"
                            name="q"
                            class="form-control form-control-lg rounded-pill ps-3 pe-5 busca-input"
                            placeholder="Digite o que deseja encontrar…"
                            aria-label="Buscar"
                            autocomplete="off"
                            required>
                        <!-- Lupa transparente 'dentro' do input -->
                        <button class="btn btn-icon position-absolute top-50 end-0 translate-middle-y me-2" type="submit" title="Buscar" aria-label="Buscar">
                            <i class="bi bi-search fs-5"></i>
                        </button>
                    </form>
                    <div class="form-text mt-2">
                        Dica: <kbd>Ctrl</kbd>+<kbd>K</kbd> (ou <kbd>⌘</kbd>+<kbd>K</kbd>) abre a busca. <kbd>Esc</kbd> limpa o campo.
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">
                        Fechar
                    </button>
                    <button form="formBuscaLicao" type="submit" class="btn btn-success rounded-pill px-3">
                        Pesquisar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilo escopado ao modal */
        #modalBuscaLicao .modal-content.busca-elegante {
            --brand-1: #00BB9C;
            --brand-2: #FF9C00;
            --bg: #0b1220;
            --card: #112240;
            --line: rgba(255, 255, 255, .08);
            background: radial-gradient(1000px 400px at -10% -10%, rgba(255, 255, 255, .06), transparent 40%), var(--card);
            border: 1px solid var(--line);
        }

        #modalBuscaLicao .modal-title {
            color: var(--brand-1);
            letter-spacing: .2px;
        }

        #modalBuscaLicao .busca-input {
            background: #0b1220;
            color: #e2e8f0;
            border: 1px solid var(--line);
            box-shadow: none;
            transition: border-color .2s ease, box-shadow .2s ease;
            height: 48px;
        }

        #modalBuscaLicao .busca-input::placeholder {
            color: #7c8896;
        }

        #modalBuscaLicao .busca-input:focus {
            border-color: var(--brand-1);
            box-shadow: 0 0 0 .2rem rgba(0, 187, 156, .15);
        }

        #modalBuscaLicao .btn-icon {
            background: transparent;
            border: 0;
            color: #9aa4b2;
            padding: .25rem .4rem;
            line-height: 1;
        }

        #modalBuscaLicao .btn-icon:hover {
            color: #e2e8f0;
        }

        /* leve blur no backdrop (opcional) */
        .modal-backdrop.show {
            backdrop-filter: blur(2px);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnBuscaLicao');
            const el = document.getElementById('modalBuscaLicao');
            const input = document.getElementById('modal_q');

            if (btn && el && window.bootstrap) {
                // remover o comportamento antigo (window.open do onclick inline)
                btn.onclick = null;

                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const modal = bootstrap.Modal.getOrCreateInstance(el);
                    modal.show();
                    setTimeout(() => input && input.focus(), 200);
                });
            }

            // atalhos de teclado: Ctrl/⌘+K abre e foca; ESC limpa
            document.addEventListener('keydown', (e) => {
                const isCtrlK = (e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k';
                if (isCtrlK) {
                    e.preventDefault();
                    if (el && window.bootstrap) {
                        const modal = bootstrap.Modal.getOrCreateInstance(el);
                        modal.show();
                        setTimeout(() => input && (input.focus(), input.select()), 200);
                    }
                }
            });

            if (input) {
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') input.value = '';
                });
            }
        });
    </script>



    <!-- PORTFOLIO -->

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnPortfolio');
            if (!btn) return;

            btn.addEventListener('click', async () => {
                try {
                    const formData = new FormData();
                    formData.append('action', 'check');

                    const res = await fetch('config_Portfolio1.0/ajax_portfolioCheckCreate.php', {
                        method: 'POST',
                        body: formData,
                        credentials: 'include'
                    });
                    const json = await res.json();

                    if (json.ok && json.hasPortfolio) {
                        window.location.href = 'portfolio.php';
                        return;
                    }

                    // Não tem portfolio -> perguntar
                    const querCriar = confirm('Você ainda não possui chave de Portfólio. Deseja criar agora?');
                    if (!querCriar) return;

                    const formCreate = new FormData();
                    formCreate.append('action', 'create');

                    const resCreate = await fetch('config_Portfolio1.0/ajax_portfolioCheckCreate.php', {
                        method: 'POST',
                        body: formCreate,
                        credentials: 'include'
                    });
                    const j2 = await resCreate.json();

                    if (j2.ok) {
                        // opcional: alert(`Chave criada: ${j2.chave}`);
                        window.location.href = 'portfolio.php';
                    } else {
                        alert(j2.msg || 'Não foi possível criar o portfólio.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Falha ao acessar o Portfólio.');
                }
            });
        });
    </script>

    <!-- FIM MÓDULO BUSCA -->

    <!-- JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            duration: 700,
            easing: 'ease-out-cubic'
        });
        document.getElementById('ano').textContent = new Date().getFullYear();
        // Progresso vindo do backend já aplicado na barra/label
    </script>

    <?php require 'afiliadosv1.0/require_ModalAfiliado.php'; ?>

    <button id="btnTopo" class="btn btn-primary" onclick="voltarAoTopo()">&#8679;</button>
    <script src="config_default1.0/JS_scroolTop.js?<?= time(); ?>"></script>


    <?php require 'config_aulas1.0/require_javascript.html'; ?>

    <script src="config_turmas1.0/JS_accessturma.js"></script>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>
    <!-- <script src="regixv2.0/acessopaginas.js?<?= time(); ?>"></script> -->
    <script src="config_curso1.0/JS_liberaLicao.js?<? time(); ?>"></script>
    <script src="config_curso1.0/JS_copiarPre.js?<? time(); ?>"></script>
    <div id="copyTooltip">✅ Texto copiado!</div>


    <script src="config_default1.0/JS_logoff.js?<?= time(); ?>"></script>
    <script src="config_aulas1.0/JS_sidebarLateral.js?<?= time(); ?>"></script>
    <script src="config_aulas1.0/JS_listatopicosView2.js?<?= time(); ?>"></script>
    <script src="config_default1.0/JS_tooltips.js"></script>






</body>

</html>