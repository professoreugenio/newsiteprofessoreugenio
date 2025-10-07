<?php
// ...
$idParam  = isset($_GET['id'])  ? (string)$_GET['id']  : '';
$mdParam  = isset($_GET['md'])  ? (string)$_GET['md']  : '';
$pubParam = isset($_GET['pub']) ? (string)$_GET['pub'] : '';
?>
<?php
// --- MÓDULO: Adicionar Perguntas ao Questionário (modulado) ---
function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}



if (!isset($idPublicacao)) {
    $idPublicacao = 0;
}
$idPub = (int)$idPublicacao;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-ui-checks-grid me-2"></i> Novo Questionário da Publicação
        <small class="text-muted ms-2">#<?= h($idPub) ?></small>
    </h5>
    <a href="cursos_publicacaoQuestionarioNovo.php?idpublicacao=<?= h($idPub) ?>" class="btn btn-outline-primary">
        <i class="bi bi-arrow-clockwise me-1"></i> Atualizar
    </a>
</div>

<!-- Seletor de Tipo -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="tipoSelect" class="form-label fw-semibold">Tipo de questionário</label>
                <select id="tipoSelect" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="1">1 - Pergunta e Resposta</option>
                    <option value="2">2 - Marque a opção correta (A, B, C, D)</option>
                    <option value="3">3 - Marque V e F (A, B, C, D)</option>
                </select>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">Escolha o tipo para exibir o formulário correspondente.</small>
            </div>
        </div>
    </div>
</div>

<!-- Alertas -->
<div id="qAlert" class="alert d-none" role="alert"></div>

<!-- FORM TIPO 1: Pergunta e Resposta -->
<form id="formTipo1" class="card d-none mb-4" novalidate>
    <div class="card-body">
        <input type="hidden" name="idpublicacao" value="<?= h($idPub) ?>">
        <input type="hidden" name="tipocq" value="1">
        <div class="mb-3">
            <label class="form-label">Título da pergunta</label>
            <input type="text" name="titulocq" class="form-control" maxlength="255">
        </div>
        <div class="mb-3">
            <label class="form-label">Resposta</label>
            <textarea name="respostacq" class="form-control" rows="4"></textarea>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-1"></i> Salvar
            </button>
        </div>
    </div>
</form>

<!-- FORM TIPO 2: Múltipla escolha A/B/C/D -->
<form id="formTipo2" class="card d-none mb-4" novalidate>
    <div class="card-body">
        <input type="hidden" name="idpublicacao" value="<?= h($idPub) ?>">
        <input type="hidden" name="tipocq" value="2">
        <div class="mb-3">
            <label class="form-label">Título da pergunta</label>
            <input type="text" name="titulocq" class="form-control" maxlength="255">
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Opção A</label>
                <input type="text" name="opcaoa" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Opção B</label>
                <input type="text" name="opcaob" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Opção C</label>
                <input type="text" name="opcaoc" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Opção D</label>
                <input type="text" name="opcaod" class="form-control">
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <label class="form-label">Correta</label>
                <select name="correta" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
        </div>
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-1"></i> Salvar
            </button>
        </div>
    </div>
</form>

<!-- FORM TIPO 3: Verdadeiro/Falso por alternativa -->
<form id="formTipo3" class="card d-none mb-4" novalidate>
    <div class="card-body">
        <input type="hidden" name="idpublicacao" value="<?= h($idPub) ?>">
        <input type="hidden" name="tipocq" value="3">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulocq" class="form-control" maxlength="255">
        </div>
        <div class="row g-3">
            <!-- A -->
            <div class="col-md-8">
                <label class="form-label">Opção A</label>
                <input type="text" name="opcaoa" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">A é</label>
                <select name="vf_a" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="V">V</option>
                    <option value="F">F</option>
                </select>
            </div>
            <!-- B -->
            <div class="col-md-8">
                <label class="form-label">Opção B</label>
                <input type="text" name="opcaob" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">B é</label>
                <select name="vf_b" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="V">V</option>
                    <option value="F">F</option>
                </select>
            </div>
            <!-- C -->
            <div class="col-md-8">
                <label class="form-label">Opção C</label>
                <input type="text" name="opcaoc" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">C é</label>
                <select name="vf_c" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="V">V</option>
                    <option value="F">F</option>
                </select>
            </div>
            <!-- D -->
            <div class="col-md-8">
                <label class="form-label">Opção D</label>
                <input type="text" name="opcaod" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">D é</label>
                <select name="vf_d" class="form-select">
                    <option value="">Selecione...</option>
                    <option value="V">V</option>
                    <option value="F">F</option>
                </select>
            </div>
        </div>
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-1"></i> Salvar
            </button>
        </div>
    </div>
</form>

<!-- Lista de Perguntas já enviadas -->
<h6 class="mt-4 mb-2"><i class="bi bi-list-check me-2"></i> Perguntas já enviadas</h6>
<div id="listaPerguntas"></div>

