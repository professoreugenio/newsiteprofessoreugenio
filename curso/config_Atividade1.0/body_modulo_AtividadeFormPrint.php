<div class="container mt-5">
    <!-- <h2 class="mb-4 text-center" style="color:#ea00ea; font-weight:600; font-family:'Poppins', sans-serif;">
        <i class="bi bi-upload me-2"></i> Envio de Atividade
    </h2> -->
    <?php if ($codigoUser != 1): ?>
        <form id="formAtividadeEnvio" enctype="multipart/form-data" class="rounded p-3 shadow-sm bg-light border">
            <input type="hidden" name="idpublicacacaoAA" value="<?= $codigoaula ?>">
            <input type="hidden" name="idalulnoAA" value="<?= $codigoUser ?>">
            <input type="hidden" name="idmoduloAA" value="<?= $codigomodulo ?>">
            <input type="hidden" name="pastaAA" value="<?= $pasta ?>">
            <div class="row align-items-end mb-3">
                <div class="col-md-9">
                    <label for="arquivos" class="form-label text-dark fw-semibold">
                        <i class="bi bi-paperclip me-1"></i> Selecionar arquivos**
                    </label>
                    <input type="file" name="arquivos[]" id="arquivos" class="form-control border-primary"
                        multiple accept=".jpg,.jpeg,.png,.zip,.rar,.doc,.docx,.xlsx,.xls,.pptx,.txt,.pdf" required>
                </div>
                <div class="col-md-3 d-grid">
                    <label class="d-none d-md-block invisible">Enviar</label>
                    <button type="submit" id="btnEnviar" class="btn btn-primary shadow-sm">
                        <i class="bi bi-send me-1"></i>
                        <span id="btnTexto">Enviar Arquivos</span>
                        <span id="btnLoad" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
            <div id="previewContainer" class="row g-2 mb-3"></div>
        </form>
</div>
<div class="container mt-5">
    <div id="loadfotos"></div>
</div>
<?php endif; ?>
<!-- Toast de resposta -->
<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1055">
    <div id="toastResposta" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">Enviado com sucesso!</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
<?php if ($codigoUser == 1): ?>
    <?php require 'config_Atividade1.0/loadNomeAlunosTurma.php' ?>
<?php else: ?>
    <script src="config_Atividade1.0/JS_carregafotosAtividade.js?<?= time(); ?>"></script>
<?php endif; ?>
<style>
    .preview-item {
        position: relative;
        display: inline-block;
        width: 110px;
        height: 110px;
        overflow: hidden;
        border-radius: 0.5rem;
        border: 1px solid #ccc;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }

    .preview-item:hover {
        transform: scale(1.03);
    }

    .preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .remove-preview {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255, 0, 0, 0.75);
        color: #fff;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        width: 22px;
        height: 22px;
        line-height: 20px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .remove-preview:hover {
        background: rgba(220, 0, 0, 0.9);
    }
</style>
<script>
    const inputArquivos = document.getElementById("arquivos");
    const previewContainer = document.getElementById("previewContainer");
    let arquivosSelecionados = [];
    inputArquivos.addEventListener("change", function() {
        const files = Array.from(inputArquivos.files);
        arquivosSelecionados = files;
        previewContainer.innerHTML = "";
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
    previewContainer.addEventListener("click", function(e) {
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
</script>
<script>
    function enviarComentario(idanexo, form) {
        const texto = form.texto.value.trim();
        if (texto === '') return false;
        const formData = new FormData();
        formData.append('idfile', idanexo);
        formData.append('texto', texto);
        formData.append('iduserde', <?= $codigoUser ?>); // ID do usuário logado
        formData.append('iduserpara', 0); // ou outro destino, se desejar
        fetch('config_Atividade1.0/ajax_insertComentarioAtividade.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    form.reset();
                    carregarComentarios(idanexo); // Atualiza a lista
                } else {
                    alert(res.mensagem || "Erro ao enviar comentário.");
                }
            })
            .catch(() => alert("Erro de conexão ao enviar comentário."));
        return false; // Impede submit normal
    }
</script>
<script>
    function carregarComentarios(idanexo) {
        const formData = new FormData();
        formData.append('idfile', idanexo);
        fetch('config_Atividade1.0/ajaxloadComentarioAtividade.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.text())
            .then(html => {
                document.getElementById('comentarios_' + idanexo).innerHTML = html;
            })
            .catch(() => {
                document.getElementById('comentarios_' + idanexo).innerHTML = '<p class="text-danger">Erro ao carregar comentários.</p>';
            });
    }
</script>
<script>
    function excluirArquivo(form) {
        const formData = new FormData(form);
        const btn = form.querySelector("button");
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = "Excluindo...";
        fetch("config_Atividade1.0/ajax_deleteFileAtividade.php", {
                method: "POST",
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    carregarFotosAtividade(); // Recarrega a lista de arquivos
                } else {
                    alert(res.mensagem || "Erro ao excluir.");
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(() => {
                alert("Erro inesperado ao excluir.");
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        return false; // Impede o envio tradicional do formulário
    }
</script>