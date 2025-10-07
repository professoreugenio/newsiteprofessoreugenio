$(document).ready(function () {

    // 🔄 Atualizar curso
    $('#formEditarModulo').on('submit', function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: 'modulosv1.0/ajax_moduloUpdateform1.php',
            data: formData,
            dataType: 'json',
            success: function (resposta) {
                showToast(resposta.mensagem, resposta.sucesso ? 'success' : 'danger');
            },
            error: function () {
                showToast('Erro ao processar os dados. Tente novamente.', 'danger');
            }
        });
    });

    // ❌ Excluir curso
    $('#BtExcluirModulo').on('click', function () {
        if (!confirm("Tem certeza que deseja excluir este módulo? Essa ação não pode ser desfeita.")) return;

        const idModulo = $('input[name="idModulo"]').val();
        const idCurso = $('input[name="idCurso"]').val();

        $.ajax({
            type: 'POST',
            url: 'modulosv1.0/ajax_moduloExcluir.php',
            data: {
                idModulo: idModulo,
               
            },
            dataType: 'json',
            success: function (resposta) {
                showToast(resposta.mensagem, resposta.sucesso ? 'success' : 'danger');

                if (resposta.sucesso) {
                    setTimeout(() => {
                        window.location.href = 'cursos_modulos.php?id=' + idCurso;
                    }, 1500);
                }
            },
            error: function () {
                showToast('Erro inesperado ao excluir o curso.', 'danger');
            }
        });
    });

    // ✅ Função única de toast
    function showToast(mensagem, tipo) {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
    <div id="${toastId}" class="toast align-items-center text-white bg-${tipo} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
        <div class="d-flex">
            <div class="toast-body">${mensagem}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
    `;
        $('#toastContainer').append(toastHtml);
        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();
    }

});
