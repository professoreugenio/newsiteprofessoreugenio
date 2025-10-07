<form id="formReceita">
    <div class="row g-3">
        <div class="col-md-6">
            <label for="idLancamento" class="form-label fw-semibold">Tipo de <?= ($tipo == 1) ? 'Receitas' : 'Despesas'; ?>
            </label>
            <select name="idLancamento" id="idLancamento" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach ($tipos as $tp): ?>
                    <option value="<?= $tp['codigolancamentos'] ?>"><?= htmlspecialchars($tp['nomelancamentosFL']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="valor" class="form-label fw-semibold">Valor (R$)</label>
            <input type="text" name="valor" id="valor" class="form-control text-end" required placeholder="0,00">
            <input type="hidden" name="idusuario" id="idusuario" value="<?php echo $codadm;  ?>">
            <input type="hidden" name="tipolancamento" id="tipolancamento" value="<?php echo $tipo;  ?>">
        </div>

        <div class="col-md-3">
            <label for="dataentrada" class="form-label fw-semibold">Data Entrada</label>
            <input type="date" name="dataentrada" id="dataentrada" class="form-control" value="<?php echo $data;  ?>" required>
        </div>

        <div class="col-md-12">
            <label for="descricao" class="form-label fw-semibold">Descrição</label>
            <input type="text" name="descricao" id="descricao" class="form-control" maxlength="120">
        </div>
    </div>

    <div class="text-end mt-4">
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> *Lançar <?= ($tipo == 1) ? 'Receitas' : 'Despesas'; ?>

        </button>
    </div>
</form>