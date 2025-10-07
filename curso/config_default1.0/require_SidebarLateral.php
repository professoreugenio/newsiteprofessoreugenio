<div id="sidebarLateral" class="sidebarLateral">
    <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
        <?php $encAula = encrypt($codigoaula . "&" . $codigomodulo . "&" . $aulaLiberada, $action = 'e'); ?>
    <?php endif; ?>
    <button onclick="sair()" data-bs-toggle="tooltip" data-bs-placement="left" title="Sair <?= $codigoUser; ?>">
        <i class="bi bi-power text-danger"></i>
    </button>
    <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
        <?php if ($quantAnexoLicao > 0): ?>
            <button
                data-bs-toggle="modal"
                data-bs-target="#modalAnexos"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                title="Anexos"
                class="btn btn-anexo rounded-circle shadow-sm">
                <i class="bi bi-paperclip text-laranja"></i>
            </button>
        <?php endif; ?>
        <button onclick="window.location.href='modulo_licao_anexos.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Todos os Anexos">
            <i class="bi bi-paperclip text-roxo"></i>
        </button>
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
        <?php if ($quantAtv >= 1): ?>
            <button class="btn-roxo" onclick="window.location.href='actionCurso.php?Atv=<?php echo $QuestInicial; ?>'" data-bs-toggle="tooltip" data-bs-placement="left" title="Atividades">
                <i class="bi bi-clipboard-check text-primary"></i>
            </button>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php'): ?>
        <button onclick="window.location.href='../curso/modulos.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Módulos">
            <i class="bi bi-layers"></i>
        </button>
    <?php endif; ?>
    <button onclick="window.location.href='../curso/modulos.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Meus Cursos">
        <i class="bi bi-people-fill"></i>
    </button>

    <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
        <?php if ($codigoUser == 1): ?>

            <button id="btliberaLicao" data-id="<?php echo $encAula;  ?>" data-bs-toggle="tooltip" data-bs-placement="left" title="Liberar Lição">
                <?php if ($aulaLiberada == '1'): ?>
                    <i class="bi bi-lock-fill text-success"></i>
                <?php else: ?>
                    <i class="bi bi-unlock-fill text-danger"></i>
                <?php endif; ?>
            </button>

            <button id="btpublicalicao"
                data-id="<?php echo $encAula; ?>"
                data-titulo="***<?= $ordem; ?>-<?= htmlspecialchars($titulo) ?>"
                data-idturma="<?= $idTurma ?>"
                data-idmodulo="<?= $codigomodulo ?>"
                data-idartigo="<?= $codigoaula ?>"
                data-bs-toggle="modal"
                data-bs-target="#modalPublicaAula"
                title="Liberar Lição">
                <i class="bi bi-person-badge-fill text-info"></i>
            </button>


        <?php endif; ?>
    <?php endif; ?>
    <button onclick="window.location.href='configuracoes_perfil.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Configurações">
        <i class="bi bi-gear-fill"></i>
    </button>
    <!-- Botão de lições -->
    <?php if (basename($_SERVER['PHP_SELF']) != 'turmas.php' && basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
        <div class="btn-container">
            <button id="toggleLicoesBox" class="botao-onda" data-bs-toggle="tooltip" data-bs-placement="left" title="Ver Lições">
                <i class="bi bi-list-check"></i>
            </button>
            <!-- <div class="box-comece">Suas Lições</div> -->
        <?php endif; ?>
        </div>
</div>