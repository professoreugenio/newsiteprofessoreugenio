<?php
$online = isset($_GET['online']) ? $_GET['online'] : null;
$matriz = isset($_GET['matriz']) ? $_GET['matriz'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

$stmt = config::connect()->prepare("SELECT codigocursos, nome, visivelsc, ordemsc, datasc, horasc, comercialsc,onlinesc, matriz, lixeirasc  
FROM new_sistema_cursos 
WHERE 
comercialsc =:status 
AND onlinesc =:online 
AND matriz =:matriz 
AND lixeirasc != 1  
ORDER BY 
datasc DESC, horasc DESC
");
$stmt->bindParam(":status", $status);
$stmt->bindParam(":online", $online);
$stmt->bindParam(":matriz", $matriz);
$stmt->execute();
?>
<?php if ($stmt->rowCount() > 0): ?>
    <ul class="list-group">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <?php $id = $row['codigocursos'];  ?>
            <?php $encId = encrypt($id, $action = 'e'); ?>
            <?php $nm = $row['nome'];  ?>
            <?php $ordem = $row['ordemsc'];  ?>
            <?php $comercial = $row['comercialsc'];  ?>
            <?php $status = $row['visivelsc'];  ?>
            <?php
            $query = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE codcursost = :idcurso ");
            $query->bindParam(":idcurso", $id, PDO::PARAM_INT);
            $query->execute();
            $quant = $query->rowCount();
            ?>
            <?php $encTurm = encrypt($row['codigocursos'], $action = 'e'); ?>
            <li class="list-group-item flex-column mb-2 shadow-sm rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-mortarboard me-2 text-primary fs-5"></i>
                        <a href="cursos_editar.php?id=<?= $encId; ?>" class="text-decoration-none fw-semibold text-dark me-3">
                            <?= $nm; ?> <?= $row['matriz']; ?> (<?= $quant; ?> turma<?= $quant != 1 ? 's' : '' ?>) *
                        </a>
                        <button class="btn btn-sm btn-outline-secondary" onclick="verTurmas(<?= $id; ?>, this)">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-secondary rounded-pill me-3"><?= $ordem; ?></span>
                        <span data-bs-toggle="tooltip" title="<?= $status == 1 ? 'Curso online' : 'Curso offline'; ?>">
                            <i class="bi bi-globe2 fs-5 <?= $status == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </span>&nbsp;
                        <span data-bs-toggle="tooltip" title="<?= $comercial == 1 ? 'Comercial' : 'Livre'; ?>">
                            <i class="fa fa-dollar fs-5 <?= $comercial == 1 ? 'text-success' : 'text-danger'; ?>"></i>
                        </span>
                    </div>
                </div>
                <!-- Aqui aparecerão as turmas -->
                <div class="mt-2 ps-4 d-none" id="turmas-<?= $id; ?>"></div>
            </li>

        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>Nenhum curso encontrado.</p>
<?php endif; ?>

<script>
    function verTurmas(idCurso, btn) {
        const box = document.getElementById("turmas-" + idCurso);
        if (!box.classList.contains("d-none")) {
            box.classList.add("d-none");
            btn.innerHTML = '<i class="bi bi-chevron-down"></i>';
            return;
        }

        // Carrega via AJAX apenas se estiver fechado
        box.innerHTML = '<small class="text-muted">Carregando turmas...</small>';
        fetch('cursosv1.0/ajax_carregar_turmas.php?id=' + idCurso)
            .then(res => res.text())
            .then(html => {
                box.innerHTML = html;
                box.classList.remove("d-none");
                btn.innerHTML = '<i class="bi bi-chevron-up"></i>';
            })
            .catch(() => {
                box.innerHTML = '<small class="text-danger">Erro ao carregar turmas.</small>';
            });
    }
</script>