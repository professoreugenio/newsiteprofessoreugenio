<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>
<form action="" id="formEditarCurso" method="post" class="row g-4" data-aos="fade-up">
    <input type="hidden" name="idCurso" value="<?= $_GET['id'] ?>">

    <div class="col-md-6">
        <label for="nomeCurso" class="form-label">Nome do Curso</label>
        <input type="text" class="form-control" id="nomeCurso" name="nome" value="<?= htmlspecialchars($Nomecurso) ?>" required>
    </div>

    <div class="col-md-6">
        <label for="pastaCurso" class="form-label">Pasta</label>
        <input type="text" class="form-control" id="pastaCurso" name="pasta" value="<?= htmlspecialchars($Pasta) ?>">
    </div>

    <!-- Horários -->
    <div class="col-12">
        <h5 class="mt-3">⏰ Horários das Turmas</h5>
        <div class="row g-3">
            <!-- Manhã -->
            <div class="col-md-3">
                <label class="form-label">Manhã (De)</label>
                <input type="time" class="form-control" name="manha_de" value="<?= htmlspecialchars($manha_de ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Manhã (Às)</label>
                <input type="time" class="form-control" name="manha_as" value="<?= htmlspecialchars($manha_as ?? '') ?>">
            </div>

            <!-- Tarde -->
            <div class="col-md-3">
                <label class="form-label">Tarde (De)</label>
                <input type="time" class="form-control" name="tarde_de" value="<?= htmlspecialchars($tarde_de ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tarde (Às)</label>
                <input type="time" class="form-control" name="tarde_as" value="<?= htmlspecialchars($tarde_as ?? '') ?>">
            </div>

            <!-- Noite -->
            <div class="col-md-3">
                <label class="form-label">Noite (De)</label>
                <input type="time" class="form-control" name="noite_de" value="<?= htmlspecialchars($noite_de ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Noite (Às)</label>
                <input type="time" class="form-control" name="noite_as" value="<?= htmlspecialchars($noite_as ?? '') ?>">
            </div>
        </div>
    </div>

    <!-- Campo e preview do vídeo lado a lado -->
    <div class="col-md-6">
        <label for="youtubeCurso" class="form-label">Vídeo YouTube (URL)</label>
        <input type="url" class="form-control" id="youtubeCurso" name="youtube" value="<?= htmlspecialchars($Videoyoutube) ?>">
    </div>

    <?php if ($videoID): ?>
        <div class="col-md-6">
            <label class="form-label">Preview do Vídeo</label>
            <div class="ratio ratio-16x9 rounded border">
                <iframe src="https://www.youtube.com/embed/<?= $videoID ?>" title="Vídeo do Curso" allowfullscreen></iframe>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-6">
        <label for="linkExterno" class="form-label">Link Externo</label>
        <input type="url" class="form-control" id="linkExterno" name="linkexterno" value="<?= htmlspecialchars($urlterno) ?>">
    </div>

    <div class="col-md-6">
        <label for="bgcolor" class="form-label">Cor de Fundo</label>
        <input type="color" class="form-control form-control-color" id="bgcolor" name="bgcolor" value="<?= htmlspecialchars($Bocolor) ?>">
    </div>

    <!-- Checkboxes -->
    <div class="col-12 d-flex flex-wrap align-items-center mt-2">
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="matriz" id="matriz" <?= $mtzon ?>>
            <label class="form-check-label" for="chkOnline">Conteúdo Matriz</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="onlinesc" id="chkOnline" <?= $chkon ?>>
            <label class="form-check-label" for="chkOnline">Curso Online</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="comercialsc" id="chkComercial" <?= $com ?>>
            <label class="form-check-label" for="chkComercial">Comercial</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="institucionalsc" id="chkInstitucional" <?= $inst ?>>
            <label class="form-check-label" for="chkInstitucioanl">Institucional</label>
        </div>
        <div class="form-check me-4">
            <input class="form-check-input" type="checkbox" name="visivelsc" id="chkVisivel" <?= $chkv ?>>
            <label class="form-check-label" for="chkVisivel">Visível</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="visivelhomesc" id="chkHome" <?= $chkvh ?? '' ?>>
            <label class="form-check-label" for="chkHome">Visível na Home</label>
        </div>
    </div>

    <!-- Botões -->
    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-success me-2">
            <i class="bi bi-save me-1"></i>Salvar Alterações
        </button>
        <button type="submit" id="BtExcluirCurso" class="btn btn-danger me-2">
            <i class="bi bi-save me-1"></i>Excluir Curso
        </button>
        <a href="cursos.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</form>