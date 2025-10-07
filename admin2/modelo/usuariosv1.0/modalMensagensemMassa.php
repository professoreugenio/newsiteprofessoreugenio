<!-- Botão Mensagem em Massa -->
<button type="button" class="btn btn-outline-success ms-3" data-bs-toggle="modal" data-bs-target="#modalMsgMassa">
    <i class="bi bi-envelope-paper me-1"></i> Mensagem em Massa
</button>

<!-- Modal largo e centralizado -->
<div class="modal fade" id="modalMsgMassa" tabindex="-1" aria-labelledby="modalMsgMassaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"> <!-- modal-xl para largura maior -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMsgMassaLabel"><i class="bi bi-envelope-paper me-2"></i> Enviar Mensagem em Massa aos Aniversariantes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php require 'cursosv1.0/formMsg.php'; ?>
                <?php require 'cursosv1.0/formMsgJS.php'; ?>
            </div>
        </div>
    </div>
</div>