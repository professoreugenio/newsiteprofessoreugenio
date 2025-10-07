**<?php

    // $decpub = encrypt($_GET['pub'], $action = 'd');
    echo $idPublicacao;
    echo "-";
    echo $idModuloPublicacao;
    echo "-";
    echo $idCursoPublicacao;
    ?>
**
<form id="formEditarPublicacao" method="post" action="ajax_publicacaoEditar.php">
    <input type="hidden" name="idpublicacao" value="<?= $encIdPublicacao; ?>">
    <input type="hidden" name="idcurso" value="<?= $_GET['id']; ?>">




    <!-- Título -->
    <div class="mb-3">
        <label for="tituloPublicacao" class="form-label">Título</label>
        <input type="text" name="titulo" id="tituloPublicacao" class="form-control" value="<?= htmlspecialchars($tituloPublicacao); ?>" required>
    </div>

    <!-- Link Externo -->
    <div class="row mb-3">
        <!-- Link Externo -->
        <div class="col-md-4 col-lg-6">
            <label for="linkExterno" class="form-label">Link externo (opcional)</label>
            <input type="url" name="linkexterno" id="linkExterno" class="form-control" value="<?= htmlspecialchars($linkExterno); ?>">
        </div>
        <div class="col-md-2 col-lg-2">
            <label for="linkExterno" class="form-label">Ordem</label>
            <input type="number" name="ordem" id="ordem" class="form-control" value="<?= htmlspecialchars($ordem); ?>">
        </div>
        <?= $comercial ?>
        <input type="hidden" name="comercial" id="comercial" value="<?= $comercial ?>">

        <!-- Módulo -->
        <div class="col-md-3 col-lg-2">
            <label for="modulo" class="form-label">Módulo <?= $idModuloPublicacao = encrypt($_GET['md'], $action = 'd'); ?> C <?= $idCurso = encrypt($_GET['id'], $action = 'd'); ?></label>
            <select name="idmodulo" id="idmodulo" class="form-select" required>
                <option value="">Selecione</option>

                <?php
                $stmt = config::connect()->prepare("SELECT codcursos,modulo,ordemm,visivelm,codigomodulos   FROM new_sistema_modulos_PJA WHERE codcursos =:idcurso ORDER BY ordemm");
                $stmt->bindParam(":idcurso", $idCurso);
                $stmt->execute();
                ?>
                <?php if ($stmt->rowCount() > 0): ?>
                    <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                        <?php $id = $row['codigomodulos'];  ?>
                        <?php $encId = encrypt($id, $action = 'e'); ?>
                        <?php $nm = $row['modulo'];  ?>
                        <?php $ordem = $row['ordemm'];  ?>
                        <?php $status = $row['visivelm'];  ?>

                        <option value="<?= $encId ?>" <?= ($id ?? '') == $idModuloPublicacao ? 'selected' : '' ?>><?= $nm; ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Nenhum módulo encontrado.</p>
                <?php endif; ?>

            </select>
        </div>

        <!-- Aula -->
        <div class="col-md-2 col-lg-2">
            <label for="aula" class="form-label">Aula</label>
            <select name="aula" id="aula" class="form-select" required>
                <option value="">Selecione</option>
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <option value="<?= $i ?>" <?= ($rwPublicacao['aula'] ?? '') == $i ? 'selected' : '' ?>>Aula <?= $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>


    <!-- Descrição -->
    <div class="mb-3">
        <label for="descricao" class="form-label">Descrição / Olho</label>
        <textarea name="descricao" id="descricao" class="form-control" rows="3"><?= htmlspecialchars($Descricao); ?></textarea>
    </div>

    <!-- Tags -->
    <?php
    $ntags = $tags;
    if (empty($tags)):
        $ntags = gerarTags($rwPublicacao['texto'] ?? '');
    endif;
    ?>
    <div class="mb-3">
        <label for="tags" class="form-label">Tags (separadas por vírgula)</label>
        <input type="text" name="tags" id="tags" class="form-control" value="<?= $ntags; ?>">
    </div>

    <!-- Visibilidade -->
    <div class="form-check form-switch mb-2">
        <input class="form-check-input" type="checkbox" name="visivel" id="visivel" value="1" <?= $chkon; ?>>
        <label class="form-check-label" for="visivel">Visível</label>
    </div>

    <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" name="visivelhome" id="visivelhome" value="1" <?= $chkvh; ?>>
        <label class="form-check-label" for="visivelhome">Mostrar na Home</label>
    </div>

    <!-- Botões -->
    <!-- <div class="d-flex justify-content-between">
        <a href="javascript:history.back()" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Salvar alterações</button>
    </div> -->

    <!-- Botões -->
    <div class="d-flex justify-content-between">
        <a href="javascript:history.back()" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Botão fixo -->
    <button type="submit" class="btn btn-success btn-lg shadow"
        style="position:fixed; bottom:40px; right:40px; z-index:1050;">
        <i class="bi bi-save"></i> Salvar alterações
    </button>

</form>



<?php require_once APP_ROOT . '/admin2/modelo/adm/idcursomodulo.php'; ?>