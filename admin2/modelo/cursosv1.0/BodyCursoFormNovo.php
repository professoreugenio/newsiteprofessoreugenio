<form id="formNovoCurso" class="row g-4" method="post" data-aos="fade-up">
    <div class="col-md-6">
        <label for="nomeCurso" class="form-label">Nome do Curso</label>
        <input type="text" class="form-control" id="nomeCurso" name="nome" required>
    </div>

    <div class="col-md-6">
        <label for="pastaCurso" class="form-label">Pasta</label>
        <input type="text" class="form-control" id="pastaCurso" name="pasta" value="<?php echo date("Ymd") . time();  ?>">
    </div>

    <div class="col-md-6">
        <label for="youtubeCurso" class="form-label">Vídeo YouTube (URL)</label>
        <input type="url" class="form-control" id="youtubeCurso" name="youtube">
    </div>

    <div class="col-md-6">
        <label for="linkExterno" class="form-label">Link Externo</label>
        <input type="url" class="form-control" id="linkExterno" name="linkexterno">
    </div>

    <div class="col-md-4">
        <label for="bgcolor" class="form-label">Cor de Fundo</label>
        <input type="color" class="form-control form-control-color" id="bgcolor" name="bgcolor" value="#f57c00">
    </div>

    <div class="col-md-8 d-flex align-items-center mt-4">
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="onlinesc" id="chkOnline">
            <label class="form-check-label" for="chkOnline">Curso Online</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="comercialsc" id="chkComercial">
            <label class="form-check-label" for="chkComercial">Comercial</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="institucionalsc" id="chkInstitucional">
            <label class="form-check-label" for="chkInstitucioanl">Institucional</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="visivelsc" id="chkVisivel">
            <label class="form-check-label" for="chkVisivel">Visível</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="visivelhomesc" id="chkHome">
            <label class="form-check-label" for="chkHome">Visível na Home</label>
        </div>
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-primary me-2">
            <i class="bi bi-plus-circle me-1"></i>Cadastrar Curso
        </button>
        <a href="cursos.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</form>