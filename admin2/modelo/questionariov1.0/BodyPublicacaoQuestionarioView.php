<?php
// --- MÓDULO: View/Edição de Questão do Questionário ---
// Requisitos: $idPublicacao (int) definido na página pai, e $_GET['idQuest'].

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$idPub = isset($idPublicacao) ? (int)$idPublicacao : 0;
$idQuest = isset($_GET['idQuest']) ? (int)$_GET['idQuest'] : 0;

// Parâmetros extras para manter navegação
$idParam  = isset($_GET['id'])  ? (string)$_GET['id']  : '';
$mdParam  = isset($_GET['md'])  ? (string)$_GET['md']  : '';
$pubParam = isset($_GET['pub']) ? (string)$_GET['pub'] : '';

$erro = null;
$row = null;

try {
    if ($idPub <= 0) throw new RuntimeException('Publicação inválida.');
    if ($idQuest <= 0) throw new RuntimeException('Questão não informada.');

    $pdo = config::connect();
    $st = $pdo->prepare("
        SELECT codigoquestionario, idpublicacaocq, tipocq, titulocq, idmodulocq, ordemcq,
               respostacq, opcaoa, opcaob, opcaoc, opcaod, visivelcq, datacq, horacq
        FROM a_curso_questionario
        WHERE codigoquestionario = :cod AND idpublicacaocq = :idpub
        LIMIT 1
    ");
    $st->bindValue(':cod', $idQuest, PDO::PARAM_INT);
    $st->bindValue(':idpub', $idPub, PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) throw new RuntimeException('Questão não encontrada para esta publicação.');
} catch (Throwable $th) {
    $erro = $th->getMessage();
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-0">
            <i class="bi bi-pencil-square me-2"></i> Editar Questão
            <small class="text-muted ms-2">#<?= h($idQuest) ?></small>
        </h5>
        <small class="text-muted">Publicação #<?= h($idPub) ?></small>
    </div>

    <a class="btn btn-primary"
        href="cursos_publicacaoQuestionarioNovo.php?id=<?= h($idParam) ?>&md=<?= h($mdParam) ?>&pub=<?= h($pubParam) ?>&idpublicacao=<?= h($idPub) ?>">
        <i class="bi bi-plus-circle me-1"></i> Adicionar novo questionário
    </a>
</div>

<?php if ($erro): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= h($erro) ?></div>
    <?php return; ?>
<?php endif; ?>

<?php
// Pré-processa valores
$tipo   = (int)$row['tipocq'];
$titulo = (string)($row['titulocq'] ?? '');
$oa = (string)($row['opcaoa'] ?? '');
$ob = (string)($row['opcaob'] ?? '');
$oc = (string)($row['opcaoc'] ?? '');
$od = (string)($row['opcaod'] ?? '');
$resposta = (string)($row['respostacq'] ?? '');
$correta = ($tipo === 2) ? strtoupper($resposta) : '';
$vf = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
if ($tipo === 3 && $resposta !== '') {
    // Esperado: A=V;B=F;C=V;D=F
    foreach (explode(';', $resposta) as $pair) {
        [$k, $v] = array_map('trim', explode('=', $pair) + [null, null]);
        if (in_array($k, ['A', 'B', 'C', 'D'], true) && in_array($v, ['V', 'F'], true)) {
            $vf[$k] = $v;
        }
    }
}
?>

<!-- Alertas -->
<div id="qvAlert" class="alert d-none" role="alert"></div>

<!-- Form principal (dinâmico por tipo) -->
<form id="formQuestao" class="card mb-4" novalidate>
    <div class="card-body">
        <input type="hidden" name="codigo" value="<?= h($idQuest) ?>">
        <input type="hidden" name="idpublicacao" value="<?= h($idPub) ?>">

        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Tipo de questão</label>
                <select id="tipoSelect" name="tipocq" class="form-select" required>
                    <option value="1" <?= $tipo === 1 ? 'selected' : '' ?>>1 - Pergunta e Resposta</option>
                    <option value="2" <?= $tipo === 2 ? 'selected' : '' ?>>2 - Marque a opção correta (A, B, C, D)</option>
                    <option value="3" <?= $tipo === 3 ? 'selected' : '' ?>>3 - Marque V e F (A, B, C, D)</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Título da pergunta</label>
                <input type="text" name="titulocq" class="form-control" maxlength="255" value="<?= h($titulo) ?>" required>
            </div>
        </div>

        <!-- TIPO 1 -->
        <div id="boxTipo1" class="mt-3 <?= $tipo === 1 ? '' : 'd-none' ?>">
            <label class="form-label">Resposta</label>
            <textarea name="respostacq" class="form-control" rows="4" <?= $tipo === 1 ? 'required' : '' ?>><?= $tipo === 1 ? h($resposta) : '' ?></textarea>
        </div>

        <!-- TIPO 2 -->
        <div id="boxTipo2" class="mt-3 <?= $tipo === 2 ? '' : 'd-none' ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Opção A</label>
                    <input type="text" name="opcaoa" class="form-control" value="<?= h($tipo === 2 ? $oa : '') ?>" <?= $tipo === 2 ? 'required' : '' ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Opção B</label>
                    <input type="text" name="opcaob" class="form-control" value="<?= h($tipo === 2 ? $ob : '') ?>" <?= $tipo === 2 ? 'required' : '' ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Opção C</label>
                    <input type="text" name="opcaoc" class="form-control" value="<?= h($tipo === 2 ? $oc : '') ?>" <?= $tipo === 2 ? 'required' : '' ?>>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Opção D</label>
                    <input type="text" name="opcaod" class="form-control" value="<?= h($tipo === 2 ? $od : '') ?>" <?= $tipo === 2 ? 'required' : '' ?>>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Correta</label>
                    <select name="correta" class="form-select" <?= $tipo === 2 ? 'required' : '' ?>>
                        <option value="">Selecione...</option>
                        <?php foreach (['A', 'B', 'C', 'D'] as $L): ?>
                            <option value="<?= $L ?>" <?= $correta === $L ? 'selected' : '' ?>><?= $L ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- TIPO 3 -->
        <div id="boxTipo3" class="mt-3 <?= $tipo === 3 ? '' : 'd-none' ?>">
            <div class="row g-3">
                <?php foreach (['A', 'B', 'C', 'D'] as $L): ?>
                    <div class="col-md-8">
                        <label class="form-label">Opção <?= $L ?></label>
                        <input type="text" name="opcao<?= strtolower($L) ?>" class="form-control"
                            value="<?= h($tipo === 3 ? ${strtolower($L)} : '') ?>" <?= $tipo === 3 ? 'required' : '' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><?= $L ?> é</label>
                        <select name="vf_<?= strtolower($L) ?>" class="form-select" <?= $tipo === 3 ? 'required' : '' ?>>
                            <option value="">Selecione...</option>
                            <option value="V" <?= ($vf[$L] ?? '') === 'V' ? 'selected' : '' ?>>V</option>
                            <option value="F" <?= ($vf[$L] ?? '') === 'F' ? 'selected' : '' ?>>F</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-1"></i> Salvar alterações
            </button>
        </div>
    </div>
</form>

<!-- Lista de Questões da Publicação -->
<h6 class="mt-4 mb-2"><i class="bi bi-list-check me-2"></i> Questões desta publicação</h6>

<?php
// Carrega lista completa
$stL = $pdo->prepare("
    SELECT codigoquestionario, titulocq, ordemcq, visivelcq
    FROM a_curso_questionario
    WHERE idpublicacaocq = :idpub
    ORDER BY ordemcq ASC, codigoquestionario ASC
");
$stL->bindValue(':idpub', $idPub, PDO::PARAM_INT);
$stL->execute();
$rowsList = $stL->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!$rowsList): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Nenhuma questão cadastrada.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:90px">Ordem</th>
                    <th>Pergunta</th>
                    <th style="width:130px" class="text-center">Visível</th>
                    <th style="width:160px" class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="listaQuestoes">
                <?php foreach ($rowsList as $r):
                    $cod = (int)$r['codigoquestionario'];
                    $vis = (int)$r['visivelcq'] === 1;
                    $badge = $vis ? 'bg-success' : 'bg-secondary';
                    $txt = $vis ? 'Visível' : 'Oculto';
                    $linkEdit = 'cursos_publicacaoQuestionarioView.php'
                        . '?id='  . rawurlencode($idParam)
                        . '&md='  . rawurlencode($mdParam)
                        . '&pub=' . rawurlencode($pubParam)
                        . '&idQuest=' . $cod;
                ?>
                    <tr id="row-<?= $cod ?>">
                        <td><span class="badge bg-dark-subtle text-dark-emphasis"><?= h($r['ordemcq']) ?></span></td>
                        <td class="fw-semibold">
                            <a class="link-primary text-decoration-none" href="<?= h($linkEdit) ?>">
                                <?= h($r['titulocq']) ?>
                            </a>
                        </td>
                        <td class="text-center">
                            <span id="badge-vis-<?= $cod ?>" class="badge <?= $badge ?>"><?= $txt ?></span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle"
                                    data-cod="<?= $cod ?>" title="<?= $vis ? 'Tornar oculto' : 'Tornar visível' ?>">
                                    <i id="ico-vis-<?= $cod ?>" class="bi <?= $vis ? 'bi-eye' : 'bi-eye-slash' ?>"></i>
                                </button>
                                <a class="btn btn-sm btn-outline-primary" href="<?= h($linkEdit) ?>">
                                    <i class="bi bi-box-arrow-up-right"></i> Abrir
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<script>
    (function() {
        const tipoSelect = document.getElementById('tipoSelect');
        const box1 = document.getElementById('boxTipo1');
        const box2 = document.getElementById('boxTipo2');
        const box3 = document.getElementById('boxTipo3');
        const form = document.getElementById('formQuestao');
        const alertBox = document.getElementById('qvAlert');
        const idpub = <?= json_encode($idPub) ?>;

        function showAlert(kind, msg) {
            alertBox.className = 'alert alert-' + kind;
            alertBox.textContent = msg;
            alertBox.classList.remove('d-none');
            setTimeout(() => alertBox.classList.add('d-none'), 3000);
        }

        function setReq(sel, flag) {
            if (!sel) return;
            sel.toggleAttribute('required', !!flag);
        }

        function toggleBoxes() {
            const t = tipoSelect.value;
            box1.classList.add('d-none');
            box2.classList.add('d-none');
            box3.classList.add('d-none');
            // limpa required
            form.querySelectorAll('[name]').forEach(i => i.removeAttribute('required'));

            if (t === '1') {
                box1.classList.remove('d-none');
                setReq(form.querySelector('[name="titulocq"]'), true);
                setReq(form.querySelector('[name="respostacq"]'), true);
            }
            if (t === '2') {
                box2.classList.remove('d-none');
                ['titulocq', 'opcaoa', 'opcaob', 'opcaoc', 'opcaod', 'correta'].forEach(n => setReq(form.querySelector('[name="' + n + '"]'), true));
            }
            if (t === '3') {
                box3.classList.remove('d-none');
                ['titulocq', 'opcaoa', 'opcaob', 'opcaoc', 'opcaod', 'vf_a', 'vf_b', 'vf_c', 'vf_d'].forEach(n => setReq(form.querySelector('[name="' + n + '"]'), true));
            }
        }

        tipoSelect.addEventListener('change', toggleBoxes);
        // Aplica estado inicial
        toggleBoxes();

        function btnLoading(btn, on) {
            if (!btn) return;
            if (on) {
                btn.disabled = true;
                btn.dataset.oldHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            } else {
                btn.disabled = false;
                if (btn.dataset.oldHtml) btn.innerHTML = btn.dataset.oldHtml;
            }
        }

        // Salvar alterações
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            try {
                btnLoading(btn, true);
                const fd = new FormData(form);
                const r = await fetch('questionariov1.0/ajax_updatePergunta.php', {
                    method: 'POST',
                    body: fd
                });
                const j = await r.json();
                if (!j?.success) throw new Error(j?.message || 'Erro ao salvar.');
                showAlert('success', 'Alterações salvas!');
            } catch (err) {
                showAlert('danger', err.message || 'Falha ao salvar.');
            } finally {
                btnLoading(btn, false);
            }
        });

        // Toggle visibilidade na lista (delegação)
        const lista = document.getElementById('listaQuestoes');
        if (lista) {
            lista.addEventListener('click', async (e) => {
                const btn = e.target.closest('.btn-toggle');
                if (!btn) return;
                const cod = btn.dataset.cod;
                if (!cod) return;
                btnLoading(btn, true);
                try {
                    const fd = new FormData();
                    fd.append('codigo', cod);
                    fd.append('idpublicacao', String(idpub));
                    const r = await fetch('questionariov1.0/ajax_toggleVisivel.php', {
                        method: 'POST',
                        body: fd
                    });
                    const j = await r.json();
                    if (!j?.success) throw new Error(j?.message || 'Erro ao atualizar visibilidade.');
                    const icon = document.getElementById('ico-vis-' + cod);
                    const badge = document.getElementById('badge-vis-' + cod);
                    if (icon) icon.className = 'bi ' + (j.visivel === 1 ? 'bi-eye' : 'bi-eye-slash');
                    if (badge) {
                        badge.className = 'badge ' + (j.visivel === 1 ? 'bg-success' : 'bg-secondary');
                        badge.textContent = j.visivel === 1 ? 'Visível' : 'Oculto';
                    }
                } catch (err) {
                    showAlert('danger', err.message || 'Falha ao alterar visibilidade.');
                } finally {
                    btnLoading(btn, false);
                }
            });
        }
    })();
</script>