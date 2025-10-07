<!-- Barra Lateral Direita -->
<div id="sidebarLateral" class="sidebarLateral">
    <?php $encAula = encrypt($codigoaula . "&" . $codigomodulo . "&" . $aulaLiberada, $action = 'e'); ?>
    <button onclick="sair()" data-bs-toggle="tooltip" data-bs-placement="left" title="Sair <?php echo $codigoaula;  ?><?php echo $aulaLiberada;  ?>">
        <i class="bi bi-power text-danger"></i>
    </button>
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
    <button onclick="window.location.href='../curso/modulos.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Módulos">
        <i class="bi bi-layers"></i>
    </button>
    <!-- ATIVIDADES -->

    <!-- <button onclick="window.location.href='../curso/modulos.phpmodulo_atividades.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Atividades">

        <i class="bi bi-journal-bookmark-fill text-roxo"></i>
    </button> -->
    <!-- CURSOS -->
    <!-- <button data-bs-toggle="tooltip" data-bs-placement="left" title="Lições">
        <i class="bi bi-journal-text"></i>
    </button> -->
    <button onclick="window.location.href='../curso/modulos.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Meus Cursos">
        <i class="bi bi-people-fill"></i>
    </button>

    <button onclick="window.location.href='../curso/modulos.phpsobreocurso.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Sobre o Curso">
        <i class="bi bi-mortarboard-fill text-azul"></i>
    </button>
    <?php if ($codigoUser == 1): ?>
        <button id="btliberaLicao" data-id="<?php echo $encAula;  ?>" data-bs-toggle="tooltip" data-bs-placement="left" title="Liberar Lição">

            <?php if ($aulaLiberada == '1'): ?>
                <i class="bi bi-lock-fill text-success"></i>
            <?php else: ?>
                <i class="bi bi-unlock-fill text-danger"></i>
            <?php endif; ?>
        </button>

        <button onclick="window.location.href='configuracoes_perfil.php';" data-bs-toggle="tooltip" data-bs-placement="left" title="Configurações">
            <i class="bi bi-gear-fill"></i>
        </button>
    <?php endif; ?>

    <!-- Botão de lições -->
    <div class="btn-container">
        <button id="toggleLicoesBox" class="botao-onda" data-bs-toggle="tooltip" data-bs-placement="left" title="Ver Lições">
            <i class="bi bi-list-check"></i>

        </button>
        <div class="box-comece">Suas Lições</div>
    </div>
</div>


<?php if ($codigoUser == 1):  ?>
<?php endif; ?>

<div class="modal fade" id="modalAnexos" tabindex="-1" aria-labelledby="modalAnexosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg rounded-4 border-0" style="background-color: #f8f9fa;">
            <div class="modal-header" style="background-color: #525870; color: white; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalAnexosLabel">
                    <i class="bi bi-folder2-open"></i> Anexos Disponíveis
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <?php
                    if (!empty($fetchContAnexo) && is_array($fetchContAnexo)) {
                        foreach ($fetchContAnexo as $key => $value):
                            $titulopa = $value['titulopa'];
                            $extensao = strtolower($value['extpa']);

                            // Ícones por tipo de extensão
                            switch ($extensao) {
                                case 'pdf':
                                    $icone = '<i class="bi bi-file-earmark-pdf text-danger me-2"></i>';
                                    break;
                                case 'doc':
                                case 'docx':
                                    $icone = '<i class="bi bi-file-earmark-word" style="color:#525870; margin-right: 0.5rem;"></i>';
                                    break;
                                case 'xls':
                                case 'xlsx':
                                    $icone = '<i class="bi bi-file-earmark-excel text-success me-2"></i>';
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                case 'png':
                                    $icone = '<i class="bi bi-image" style="color:#525870; margin-right: 0.5rem;"></i>';
                                    break;
                                default:
                                    $icone = '<i class="bi bi-file-earmark text-secondary me-2"></i>';
                            }

                            if ($value['urlpa'] == "#") {
                                $url = "../anexos/publicacoes/{$value['pastapa']}/{$value['anexopa']}";
                            } else {
                                $url = $value['urlpa'];
                            }
                    ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2" style="background-color: #ffffff;">
                                <div class="d-flex align-items-center text-dark">
                                    <a href="<?= $url ?>" target="_blank" download title="Baixar <?= htmlspecialchars($titulopa) ?>">

                                        <?= $icone ?><span class="fw-medium"><?= htmlspecialchars($titulopa, ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>

                                </div>
                                <a href="<?= $url ?>" class="btn btn-sm btn-warning d-flex align-items-center gap-1" target="_blank" download title="Baixar <?= htmlspecialchars($titulopa) ?>">
                                    <i class="bi bi-download"></i> Baixar
                                </a>
                            </li>
                    <?php endforeach;
                    }
                    ?>
                </ul>
            </div>
            <div class="modal-footer" style="background-color: #f1f1f1; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
                <button type="button" class="btn" style="background-color: #525870; color: white;" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>

    </div>
</div>