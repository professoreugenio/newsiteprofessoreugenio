<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>
<form id="formEditarLeadCurso" action=" cursosv1.0/SalvarLeadCurso.php" method="post">
    <input type="hidden" name="curso_id" value="<?= $_GET['id'] ?>">

    <!-- Lead -->
    <div class="mb-3">
        <label for="lead" class="form-label fw-bold">Texto de Chamada (Lead)</label>
        <input type="text" class="form-control" name="lead" id="lead" value="<?= htmlspecialchars($lead) ?>" maxlength="255" required>
    </div>

    <!-- Detalhes com Summernote -->
    <div class="mb-3">
        <label for="detalhes" class="form-label fw-bold">Detalhes do Curso</label>
        <textarea name="detalhes" id="detalhes" class="form-control summernote" rows="6"><?= stripslashes($detalhes) ?></textarea>
    </div>

    <!-- Sobre o Curso -->
    <div class="mb-3">
        <label for="sobreocurso" class="form-label fw-bold">Sobre o Curso</label>
        <textarea name="sobreocurso" id="sobreocurso" class="form-control summernote" rows="6"><?= stripslashes($sobreocurso) ?></textarea>
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
            height: 250,
            toolbar: [
                ['style', ['bold', 'italic', 'underline']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ]
        });
    });
</script>