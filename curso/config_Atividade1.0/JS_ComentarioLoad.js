
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
