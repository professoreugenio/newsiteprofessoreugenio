    <!-- Toast para mensagens de feedback -->
    <!-- Toast para mensagens de feedback, CENTRALIZADO NO TOPO -->
    <div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080; min-width:300px;" id="toastContainer">
        <div id="toastMsg" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>


    <form id="formMensagem" method="post" action="">
        <div class="mb-3 d-flex align-items-end gap-2">
            <!-- Select: 30% -->
            <div style="flex-basis:30%;min-width:180px;">
                <label for="msgPadraoSelect" class="form-label fw-semibold">
                    Escolha uma mensagem salva:
                </label>
                <select id="msgPadraoSelect" class="form-select">
                    <option value="">Nova mensagem...</option>
                    <!-- Opções via AJAX -->
                </select>
            </div>
            <!-- Input: 70% -->
            <div style="flex-basis:70%;">
                <label for="titulo" class="form-label fw-semibold">Título da Mensagem</label>
                <input type="text" id="titulo" name="titulo" class="form-control" maxlength="120" placeholder="Ex: Aviso importante, Promoção, Nova Aula, etc.">
            </div>
            <!-- Botão de refresh -->
            <button type="button" class="btn btn-outline-secondary mb-1" id="btnRefreshPadroes" title="Atualizar lista de mensagens">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="mensagem" class="form-label fw-semibold mb-0">Mensagem</label>
                <button type="button" class="btn btn-outline-success btn-sm" id="btnCopiarMsg" title="Copiar mensagem">
                    <i class="bi bi-clipboard"></i> Copiar
                </button>
            </div>
            <textarea id="mensagem" name="mensagem" class="form-control"></textarea>
            <div class="form-text">Personalize a mensagem com textos, imagens ou links.</div>
        </div>
        <div class="d-flex gap-3">
            <button type="button" class="btn btn-outline-secondary px-4" id="btnSalvarMsg">
                <i class="bi bi-bookmark-check me-2"></i> Salvar Mensagem
            </button>
            <button type="button" class="btn btn-outline-danger px-4" id="btnExcluirMsg" disabled>
                <i class="bi bi-trash me-2"></i> Excluir Mensagem
            </button>
        </div>

        <div id="retornoMsg" class="mt-3"></div>
    </form>



    <!-- Feedback -->
    <div id="retornoMsg" class="mt-3"></div>