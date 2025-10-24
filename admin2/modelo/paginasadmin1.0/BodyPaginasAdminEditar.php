<?php

/**
 * BodyPaginasAdminEditar.php (MOD)
 * Requisitos: $con (PDO), encrypt(), Bootstrap 5+, Bootstrap Icons, AOS.
 */

if (!isset($con) || !($con instanceof PDO)) {
    echo '<div class="alert alert-danger">Conexão indisponível.</div>';
    return;
}

// ===== Helpers =====
$decrypt = function (string $v) {
    try {
        return (int)encrypt($v, 'd');
    } catch (\Throwable $e) {
        return 0;
    }
};
$safe = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

// ===== Entrada: idpg encryptado em GET =====
$encId = $_GET['idpg'] ?? '';
$idpg  = $encId ? $decrypt($encId) : 0;
if ($idpg <= 0) {
    echo '<div class="alert alert-warning">Parâmetro inválido.</div>';
    return;
}

// ===== Carrega página =====
$st = $con->prepare("
  SELECT codigopaginas, idsessaosp, nomepaginasp, pastasp, iconesp,
         ordemsp, visivelsp, manutencaosp
  FROM a_site_paginas
  WHERE codigopaginas = :id
  LIMIT 1
");
$st->bindValue(':id', $idpg, PDO::PARAM_INT);
$st->execute();
$pg = $st->fetch(PDO::FETCH_ASSOC);

if (!$pg) {
    echo '<div class="alert alert-warning">Página não encontrada.</div>';
    return;
}

// ===== Carrega sessões (dropdown) =====
$sessoes = [];
$qs = $con->query("SELECT codigosessao, nomesessao FROM a_site_sessao ORDER BY ordemss ASC, codigosessao ASC");
if ($qs) $sessoes = $qs->fetchAll(PDO::FETCH_ASSOC);

// ===== Vars base =====
$idsess     = (int)($pg['idsessaosp'] ?? 0);
$nome       = (string)($pg['nomepaginasp'] ?? '');
$pasta      = (string)($pg['pastasp'] ?? '');
$icone      = (string)($pg['iconesp'] ?? 'bi-file-earmark-code');
$ordem      = (int)($pg['ordemsp'] ?? 0);
$visivel    = (int)($pg['visivelsp'] ?? 0);
$manut      = (int)($pg['manutencaosp'] ?? 0);

$badgeManut = $manut === 1 ? '<span class="badge bg-warning text-dark">Manutenção</span>' : '';
$indicador  = $visivel === 1 ? '<span class="text-success" title="Visível"><i class="bi bi-toggle-on fs-5"></i></span>'
    : '<span class="text-secondary" title="Oculta"><i class="bi bi-toggle-off fs-5"></i></span>';

?>

<!-- Título / Ações rápidas -->
<div class="d-flex align-items-center justify-content-between mb-3" data-aos="fade-right">
    <div class="d-flex align-items-center gap-2">
        <i class="bi bi-file-earmark-code fs-4 text-emerald"></i>
        <div>
            <div class="heading-2 fw-semibold mb-0">Editar Página (Admin)</div>
            <div class="small text-muted">
                ID: <?= (int)$idpg ?> · <?= $indicador ?> <?= $badgeManut ?>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Toggle Visível -->
        <button id="btnToggleVis" class="btn btn-sm <?= ($visivel ? 'btn-outline-secondary' : 'btn-outline-success') ?>"
            data-idpg="<?= (int)$idpg ?>" data-vis="<?= (int)$visivel ?>">
            <i class="bi <?= ($visivel ? 'bi-eye-slash' : 'bi-eye') ?>"></i>
            <?= ($visivel ? 'Ocultar' : 'Exibir') ?>
        </button>

        <!-- Toggle Manutenção -->
        <button id="btnToggleMan" class="btn btn-sm <?= ($manut ? 'btn-outline-warning' : 'btn-outline-dark') ?>"
            data-idpg="<?= (int)$idpg ?>" data-man="<?= (int)$manut ?>">
            <i class="bi bi-tools"></i>
            <?= ($manut ? 'Encerrar Manut.' : 'Manutenção') ?>
        </button>
    </div>
</div>

<!-- Form -->
<div class="card shadow-sm" data-aos="fade-up">
    <div class="card-header fw-semibold">Dados da Página</div>
    <div class="card-body">
        <form id="formEditarPagina" class="row g-3">
            <input type="hidden" name="codigopaginas" value="<?= (int)$idpg ?>">

            <div class="col-md-4">
                <label class="form-label fw-semibold">Sessão</label>
                <select name="idsessaosp" id="idsessaosp" class="form-select" required>
                    <option value="">— selecione —</option>
                    <?php foreach ($sessoes as $s):
                        $sid   = (int)$s['codigosessao'];
                        $snome = $safe($s['nomesessao'] ?? ('Sessão ' . $sid));
                        $sel   = $sid === $idsess ? 'selected' : '';
                    ?>
                        <option value="<?= $sid ?>" <?= $sel ?>><?= $snome ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label fw-semibold">Nome da Página</label>
                <input type="text" id="nomepaginasp" name="nomepaginasp" class="form-control"
                    required maxlength="50" value="<?= $safe($nome) ?>">
                <div class="form-text">Ex.: Usuários, Relatórios, Integrações</div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Ordem</label>
                <input type="number" name="ordemsp" class="form-control" min="1" step="1" value="<?= (int)$ordem ?>">
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Pasta (rota)</label>
                <input type="text" id="pastasp" name="pastasp" class="form-control" maxlength="100"
                    value="<?= $safe($pasta) ?>" placeholder="Ex.: pg_paginaadmin">
                <div class="form-text">Gerada a partir do nome (minúsculas, sem acentos/espaços, prefixo <code>pg_</code>).</div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Ícone (Bootstrap Icons)</label>
                <div class="input-group">
                    <span class="input-group-text bg-light" id="previewIcon"><i class="bi <?= $safe($icone) ?>"></i></span>
                    <input type="text" id="iconesp" name="iconesp" class="form-control" maxlength="30"
                        value="<?= $safe($icone) ?>" placeholder="Ex.: bi-gear, bi-people">
                </div>
                <div class="form-text">Use classes do Bootstrap Icons (ex.: <code>bi-speedometer2</code>).</div>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Visível?</label>
                <select name="visivelsp" id="visivelsp" class="form-select">
                    <option value="1" <?= $visivel ? 'selected' : '' ?>>Sim</option>
                    <option value="0" <?= !$visivel ? 'selected' : '' ?>>Não</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Manutenção?</label>
                <select name="manutencaosp" id="manutencaosp" class="form-select">
                    <option value="0" <?= !$manut ? 'selected' : '' ?>>Não</option>
                    <option value="1" <?= $manut ? 'selected' : '' ?>>Sim</option>
                </select>
            </div>

            <div class="col-12 d-flex justify-content-end pt-2">
                <button type="button" id="btnSalvar" class="btn btn-emerald">
                    <i class="bi bi-check2-circle me-1"></i> Salvar alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080;">
    <div id="toastPg" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">Alterações salvas com sucesso.</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    (function() {
        const idpg = <?= (int)$idpg ?>;
        const enc = <?= json_encode($encId) ?>;

        const btnSalvar = document.getElementById('btnSalvar');
        const toastEl = document.getElementById('toastPg');
        const toastMsgEl = document.getElementById('toastMsg');
        let bsToast = null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            bsToast = toastEl ? new bootstrap.Toast(toastEl) : null;
        } else {
            console.warn('Bootstrap JS não carregado — Toast desativado.');
        }


        const btnToggleVis = document.getElementById('btnToggleVis');
        const btnToggleMan = document.getElementById('btnToggleMan');

        const inpNome = document.getElementById('nomepaginasp');
        const inpRota = document.getElementById('pastasp');
        const inpIcone = document.getElementById('iconesp');
        const previewIc = document.querySelector('#previewIcon i');

        // ===== Auto-gerar rota a partir do nome (respeitando edição manual) =====
        let rotaEditadaManualmente = false;

        // Se usuário mexer manualmente na rota, paramos de sobrepor
        if (inpRota) {
            inpRota.addEventListener('input', () => {
                rotaEditadaManualmente = true;
            });
        }

        function gerarRota(nome) {
            let s = (nome || '').toString().toLowerCase();
            try {
                s = s.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            } catch (e) {}
            s = s.replace(/[^a-z0-9]/g, ''); // remove espaços e símbolos
            if (!s.startsWith('pg_')) s = 'pg_' + s; // prefixo
            return s;
        }

        if (inpNome && inpRota) {
            inpNome.addEventListener('input', () => {
                if (rotaEditadaManualmente) return;
                inpRota.value = gerarRota(inpNome.value);
            });
            // Se nome tem valor e rota está vazia, preenche inicial
            if (inpNome.value && !inpRota.value) {
                inpRota.value = gerarRota(inpNome.value);
            }
        }

        // ===== Pré-visualização do ícone =====
        if (inpIcone && previewIc) {
            const applyIcon = () => {
                const cls = (inpIcone.value || '').trim() || 'bi-file-earmark-code';
                previewIc.className = 'bi ' + cls;
            };
            inpIcone.addEventListener('input', applyIcon);
            applyIcon();
        }

        // ===== Ações rápidas: toggle visibilidade =====
        if (btnToggleVis) {
            btnToggleVis.addEventListener('click', () => {
                const visAtual = parseInt(btnToggleVis.getAttribute('data-vis') || '0', 10);
                fetch('paginasadmin1.0/ajax_paginaToggleVisivel.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            idpg: idpg,
                            visivelsp: (visAtual ? 0 : 1)
                        })
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (j && j.status === 'ok') {
                            // Atualiza rótulo/botão
                            const novo = parseInt(j.visivelsp, 10) === 1;
                            btnToggleVis.setAttribute('data-vis', novo ? '1' : '0');
                            btnToggleVis.classList.toggle('btn-outline-secondary', novo);
                            btnToggleVis.classList.toggle('btn-outline-success', !novo);
                            btnToggleVis.innerHTML = `<i class="bi ${novo ? 'bi-eye-slash' : 'bi-eye'}"></i> ${novo ? 'Ocultar' : 'Exibir'}`;

                            // Atualiza combo "Visível?"
                            const selVis = document.getElementById('visivelsp');
                            if (selVis) selVis.value = novo ? '1' : '0';

                            toastMsgEl && (toastMsgEl.textContent = 'Visibilidade atualizada.');
                            bsToast && bsToast.show();
                        }
                    })
                    .catch(console.error);
            });
        }

        // ===== Ações rápidas: toggle manutenção =====
        if (btnToggleMan) {
            btnToggleMan.addEventListener('click', () => {
                const manAtual = parseInt(btnToggleMan.getAttribute('data-man') || '0', 10);
                fetch('paginasadmin1.0/ajax_paginaToggleManutencao.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            idpg: idpg,
                            manutencaosp: (manAtual ? 0 : 1)
                        })
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (j && j.status === 'ok') {
                            const novo = parseInt(j.manutencaosp, 10) === 1;
                            btnToggleMan.setAttribute('data-man', novo ? '1' : '0');
                            btnToggleMan.classList.toggle('btn-outline-warning', novo);
                            btnToggleMan.classList.toggle('btn-outline-dark', !novo);
                            btnToggleMan.innerHTML = `<i class="bi bi-tools"></i> ${novo ? 'Encerrar Manut.' : 'Manutenção'}`;

                            const selMan = document.getElementById('manutencaosp');
                            if (selMan) selMan.value = novo ? '1' : '0';

                            toastMsgEl && (toastMsgEl.textContent = 'Status de manutenção atualizado.');
                            bsToast && bsToast.show();
                        }
                    })
                    .catch(console.error);
            });
        }

        // ===== Salvar alterações =====
        if (btnSalvar) {
            btnSalvar.addEventListener('click', () => {
                const form = document.getElementById('formEditarPagina');
                const fd = new FormData(form);
                const data = Object.fromEntries(fd.entries());

                // Sanitizações mínimas no front
                data.codigopaginas = parseInt(data.codigopaginas || '0', 10);
                data.idsessaosp = parseInt(data.idsessaosp || '0', 10);
                data.ordemsp = parseInt(data.ordemsp || '0', 10);
                data.visivelsp = parseInt(data.visivelsp || '0', 10);
                data.manutencaosp = parseInt(data.manutencaosp || '0', 10);

                fetch('paginasadmin1.0/ajax_paginaAtualizar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                    .then(r => r.json())
                    .then(j => {
                        if (j && j.status === 'ok') {
                            toastMsgEl && (toastMsgEl.textContent = 'Alterações salvas com sucesso.');
                            bsToast && bsToast.show();
                        } else {
                            toastMsgEl && (toastMsgEl.textContent = (j && j.msg) ? j.msg : 'Falha ao salvar.');
                            if (toastEl) {
                                toastEl.classList.remove('bg-success');
                                toastEl.classList.add('bg-danger');
                                bsToast && bsToast.show();
                                // volta para verde na próxima
                                setTimeout(() => {
                                    toastEl.classList.remove('bg-danger');
                                    toastEl.classList.add('bg-success');
                                }, 1200);
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        toastMsgEl && (toastMsgEl.textContent = 'Erro de rede.');
                        if (toastEl) {
                            toastEl.classList.remove('bg-success');
                            toastEl.classList.add('bg-danger');
                            bsToast && bsToast.show();
                            setTimeout(() => {
                                toastEl.classList.remove('bg-danger');
                                toastEl.classList.add('bg-success');
                            }, 1200);
                        }
                    });
            });
        }
    })();
</script>