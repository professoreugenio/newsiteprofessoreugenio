<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$clienteId = isset($_GET['cliente']) ? (int)$_GET['cliente'] : 0;
if ($clienteId <= 0) {
    echo "<div class='alert alert-warning'>Cliente inválido.</div>";
    return;
}

// Nome do cliente (opcional no cabeçalho)
$cliNome = '';
try {
    $st = $con->prepare("SELECT nomeclienteAC FROM a_site_anuncios_clientes WHERE codigoclienteanuncios = :id LIMIT 1");
    $st->bindValue(':id', $clienteId, PDO::PARAM_INT);
    $st->execute();
    if ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $cliNome = $r['nomeclienteAC'];
    }
} catch (Throwable $e) { /* silencioso */
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-0 text-white">Adicionar Campanha</h5>
            <?php if ($cliNome): ?>
                <small class="text-muted">Cliente: <?= e($cliNome) ?> (ID <?= (int)$clienteId ?>)</small>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
            <a href="anuncios_campanhas.php?cliente=<?= (int)$clienteId ?>" class="btn btn-outline-light btn-sm">← Voltar</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body">
            <form id="formCampanhaNovo" novalidate>
                <input type="hidden" name="idclienteACAM" value="<?= (int)$clienteId ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Título da Campanha <span class="text-danger">*</span></label>
                        <input type="text" name="tituloACAM" class="form-control" maxlength="140" required placeholder="Ex.: Inauguração da Loja - Semana 1">
                        <div class="invalid-feedback">Informe o título.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                        <input type="text" name="valorACAM" class="form-control" required placeholder="0,00">
                        <div class="form-text">Ex.: 1.500,00</div>
                        <div class="invalid-feedback">Informe o valor.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ID do Vendedor</label>
                        <input type="text" name="idvendedorACAM" class="form-control" placeholder="Opcional">
                    </div>

                    <!-- NOVO: seletor de período por dias -->
                    <div class="col-12">
                        <label class="form-label d-block">Duração (dias)</label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="planoDias" id="dias30" value="30" checked>
                                <label class="form-check-label" for="dias30">30 dias</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="planoDias" id="dias60" value="60">
                                <label class="form-check-label" for="dias60">60 dias</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="planoDias" id="dias90" value="90">
                                <label class="form-check-label" for="dias90">90 dias</label>
                            </div>
                        </div>
                        <div class="form-text">Ao escolher a data inicial, a data final será preenchida automaticamente conforme a duração.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data Início <span class="text-danger">*</span></label>
                        <input type="date" name="datainicioACAM" class="form-control" required>
                        <div class="invalid-feedback">Informe a data inicial.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data Fim <span class="text-danger">*</span></label>
                        <input type="date" name="datafimACAM" class="form-control" required>
                        <div class="invalid-feedback">Informe a data final.</div>
                        <div class="form-text">Preenchida automaticamente, mas você pode ajustar manualmente se necessário.</div>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-success">Salvar Campanha</button>
                        <button type="reset" class="btn btn-outline-secondary">Limpar</button>
                    </div>
                </div>
            </form>

            <!-- Toast -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
                <div id="toastRetorno" class="toast text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body" id="toastMsg">...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Máscara monetária (se jQuery Mask estiver disponível)
    if (window.jQuery && typeof jQuery.fn.mask === 'function') {
        jQuery('[name="valorACAM"]').mask('000.000.000,00', {
            reverse: true
        });
    }

    (function() {
        const form = document.getElementById('formCampanhaNovo');
        const toastEl = document.getElementById('toastRetorno');
        const toastMsg = document.getElementById('toastMsg');
        const inputIni = form.querySelector('[name="datainicioACAM"]');
        const inputFim = form.querySelector('[name="datafimACAM"]');
        const radios = form.querySelectorAll('input[name="planoDias"]');

        function showToast(msg) {
            toastMsg.textContent = msg;
            new bootstrap.Toast(toastEl, {
                delay: 2500
            }).show();
        }

        // Retorna valor do rádio selecionado (30/60/90)
        function getDiasSelecionados() {
            const r = form.querySelector('input[name="planoDias"]:checked');
            return r ? parseInt(r.value, 10) : 30;
        }

        // Soma N dias a uma data AAAA-MM-DD e devolve no mesmo formato
        function addDias(dataISO, dias) {
            const [y, m, d] = dataISO.split('-').map(n => parseInt(n, 10));
            const dt = new Date(y, (m - 1), d);
            dt.setDate(dt.getDate() + dias);
            const yy = dt.getFullYear();
            const mm = String(dt.getMonth() + 1).padStart(2, '0');
            const dd = String(dt.getDate()).padStart(2, '0');
            return `${yy}-${mm}-${dd}`;
        }

        // Recalcula a data final quando:
        // - escolher/alterar a data inicial
        // - mudar o rádio de duração
        function recalcularFim() {
            const di = inputIni.value;
            if (!di) return;
            const dias = getDiasSelecionados();
            // regra de negócio: se queremos "30 dias corridos", somar 29 ou 30?
            // Aqui somamos exatamente 'dias' a partir do DI (ex.: 01 + 30 => 31)
            inputFim.value = addDias(di, dias);
            // também ajusta min de data fim para evitar voltar antes do início
            inputFim.min = di;
        }

        inputIni.addEventListener('change', recalcularFim);
        radios.forEach(r => r.addEventListener('change', recalcularFim));

        // Validação + envio
        function datasValidas(dtIni, dtFim) {
            if (!dtIni || !dtFim) return false;
            try {
                return new Date(dtIni) <= new Date(dtFim);
            } catch (e) {
                return false;
            }
        }

        form.addEventListener('submit', async function(ev) {
            ev.preventDefault();
            ev.stopPropagation();

            // garante coerência antes de validar
            if (inputIni.value && !inputFim.value) recalcularFim();

            form.classList.add('was-validated');
            if (!datasValidas(inputIni.value, inputFim.value)) {
                showToast('A data final deve ser maior ou igual à data inicial.');
                return;
            }
            if (!form.checkValidity()) {
                showToast('Verifique os campos obrigatórios.');
                return;
            }

            const fd = new FormData(form);

            try {
                const resp = await fetch('anuncios1.0/ajax_campanhaInsert.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();
                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Campanha criada com sucesso!');
                    setTimeout(() => {
                        window.location.href = 'anuncios_campanhas.php?cliente=<?= (int)$clienteId ?>';
                    }, 900);
                } else {
                    showToast(json.mensagem || 'Não foi possível salvar a campanha.');
                }
            } catch (e) {
                showToast('Erro inesperado no envio. Tente novamente.');
            }
        }, false);
    })();
</script>