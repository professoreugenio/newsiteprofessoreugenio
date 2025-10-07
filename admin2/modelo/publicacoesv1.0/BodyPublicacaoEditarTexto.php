<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>

<form id="formEditarTexto" method="post" action="ajax_publicacaoEditarTexto.php">
    <input type="hidden" name="idpublicacao" value="<?= $encIdPublicacao; ?>">

    <div class="mb-3">
        <textarea name="texto" id="editorTexto"><?= stripslashes($rwPublicacao['texto']); ?></textarea>
    </div>

    <div class="position-fixed bottom-0 end-0 m-4" style="z-index: 1050;">
        <div class="d-flex justify-content-between mt-4">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Salvar conteúdo
            </button>
        </div>
    </div>
</form>

<!-- Ativar Summernote -->
<script>
    $(document).ready(function() {
        $('#editorTexto').summernote({
            height: 500,
            lang: 'pt-BR', // Se desejar em português
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>