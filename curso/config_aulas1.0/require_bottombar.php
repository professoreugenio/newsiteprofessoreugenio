<!-- BOTTOM BAR -->
<div class="bottom-bar py-2 bg-dark border-top border-secondary shadow-sm fixed-bottom">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-2">

        <!-- Navegação -->
        <div class="d-flex align-items-center gap-2 flex-wrap" id="botoesNavegacao">

            <?php if (!empty($codigoAnterior)): ?>
                <a class="btn btn-outline-light d-flex align-items-center gap-2 px-3"
                    href="actionCurso.php?lc=<?= urlencode($encAnt ?? '') ?>"
                    title="Voltar à aula anterior">
                    <i class="bi bi-arrow-left-circle-fill fs-5"></i>
                    <span>Anterior</span>
                </a>
            <?php endif; ?>

            <?php if (!empty($codigoProxima)): ?>
                <a class="btn btn-emerald d-flex align-items-center gap-2 px-3"
                    href="actionCurso.php?lc=<?= urlencode($encProx ?? '') ?>"
                    title="Ir para próxima aula">
                    <span>Próxima</span>
                    <i class="bi bi-arrow-right-circle-fill fs-5"></i>
                </a>
            <?php endif; ?>

            <?php if (basename($_SERVER['PHP_SELF']) === 'modulo_licao.php'): ?>
                <?php if ($codigoUser == 1): ?>
                    <button id="btliberaLicao" data-id="<?= $encAula ?>"
                        class="btn btn-outline-light d-flex align-items-center gap-2"
                        title="Bloquear/Desbloquear lição">
                        <i class="bi <?= $aulaLiberada == '1' ? 'bi-unlock-fill text-success' : 'bi-lock-fill text-danger' ?> fs-5"></i>
                    </button>

                    <button id="btpublicalicao"
                        data-id="<?= $encAula ?>"
                        data-titulo="<?= htmlspecialchars($titulo) ?>"
                        data-idturma="<?= $idTurma ?>"
                        data-idmodulo="<?= $codigomodulo ?>"
                        data-idartigo="<?= $codigoaula ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#modalPublicaAula"
                        class="btn btn-outline-light d-flex align-items-center gap-2"
                        title="Publicar conteúdo">
                        <i class="bi bi-person-badge-fill fs-5"></i>
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <button class="btn btn-success d-flex align-items-center gap-2 px-3"
                id="btnBuscaLicao"
                title="Buscar na lição"
                onclick="window.open('modulos_buscalicao.php', '_self')">
                <i class="bi bi-search fs-5"></i>
            </button>

        </div>

        <!-- Ações extras -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button class="btn btn-amber d-flex align-items-center gap-2 px-3"
                data-bs-toggle="modal"
                data-bs-target="#modalCaderno"
                title="Abrir caderno de anotações">
                <i class="bi bi-journal-text fs-5"></i>
                
            </button>

            <a href="../depoimentonovo.php?idUser=<?= $encIdUser?>"
                target="_blank"
                class="btn btn-light d-flex align-items-center gap-2 px-3"
                title="Enviar seu depoimento">
                <i class="bi bi-chat-heart-fill text-danger fs-5"></i>
                <span>Depoimento</span>
            </a>
        </div>

    </div>
</div>

<!-- ESTILO EXTRA -->
<style>
    .bottom-bar {
        backdrop-filter: blur(6px);
        background: rgba(17, 34, 64, 0.2);
        transition: all 0.3s ease;
    }

    .bottom-bar .btn {
        border-radius: 30px;
        font-weight: 500;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .bottom-bar .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.15);
    }

    .btn-emerald {
        background-color: #00BB9C;
        color: #fff;
        border: none;
    }

    .btn-emerald:hover {
        background-color: #00a88c;
    }

    .btn-amber {
        background-color: #FF9C00;
        color: #fff;
        border: none;
    }

    .btn-amber:hover {
        background-color: #e38a00;
    }
</style>