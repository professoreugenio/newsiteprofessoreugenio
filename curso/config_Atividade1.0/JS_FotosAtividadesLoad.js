function carregarFotosAtividade() {
    const idpublicacacaoAA = document.querySelector('input[name="idpublicacacaoAA"]').value;
    const idalulnoAA = document.querySelector('input[name="idalulnoAA"]').value;
    const idmoduloAA = document.querySelector('input[name="idmoduloAA"]').value;

    const formData = new FormData();
    formData.append('idpublicacao', idpublicacacaoAA);
    formData.append('idaluno', idalulnoAA);
    formData.append('idmodulo', idmoduloAA);

    fetch("config_Atividade1.0/ajax_loadAtividade.php", {
        method: "POST",
        body: formData
    })
        .then(r => r.text())
        .then(html => {
            // Insere os cards de atividades com #comentarios_ID
            document.getElementById("loadfotos").innerHTML = html;

            // 🔁 Após inserir o HTML, carrega os comentários de todos os anexos:
            setTimeout(() => {
                document.querySelectorAll("[id^='comentarios_']").forEach(div => {
                    const idanexo = div.id.replace("comentarios_", "");
                    carregarComentarios(idanexo);
                });
            }, 100); // pequeno delay para garantir que os elementos existam
        });
}
// Chamar no carregamento da página:
carregarFotosAtividade();
