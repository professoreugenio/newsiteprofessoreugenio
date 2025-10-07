
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
