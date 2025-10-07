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
