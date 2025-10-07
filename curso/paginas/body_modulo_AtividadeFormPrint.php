<div class="container mt-5">
    <h2 class="mb-4 text-center text-primary">Envio de Atividade ***</h2>

    <form id="formAtividadeEnvio" enctype="multipart/form-data">
        <input type="hidden" name="idpublicacacaoAA" value="<?= $idpublicacao ?>">
        <input type="hidden" name="idalulnoAA" value="<?= $codigoUser ?>">
        <input type="hidden" name="idmoduloAA" value="<?= $idmodulo ?>">
        <input type="hidden" name="pastaAA" value="<?= $idmodulo ?>">

        <div class="mb-3">
            <label for="arquivos" class="form-label">Selecione os arquivos:</label>
            <input type="file" name="arquivos[]" id="arquivos" class="form-control" multiple
                accept=".jpg,.jpeg,.png,.zip,.rar,.doc,.docx,.xlsx,.xls,.pptx,.txt,.pdf" required>
        </div>

        <div id="previewContainer" class="row g-3 mb-3"></div>

        <button type="submit" id="btnEnviar" class="btn btn-primary">
            <span id="btnTexto">Enviar Arquivos</span>
            <span id="btnLoad" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </button>
    </form>
</div>

<!-- Toast de resposta -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055">
    <div id="toastResposta" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">Enviado com sucesso!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<style>
    .preview-item {
        position: relative;
        display: inline-block;
        width: 120px;
        height: 120px;
        overflow: hidden;
        border-radius: 0.5rem;
        border: 1px solid #ddd;
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-preview {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(255, 0, 0, 0.8);
        color: #fff;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        width: 24px;
        height: 24px;
        text-align: center;
        line-height: 22px;
        cursor: pointer;
    }
</style>

<script>
    const inputArquivos = document.getElementById("arquivos");
    const previewContainer = document.getElementById("previewContainer");
    let arquivosSelecionados = [];

    inputArquivos.addEventListener("change", function() {
        const files = Array.from(inputArquivos.files);
        arquivosSelecionados = files; // Atualiza o array com todos os arquivos
        previewContainer.innerHTML = ""; // Limpa visualização

        files.forEach((file, index) => {
            if (file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function(e) {
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

    // Remover imagem do array antes do envio
    previewContainer.addEventListener("click", function(e) {
        if (e.target.classList.contains("remove-preview")) {
            const index = parseInt(e.target.dataset.index);
            arquivosSelecionados.splice(index, 1);
            inputArquivos.files = createFileList(arquivosSelecionados);
            inputArquivos.dispatchEvent(new Event("change"));
        }
    });

    // Utilitário para criar FileList novamente
    function createFileList(filesArray) {
        const dt = new DataTransfer();
        filesArray.forEach(file => dt.items.add(file));
        return dt.files;
    }

    // Envio via AJAX
    document.getElementById("formAtividadeEnvio").addEventListener("submit", function(e) {
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
        arquivosSelecionados.forEach(file => formData.append("arquivos[]", file));

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
</script>