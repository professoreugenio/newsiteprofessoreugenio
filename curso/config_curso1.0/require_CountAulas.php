<?php if ($comercialDados == '0'): ?>
    <div class="mt-3">
        <?php
        // Contagem distinta de aulas (por data)
        $qA = $con->prepare("
        SELECT COUNT(DISTINCT datasam) 
        FROM new_sistema_msg_alunos 
        WHERE idturmasam = :idturma
    ");
        $qA->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
        $qA->execute();
        $quantAulas = (int)$qA->fetchColumn();

        // Contagem distinta de aulas dentro do módulo atual
        $qM = $con->prepare("
        SELECT COUNT(DISTINCT datasam) 
        FROM new_sistema_msg_alunos 
        WHERE idturmasam = :idturma AND idmodulosam = :idmodulo
    ");
        $qM->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
        $qM->bindParam(":idmodulo", $codigomodulo, PDO::PARAM_INT);
        $qM->execute();
        $quantModulos = (int)$qM->fetchColumn();
        ?>

        <div class="d-flex flex-wrap gap-3 p-3 rounded-3 shadow-sm align-items-center"
            style="background:#112240; ">

            <!-- Total de Aulas -->
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-3 py-2"
                    style="background:#00BB9C; font-size:.9rem; color:#fff;">
                    <i class="bi bi-book-half me-1"></i> <?= $quantAulas ?> Aulas
                </span>
            </div>

            <!-- Aula atual dentro do módulo -->
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill px-3 py-2"
                    style="background:#FF9C00; font-size:.9rem; color:#fff;">
                    <i class="bi bi-collection-play me-1"></i> <?= $quantModulos ?>ª Aula de <?= htmlspecialchars($nmmodulo) ?>
                </span>
            </div>
        </div>
    </div>

<?php endif; ?>