<?php
function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<div class='alert alert-warning'>ID do cliente inválido.</div>";
    return;
}

// Carrega cliente
try {
    $sql = "SELECT 
                codigoclienteanuncios,
                nomeclienteAC,
                idcategoriaAC,
                celularAC,
                whatsappAC,
                linksiteAC,
                dataAC,
                horaAC
            FROM a_site_anuncios_clientes
            WHERE codigoclienteanuncios = :id
            LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo "<div class='alert alert-danger'>Cliente não encontrado.</div>";
        return;
    }
    $cli = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar cliente: " . e($e->getMessage()) . "</div>";
    return;
}

// Carrega categorias
$cats = [];
try {
    $sqlCat = "SELECT codigocategoriaanuncio, nomecategoriaACT
               FROM a_site_anuncios_categorias
               ORDER BY nomecategoriaACT ASC";
    $stmtCat = $con->prepare($sqlCat);
    $stmtCat->execute();
    $cats = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar categorias: " . e($e->getMessage()) . "</div>";
    return;
}

// Helpers
function dataBR2($date)
{
    if (!$date) return '';
    try {
        return (new DateTime($date))->format('d/m/Y');
    } catch (Throwable) {
        return e($date);
    }
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="mb-0 text-white">Editar Cliente</h5>
            <small class="text-muted">ID #<?= (int)$cli['codigoclienteanuncios'] ?> • Cadastrado em <?= e(dataBR2($cli['dataAC'])) ?> <?= e($cli['horaAC']) ?></small>
        </div>
        <div class="d-flex gap-2">
            <a href="anuncios.php" class="btn btn-outline-light btn-sm">← Voltar</a>
            <a href="anuncios_campanhas.php?cliente=<?= (int)$cli['codigoclienteanuncios'] ?>" class="btn btn-primary btn-sm">Ver Campanhas</a>
        </div>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body">
            <form id="formClienteEditar" novalidate>
                <input type="hidden" name="id" value="<?= (int)$cli['codigoclienteanuncios'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome do Cliente <span class="text-danger">*</span></label>
                        <input type="text" name="nomeclienteAC" class="form-control" required maxlength="120"
                            value="<?= e($cli['nomeclienteAC']) ?>">
                        <div class="invalid-feedback">Informe o nome do cliente.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select name="categoriaAC" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($cats as $cat):
                                $sel = ((int)$cli['idcategoriaAC'] === (int)$cat['codigocategoriaanuncio']) ? 'selected' : ''; ?>
                                <option value="<?= (int)$cat['codigocategoriaanuncio'] ?>" <?= $sel ?>>
                                    <?= e($cat['nomecategoriaACT']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione uma categoria.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celularAC" class="form-control" placeholder="(85) 99999-9999"
                            value="<?= e($cli['celularAC']) ?>">
                        <div class="form-text">Somente dígitos serão mantidos no salvamento.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsappAC" class="form-control" placeholder="(85) 99999-9999"
                            value="<?= e($cli['whatsappAC']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Site / Link</label>
                        <input type="url" name="linksiteAC" class="form-control" placeholder="https://exemplo.com.br"
                            value="<?= e($cli['linksiteAC']) ?>">
                    </div>

                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-success">Salvar Alterações</button>
                        <button type="reset" class="btn btn-outline-secondary">Restaurar</button>
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
    // Validação + AJAX update
    (function() {
        const form = document.getElementById('formClienteEditar');
        const toastEl = document.getElementById('toastRetorno');
        const toastMsg = document.getElementById('toastMsg');

        function showToast(msg) {
            toastMsg.textContent = msg;
            const t = new bootstrap.Toast(toastEl, {
                delay: 2500
            });
            t.show();
        }

        form.addEventListener('submit', async function(ev) {
            ev.preventDefault();
            ev.stopPropagation();

            form.classList.add('was-validated');
            if (!form.checkValidity()) {
                showToast('Verifique os campos obrigatórios.');
                return;
            }

            const fd = new FormData(form);

            try {
                const resp = await fetch('anuncios1.0/ajax_clienteUpdate.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();

                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Dados atualizados com sucesso!');
                } else {
                    showToast(json.mensagem || 'Não foi possível salvar.');
                }
            } catch (e) {
                showToast('Erro inesperado no envio. Tente novamente.');
            }
        }, false);
    })();
</script>