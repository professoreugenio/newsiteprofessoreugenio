<?php $id = "1"; ?>
<?php $ordem = "1"; ?>
<?php $status = "1"; ?>
<?= $idCurso; ?>
<?php
$stmt = config::connect()->prepare("SELECT codcursos,modulo,ordemm,visivelm,codigomodulos   FROM new_sistema_modulos_PJA WHERE codcursos =:idcurso ORDER BY ordemm");
$stmt->bindParam(":idcurso", $idCurso);
$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $id = $row['codigomodulos'];  ?>
            <?php $encId = encrypt($id, $action = 'e'); ?>
            <?php $nm = $row['modulo'];  ?>
            <?php $ordem = $row['ordemm'];  ?>
            <?php $status = $row['visivelm'];  ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-mortarboard me-2 text-primary fs-5"></i>
                        <a href="cursos_publicacoes.php?id=<?= $_GET['id']; ?>&md=<?= $encId; ?>" class="text-decoration-none fw-semibold text-dark me-3">
                            <?= $nm; ?> : Md: <?= $id; ?>
                        </a>

                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary rounded-pill me-3"><?= $ordem; ?></span>
                        <span data-bs-toggle="tooltip" title="menu">
                            <i class="bi bi-globe2 fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </span>
                    </div>
                </div>

            </li>

        <?php endwhile; ?>

    </ul>
<?php else: ?>
    <p>Nenhum módulo encontrado.</p>
<?php endif; ?>