<?php
$idCurso = encrypt($_GET['id'], 'd');

// Filtro de ano da URL
$anoSelecionado = isset($_GET['ano']) ? $_GET['ano'] : '';

// Carregar anos disponíveis
$stmtAnos = config::connect()->prepare("
    SELECT DISTINCT YEAR(datast) AS ano 
    FROM new_sistema_cursos_turmas 
    WHERE codcursost = :idcurso 
    ORDER BY ano DESC
");
$stmtAnos->bindParam(":idcurso", $idCurso);
$stmtAnos->execute();
?>

<!-- Filtro visual por ano -->
<form method="get" class="mb-3 d-flex align-items-center gap-2">
    <input type="hidden" name="id" value="<?= htmlspecialchars($_GET['id']) ?>">
    <label for="anoFiltro" class="form-label mb-0">Filtrar por ano:</label>
    <select name="ano" id="anoFiltro" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">Todos os anos</option>
        <?php while ($anoRow = $stmtAnos->fetch(PDO::FETCH_ASSOC)): ?>
            <option value="<?= $anoRow['ano'] ?>" <?= $anoSelecionado == $anoRow['ano'] ? 'selected' : '' ?>>
                <?= $anoRow['ano'] ?>
            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php
// Query principal com filtro
$query = "SELECT codigoturma, nometurma, ordemct, visivelst, codcursost, chave, datast, horast 
          FROM new_sistema_cursos_turmas 
          WHERE codcursost = :idcurso";

if ($anoSelecionado) {
    $query .= " AND YEAR(datast) = :ano";
}

$query .= " ORDER BY datast DESC, nometurma ASC";

$stmt = config::connect()->prepare($query);
$stmt->bindParam(":idcurso", $idCurso);
if ($anoSelecionado) {
    $stmt->bindParam(":ano", $anoSelecionado, PDO::PARAM_INT);
}
$stmt->execute();
?>

<?php if ($stmt->rowCount() > 0): ?>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php
            $id = $row['codigoturma'];
            $encId = encrypt($id, 'e');
            $nm = $row['nometurma'];
            $ordem = $row['ordemct'];
            $status = $row['visivelst'];
            $ChaveTurma = $row['chave'];

            // Contar alunos
            $stmtIns = config::connect()->prepare("SELECT chaveturma FROM new_sistema_inscricao_PJA WHERE chaveturma = :chaveturma");
            $stmtIns->bindParam(":chaveturma", $ChaveTurma);
            $stmtIns->execute();
            $countaluno = $stmtIns->rowCount();
            ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-mortarboard me-2 text-primary fs-5"></i>
                        <a href="cursos_TurmasAlunos.php?id=<?= $_GET['id']; ?>&tm=<?= $encId; ?>" class="text-decoration-none fw-semibold text-dark me-3">
                            <?= $nm; ?> [<?= $countaluno; ?>]
                        </a>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary rounded-pill me-3"><?= $ordem; ?></span>
                        <span data-bs-toggle="tooltip" title="Status da turma">
                            <i class="bi bi-globe2 fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </span>
                    </div>
                </div>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>Nenhuma turma encontrada<?= $anoSelecionado ? " para o ano $anoSelecionado" : "" ?>.</p>
<?php endif; ?>