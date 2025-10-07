<?php $paginaAtual = basename($_SERVER['PHP_SELF']); ?>

<?php
if ($paginaAtual != 'cursos_publicacoes.php' && $paginaAtual != 'cursos_publicacoesNovo.php'):
    if (!isset($_GET['pub']) || empty($_GET['pub'])) {
        $pub = $_GET['pub'];
    } else {
        $pub = $_GET['pub'];
    }
endif;
?>

<!-- Botão flutuante (mostrado sempre) -->
<button id="toggleNavBtn"
    type="button"
    class="btn btn-primary shadow rounded-start-pill d-flex align-items-center gap-2"
    aria-controls="slideNav"
    aria-expanded="false"
    title="Abrir navegação">
    <i class="bi bi-chevron-left"></i>
    <span class="d-none d-md-inline">*</span>
</button>

<!-- Overlay para clicar fora e fechar -->
<div id="slideNavOverlay" hidden></div>

<!-- Painel deslizante -->
<aside id="slideNav" class="shadow">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- <h6 class="mb-0">Navegação</h6> -->
        <button type="button" class="btn-close" aria-label="Fechar" id="closeSlideNavBtn"></button>
    </div>

    <nav aria-label="Navegação de edição de curso">
        <ul class="pagination pagination-vertical w-100">

            <?php
            // Grupos de rotas que pertencem à mesma navegação
            $grupoNav = [
                'editar'        => ['cursos_publicacaoEditar.php'],
                'questionario'  => ['cursos_publicacaoQuestionario.php', 'cursos_publicacaoQuestionarioView.php', 'cursos_publicacaoQuestionarioNovo.php'],
                'editar_texto'  => ['cursos_publicacaoEditarTexto.php'],
                'fotos'         => ['cursos_publicacaoFotos.php'],
                'youtube'         => ['cursos_publicacaoYoutube.php'],
                'video'         => ['cursos_publicacaoVideo.php'],
                'anexos'        => ['cursos_publicacaoAnexos.php'],
            ];

            // Verifica se deve exibir a navegação (se a página atual pertence a QUALQUER grupo)
            $exibirNav = false;
            foreach ($grupoNav as $pages) {
                if (in_array($paginaAtual, $pages, true)) {
                    $exibirNav = true;
                    break;
                }
            }

            if ($exibirNav):
                // Flags de ativo para cada link
                $isAtivoEditar       = in_array($paginaAtual, $grupoNav['editar'], true);
                $isAtivoQuestionario = in_array($paginaAtual, $grupoNav['questionario'], true);
                $isAtivoEditarTexto  = in_array($paginaAtual, $grupoNav['editar_texto'], true);
                $isAtivoFotos        = in_array($paginaAtual, $grupoNav['fotos'], true);
                $isAtivoAnexos       = in_array($paginaAtual, $grupoNav['anexos'], true);
            ?>

                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoEditar.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoEditar.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Editar Publicacao">
                        <i class="bi bi-pencil-square"></i> *
                    </a>
                </li>



                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoEditarTexto.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoEditarTexto.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Editar texto da Publicacao">
                        <i class="bi bi-file-text"></i> *
                    </a>
                </li>

                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoQuestionario.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoQuestionario.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Questionários">
                        <i class="bi bi-ui-checks"></i>
                    </a>
                </li>

                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoFotos.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoFotos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Lista módulos">
                        <i class="bi bi-camera text-default"></i>
                    </a>
                </li>

                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoYoutube.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoYoutube.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Lista Youtube">
                        <i class="bi bi-youtube text-danger"></i>
                    </a>
                </li>
