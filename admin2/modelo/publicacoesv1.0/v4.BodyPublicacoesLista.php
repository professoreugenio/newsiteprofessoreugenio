<?php
// ====== Parâmetros do link ======
echo "aqui";

$idCurso = $_GET['id'];
$idModulo = $_GET['md'];
$idCurso = encrypt($idCurso, $action = 'd');
$idModulo = encrypt($idModulo, $action = 'd');
// ====== Consulta ======
/**
 * Lista de a_aluno_publicacoes_cursos para o módulo do link,
 * ordenada por ordempc, com título da publicação (JOIN em new_sistema_publicacoes_PJA).
 * Também calcula se foi atualizada nos últimos 2 dias para destaque visual.
 */

try {
    $pdo = config::connect();

    $sql = "
        SELECT
            pc.codigopublicacoescursos,
            pc.idpublicacaopc,
            pc.idcursopc,
            pc.idmodulopc,
            pc.publicopc,
            pc.visivelpc,
            pc.aulaliberadapc,
            pc.ordempc,
            pc.aulaliberadapc,
            pc.datapc,
            pc.horapc,
            pc.destaquepc,
            pc.dataatualizacaopc,
            pc.horaatualizacaopc,
            pub.titulo,
            -- marca se atualizado nas últimas 48h
            CASE
              WHEN CONCAT(COALESCE(pc.dataatualizacaopc, pc.datapc), ' ', COALESCE(pc.horaatualizacaopc, pc.horapc)) >= (NOW() - INTERVAL 2 DAY)
              THEN 1 ELSE 0
            END AS atualizado_recente
        FROM a_aluno_publicacoes_cursos pc
        LEFT JOIN new_sistema_publicacoes_PJA pub
               ON pub.codigopublicacoes = pc.idpublicacaopc
        WHERE pc.idcursopc = :idc
          AND pc.idmodulopc = :idm
        ORDER BY pc.ordempc ASC, pc.codigopublicacoescursos ASC
    ";

    $st = $pdo->prepare($sql);
    $st->bindValue(':idc', $idCurso, PDO::PARAM_INT);
    $st->bindValue(':idm', $idModulo, PDO::PARAM_INT);
    $st->execute();
    $linhas = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo '<div class="alert alert-danger">Erro ao carregar lista: ' . htmlspecialchars($e->getMessage()) . '</div>';
    return;
}

// helper simples
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// URL base para editar publicação (mantendo id e md do link)
$baseEditar = 'cursos_publicacaoEditar.php?id=' . $idCurso . '&md=' . $idModulo;

