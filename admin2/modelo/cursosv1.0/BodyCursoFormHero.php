<?php // require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>
<form id="formEditarLeadCurso" action=" cursosv1.0/SalvarLeadCurso.php" method="post">
    <input type="hidden" name="curso_id" value="<?= $_GET['id'] ?>">



    <!-- Detalhes com Summernote -->
    <div class="mb-3">
        <label for="detalhes" class="form-label fw-bold">Section Hero</label>
        <textarea name="hero" id="hero" class="form-control summernote" rows="6"><?= stripslashes($hero) ?></textarea>
    </div>


    <!-- Botão -->
    <button type="submit" class="btn btn-success">
        <i class="bi bi-check-circle"></i> Salvar Alterações
    </button>
</form>

<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            placeholder: 'Descreva aqui os detalhes do curso...',
            tabsize: 2,
            height: 350,
            toolbar: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    });
</script>