<!-- Formulário com botão para abrir modal -->
<form method="get" class="d-flex align-items-center gap-2 mb-4">
    <label for="data" class="fw-bold">Data do Aniversário:</label>
    <input type="date" name="data" id="data" value="<?= htmlspecialchars($dataFiltro) ?>" class="form-control" style="width:180px" onchange="this.form.submit()">
    <span class="badge bg-primary ms-3"><?= $stmt->rowCount() ?> aniversariante<?= $stmt->rowCount() == 1 ? '' : 's' ?></span>
    <?php require 'usuariosv1.0/modalMensagensemMassa.php' ?>
</form>