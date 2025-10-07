
// Função para pegar parâmetro da URL do próprio script
function getScriptParameter(paramName) {
    const scripts = document.getElementsByTagName('script');
    for (let i = 0; i < scripts.length; i++) {
        const script = scripts[i];
        if (script.src && script.src.includes('JS_cursoImgApresentacao.js')) {
            const url = new URL(script.src);
            return url.searchParams.get(paramName);
        }
    }
    return null;
}

$(document).ready(function () {
    const cursoId = getScriptParameter('id');
    // Pega o valor do campo 'tipo' do formulário, se existir
    const tipo = $('#tipo').val();
    
    console.log(cursoId);
    if (cursoId) {
        $("#showfoto").load("cursosv1.0/ajax_CursoImgApresentacaoLoad.php?id=" + encodeURIComponent(cursoId) + "&tipo=" + encodeURIComponent(tipo));
    } else {
        console.error("ID do curso não encontrado na URL do script.");
    }
});


$(document).ready(function () {
    $('#formImagemCapa').on('submit', function (e) {
        e.preventDefault();

        const cursoId = getScriptParameter('id');
        const formData = new FormData(this);

        formData.append('idCurso', cursoId); // ← Aqui está o conserto

        console.log("ID enviado via form:", cursoId); // Para confirmar

        $.ajax({
            url: 'cursosv1.0/ajax_CursoImgApresentacaoInsert2.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#respostaUpload').html('<div class="text-info">Enviando imagem...</div>');
            },
            success: function (resposta) {
                $('#respostaUpload').html(resposta);
                const tipo = $('#tipo').val();
                $("#showfoto").load("cursosv1.0/ajax_CursoImgApresentacaoLoad.php?id=" + encodeURIComponent(cursoId) + "&tipo=" + encodeURIComponent(tipo));
            },
            error: function () {
                $('#respostaUpload').html('<div class="alert alert-danger">Erro ao enviar a imagem.</div>');
            }
        });
    });

});



