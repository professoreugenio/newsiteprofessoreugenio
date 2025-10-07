<?php

require 'conteudos1.0/require_publicacoesOriginais.php';

?>


<script>
    function toggleVisivel(id, novoStatus) {
        fetch('conteudos1.0/ajax_categoria_toggle_visivel.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id + '&visivel=' + novoStatus
            })
            .then(res => res.json())
            .then(ret => {
                if (ret.sucesso) {
                    location.reload();
                } else {
                    alert('Erro ao atualizar visibilidade!');
                }
            });
    }

    function enviarLixeira(id) {
        if (!confirm('Tem certeza que deseja enviar esta categoria para a lixeira?')) return;
        fetch('conteudos1.0/ajax_categoria_enviar_lixeira.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(res => res.json())
            .then(ret => {
                if (ret.sucesso) {
                    document.getElementById('cat_' + id).style.opacity = 0.5;
                    location.reload();
                } else {
                    alert('Erro ao enviar para lixeira!');
                }
            });
    }
</script>

<script>
    function restaurarCategoria(id) {
        fetch('conteudos1.0/ajax_categoria_restaurar.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id=' + id
            })
            .then(res => res.json())
            .then(ret => {
                if (ret.sucesso) {
                    location.reload();
                } else {
                    alert('Erro ao restaurar!');
                }
            });
    }
</script>