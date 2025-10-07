function enviarRespostaVF() {
    // Botões e Loading
    var ordem = $('#ordem').val();
    var $btnEnviar = $('#btnEnviar');
    var originalHtml = $btnEnviar.html();
    $btnEnviar.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
    $btnEnviar.prop('disabled', true);

    // Coleta de dados das respostas selecionadas
    var respostas = {};
    var validado = true;

    // Iterar sobre cada select de resposta e verificar se foi selecionado
    $('select[name^="respostas"]').each(function () {
        var respostaValue = $(this).val();
        var perguntaId = $(this).attr('name').match(/\[(.*?)\]/)[1]; // Extrai o ID da pergunta do name

        if (respostaValue === null || respostaValue === "") {
            validado = false;
            return false; // Se algum select não foi preenchido, interrompe o loop
        }

        // Salva as respostas selecionadas
        respostas[perguntaId] = respostaValue;
    });

    // Se alguma resposta não foi selecionada, mostrar erro e sair da função
    if (!validado) {
        showToast('❌ Por favor, responda todas as perguntas.', true);
        $btnEnviar.prop('disabled', false).html(originalHtml);
        return;
    }

    // Envio via Ajax
    $.ajax({
        type: 'POST',
        url: 'config_Atividades/AtividadeInsertVF.php',
        data: {
            respostas: respostas,
            ordem: ordem
        },
        success: function (response) {
            showToast('✅ Sua resposta foi enviada para avaliação do professor.');
            $('#atividade').load('config_Atividades/AtividadeLoad.php');

            // Oculta o botão enviar e mostra o botão próxima
            $btnEnviar.hide();
            $('#btnProxima').show();
        },
        error: function () {
            showToast('❌ Erro ao enviar a atividade. Tente novamente.', true);
        },
        complete: function () {
            $btnEnviar.prop('disabled', false).html(originalHtml);
        }
    });
}

function finalizarAtividades() {
    // Botões e Loading
    var ordem = $('#ordem').val();
    var $btnEnviar = $('#btnEnviar');
    var originalHtml = $btnEnviar.html();
    $btnEnviar.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
    $btnEnviar.prop('disabled', true);
    // Envio via Ajax
    $.ajax({
        type: 'POST',
        url: 'config_Atividades/AtividadeFinaliza.php',
        data: {

            ordem: ordem
        },
        success: function (response) {
            showToast('✅ Questionário finalizado com sucesso.');
            $('#atividade').load('config_Atividades/AtividadeLoad.php');
            
            location.reload();

            // Oculta o botão enviar e mostra o botão próxima
            $btnEnviar.hide();
            $('#btnProxima').show();
        },
        error: function () {
            showToast('❌ Erro ao finalizar a atividade. Tente novamente.', true);
        },
        complete: function () {
            $btnEnviar.prop('disabled', false).html(originalHtml);
        }
    });
}

function showToast(mensagem, erro = false) {
    const toastClass = erro ? 'bg-danger' : 'bg-success';

    const toast = $(`
    <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true"
        style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
        <div class="d-flex">
            <div class="toast-body">
                ${mensagem}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
    `);

    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    // Remove toast após 5 segundos
    setTimeout(() => toast.remove(), 5000);
}