<script>
    (function() {
        const tipoSelect = document.getElementById('tipoSelect');
        const form1 = document.getElementById('formTipo1');
        const form2 = document.getElementById('formTipo2');
        const form3 = document.getElementById('formTipo3');
        const qAlert = document.getElementById('qAlert');
        const idpub = <?= json_encode((int)$idPub) ?>;
        const extra = <?= json_encode(['id' => $idParam, 'md' => $mdParam, 'pub' => $pubParam]) ?>;

        function showAlert(kind, msg) {
            qAlert.className = 'alert alert-' + kind;
            qAlert.textContent = msg;
            qAlert.classList.remove('d-none');
            setTimeout(() => qAlert.classList.add('d-none'), 3000);
        }

        function setRequired(el, flag) {
            if (el) el.toggleAttribute('required', !!flag);
        }

        function clearForms() {
            form1.reset();
            form2.reset();
            form3.reset();
        }

        function toggleForms(tipo) {
            form1.classList.add('d-none');
            form2.classList.add('d-none');
            form3.classList.add('d-none');
            [...document.querySelectorAll('#formTipo1 [name],#formTipo2 [name],#formTipo3 [name]')].forEach(i => i.removeAttribute('required'));
            if (tipo === '1') {
                form1.classList.remove('d-none');
                setRequired(form1.querySelector('[name="titulocq"]'), true);
                setRequired(form1.querySelector('[name="respostacq"]'), true);
            }
            if (tipo === '2') {
                form2.classList.remove('d-none');
                ['titulocq', 'opcaoa', 'opcaob', 'opcaoc', 'opcaod', 'correta'].forEach(n => setRequired(form2.querySelector('[name="' + n + '"]'), true));
            }
            if (tipo === '3') {
                form3.classList.remove('d-none');
                ['titulocq', 'opcaoa', 'opcaob', 'opcaoc', 'opcaod', 'vf_a', 'vf_b', 'vf_c', 'vf_d'].forEach(n => setRequired(form3.querySelector('[name="' + n + '"]'), true));
            }
        }
        tipoSelect.addEventListener('change', e => toggleForms(e.target.value));

        function btnLoading(btn, loading) {
            if (!btn) return;
            if (loading) {
                btn.disabled = true;
                btn.dataset.oldHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            } else {
                btn.disabled = false;
                if (btn.dataset.oldHtml) btn.innerHTML = btn.dataset.oldHtml;
            }
        }

        async function salvar(form, tipo, btn) {
            try {
                btnLoading(btn, true);
                const fd = new FormData(form);
                fd.append('tipocq', tipo);
                const r = await fetch('questionariov1.0/ajax_insertPergunta.php', {
                    method: 'POST',
                    body: fd
                });
                const j = await r.json();
                if (!j?.success) throw new Error(j?.message || 'Erro ao salvar.');
                clearForms();
                showAlert('success', 'Pergunta salva!');
                await loadLista();
            } catch (err) {
                showAlert('danger', err.message || 'Falha ao salvar.');
            } finally {
                btnLoading(btn, false);
            }
        }
        form1.addEventListener('submit', e => {
            e.preventDefault();
            salvar(form1, '1', form1.querySelector('button[type="submit"]'));
        });
        form2.addEventListener('submit', e => {
            e.preventDefault();
            salvar(form2, '2', form2.querySelector('button[type="submit"]'));
        });
        form3.addEventListener('submit', e => {
            e.preventDefault();
            salvar(form3, '3', form3.querySelector('button[type="submit"]'));
        });

        async function loadLista() {
            const cont = document.getElementById('listaPerguntas');
            const params = new URLSearchParams({
                idpublicacao: String(idpub),
                id: extra.id || '',
                md: extra.md || '',
                pub: extra.pub || ''
            });
            const r = await fetch('questionariov1.0/ajax_listPerguntas.php?' + params.toString());
            cont.innerHTML = await r.text();
        }

        // Delegação de eventos para EXCLUIR
        document.getElementById('listaPerguntas').addEventListener('click', async (e) => {
            const btn = e.target.closest('.btn-del-pergunta');
            if (!btn) return;
            const codigo = btn.dataset.cod;
            if (!codigo) return;
            if (!confirm('Confirma excluir esta pergunta do questionário?')) return;

            try {
                btnLoading(btn, true);
                const fd = new FormData();
                fd.append('codigo', codigo);
                fd.append('idpublicacao', String(idpub));
                const r = await fetch('questionariov1.0/ajax_deletePergunta.php', {
                    method: 'POST',
                    body: fd
                });
                const j = await r.json();
                if (!j?.success) throw new Error(j?.message || 'Erro ao excluir.');
                showAlert('success', 'Pergunta excluída.');
                await loadLista();
            } catch (err) {
                showAlert('danger', err.message || 'Falha ao excluir.');
            } finally {
                btnLoading(btn, false);
            }
        });

        // Carrega lista ao abrir
        loadLista();
    })();
</script>