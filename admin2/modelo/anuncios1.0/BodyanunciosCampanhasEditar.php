<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
function dataBR2($d)
{
    if (!$d) return '';
    try {
        return (new DateTime($d))->format('d/m/Y');
    } catch (Throwable) {
        return e($d);
    }
}
function moneyBR($n)
{
    if ($n === null || $n === '') return '';
    if (is_numeric($n)) return number_format((float)$n, 2, ',', '.');
    // normaliza "1234,56" => "1.234,56"
    $n = str_replace([' ', 'R$', "\xc2\xa0"], '', (string)$n);
    $n = preg_replace('/[^\d,\.]/', '', $n);
    $n = str_replace('.', '', $n);
    $n = str_replace(',', '.', $n);
    $num = (float)$n;
    return number_format($num, 2, ',', '.');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<div class='alert alert-warning'>ID de campanha inválido.</div>";
    return;
}

// Carrega campanha
try {
    $sql = "SELECT 
                codigocampanhaanuncio,
                chaveACAM,
                idclienteACAM,
                tituloACAM,
                valorACAM,
                datainicioACAM,
                datafimACAM,
                idvendedorACAM,
                visivelACAM,
                dataACAM,
                horaACAM
            FROM a_site_anuncios_campanhas
            WHERE codigocampanhaanuncio = :id
            LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        echo "<div class='alert alert-danger'>Campanha não encontrada.</div>";
        return;
    }
    $cam = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar: " . e($e->getMessage()) . "</div>";
    return;
}

// Carrega cliente (para header/atalhos)
$clienteId = (int)$cam['idclienteACAM'];
$cliNome = '';
try {
    $stCli = $con->prepare("SELECT nomeclienteAC FROM a_site_anuncios_clientes WHERE codigoclienteanuncios = :id LIMIT 1");
    $stCli->bindValue(':id', $clienteId, PDO::PARAM_INT);
    $stCli->execute();
    $r = $stCli->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $cliNome = $r['nomeclienteAC'] ?? '';
    }
} catch (Throwable $e) {/* opcional silenciar */
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-0 text-white">Editar Campanha</h5>
            <small class="text-muted">
                Campanha #<?= (int)$cam['codigocampanhaanuncio'] ?> • Criada em <?= e(dataBR2($cam['dataACAM'])) ?> <?= e($cam['horaACAM']) ?>
                <?php if ($cliNome): ?> • Cliente: <?= e($cliNome) ?> (ID <?= $clienteId ?>)<?php endif; ?>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="anuncios_campanhas.php?cliente=<?= $clienteId ?>" class="btn btn-outline-light btn-sm">← Voltar</a>
            <a href="anuncios_midias.php?campanha=<?= (int)$cam['codigocampanhaanuncio'] ?>" class="btn btn-primary btn-sm">Ver mídias</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body">
            <form id="formCampanhaEditar" novalidate>

                <div class="col-md-6">
                    <label class="form-label">Chave da Campanha</label>
                    <div class="input-group">
                        <input type="text" name="chaveACAM" class="form-control" value="<?= e($cam['chaveACAM']) ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btnCopiarChave">Copiar</button>
                    </div>
                    <div class="form-text">Gerada automaticamente no cadastro (uniqid). Não editável.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label d-block">Campanha visível?</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="swVisivel" name="visivelACAM"
                            <?= ((int)$cam['visivelACAM'] === 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="swVisivel">
                            <?= ((int)$cam['visivelACAM'] === 1) ? 'Sim (ON)' : 'Não (OFF)' ?>
                        </label>
                    </div>
                </div>
                <input type="hidden" name="id" value="<?= (int)$cam['codigocampanhaanuncio'] ?>">
                <input type="hidden" name="idclienteACAM" value="<?= $clienteId ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Título da Campanha <span class="text-danger">*</span></label>
                        <input type="text" name="tituloACAM" class="form-control" maxlength="140" required
                            value="<?= e($cam['tituloACAM']) ?>">
                        <div class="invalid-feedback">Informe o título.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Valor (R$) <span class="text-danger">*</span></label>
                        <input type="text" name="valorACAM" class="form-control" required
                            value="<?= e(moneyBR($cam['valorACAM'])) ?>">
                        <div class="form-text">Formato: 1.500,00</div>
                        <div class="invalid-feedback">Informe o valor.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">ID do Vendedor</label>
                        <input type="text" name="idvendedorACAM" class="form-control"
                            value="<?= e($cam['idvendedorACAM']) ?>" placeholder="Opcional">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data Início <span class="text-danger">*</span></label>
                        <input type="date" name="datainicioACAM" class="form-control" required
                            value="<?= e($cam['datainicioACAM']) ?>">
                        <div class="invalid-feedback">Informe a data inicial.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Data Fim <span class="text-danger">*</span></label>
                        <input type="date" name="datafimACAM" class="form-control" required
                            value="<?= e($cam['datafimACAM']) ?>">
                        <div class="invalid-feedback">Informe a data final.</div>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-success">Salvar Alterações</button>
                        <a href="anuncios_campanhas.php?cliente=<?= $clienteId ?>" class="btn btn-outline-secondary">Cancelar</a>
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
    // Máscara + validação + AJAX
    (function() {
        const form = document.getElementById('formCampanhaEditar');
        const toastEl = document.getElementById('toastRetorno');
        const toastMsg = document.getElementById('toastMsg');

        if (window.jQuery && typeof jQuery.fn.mask === 'function') {
            jQuery('[name=\"valorACAM\"]').mask('000.000.000,00', {
                reverse: true
            });
        }

        function showToast(msg) {
            toastMsg.textContent = msg;
            new bootstrap.Toast(toastEl, {
                delay: 2500
            }).show();
        }

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

            form.classList.add('was-validated');

            const dtIni = form.datainicioACAM.value;
            const dtFim = form.datafimACAM.value;
            if (!datasValidas(dtIni, dtFim)) {
                showToast('A data final deve ser maior ou igual à data inicial.');
                return;
            }
            if (!form.checkValidity()) {
                showToast('Verifique os campos obrigatórios.');
                return;
            }

            const fd = new FormData(form);
            try {
                const resp = await fetch('anuncios1.0/ajax_campanhaUpdate.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();

                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Campanha atualizada com sucesso!');
                } else {
                    showToast(json.mensagem || 'Não foi possível salvar as alterações.');
                }
            } catch (e) {
                showToast('Erro inesperado no envio. Tente novamente.');
            }
        }, false);
    })();
</script>