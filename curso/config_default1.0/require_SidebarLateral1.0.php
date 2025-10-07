<?php $lkwhats ??= '#'; ?>
<?php $quantAtv = $quantAtv ?? '0'; ?>
<?php $aulaLiberada = $aulaLiberada ?? '0'; ?>
<?php $encAula = encrypt($codigoaula . "&" . $codigomodulo . "&" . $aulaLiberada, $action = 'e'); ?>

<!-- BOTÃO FLUTUANTE PARA ABRIR O MENU -->
<button id="btnAbrirSidebar" class="btn btn-emerald shadow rounded-circle position-fixed top-50 end-0 translate-middle-y me-3"
    style="z-index:1050; width:48px; height:48px;">
    <i class="bi bi-list fs-4"></i>
</button>

<!-- BARRA LATERAL DIREITA -->
<div id="sidebarLateral" class="sidebarLateral bg-dark text-light shadow-lg">
    <div class="sidebar-header d-flex justify-content-between align-items-center p-3 border-bottom border-secondary">
        <h5 class="mb-0"><i class="bi bi-menu-button me-2"></i> Menu</h5>
        <button class="btn btn-sm btn-outline-light rounded-circle" id="btnFecharSidebar" aria-label="Fechar menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="nav flex-column p-3 gap-2">

        <!-- Botão sair -->
        <button onclick="sair()" class="btn btn-outline-danger text-start w-100">
            <i class="bi bi-power me-2"></i>Sair
        </button>

        <!-- Condicional: se estiver em módulo/licão -->
        <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
            <?php if ($quantAnexoLicao > 0): ?>
                <button class="btn btn-outline-secondary text-start w-100" data-bs-toggle="modal" data-bs-target="#modalAnexos">
                    <i class="bi bi-paperclip me-2"></i>Anexos da Lição
                </button>
            <?php endif; ?>
            <button onclick="window.location.href='modulo_licao_anexos.php';" class="btn btn-outline-secondary text-start w-100">
                <i class="bi bi-archive me-2"></i>Banco de Anexos
            </button>
        <?php endif; ?>

        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
            <?php if ($quantAtv >= 1): ?>
                <button class="btn btn-outline-secondary text-start w-100"
                    onclick="window.location.href='actionCurso.php?Atv=<?= $QuestInicial ?>'">
                    <i class="bi bi-clipboard-check me-2"></i>Atividades
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Caderno -->
        <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
            <button class="btn btn-outline-secondary text-start w-100" data-bs-toggle="modal" data-bs-target="#modalCaderno">
                <i class="bi bi-journal-text me-2"></i>Caderno de anotações
            </button>
        <?php endif; ?>

        <!-- Módulos -->
        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php'): ?>
            <button onclick="window.location.href='../curso/modulos.php';" class="btn btn-outline-secondary text-start w-100">
                <i class="bi bi-journals me-2"></i>Módulos do Curso
            </button>
        <?php endif; ?>

        <!-- Cursos -->
        <button onclick="window.location.href='../curso/';" class="btn btn-outline-secondary text-start w-100">
            <i class="bi bi-people-fill me-2"></i>Cursos
        </button>
        <?php if ($codigoUser == '1' || $codigoUser == '115507' || $codigoUser == '115488'): ?>
            <button id="btnChaveAfiliado" class="btn btn-success">
                <i class="bi bi-link-45deg me-1"></i> Afiliados
            </button>
        <?php endif; ?>
        <!-- Cursos -->
        <!-- <button onclick="window.location.href='../curso/';" class="btn btn-outline-secondary text-start w-100">
            <i class="bi bi-people-fill me-2"></i>Cursos
        </button> -->

        <!-- Admin -->

        <!-- Configurações -->
        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php'): ?>
            <button class="btn btn-outline-secondary text-start w-100" data-bs-toggle="modal" data-bs-target="#modalConfiguracoes">
                <i class="bi bi-gear-fill me-2"></i>Configurações*
            </button>
        <?php endif; ?>

        <!-- Configurações -->
        <?php if (basename($_SERVER['PHP_SELF']) == 'modulo_licao.php'): ?>

            <button id="btnPortfolio" class="btn btn-outline-secondary text-start w-100">
                <i class="bi bi-images me-2"></i> Portfólio
            </button>

        <?php endif; ?>
        <!-- Configurações -->




        <!-- Lista tópicos -->
        <?php if (basename($_SERVER['PHP_SELF']) == 'modulo_licao.php'): ?>
            <button id="toggleLicoesBox" class="btn btn-outline-secondary text-start w-100">
                <i class="bi bi-list-check me-2"></i>Tópicos da lição
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- SCRIPT JS PARA ABRIR E FECHAR -->
<script>
    const sidebar = document.getElementById('sidebarLateral');
    const btnAbrir = document.getElementById('btnAbrirSidebar');
    const btnFechar = document.getElementById('btnFecharSidebar');

    btnAbrir.addEventListener('click', () => sidebar.classList.add('is-open'));
    btnFechar.addEventListener('click', () => sidebar.classList.remove('is-open'));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') sidebar.classList.remove('is-open');
    });
</script>

<!-- Modal Caderno -->
<?php require 'config_default1.0/require_ModalAnotacoes1.0.php' ?>

<!-- Modal Configurações -->
<div class="modal fade" id="modalConfiguracoes" tabindex="-1" aria-labelledby="modalConfiguracoesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalConfiguracoesLabel">
                    <i class="bi bi-gear-fill me-2"></i> Configurações
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <a href="configuracoes_perfil.php" class="list-group-item list-group-item-action d-flex align-items-center bg-transparent text-light">
                        <i class="bi bi-person-fill me-2 text-primary"></i> Perfil
                    </a>
                    <a href="configuracoes_foto.php" class="list-group-item list-group-item-action d-flex align-items-center bg-transparent text-light">
                        <i class="bi bi-image-fill me-2 text-success"></i> Foto
                    </a>

                    <a href="<?= $lkwhats; ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center bg-transparent text-light">
                        <i class="bi bi-whatsapp me-2 text-success"></i> Grupo WhatsApp
                    </a>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>