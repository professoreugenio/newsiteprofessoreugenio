<form id="formModuloNovo" method="post" class="row g-4 mt-3 needs-validation" novalidate>
    <!-- Chave do curso vinculada -->
    <input type="text" name="chavem" value="<?= gerarChaveFormulario(); ?>">

    <!-- Nome do Módulo -->
    <div class="col-md-6">
        <label for="modulo" class="form-label">Nome do Módulo</label>
        <input type="text" class="form-control" id="modulo" name="modulo" required>
    </div>

    <!-- Descrição -->
    <div class="col-md-6">
        <label for="descricao" class="form-label">Descrição</label>
        <input type="text" class="form-control" id="descricao" name="descricao">
    </div>

    <!-- Valor -->
    <div class="col-md-3">
        <label for="valorm" class="form-label">Valor (R$)</label>
        <input type="number" step="0.01" class="form-control" id="valorm" name="valorm">
    </div>

    <!-- Valor Hora -->
    <div class="col-md-3">
        <label for="valorh" class="form-label">Valor Hora (R$)</label>
        <input type="number" step="0.01" class="form-control" id="valorh" name="valorh">
    </div>

    <!-- Nº de Aulas -->
    <div class="col-md-3">
        <label for="nraulasm" class="form-label">Quantidade de Aulas</label>
        <input type="number" class="form-control" id="nraulasm" name="nraulasm">
    </div>

    <!-- Ordem -->
    <div class="col-md-3">
        <label for="ordemm" class="form-label">Ordem</label>
        <input type="number" class="form-control" id="ordemm" name="ordemm" value="1">
    </div>

    <!-- Cor -->
    <div class="col-md-3">
        <label for="bgcolor" class="form-label">Cor de Fundo</label>
        <input type="color" class="form-control form-control-color" id="bgcolor" name="bgcolor" value="#ffffff">
    </div>

    <!-- Visível no curso -->
    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" id="visivelm" name="visivelm" checked>
            <label class="form-check-label" for="visivelm">Visível no Curso</label>
        </div>
    </div>

    <!-- Visível na Home -->
    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" id="visivelhome" name="visivelhome">
            <label class="form-check-label" for="visivelhome">Destaque na Home</label>
        </div>
    </div>

    <!-- Botão de envio -->
    <div class="col-12">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Adicionar Módulo
        </button>
    </div>
</form>

<!-- Toast container -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;" id="toastContainerModulo"></div>

<!-- Script AJAX -->
<script>
    document.getElementById('formModuloNovo').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const botao = form.querySelector('button[type="submit"]');
        const originalText = botao.innerHTML;

        botao.disabled = true;
        botao.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Salvando...';

        $.ajax({
            type: 'POST',
            url: 'modulosv1.0/ajax_moduloInsertform.php',
            data: $(form).serialize(),
            dataType: 'json',
            success: function(res) {
                botao.disabled = false;
                botao.innerHTML = originalText;

                const cor = res.sucesso ? 'bg-success' : 'bg-danger';
                const toast = `
                <div class="toast align-items-center text-white ${cor} border-0 show" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">${res.mensagem}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                    </div>
                </div>`;
                $('#toastContainerModulo').html(toast);

                if (res.sucesso) {
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function() {
                botao.disabled = false;
                botao.innerHTML = originalText;
                alert('Erro ao comunicar com o servidor.');
            }
        });
    });
</script>