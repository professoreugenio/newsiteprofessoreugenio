<?php
// Carrega categorias para o select
$cats = [];
try {
    $sqlCat = "SELECT codigocategoriaanuncio, nomecategoriaACT 
               FROM a_site_anuncios_categorias 
               ORDER BY nomecategoriaACT ASC";
    $stmtCat = $con->prepare($sqlCat);
    $stmtCat->execute();
    $cats = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar categorias: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
    return;
}

function e($v)
{
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid px-0">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <h5 class="mb-0 text-white">Novo Cliente de Anúncios</h5>
        <a href="anuncios.php" class="btn btn-outline-light btn-sm">← Voltar</a>
    </div>

    <div class="card bg-dark text-light border-0 shadow-sm">
        <div class="card-body">
            <form id="formClienteNovo" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome do Cliente <span class="text-danger">*</span></label>
                        <input type="text" name="nomeclienteAC" class="form-control" required maxlength="120" placeholder="Ex.: Farmácia São José">
                        <div class="invalid-feedback">Informe o nome do cliente.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select name="categoriaAC" class="form-select" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($cats as $cat): ?>
                                <option value="<?= (int)$cat['codigocategoriaanuncio'] ?>">
                                    <?= e($cat['nomecategoriaACT']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Selecione uma categoria.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Celular</label>
                        <input type="text" name="celularAC" class="form-control" placeholder="(85) 99999-9999">
                        <div class="form-text">Apenas números e símbolos serão higienizados no backend.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">WhatsApp</label>
                        <input type="text" name="whatsappAC" class="form-control" placeholder="(85) 99999-9999">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Site / Link</label>
                        <input type="url" name="linksiteAC" class="form-control" placeholder="https://exemplo.com.br">
                    </div>

                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-success">
                            Salvar Cliente
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            Limpar
                        </button>
                    </div>
                </div>
            </form>

            <!-- Toast de retorno -->
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index:1080">
                <div id="toastRetorno" class="toast text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-body" id="toastMsg">...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Validação Bootstrap + envio AJAX
    (function() {
        const form = document.getElementById('formClienteNovo');
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

            // Envio via fetch para o AJAX padrão
            try {
                const resp = await fetch('anuncios1.0/ajax_clienteInsert.php', {
                    method: 'POST',
                    body: fd
                });
                const json = await resp.json();

                if (json.status === 'ok') {
                    showToast(json.mensagem || 'Cliente cadastrado com sucesso!');
                    // Redireciona para edição do cliente após breve delay (opcional)
                    const id = json.id || '';
                    setTimeout(() => {
                        if (id) {
                            window.location.href = 'anuncios_clientesEditar.php?id=' + id;
                        } else {
                            window.location.reload();
                        }
                    }, 900);
                } else {
                    showToast(json.mensagem || 'Não foi possível cadastrar o cliente.');
                }
            } catch (e) {
                showToast('Erro inesperado no envio. Tente novamente.');
            }
        }, false);
    })();
</script>