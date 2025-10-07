<?php
// --- BUSCA DA ÚLTIMA AULA LIBERADA NO CURSO ---
$stmtUltAula = $con->prepare("
    SELECT 
        p.codigopublicacoes,
        p.titulo,
        m.codigomodulos,
        m.modulo AS nome_modulo,
        a.idpublicacaopc,
        a.idmodulopc
    FROM a_aluno_publicacoes_cursos a
    INNER JOIN new_sistema_publicacoes_PJA p 
        ON p.codigopublicacoes = a.idpublicacaopc
    INNER JOIN new_sistema_modulos_PJA m
        ON m.codigomodulos = a.idmodulopc
    WHERE 
        m.codcursos = :idcurso
        AND a.visivelpc = '1'
        AND a.aulaliberadapc = '1'
    ORDER BY p.codigopublicacoes DESC
    LIMIT 1
");
$stmtUltAula->bindParam(':idcurso', $codigocurso, PDO::PARAM_INT);
$stmtUltAula->execute();
$ultimaAula = $stmtUltAula->fetch(PDO::FETCH_ASSOC);

$encUltAula = '';
if ($ultimaAula) {
    $encUltAula = encrypt($idUser . "&" . $codigocurso . "&" . $idTurma . "&" . $ultimaAula['codigomodulos'], 'e');
}
?>

<div id="cabecalhoAulas" class="p-4 bg-dark text-light rounded-4 shadow-lg mb-4 border border-secondary">
    <div class="row g-4 align-items-stretch">

        <!-- COLUNA ESQUERDA (já existente) -->
        <div class="col-12 col-md-8 d-flex flex-column">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                <div>
                    <h6 class="mb-1 text-uppercase text-muted"><?= $nomeTurma; ?></h6>
                    <h2 class="fw-bold text-white mb-2">
                        <?= $nmmodulo; ?>
                        <span class="badge <?= $corBarra ?> ms-2 align-middle"><?= $perc; ?>%</span>
                    </h2>

                    <?php require 'config_curso1.0/require_CountAulas.php'; ?>

                    <div class="d-flex gap-2 mt-3 ">
                        <a class="btn btn-warning btn-sm" href="modulo_licoes.php">
                            <i class="bi bi-collection-play me-1"></i> Ver todas as lições
                        </a>
                        <a class="btn btn-outline-light btn-sm" href="./">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> + MÓDULOS
                        </a>
                    </div>
                </div>


            </div>

            <!-- (Se tiver mais conteúdo do lado esquerdo, pode manter aqui) -->
        </div>

        <!-- COLUNA DIREITA (card da última aula liberada) -->
        <div class="col-12 col-md-4">
            <div class="h-100 p-3 rounded-4 border border-secondary bg-opacity-100" style="background:#112240;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="mb-0 text-white">Última aula liberada</h5>
                    <span class="badge fw-bold" style="background:#FF9C00; color:#112240;">NOVA</span>
                </div>

                <?php if ($ultimaAula): ?>
                    <!-- <div class="mb-1 text-white-50 small">Módulo</div> -->
                    <div class="fw-semibold mb-3">
                        <i class="bi bi-box-seam me-1"></i>
                        <?= htmlspecialchars($ultimaAula['nome_modulo']); ?>
                    </div>

                    <!-- <div class="mb-1 text-white-50 small">Título da aula</div> -->
                    <div class="fs-6 fw-bold mb-3" style="color:#00BB9C;">
                        <i class="bi bi-bookmark-check me-1"></i>
                        <?= htmlspecialchars($ultimaAula['titulo']); ?>
                        <i class="bi bi-chevron-right ms-2"></i>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="actionCurso.php?mdl=<?= $encUltAula; ?>"
                            class="btn btn-success btn-sm fw-semibold"
                            style="background:#00BB9C; border-color:#00BB9C;">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Acessar módulo
                        </a>
                        <a href="modulo_licoes.php" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-collection me-1"></i> Ver todas as aulas
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-white-50">Nenhuma aula liberada recentemente.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>