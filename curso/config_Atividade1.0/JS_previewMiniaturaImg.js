const inputArquivos = document.getElementById("arquivos");
const previewContainer = document.getElementById("previewContainer");
let arquivosSelecionados = [];
inputArquivos.addEventListener("change", function () {
    const files = Array.from(inputArquivos.files);
    arquivosSelecionados = files;
    previewContainer.innerHTML = "";
    files.forEach((file, index) => {
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const col = document.createElement("div");
                col.className = "col-auto";
                col.innerHTML = `
    <div class="preview-item">
        <img src="${e.target.result}" alt="preview">
            <button type="button" class="remove-preview" data-index="${index}">&times;</button>
    </div>
    `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    });
});
previewContainer.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-preview")) {
        const index = parseInt(e.target.dataset.index);
        arquivosSelecionados.splice(index, 1);
        inputArquivos.files = createFileList(arquivosSelecionados);
        inputArquivos.dispatchEvent(new Event("change"));
    }
});

function createFileList(filesArray) {
    const dt = new DataTransfer();
    filesArray.forEach(file => dt.items.add(file));
    return dt.files;
}
document.getElementById("formAtividadeEnvio").addEventListener("submit", function (e) {
    e.preventDefault();
    const form = this;
    const btn = document.getElementById("btnEnviar");
    const btnTexto = document.getElementById("btnTexto");
    const btnLoad = document.getElementById("btnLoad");
    const toastEl = document.getElementById("toastResposta");
    const toastMsg = document.getElementById("toastMsg");
    btnTexto.classList.add("d-none");
    btnLoad.classList.remove("d-none");
    const formData = new FormData(form);
    fetch("config_Atividade1.0/ajax_uploadAtividade.php", {
        method: "POST",
        body: formData
    })
        .then(r => r.json())
        .then(res => {
            btnTexto.classList.remove("d-none");
            btnLoad.classList.add("d-none");
            if (res.sucesso) {
                toastEl.classList.remove("bg-danger");
                toastEl.classList.add("bg-success");
                toastMsg.innerText = res.mensagem || "Arquivos enviados com sucesso!";
                form.reset();
                previewContainer.innerHTML = "";
                arquivosSelecionados = [];
                // ✅ Atualiza a lista de arquivos exibidos após upload
                carregarFotosAtividade();
            } else {
                toastEl.classList.remove("bg-success");
                toastEl.classList.add("bg-danger");
                toastMsg.innerText = res.mensagem || "Erro ao enviar arquivos.";
            }
            new bootstrap.Toast(toastEl).show();
        })
        .catch(() => {
            btnTexto.classList.remove("d-none");
            btnLoad.classList.add("d-none");
            toastEl.classList.remove("bg-success");
            toastEl.classList.add("bg-danger");
            toastMsg.innerText = "Erro inesperado ao enviar.";
            new bootstrap.Toast(toastEl).show();
        });
});
// ✅ Função para carregar imagens (já existente ou incluída anteriormente)
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

// Carrega as imagens ao abrir a página
carregarFotosAtividade();
