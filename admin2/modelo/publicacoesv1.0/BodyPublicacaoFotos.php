<!-- Formulário de Upload de Fotos -->
<form id="formUploadFotos" method="post" enctype="multipart/form-data" class="mb-4">
    <h5 class="mb-3">Adicionar Fotos à Publicação: <strong><?= htmlspecialchars($rwPublicacao['titulo']); ?></strong></h5>
    <input type="hidden" name="idpublicacao" id="idpublicacao" value="<?= $encIdPublicacao; ?>">
    <div class="input-group">
        <input type="file" name="images[]" multiple accept="image/*" class="form-control" required>
        <button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Enviar Imagens</button>
    </div>
</form>

<!-- Loader (oculto inicialmente) -->


<!-- Galeria de Fotos -->
<div id="exibeFotos"></div>


<!-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const idpub = document.getElementById('idpublicacao').value;
        if (idpub) {
            $.post('publicacoesv1.0/BodyPublicacaoListaFotos.php', {
                idpublicacao: idpub
            }, function(data) {
                $('#exibeFotos').html(data);
            });
        }
    });
</script> -->


<script>
    $(document).ready(function() {

        const idpub = $('#idpublicacao').val();

        // Submete o formulário com AJAX
        $('#formUploadFotos').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            $('#loaderFotos').fadeIn(); // Mostra o loader

            $.ajax({
                url: 'publicacoesv1.0/ajax_publicacaoUploadFotos.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#loaderFotos').fadeOut(); // Esconde o loader
                    if (response.trim() === '1') {
                        carregarFotos(); // Recarrega galeria
                        form.reset(); // Limpa input file
                    } else {
                        alert("Erro ao enviar: " + response);
                    }
                },
                error: function(xhr, status, error) {
                    $('#loaderFotos').fadeOut();
                    alert("Erro inesperado: " + error);
                }
            });
        });

        // Exibe imagem em lightbox
        $(document).on('click', '.miniatura-foto', function() {
            const src = $(this).data('img');
            $('#modalImagem').attr('src', src);
            $('#modalViewFoto').modal('show');
        });

        // Excluir foto
        $(document).on('click', '.btnExcluirFoto', function() {
            const idfoto = $(this).data('id');
            if (!confirm("Tem certeza que deseja excluir esta foto?")) return;

            $.post('publicacoesv1.0/ajax_publicacaoExcluirFoto.php', {
                idfoto: idfoto,
                idpublicacao: idpub
            }, function(res) {
                if (res.sucesso) {
                    carregarFotos();
                } else {
                    alert(res.mensagem);
                }
            }, 'json');
        });

        // Favoritar foto
        $(document).on('click', '.btnFavoritarFoto', function() {
            const idfoto = $(this).data('id');
            $.post('publicacoesv1.0/ajax_publicacaoFavoritarFoto.php', {
                idfoto: idfoto,
                idpublicacao: idpub
            }, function(res) {
                if (res.sucesso) {
                    carregarFotos();
                } else {
                    alert(res.mensagem);
                }
            }, 'json');
        });

        // Carregar galeria
        function carregarFotos() {
            $.post('publicacoesv1.0/BodyPublicacaoListaFotos.php', {
                idpublicacao: idpub
            }, function(data) {
                $('#exibeFotos').html(data);
            });
        }

        // Carrega na inicialização apenas uma vez
        if (idpub) carregarFotos();
    });
</script>