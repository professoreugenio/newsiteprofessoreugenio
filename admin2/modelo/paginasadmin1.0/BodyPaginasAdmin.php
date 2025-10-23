<?php

/**
 * BodyPaginasAdmin.php (MOD)
 * Requisitos: $con (PDO), encrypt(), Bootstrap 5+, Bootstrap Icons, AOS já carregados pela página principal.
 * Este módulo apenas renderiza a UI e faz chamadas AJAX para endpoints em paginasadmin1.0/.
 */

// Helpers locais (apenas apresentação)
$getSessoes = function (PDO $con): array {
    $sql = "SELECT codigosessao, ordemss, nomesessao, iconess, visivelss
            FROM a_site_sessao
            ORDER BY ordemss ASC, codigosessao ASC";
    $st = $con->query($sql);
    return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
};

$getPaginasBySessao = function (PDO $con, $idSessao): array {
    $st = $con->prepare("SELECT codigopaginas, idsessaosp, nomepaginasp, pastasp, iconesp, ordemsp, visivelsp, manutencaosp
                         FROM a_site_paginas
                         WHERE idsessaosp = :s
                         ORDER BY ordemsp ASC, codigopaginas ASC");
    $st->execute([':s' => $idSessao]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
};

// Carrega sessões para montar os botões de filtro iniciais
$sessoes = $getSessoes($con);
$primeiraSessao = $sessoes[0]['codigosessao'] ?? null;
?>

<!-- Cabeçalho -->
<div class="d-flex align-items-center justify-content-between mb-3" data-aos="fade-right">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-layout-text-sidebar-reverse fs-4 text-emerald"></i>
        <div class="heading-2 fw-semibold mb-0">Gerenciamento de Páginas (Admin)</div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalNovaSessao">
            <i class="bi bi-collection me-1"></i> Nova Sessão
        </button>
        <button class="btn btn-emerald" data-bs-toggle="modal" data-bs-target="#modalNovaPagina">
            <i class="bi bi-plus-lg me-1"></i> Nova Página
        </button>
    </div>
</div>

<!-- Filtros de Sessão -->
<div class="mb-3" data-aos="fade-up">
    <div id="wrapBotoesSessoes" class="d-flex flex-wrap gap-2">
        <?php if (!empty($sessoes)): ?>
            <?php foreach ($sessoes as $i => $s):
                $idS  = (int)$s['codigosessao'];
                $txt  = htmlspecialchars($s['nomesessao'] ?? ('Sessão ' . $idS));
                $ico  = trim((string)($s['iconess'] ?? 'bi-folder'));
                $vis  = (int)($s['visivelss'] ?? 0);
                $ativo = ($i === 0) ? 'active' : '';
            ?>
                <button
                    class="btn btn-sm btn-outline-dark <?= $ativo ?>"
                    data-id-sessao="<?= $idS ?>"
                    title="Filtrar por sessão">
                    <i class="bi <?= htmlspecialchars($ico) ?> me-1"></i><?= $txt ?>
                    <?php if ($vis !== 1): ?>
                        <span class="badge bg-secondary ms-1">Off</span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning mb-0">Nenhuma sessão cadastrada ainda.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Lista de Páginas (drag & drop) -->
<div class="card shadow-sm" data-aos="fade-up">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="fw-semibold">Páginas da Sessão</div>
        <small class="text-muted">Arraste os itens para reordenar</small>
    </div>
    <div class="card-body p-0">
        <ul id="listaPaginas" class="list-group list-group-flush">
            <?php if ($primeiraSessao !== null):
                $paginas = $getPaginasBySessao($con, $primeiraSessao);
                if (empty($paginas)): ?>
                    <li class="list-group-item py-3 text-muted">Nenhuma página nesta sessão.</li>
                    <?php else:
                    foreach ($paginas as $pg):
                        $idpg  = (int)$pg['codigopaginas'];
                        $enc   = encrypt((string)$idpg, 'e');
                        $nome  = htmlspecialchars($pg['nomepaginasp'] ?? ('Página ' . $idpg));
                        $ico   = htmlspecialchars($pg['iconesp'] ?? 'bi-file-earmark-code');
                        $vis   = (int)($pg['visivelsp'] ?? 0);        // 1=on, 0=off
                        $man   = (int)($pg['manutencaosp'] ?? 0);     // 1=manutenção
                        $ordem = (int)($pg['ordemsp'] ?? 0);
                    ?>
                        <li class="list-group-item d-flex align-items-center justify-content-between" data-idpg="<?= $idpg ?>">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-grip-vertical text-muted"></i>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi <?= $ico ?> fs-5"></i>
                                    <a class="fw-semibold text-decoration-none" href="paginasAdminEditar.php?idpg=<?= urlencode($enc) ?>">
                                        <?= $nome ?>
                                    </a>
                                    <?php if ($man === 1): ?>
                                        <span class="badge bg-warning text-dark">Manutenção</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <!-- Indicador ON/OFF -->
                                <?php if ($vis === 1): ?>
                                    <span class="text-success" title="Visível"><i class="bi bi-toggle-on fs-5"></i></span>
                                <?php else: ?>
                                    <span class="text-secondary" title="Oculta"><i class="bi bi-toggle-off fs-5"></i></span>
                                <?php endif; ?>

                                <!-- Ocultar/Exibir -->
                                <button class="btn btn-sm <?= $vis ? 'btn-outline-secondary' : 'btn-outline-success' ?> btnToggleVisivel"
                                    data-idpg="<?= $idpg ?>" data-vis="<?= $vis ?>">
                                    <i class="bi <?= $vis ? 'bi-eye-slash' : 'bi-eye' ?>"></i>
                                    <?= $vis ? 'Ocultar' : 'Exibir' ?>
                                </button>

                                <!-- Manutenção ON/OFF -->
                                <button class="btn btn-sm <?= $man ? 'btn-outline-warning' : 'btn-outline-dark' ?> btnToggleManut"
                                    data-idpg="<?= $idpg ?>" data-man="<?= $man ?>">
                                    <i class="bi bi-tools"></i>
                                    <?= $man ? 'Encerrar Manut.' : 'Manutenção' ?>
                                </button>
                            </div>
                        </li>
                <?php endforeach;
                endif;
            else: ?>
                <li class="list-group-item py-3 text-muted">Crie uma sessão para começar.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Modal: Nova Página -->
<div class="modal fade" id="modalNovaPagina" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formNovaPagina" class="modal-content">
            <div class="modal-header">
                <div class="fw-semibold">Adicionar Nova Página (Admin)</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sessão</label>
                    <select name="idsessaosp" class="form-select" required>
                        <option value="">— selecione —</option>
                        <?php foreach ($sessoes as $s):
                            $idS = (int)$s['codigosessao']; ?>
                            <option value="<?= $idS ?>"><?= htmlspecialchars($s['nomesessao'] ?? ('Sessão ' . $idS)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome da Página</label>
                    <input type="text" name="nomepaginasp" class="form-control" required maxlength="50" placeholder="Ex.: Usuários, Relatórios, Integrações">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pasta (rota)</label>
                    <input type="text" name="pastasp" class="form-control" maxlength="100" placeholder="Ex.: admin/usuarios">
                    <div class="form-text">Somente o caminho/slug da página no admin.</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Ícone (Bootstrap Icons)</label>
                        <input type="text" name="iconesp" class="form-control" maxlength="30" placeholder="Ex.: bi-gear, bi-people">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Visível?</label>
                        <select name="visivelsp" class="form-select">
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold">Manutenção?</label>
                    <select name="manutencaosp" class="form-select">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer">
                <button class="btn btn-emerald" type="submit"><i class="bi bi-check2-circle me-1"></i> Adicionar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Nova Sessão -->
<div class="modal fade" id="modalNovaSessao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formNovaSessao" class="modal-content">
            <div class="modal-header">
                <div class="fw-semibold">Adicionar Nova Sessão</div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome da Sessão</label>
                    <input type="text" name="nomesessao" class="form-control" required maxlength="20" placeholder="Ex.: Conteúdo, Sistema, Cadastros">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Ícone (Bootstrap Icons)</label>
                    <input type="text" name="iconess" class="form-control" maxlength="20" placeholder="Ex.: bi-folder, bi-speedometer2">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Visível?</label>
                    <select name="visivelss" class="form-select">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-emerald" type="submit"><i class="bi bi-check2-circle me-1"></i> Adicionar</button>
            </div>
        </form>
    </div>
</div>

<!-- SortableJS (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    (function() {
        const listaPaginas = document.getElementById('listaPaginas');
        const wrapBotoesSessoes = document.getElementById('wrapBotoesSessoes');

        // Inicializa drag & drop
        if (listaPaginas) {
            new Sortable(listaPaginas, {
                animation: 150,
                handle: '.bi-grip-vertical',
                ghostClass: 'bg-light',
                onEnd: function() {
                    // Coleta a nova ordem
                    const ordem = [];
                    listaPaginas.querySelectorAll('li[data-idpg]').forEach((li, idx) => {
                        ordem.push({
                            idpg: li.getAttribute('data-idpg'),
                            pos: (idx + 1)
                        });
                    });
                    // Envia para backend
                    fetch('paginasadmin1.0/ajax_paginasReordenar.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                ordem
                            })
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (!j || j.status !== 'ok') {
                                console.warn('Falha ao salvar ordem', j);
                            }
                        })
                        .catch(console.error);
                }
            });
        }

        // Clique nos botões de sessão (filtro)
        if (wrapBotoesSessoes) {
            wrapBotoesSessoes.addEventListener('click', (ev) => {
                const btn = ev.target.closest('button[data-id-sessao]');
                if (!btn) return;
                wrapBotoesSessoes.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const idSessao = btn.getAttribute('data-id-sessao');
                carregarPaginasDaSessao(idSessao);
            });
        }

        function carregarPaginasDaSessao(idSessao) {
            if (!idSessao) return;
            listaPaginas.innerHTML = '<li class="list-group-item py-3 text-muted">Carregando…</li>';

            fetch('paginasadmin1.0/ajax_paginasListar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        idsessaosp: idSessao
                    })
                })
                .then(r => r.json())
                .then(j => {
                    if (!j || j.status !== 'ok') {
                        listaPaginas.innerHTML = '<li class="list-group-item py-3 text-danger">Falha ao carregar páginas.</li>';
                        return;
                    }
                    renderLista(j.data || []);
                })
                .catch(() => {
                    listaPaginas.innerHTML = '<li class="list-group-item py-3 text-danger">Erro de rede.</li>';
                });
        }

        function iconeVisivel(vis) {
            return vis === 1 ? '<span class="text-success" title="Visível"><i class="bi bi-toggle-on fs-5"></i></span>' : '<span class="text-secondary" title="Oculta"><i class="bi bi-toggle-off fs-5"></i></span>';
        }

        function botaoVisivel(idpg, vis) {
            return `<button class="btn btn-sm ${vis ? 'btn-outline-secondary' : 'btn-outline-success'} btnToggleVisivel" data-idpg="${idpg}" data-vis="${vis}"><i class="bi ${vis ? 'bi-eye-slash' : 'bi-eye'}"></i> ${vis ? 'Ocultar' : 'Exibir'}</button>`;
        }

        function botaoManut(idpg, man) {
            return `<button class="btn btn-sm ${man ? 'btn-outline-warning' : 'btn-outline-dark'} btnToggleManut" data-idpg="${idpg}" data-man="${man}"><i class="bi bi-tools"></i> ${man ? 'Encerrar Manut.' : 'Manutenção'}</button>`;
        }

        function renderLista(arr) {
            if (!arr.length) {
                listaPaginas.innerHTML = '<li class="list-group-item py-3 text-muted">Nenhuma página nesta sessão.</li>';
                return;
            }
            const itens = arr.map(pg => {
                const idpg = parseInt(pg.codigopaginas, 10);
                const enc = pg.enc || ''; // o endpoint já pode devolver o id encryptado
                const nome = pg.nomepaginasp || ('Página ' + idpg);
                const ico = pg.iconesp || 'bi-file-earmark-code';
                const vis = parseInt(pg.visivelsp ?? 0, 10);
                const man = parseInt(pg.manutencaosp ?? 0, 10);
                const badgeMan = man === 1 ? '<span class="badge bg-warning text-dark">Manutenção</span>' : '';
                return `
      <li class="list-group-item d-flex align-items-center justify-content-between" data-idpg="${idpg}">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-grip-vertical text-muted"></i>
          <div class="d-flex align-items-center gap-2">
            <i class="bi ${ico} fs-5"></i>
            <a class="fw-semibold text-decoration-none" href="paginasAdminEditar.php?idpg=${encodeURIComponent(enc)}">${escapeHtml(nome)}</a>
            ${badgeMan}
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          ${iconeVisivel(vis)}
          ${botaoVisivel(idpg, vis)}
          ${botaoManut(idpg, man)}
        </div>
      </li>`;
            }).join('');
            listaPaginas.innerHTML = itens;
        }

        // Delegação: cliques nos botões de cada item
        if (listaPaginas) {
            listaPaginas.addEventListener('click', (ev) => {
                const btnVis = ev.target.closest('.btnToggleVisivel');
                const btnMan = ev.target.closest('.btnToggleManut');
                if (btnVis) {
                    const idpg = btnVis.getAttribute('data-idpg');
                    const vis = parseInt(btnVis.getAttribute('data-vis'), 10);
                    fetch('paginasadmin1.0/ajax_paginaToggleVisivel.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                idpg,
                                visivelsp: (vis ? 0 : 1)
                            })
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j && j.status === 'ok') {
                                // Atualiza a sessão atual
                                const ativo = document.querySelector('#wrapBotoesSessoes .active');
                                const idSessao = ativo ? ativo.getAttribute('data-id-sessao') : '';
                                carregarPaginasDaSessao(idSessao);
                            }
                        })
                        .catch(console.error);
                }
                if (btnMan) {
                    const idpg = btnMan.getAttribute('data-idpg');
                    const man = parseInt(btnMan.getAttribute('data-man'), 10);
                    fetch('paginasadmin1.0/ajax_paginaToggleManutencao.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                idpg,
                                manutencaosp: (man ? 0 : 1)
                            })
                        })
                        .then(r => r.json())
                        .then(j => {
                            if (j && j.status === 'ok') {
                                const ativo = document.querySelector('#wrapBotoesSessoes .active');
                                const idSessao = ativo ? ativo.getAttribute('data-id-sessao') : '';
                                carregarPaginasDaSessao(idSessao);
                            }
                        })
                        .catch(console.error);
                }
            });
        }

        // Nova Página
        const formNovaPagina = document.getElementById('formNovaPagina');
        if (formNovaPagina) {
            formNovaPagina.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const fd = new FormData(formNovaPagina);
                const payload = Object.fromEntries(fd.entries());
                fetch('paginasadmin1.0/ajax_paginaCriar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (j && j.status === 'ok') {
                            // Fecha modal e recarrega a sessão dessa página
                            const idSessao = payload.idsessaosp;
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovaPagina'));
                            if (modal) modal.hide();
                            // Ativa o botão da sessão criada/selecionada
                            document.querySelectorAll('#wrapBotoesSessoes button').forEach(b => {
                                b.classList.toggle('active', b.getAttribute('data-id-sessao') === idSessao);
                            });
                            carregarPaginasDaSessao(idSessao);
                            formNovaPagina.reset();
                        }
                    })
                    .catch(console.error);
            });
        }

        // Nova Sessão
        const formNovaSessao = document.getElementById('formNovaSessao');
        if (formNovaSessao) {
            formNovaSessao.addEventListener('submit', (ev) => {
                ev.preventDefault();
                const fd = new FormData(formNovaSessao);
                const payload = Object.fromEntries(fd.entries());
                fetch('paginasadmin1.0/ajax_sessaoCriar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (j && j.status === 'ok') {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNovaSessao'));
                            if (modal) modal.hide();
                            formNovaSessao.reset();
                            // Atualiza botões de sessão (pede para o backend devolver HTML pronto ou recarregar via fetch)
                            recarregarBotoesSessoes();
                        }
                    })
                    .catch(console.error);
            });
        }

        function recarregarBotoesSessoes() {
            // Endpoint que retorna TODAS as sessões para redesenhar os botões
            fetch('paginasadmin1.0/ajax_sessoesListar.php', {
                    method: 'POST'
                })
                .then(r => r.json())
                .then(j => {
                    if (!j || j.status !== 'ok') return;
                    const data = j.data || [];
                    if (!data.length) {
                        wrapBotoesSessoes.innerHTML = '<div class="alert alert-warning mb-0">Nenhuma sessão cadastrada ainda.</div>';
                        listaPaginas.innerHTML = '<li class="list-group-item py-3 text-muted">Crie uma sessão para começar.</li>';
                        return;
                    }
                    wrapBotoesSessoes.innerHTML = data.map((s, i) => {
                        const idS = parseInt(s.codigosessao, 10);
                        const nome = s.nomesessao || ('Sessão ' + idS);
                        const ico = s.iconess || 'bi-folder';
                        const vis = parseInt(s.visivelss ?? 0, 10);
                        const ativo = i === 0 ? 'active' : '';
                        return `<button class="btn btn-sm btn-outline-dark ${ativo}" data-id-sessao="${idS}">
                    <i class="bi ${ico} me-1"></i>${escapeHtml(nome)}
                    ${vis !== 1 ? '<span class="badge bg-secondary ms-1">Off</span>' : ''}
                  </button>`;
                    }).join('');
                    // Carrega primeiras páginas
                    const first = wrapBotoesSessoes.querySelector('button[data-id-sessao]');
                    if (first) {
                        first.classList.add('active');
                        carregarPaginasDaSessao(first.getAttribute('data-id-sessao'));
                    }
                });
        }

        function escapeHtml(s) {
            return (s ?? '').toString()
                .replaceAll('&', '&amp;').replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;').replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }
    })();
</script>