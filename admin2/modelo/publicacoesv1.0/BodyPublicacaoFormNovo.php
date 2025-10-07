<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>
<form id="formCadastrarPublicacao" method="post" action="ajax_publicacaoCadastrar.php">
    <input type="hidden" name="idcurso" value="<?= $_GET['id']; ?>">
    <input type="hidden" name="idmodulo" value="<?= $_GET['md']; ?>">

    <!-- Título -->
    <div class="mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" name="titulo" id="titulo" class="form-control" required>
    </div>

    <!-- Linha com Link, Ordem, Módulo e Aula -->
    <div class="row mb-3">
        <!-- Link Externo -->
        <div class="col-md-6 col-lg-6">
            <label for="linkExterno" class="form-label">Link externo (opcional)</label>
            <input type="url" name="linkexterno" id="linkExterno" class="form-control">
        </div>

        <!-- Aula -->
        <div class="col-md-1 col-lg-1">
            <label for="aula" class="form-label">Aula</label>
            <input type="number" name="aula" id="aula" class="form-control" min="1" value="1">
        </div>

        <!-- Ordem -->
        <div class="col-md-2 col-lg-2">
            <label for="ordem" class="form-label">Ordem</label>
            <input type="number" name="ordem" id="ordem" class="form-control" min="1" value="1">
        </div>

        <!-- Módulo -->

        <div class="col-md-3 col-lg-2">
            <label for="idmodulo" class="form-label">Módulo</label>
            <select name="idmodulo" id="idmodulo" class="form-select" required>
                <option value="">Selecione</option>
                <?php
                $stmt = config::connect()->prepare("SELECT codcursos, modulo, ordemm, visivelm, codigomodulos FROM new_sistema_modulos_PJA WHERE codcursos = :idcurso ORDER BY ordemm");
                $stmt->bindParam(":idcurso", $idCurso);
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    $id = $row['codigomodulos'];
                    $encId = encrypt($id, 'e');
                    $nm = $row['modulo'];
                    // Validação: define como selecionado se o módulo atual for o mesmo
                    $selected = ($id == $idModulo) ? 'selected' : '';
                ?>
                    <option value="<?= $encId ?>" <?= $selected ?>><?= $nm ?></option>
                <?php endwhile; ?>
            </select>

        </div>


    </div>

    <!-- Descrição -->
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição / Olho</label>
        <textarea name="descricao" id="descricao" class="form-control" rows="3"></textarea>
    </div>

    <!-- Tags -->
    <div class="mb-3">
        <label for="tags" class="form-label">Tags (separadas por vírgula)</label>
        <input type="text" name="tags" id="tags" class="form-control">
    </div>

    <!-- Visibilidade -->
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="visivel" id="visivel" value="1" checked>
        <label class="form-check-label" for="visivel">Visível</label>
    </div>

    <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" name="visivelhome" id="visivelhome" value="1">
        <label class="form-check-label" for="visivelhome">Mostrar na Home</label>
    </div>

    <!-- Botões -->
    <div class="d-flex justify-content-between">
        <a href="javascript:history.back()" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Cadastrar Publicação</button>
    </div>
</form>