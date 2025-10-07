<form method="post" id="formEditarModulo" class="row g-4 mt-3 needs-validation" data-aos="fade-up">
    <input type="hidden" name="idModulo" value="<?= $encIdModulo ?>">
    <input type="hidden" name="idCurso" value="<?= $_GET['id'] ?>">
    <input type="hidden" name="chavem" value="<?= $ChaveModulo ?>">

    <!-- Nome do Módulo -->
    <div class="col-md-6">
        <label for="modulo" class="form-label">Nome do Módulo</label>
        <input type="text" class="form-control" id="modulo" name="modulo" value="<?= htmlspecialchars($rwModulo['modulo']) ?>" required>
    </div>

    <!-- Descrição -->
    <div class="col-md-6">
        <label for="descricao" class="form-label">Descrição</label>
        <input type="text" class="form-control" id="descricao" name="descricao" value="<?= htmlspecialchars($Descricao) ?>">
    </div>

    <!-- Valor do Módulo -->
    <div class="col-md-3">
        <label for="valorm" class="form-label">Valor (R$)</label>
        <input type="number" step="0.01" class="form-control" id="valorm" name="valorm" value="<?= $Valor ?>">
    </div>

    <!-- Valor Hora -->
    <div class="col-md-3">
        <label for="valorh" class="form-label">Valor Hora (R$)</label>
        <input type="number" step="0.01" class="form-control" id="valorh" name="valorh" value="<?= $ValorHora ?>">
    </div>

    <!-- Nº de Aulas -->
    <div class="col-md-3">
        <label for="nraulasm" class="form-label">Quantidade de Aulas</label>
        <input type="number" class="form-control" id="nraulasm" name="nraulasm" value="<?= $QuantidadedeAulas ?>">
    </div>

    <!-- Ordem de Classificação -->
    <div class="col-md-3">
        <label for="ordemm" class="form-label">Ordem</label>
        <input type="number" class="form-control" id="ordemm" name="ordemm" value="<?= $NumeroOrdemClassificacao ?>">
    </div>

    <!-- Cor de Fundo -->
    <div class="col-md-3">
        <label for="bgcolor" class="form-label">Cor de Fundo (bgcolor)</label>
        <input type="color" class="form-control form-control-color" id="bgcolor" name="bgcolor" value="<?= $Bocolor ?>" title="Escolha uma cor">
    </div>

    <!-- Visível no Módulo -->
    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" id="visivelm" name="visivelm" <?= $chkon ?>>
            <label class="form-check-label" for="visivelm">Visível no Curso</label>
        </div>
    </div>

    <!-- Visível na Home -->
    <div class="col-md-3 d-flex align-items-center">
        <div class="form-check form-switch mt-4">
            <input class="form-check-input" type="checkbox" role="switch" id="visivelhome" name="visivelhome" <?= $chkvh ?>>
            <label class="form-check-label" for="visivelhome">Destaque na Home</label>
        </div>
    </div>

    <!-- Botão Salvar -->
    <div class="col-12">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-save me-1"></i> Salvar Alterações
        </button>

        <button type="submit" id="BtExcluirModulo" class="btn btn-danger me-2">
            <i class="bi bi-save me-1"></i>Excluir Módulo
        </button>
    </div>
</form>