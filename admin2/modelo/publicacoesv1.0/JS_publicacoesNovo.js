$(document).ready(function () {

    // 🔄 Atualizar curso
    $('#formCadastrarPublicacao').on('submit', function (e) {
        e.preventDefault();

        const idModulo = $('input[name="idmodulo"]').val();
        const idCurso = $('input[name="idcurso"]').val();
        const formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: 'publicacoesv1.0/ajax_publicacaoCadastrar.php',
            data: formData,
            dataType: 'json',
            success: function (resposta) {
                showToast(resposta.mensagem, resposta.sucesso ? 'success' : 'danger');

                if (resposta.sucesso) {
                    setTimeout(() => {
                        window.location.href = 'cursos_publicacoes.php?id=' + idCurso+ '&md=' + idModulo;
                    }, 1500);
                }
            },
            error: function () {
                showToast('Erro ao processar os dados. Tente novamente.', 'danger');
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
