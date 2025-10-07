<!-- Link do CSS novo -->

<?php $quantAtv = $quantAtv ?? '0' ?>
<?php $aulaLiberada = $aulaLiberada ?? '0' ?>
<!-- BOTÃO FLUTUANTE PARA ABRIR O MENU -->
<button id="btnAbrirSidebar" class="sidebar-toggle-btn btn">
    <i class="bi bi-list"></i>
</button>

<!-- BARRA LATERAL DIREITA -->
<div id="sidebarLateral" class="sidebarLateral">
    <div class="sidebar-header">
        <h5 class="mb-0">Menu</h5>
        <button class="btn btn-sm btn-outline-secondary" id="btnFecharSidebar" aria-label="Fechar menu">
            <i class="bi bi-x"></i>
        </button>
    </div>

    <div class="nav flex-column">
        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php' && basename($_SERVER['PHP_SELF']) != 'configuracoes_perfil.php'): ?>
            <?php $encAula = encrypt($codigoaula . "&" . $codigomodulo . "&" . $aulaLiberada, $action = 'e'); ?>
        <?php endif; ?>

        <button onclick="sair()" class="btn btn-outline-secondary text-start ">
            <i class="bi bi-power text-danger me-2"></i>Sair
        </button>

        <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
            <?php if ($quantAnexoLicao > 0): ?>
                <button class="btn btn-outline-secondary text-start " data-bs-toggle="modal" data-bs-target="#modalAnexos">
                    <i class="bi bi-paperclip me-2"></i>Anexos da Lição
                </button>
            <?php endif; ?>
            <button onclick="window.location.href='modulo_licao_anexos.php';" class="btn btn-outline-secondary text-start ">
                <i class="bi bi-archive me-2"></i>Banco de Anexos
            </button>
        <?php endif; ?>

        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php'): ?>

            <?php if ($quantAtv >= 1): ?>
                <button class="btn btn-outline-secondary text-start " onclick="window.location.href='actionCurso.php?Atv=<?= $QuestInicial ?>'">
                    <i class="bi bi-clipboard-check me-2"></i>Atividades
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Botão abre o modal do caderno -->
        <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
            <button class="btn btn-outline-secondary text-start " data-bs-toggle="modal" data-bs-target="#modalCaderno">
                <i class="bi bi-journal-text me-2"></i>Caderno de anotações
            </button>
        <?php endif; ?>


        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php'): ?>
            <button onclick="window.location.href='../curso/modulos.php';" class="btn btn-outline-secondary text-start ">
                <i class="bi bi-journals me-2"></i>Módulos do Curso
            </button>
        <?php endif; ?>

        <button onclick="window.location.href='../curso/';" class="btn btn-outline-secondary text-start ">
            <i class="bi bi-people-fill me-2"></i>Cursos
        </button>

        <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php' && $codigoUser == 1): ?>
            <button onclick="window.location.href='depoimento.php';" class="btn btn-outline-secondary text-start ">
                <i class="bi bi-chat-dots me-2"></i>Fórum
            </button>
            <button id="btliberaLicao" data-id="<?= $encAula ?>" class="btn btn-outline-secondary text-start ">
                <i class="bi <?= $aulaLiberada == '1' ? 'bi-lock-fill text-success' : 'bi-unlock-fill text-danger' ?> me-2"></i>Liberar Lição
            </button>
            <button id="btpublicalicao"
                data-id="<?= $encAula ?>"
                data-titulo="<?= htmlspecialchars($titulo) ?>"
                data-idturma="<?= $idTurma ?>"
                data-idmodulo="<?= $codigomodulo ?>"
                data-idartigo="<?= $codigoaula ?>"
                data-bs-toggle="modal"
                data-bs-target="#modalPublicaAula"
                class="btn btn-outline-secondary text-start ">
                <i class="bi bi-person-badge-fill me-2"></i>Publicar Lição
            </button>
        <?php endif; ?>

        <!-- <button onclick="window.location.href='configuracoes_perfil.php';" class="btn btn-outline-secondary text-start ">
            <i class="bi bi-gear-fill me-2"></i>Configurações
        </button> -->

        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php'): ?>

            <!-- Botão que abre o modal -->
            <button class="btn btn-outline-secondary text-start" data-bs-toggle="modal" data-bs-target="#modalConfiguracoes">
                <i class="bi bi-gear-fill me-2"></i> Configurações
            </button>

        <?php endif; ?>



        <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php'): ?>


            <button id="toggleLicoesBox" class="btn btn-outline-secondary text-start ">
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

    btnAbrir.addEventListener('click', () => {
        sidebar.classList.add('is-open');
    });

    btnFechar.addEventListener('click', () => {
        sidebar.classList.remove('is-open');
    });

    // (Opcional) Fechar ao apertar ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') sidebar.classList.remove('is-open');
    });
</script>

<!-- Modal Caderno de Anotações -->

<?php require 'config_default1.0/require_ModalAnotacoes1.0.php' ?>

<!-- Modal Configurações -->
<div class="modal fade" id="modalConfiguracoes" tabindex="-1" aria-labelledby="modalConfiguracoesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalConfiguracoesLabel">
                    <i class="bi bi-gear-fill me-2"></i> Configurações
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="list-group">
                    <a href="configuracoes_perfil.php" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-person-fill me-2 text-primary"></i> Perfil
                    </a>
                    <a href="configuracoes_foto.php" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-image-fill me-2 text-success"></i> Foto
                    </a>
                    <a href="configuracoes_sobreocurso.php" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-book-fill me-2 text-warning"></i> Sobre o Curso
                    </a>
                    <a href="<?= $lkwhats; ?>" target="_blank" class="list-group-item list-group-item-action d-flex align-items-center">
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