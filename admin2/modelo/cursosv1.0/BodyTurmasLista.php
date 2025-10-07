<?php $id = "1"; ?>
<?php $ordem = "1"; ?>
<?php $status = "1"; ?>
<?php
$stmt = config::connect()->prepare("SELECT codigoturma,nometurma,ordemct,visivelst,codcursost,chave, datast, horast  FROM new_sistema_cursos_turmas WHERE codcursost =:idcurso ORDER BY datast DESC, nometurma ASC ");
$stmt->bindParam(":idcurso", $idCurso);
$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $id = $row['codigoturma'];  ?>
            <?php $encId = encrypt($id, $action = 'e'); ?>
            <?php $nm = $row['nometurma'];  ?>
            <?php $ordem = $row['ordemct'];  ?>
            <?php $status = $row['visivelst'];  ?>
            <?php $ChaveTurma = $row['chave'];  ?>
            <?php

            $stmtIns = config::connect()->prepare("SELECT chaveturma FROM  new_sistema_inscricao_PJA WHERE chaveturma =:chaveturma ");
            $stmtIns->bindParam(":chaveturma", $ChaveTurma);
            $stmtIns->execute();
            ?>
            <?php $countaluno = $stmtIns->rowCount(); ?>
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