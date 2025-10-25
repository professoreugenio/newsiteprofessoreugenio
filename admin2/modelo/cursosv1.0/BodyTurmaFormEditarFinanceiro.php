<div class="mb-3">
    <label class="form-label fw-semibold">Cód. do Produto:</label>
    <div class="input-group" style="max-width: 400px;">
        <input type="text" id="codigoProduto" class="form-control bg-light" readonly value="<?= htmlspecialchars($ChaveTurma) ?>">
        <button class="btn btn-outline-primary" type="button" onclick="copiarCodigoProduto()" title="Copiar código">
            <i class="bi bi-clipboard"></i>
        </button>
    </div>
</div>

<script>
    function copiarCodigoProduto() {
        const input = document.getElementById('codigoProduto');
        input.select();
        input.setSelectionRange(0, 99999); // compatibilidade mobile
        document.execCommand('copy');

        // Feedback opcional
        const botao = event.currentTarget;
        const iconeOriginal = botao.innerHTML;
        botao.innerHTML = '<i class="bi bi-clipboard-check"></i>';
        setTimeout(() => botao.innerHTML = iconeOriginal, 1500);
    }
</script>


<form id="formComercial" class="row g-4" enctype="multipart/form-data">
    <input type="hidden" name="chave" value="<?= htmlspecialchars($ChaveTurma) ?>">
    <h4>Valor venda</h4>
    <div class="col-md-2">
        <label class="form-label">Valor Venda (Bruto)</label>
        <input type="text" class="form-control valor-mask" name="valorvenda" value="<?= number_format((float) $valorvenda, 2, '.', ',') ?>">
    </div>

    <div class="col-md-2">
        <label class="form-label">Valor Cartão (R$)</label>
        <input type="text" class="form-control valor-mask" name="valorcartao" value="<?= number_format((float) $valorcartao, 2, '.', ',') ?>">
    </div>

    <div class="col-md-2">
        <label class="form-label">Valor à vista (R$)</label>
        <input type="text" class="form-control valor-mask" name="valoravista" value="<?= number_format((float) $valoravista, 2, '.', ',') ?>">
    </div>

    <h4>Anual</h4>
    <div class="col-md-2">
        <label class="form-label">Valor Anual (R$)</label>
        <input type="text" class="form-control valor-mask" name="valoranual" value="<?= number_format((float) $valoranual, 2, '.', ',') ?>">
    </div>

    <div class="col-md-2">
        <label class="form-label">Valor Anual cartão (R$)</label>
        <input type="text" class="form-control valor-mask" name="valoranualcartao" value="<?= number_format((float) $valoranualcartao, 2, '.', ',') ?>">
    </div>

    <hr>
    <h4>Anual</h4>
    <div class="col-md-2">
        <label class="form-label">Valor Anual (R$)</label>
        <input type="text" class="form-control valor-mask" name="valoranual" value="<?= number_format((float) $valoranual, 2, '.', ',') ?>">
    </div>

    <div class="col-md-2">
        <label class="form-label">Valor Cartão Anual (R$)</label>
        <input type="text" class="form-control valor-mask" name="valorcartaoanual" value="<?= number_format((float) $valorcartaoanual, 2, '.', ',') ?>">
    </div>

    <hr>


    <div class="col-md-12">
        <label class="form-label">Pix Valor à vista (Presencial)</label>
        <input type="text" class="form-control" name="chavepixvaloravista" value="<?= $chavepixvaloravista ?>">
    </div>
    <div class="col-md-12">
        <label class="form-label">Pix Valor anual à vista (Presencial)</label>
        <input type="text" class="form-control" name="chavepixvaloranualavista" value="<?= $chavepixvaloranualavista ?>">
    </div>

    <div class="col-md-3">
        <label class="form-label">Valor Hora Aula (Institucional)</label>
        <input type="text" class="form-control valor-mask" name="valorhoraaula" value="<?= number_format((float) $valorhoraaula, 2, '.', ',') ?>">
    </div>
    <div class="col-md-2">
        <label class="form-label">Horas/aula (Inst)</label>
        <input type="text" class="form-control valor-mask" name="horasaulast" value="<?= $horasaulast ?>">
    </div>
    <div class="col-md-12">
    </div>

    <div class="col-md-4">
        <label class="form-label">Chave Pix Anual</label>
        <input type="text" class="form-control" name="chavepix" value="<?= htmlspecialchars($chavepix) ?>">
    </div>

    <div class="col-md-4">
        <label class="form-label">Chave Pix Vitalícia</label>
        <input type="text" class="form-control" name="chavepixvitalicia" value="<?= htmlspecialchars($chavepixvitalicia) ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Link PagSeguro Anual</label>
        <textarea class="form-control editor-summernote" name="linkpagseguro"><?= htmlspecialchars($linkpagseguro) ?></textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Link PagSeguro Vitalício</label>
        <textarea class="form-control editor-summernote" name="linkpagsegurovitalicia"><?= htmlspecialchars($linkpagsegurovitalicia) ?></textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Link MercadoPago Anual</label>
        <textarea class="form-control editor-summernote" name="linkmercadopago"><?= htmlspecialchars($linkmercadopago) ?></textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Link MercadoPago Vitalício</label>
        <textarea class="form-control editor-summernote" name="linkmercadopagovitalicio"><?= htmlspecialchars($linkmercadopagovitalicio) ?></textarea>
    </div>
    <div class="col-md-12">
        <label class="form-label">Enviar QRCodes (opcional)</label>
        <div class="d-flex gap-3">

            <input type="file" class="form-control" name="imgqrcodecurso" id="imgqrcodecurso">
            <input type="file" class="form-control" name="imgqrcodeanual" id="imgqrcodeanual">
            <input type="file" class="form-control" name="imgqrcodevitalicio" id="imgqrcodevitalicio">
        </div>
    </div>
    <!-- LOAD FOTOS QRCODE -->
    <div class="col-md-4">
        <label>Qr code Curso</label>
        <div id="loadfoto1">
            <?php if (!empty($imgqrcodecurso)): ?>
                <div class="position-relative d-inline-block">
                    <a data-fancybox="qr" href="/fotos/qrcodes/<?= $imgqrcodecurso ?>">
                        <img src="/fotos/qrcodes/<?= $imgqrcodecurso ?>" class="img-fluid border rounded shadow-sm">
                    </a>
                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0" style="z-index:2;"
                        onclick="excluirQrCode('imgqrcodecurso', 'loadfoto1')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            <?php else: ?>
                <small class="text-muted">Aguardando envio...</small>
            <?php endif; ?>
        </div>
    </div>



    <div class="col-md-4">
        <label>Qr code Anual</label>
        <div id="loadfoto2">
            <?php if (!empty($imgqrcodeanual)): ?>
                <div class="position-relative d-inline-block">
                    <a data-fancybox="qr" href="/fotos/qrcodes/<?= $imgqrcodeanual ?>">
                        <img src="/fotos/qrcodes/<?= $imgqrcodeanual ?>" class="img-fluid border rounded shadow-sm">
                    </a>
                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0" style="z-index:2;"
                        onclick="excluirQrCode('imgqrcodeanual', 'loadfoto2')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            <?php else: ?>
                <small class="text-muted">Aguardando envio...</small>
            <?php endif; ?>
        </div>
    </div>



    <div class="col-md-4">
        <label>Qr code Vitalício</label>
        <div id="loadfoto3">
            <?php if (!empty($imgqrcodevitalicio)): ?>
                <div class="position-relative d-inline-block">
                    <a data-fancybox="qr" href="/fotos/qrcodes/<?= $imgqrcodevitalicio ?>">
                        <img src="/fotos/qrcodes/<?= $imgqrcodevitalicio ?>" class="img-fluid border rounded shadow-sm">
                    </a>
                    <button class="btn btn-sm btn-danger position-absolute top-0 end-0" style="z-index:2;"
                        onclick="excluirQrCode('imgqrcodevitalicio', 'loadfoto3')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            <?php else: ?>
                <small class="text-muted">Aguardando envio...</small>
            <?php endif; ?>
        </div>
    </div>

    <!-- END LOAD FOTOS QRCODE -->
    <div class="col-12 mt-4">

        <button type="submit" class="btn btn-success" style="position:fixed; left:40px; bottom:40px; z-index:1050;">
            <i class="bi bi-save me-1"></i> Atualizar Informações
        </button>
    </div>