<li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoVideo.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoVideo.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Lista Video">
                        <i class="bi bi-camera-video text-primary"></i>
                    </a>
                </li>

                <li class="page-item">
                    <a class="page-link <?= $paginaAtual == 'cursos_publicacaoAnexos.php' ? 'bglaranja' : '' ?>"
                        href="cursos_publicacaoAnexos.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>&pub=<?= $pub ?>"
                        title="Anexos da Publicação">
                        <i class="bi bi-paperclip text-default"></i>
                    </a>
                </li>

            <?php endif; ?>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_publicacoes.php' ? 'bglaranja' : '' ?>"
                    href="cursos_publicacoes.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
                    title="Publicacoes do Módulo">
                    <i class="bi bi-list"></i> *
                </a>
            </li>

            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_turmas.php' ? 'bglaranja' : '' ?>"
                    href="cursos_turmas.php?id=<?= $_GET['id'] ?>" title="Lista Turmas">
                    <i class="bi bi-mortarboard"></i>
                </a>
            </li>
            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_modulos.php' ? 'bglaranja' : '' ?>"
                    href="cursos_modulos.php?id=<?= $_GET['id'] ?>" title="Módulos">
                    <i class="bi bi-diagram-3 text-default"></i>
                </a>
            </li>

            <li class="page-item">
                <a class="page-link <?= $paginaAtual == 'cursos_publicacoesNovo.php' ? 'bglaranja' : 'bgverde' ?>"
                    href="cursos_publicacoesNovo.php?id=<?= $_GET['id'] ?>&md=<?= $_GET['md'] ?>"
                    title="Nova Publicação">
                    <i class="bi bi-file-text"></i> +
                </a>
            </li>

        </ul>
    </nav>

</aside>

<style>
    /* Painel deslizante */
    #slideNav {
        position: fixed;
        top: 0;
        right: 0;
        height: 100vh;
        width: 80px;
        max-width: 90vw;
        background: #fff;
        border-left: 1px solid rgba(0, 0, 0, .1);
        transform: translateX(100%);
        transition: transform .28s ease-in-out;
        z-index: 1061;
        padding: 16px;
    }

    #slideNav.show {
        transform: translateX(0);
    }

    /* Overlay */
    #slideNavOverlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .25);
        z-index: 1055;
    }

    /* Botão flutuante na lateral direita e centro vertical */
    #toggleNavBtn {
        position: fixed;
        top: 50%;
        right: 0;
        transform: translate(50%, -50%);
        /* metade pra fora da tela p/ parecer uma “aba” */
        z-index: 1061;
        padding: .5rem .75rem;
    }

    /* Ajustes menores */
    @media (max-width: 576px) {
        #toggleNavBtn span {
            display: none;
        }

        /* esconde o texto no mobile */
    }


    /* Pagination vertical dentro do slideNav */
    #slideNav .pagination.pagination-vertical {
        display: flex;
        flex-direction: column;
        gap: .25rem;
        /* espaçamento entre botões */
        align-items: stretch;
        /* ocupa toda a largura */
        margin: 0;
    }

    #slideNav .pagination.pagination-vertical .page-item {
        display: block;
    }

    #slideNav .pagination.pagination-vertical .page-link {
        width: 100%;
        text-align: center;
        border-radius: .5rem;
        padding: .6rem .75rem;
    }

    /* Opcional: remove “setas” de paginação padrão do BS */
    #slideNav .pagination.pagination-vertical .page-item:first-child .page-link,
    #slideNav .pagination.pagination-vertical .page-item:last-child .page-link {
        border-radius: .5rem;
    }
</style>

<script>
    (function() {
        const slideNav = document.getElementById('slideNav');
        const toggleBtn = document.getElementById('toggleNavBtn');
        const closeBtn = document.getElementById('closeSlideNavBtn');
        const overlay = document.getElementById('slideNavOverlay');

        function openNav() {
            slideNav.classList.add('show');
            overlay.hidden = false;
            toggleBtn.setAttribute('aria-expanded', 'true');
        }

        function closeNav() {
            slideNav.classList.remove('show');
            overlay.hidden = true;
            toggleBtn.setAttribute('aria-expanded', 'false');
        }

        // Eventos
        toggleBtn.addEventListener('click', openNav);
        closeBtn.addEventListener('click', closeNav);
        overlay.addEventListener('click', closeNav);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeNav();
        });
    })();
</script>