?>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">
                <i class="bi bi-layers-half me-2"></i> Publicações do módulo
            </h5>
            <span class="text-muted small">
                Arraste os itens para reordenar • Ordem atual salva em tempo real
            </span>
        </div>

        <?php if (empty($linhas)): ?>
            <div class="alert alert-info mb-0">Nenhuma publicação cadastrada para este módulo.</div>
        <?php else: ?>
            <ul id="listaPublicacoes" class="list-group list-group-flush" style="cursor:grab;">
                <?php foreach ($linhas as $row):
                    $idLinha   = (int)$row['codigopublicacoescursos']; // PK da tabela "pc"
                    $idPub     = (int)$row['idpublicacaopc'];
                    $ordem     = (int)$row['ordempc'];
                    $titulo    = $row['titulo'] ?: ('[Sem título] (ID ' . $idPub . ')');
                    $bloqueado = (int)$row['aulaliberadapc'] === 1;
                    $liberada  = (int)$row['aulaliberadapc'] === 1;
                    $recent    = (int)$row['atualizado_recente'] === 1;

                    // classes de destaque
                    $liClasses = 'list-group-item d-flex align-items-center justify-content-between gap-2';
                    if ($recent) $liClasses .= ' border-warning bg-warning-subtle';
                ?>
                    <li class="<?= $liClasses ?>"
                        draggable="true"
                        data-idlinha="<?= $idLinha ?>"
                        data-idpub="<?= $idPub ?>"
                        data-ordem="<?= $ordem ?>"
                        data-idcurso="<?= $idCurso ?>"
                        data-idmodulo="<?= $idModulo ?>">

                        <div class="d-flex align-items-center gap-3 flex-grow-1">
                            <!-- "alça" visual para arrastar -->
                            <span class="text-muted" title="Arraste para reordenar" style="cursor:grab;">
                                <i class="bi bi-grip-vertical fs-5"></i>
                            </span>
                            <?php

                            $idEnc = rawurlencode(encrypt($idCurso, 'e'));
                            $mdEnc = rawurlencode(encrypt($idModulo, 'e'));

                            // Base do link de edição com id e md encryptados
                            $baseEditar = 'cursos_publicacaoEditar.php?id=' . $idEnc . '&md=' . $mdEnc;
                            ?>
                            <div class="d-flex flex-column">
                                <!-- Link do título para edição -->
                                <a class="fw-semibold text-decoration-none"
                                    href="<?= $baseEditar . '&pub=' . rawurlencode(encrypt($idPub, 'e')) ?>">
                                    <span class="ordem-badge"><?= $ordem ?></span> <?= h($titulo) ?>
                                </a>
                                
                                <div class="small text-muted">
                                    Ordem:
                                    <?php if ($recent): ?>
                                        • <span class="badge bg-warning text-dark">Alterado há pouco</span>
                                    <?php endif; ?>
                                    <?php if ($liberada): ?>
                                        • <span class="badge bg-success-subtle text-success border border-success-subtle">Aula liberada</span>
                                    <?php endif; ?>
                                    <?php if ($bloqueado): ?>
                                        • <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Bloqueada</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <!-- Botão bloquear/desbloquear -->
                            <button type="button"
                                class="btn btn-sm <?= $bloqueado ? 'btn-outline-success' : 'btn-outline-danger' ?> btn-toggle-bloq"
                                data-bloqueado="<?= $bloqueado ? 1 : 0 ?>"
                                title="<?= $bloqueado ? 'Desbloquear publicação' : 'Bloquear publicação' ?>">
                                <?php if ($bloqueado): ?>
                                    <i class="bi bi-unlock"></i>
                                <?php else: ?>
                                    <i class="bi bi-lock"></i>
                                <?php endif; ?>
                            </button>
                        </div>

                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Toast feedback -->
            <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1080;">
                <div id="toastFeed" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">Salvo.</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // ===== Drag & Drop simples (HTML5) para reordenar =====
    (function() {
        const lista = document.getElementById('listaPublicacoes');
        if (!lista) return;

        let draggingEl = null;

        lista.addEventListener('dragstart', (e) => {
            const li = e.target.closest('li[list-group-item]') || e.target.closest('li');
            draggingEl = e.target.closest('li');
            if (!draggingEl) return;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', draggingEl.dataset.idlinha);
            // pequena opacidade
            draggingEl.style.opacity = '0.6';
        });

        lista.addEventListener('dragend', () => {
            if (draggingEl) draggingEl.style.opacity = '';
            draggingEl = null;
            atualizarOrdemNoServidor();
        });

        lista.addEventListener('dragover', (e) => {
            e.preventDefault();
            const afterElement = getDragAfterElement(lista, e.clientY);
            const draggable = draggingEl;
            if (!draggable) return;
            if (afterElement == null) {
                lista.appendChild(draggable);
            } else {
                lista.insertBefore(draggable, afterElement);
            }
        });

        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('li[draggable="true"]:not(.dragging)')];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - (box.top + box.height / 2);
                if (offset < 0 && offset > closest.offset) {
                    return {
                        offset: offset,
                        element: child
                    };
                } else {
                    return closest;
                }
            }, {
                offset: Number.NEGATIVE_INFINITY
            }).element;
        }

        function atualizarOrdemNoServidor() {
            const itens = [...lista.querySelectorAll('li[draggable="true"]')];
            const payload = {
                idcurso: itens[0]?.dataset.idcurso || 0,
                idmodulo: itens[0]?.dataset.idmodulo || 0,
                items: []
            };

            itens.forEach((li, idx) => {
                const novaOrdem = idx + 1;
                li.querySelector('.ordem-badge').textContent = novaOrdem;
                payload.items.push({
                    idlinha: parseInt(li.dataset.idlinha, 10),
                    idpublicacaopc: parseInt(li.dataset.idpub, 10),
                    ordempc: novaOrdem
                });
            });

            fetch('publicacoesv1.0/ajax_publicacoesCopiasReordenar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json; charset=UTF-8'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(j => {
                    if (j?.ok) showToast('Ordem atualizada.');
                    else showToast(j?.msg || 'Falha ao salvar ordem.');
                })
                .catch(() => showToast('Erro de comunicação ao salvar ordem.'));
        }

        // Toggle bloqueio
        lista.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-toggle-bloq');
            if (!btn) return;

            const li = btn.closest('li');
            const payload = {
                idcurso: parseInt(li.dataset.idcurso, 10),
                idmodulo: parseInt(li.dataset.idmodulo, 10),
                idlinha: parseInt(li.dataset.idlinha, 10),
                idpublicacaopc: parseInt(li.dataset.idpub, 10)
            };

            fetch('publicacoesv1.0/ajax_publicacoesCopiasToggleBloqueio.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json; charset=UTF-8'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(j => {
                    if (j?.ok) {
                        // atualiza UI
                        const agoraBloq = j.bloqueado == 1;
                        btn.dataset.bloqueado = agoraBloq ? 1 : 0;
                        btn.classList.toggle('btn-outline-danger', !agoraBloq);
                        btn.classList.toggle('btn-outline-success', agoraBloq);
                        btn.innerHTML = agoraBloq ? '<i class="bi bi-unlock"></i>' : '<i class="bi bi-lock"></i>';

                        // badge
                        const small = li.querySelector('.small.text-muted');
                        const badgeExist = small?.querySelector('.badge.bg-danger-subtle');
                        if (agoraBloq) {
                            if (!badgeExist) {
                                const span = document.createElement('span');
                                span.className = 'badge bg-danger-subtle text-danger border border-danger-subtle ms-1';
                                span.textContent = 'Bloqueada';
                                small.appendChild(document.createTextNode(' • '));
                                small.appendChild(span);
                            }
                        } else {
                            badgeExist?.previousSibling?.remove(); // remove " • "
                            badgeExist?.remove();
                        }

                        showToast(agoraBloq ? 'Publicação bloqueada.' : 'Publicação desbloqueada.');
                    } else {
                        showToast(j?.msg || 'Falha ao alterar bloqueio.');
                    }
                })
                .catch(() => showToast('Erro de comunicação ao alterar bloqueio.'));
        });

        function showToast(msg) {
            const el = document.getElementById('toastFeed');
            if (!el) {
                alert(msg);
                return;
            }
            el.querySelector('.toast-body').textContent = msg;
            const t = bootstrap.Toast.getOrCreateInstance(el, {
                delay: 2000
            });
            t.show();
        }
    })();
</script>