</form>
<script>
    jQuery(function($) {
        $('.editor-summernote').summernote({
            height: 100,
            toolbar: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['ul', 'ol']],
                ['insert', ['link']],
                ['view', ['codeview']]
            ]
        });
    });
</script>
<script>
    jQuery(function($) {
        $('.valor-mask').mask('000.000.000,00', {
            reverse: true
        });
        $('#formComercial').on('submit', function(e) {
            e.preventDefault();
            // Converte para o formato do banco (ponto como separador decimal)
            $('.valor-mask').each(function() {
                let valor = $(this).val().replace(/\./g, '').replace('.', ','); // ex: 1.200,50 → 1200.50
                $(this).val(valor);
            });
            const formData = new FormData(this);
            $.ajax({
                url: 'cursosv1.0/ajax_turmaUopdateComercial.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(resp) {
                    showToast(resp.mensagem, resp.status === 'ok' ? 'bg-success' : 'bg-danger');
                },
                error: function() {
                    showToast('Falha na conexão.', 'bg-danger');
                }
            });
        });

        function showToast(mensagem, cor) {
            const toastID = 'toast-' + Date.now();
            const toast = `
            <div id="${toastID}" class="toast align-items-center text-white ${cor} border-0 position-fixed start-50 translate-middle-x" style="top: 100px; z-index: 9999;" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${mensagem}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
            $('body').append(toast);
            const t = new bootstrap.Toast(document.getElementById(toastID), {
                delay: 4000
            });
            t.show();
            setTimeout(() => $('#' + toastID).remove(), 4500);
        }
    });
</script>
<script>
    function showToast(mensagem, cor) {
        const toastID = 'toast-' + Date.now();
        const toast = `
        <div id="${toastID}" class="toast align-items-center text-white ${cor} border-0 position-fixed start-50 translate-middle-x" style="top: 100px; z-index: 9999;" role="alert">
            <div class="d-flex">
                <div class="toast-body">${mensagem}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;
        $('body').append(toast);
        const t = new bootstrap.Toast(document.getElementById(toastID), {
            delay: 4000
        });
        t.show();
        setTimeout(() => {
            $('#' + toastID).remove();
        }, 4500);
    }
</script>

<script>
    function enviarQrCode(inputId, campo, destino) {
        const fileInput = document.getElementById(inputId);
        const formData = new FormData();
        formData.append('arquivo', fileInput.files[0]);
        formData.append('campo', campo);
        formData.append('chave', '<?= $ChaveTurma ?>');

        fetch('cursosv1.0/ajax_uploadQrCode.php', {
                method: 'POST',
                body: formData
            }).then(res => res.json())
            .then(data => {
                if (data.status === 'ok') {
                    document.getElementById(destino).innerHTML =
                        `<img src="/fotos/qrcodes/${data.nome}" class="img-fluid border rounded shadow-sm">`;
                } else {
                    document.getElementById(destino).innerHTML = `<div class="text-danger">${data.mensagem}</div>`;
                }
            }).catch(() => {
                document.getElementById(destino).innerHTML = '<div class="text-danger">Erro ao enviar imagem</div>';
            });
    }

    document.getElementById('imgqrcodecurso').addEventListener('change', () =>
        enviarQrCode('imgqrcodecurso', 'imgqrcodecurso', 'loadfoto1')
    );
    document.getElementById('imgqrcodeanual').addEventListener('change', () =>
        enviarQrCode('imgqrcodeanual', 'imgqrcodeanual', 'loadfoto2')
    );
    document.getElementById('imgqrcodevitalicio').addEventListener('change', () =>
        enviarQrCode('imgqrcodevitalicio', 'imgqrcodevitalicio', 'loadfoto3')
    );
</script>


<script>
    function excluirQrCode(campo, destino) {
        if (!confirm("Tem certeza que deseja excluir esta imagem?")) return;

        fetch('cursosv1.0/ajax_excluirQrCode.php', {
                method: 'POST',
                body: new URLSearchParams({
                    campo: campo,
                    chave: '<?= $ChaveTurma ?>'
                })
            }).then(r => r.json())
            .then(data => {
                if (data.status === 'ok') {
                    document.getElementById(destino).innerHTML = `<small class="text-muted">Imagem excluída.</small>`;
                } else {
                    alert(data.mensagem || 'Erro ao excluir imagem');
                }
            });
    }
</script>