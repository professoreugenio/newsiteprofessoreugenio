<div class="modal fade" id="modalPix" tabindex="-1" aria-labelledby="modalPixLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="modalPixLabel">Pagamento via Pix</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center">

                <?php if ($plano == 'Plano Anual'): ?>
                    <img src="/fotos/qrcodes/<?= $imgqrcodeanual ?>" alt="QR Code Pix" class="img-fluid rounded shadow mb-3" style="max-width: 300px;">
                    <p class="small mb-1">Chave Pix1:</p>
                    <div class="input-group mb-3">
                        <input type="text" id="chavePix" class="form-control text-center" value="<?= $chavepix; ?>" readonly>
                        <button class="btn btn-outline-light" type="button" onclick="copiarChavePix()">Copiar</button>
                    </div>

                    <div class="alert alert-success d-none" id="copiadoAlerta" role="alert">
                        ✅ Chave Pix copiada!
                    </div>
                <?php else: ?>
                    <img src="/fotos/qrcodes/<?= $imgqrcodevitalicio ?>" alt="QR Code Pix" class="img-fluid rounded shadow mb-3" style="max-width: 300px;">
                    <p class="small mb-1">Chave Pix2:</p>
                    <div class="input-group mb-3">
                        <input type="text" id="chavePix" class="form-control text-center" value="<?= $chavepixvitalicia; ?>" readonly>
                        <button class="btn btn-outline-light" type="button" onclick="copiarChavePix()">Copiar</button>
                    </div>

                    <div class="alert alert-success d-none" id="copiadoAlerta" role="alert">
                        ✅ Chave Pix copiada!
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>