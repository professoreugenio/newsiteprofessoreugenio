<form id="formEditarTurma" class="row g-4" method="post" data-aos="fade-up">
    <input type="hidden" name="chave" value="<?= htmlspecialchars($ChaveTurma) ?>">

    <div class="col-md-4">
        <label for="nomeTurma" class="form-label">Nome da Turma</label>
        <input type="text" class="form-control" id="nomeTurma" name="nometurma" value="<?= htmlspecialchars($Nometurma) ?>" required>
    </div>

    <div class="col-md-4">
        <label for="pastaTurma" class="form-label">Pasta</label>
        <input type="text" class="form-control" id="pastaTurma" name="pasta" value="<?= htmlspecialchars($Pasta) ?>">
    </div>

    <div class="col-md-4">
        <label for="nomeProfessor" class="form-label">Nome do Professor</label>
        <input type="text" class="form-control" id="nomeProfessor" name="nomeprofessor" value="<?= htmlspecialchars($NomeProfessor) ?>">
    </div>

    <div class="col-md-6">
        <label for="bgcolor" class="form-label">Cor de Fundo</label>
        <input type="color" class="form-control form-control-color" id="bgcolor" name="bgcolor_cs" value="<?= htmlspecialchars($Bocolor) ?>">
    </div>

    <div class="col-md-6">
        <label for="linkWhatsapp" class="form-label">Link do WhatsApp</label>
        <input type="url" class="form-control" id="linkWhatsapp" name="linkwhatsapp" value="<?= htmlspecialchars($linkWhatsapp) ?>">
    </div>

    <div class="col-md-6">
        <label for="linkYoutube" class="form-label">Link do YouTube</label>
        <input type="url" class="form-control" id="linkYoutube" name="youtubesct" value="<?= htmlspecialchars($linkYoutube) ?>">
    </div>

    <div class="col-md-12">
        <label for="lead" class="form-label">Lead</label>
        <input type="text" class="form-control" id="lead" name="lead" value="<?= htmlspecialchars($lead) ?>">
    </div>

    <div class="col-md-12">
        <label for="detalhes" class="form-label">Detalhes</label>
        <textarea class="form-control" id="detalhes" name="detalhes" rows="3"><?= htmlspecialchars($detalhes) ?></textarea>
    </div>

    <div class="col-md-12">
        <label for="sobreocurso" class="form-label">Sobre o Curso</label>
        <textarea class="form-control" id="sobreocurso" name="sobreocurso" rows="4"><?= htmlspecialchars($sobreocurso) ?></textarea>
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-success me-2">
            <i class="bi bi-save me-1"></i>Salvar Alterações
        </button>
        <a href="cursos_turmas.php?id=<?= $_GET['id'] ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Voltar
        </a>
    </div>
</form>