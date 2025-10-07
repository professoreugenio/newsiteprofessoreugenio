<?php

/**
 * Módulo: BodyListaAulasPorModulo.php
 * Requisitos: $idCurso, $idTurma, $idUser definidos no escopo do arquivo principal.
 * Dependências: PDO via config::connect()
 *
 * Observações:
 * - Não inclui defines/bootstrap/headers (página modulada).
 * - Usa Bootstrap 5.3+ e classes utilitárias.
 */

// (Opcional) Caso precise ler por GET/decodificar, descomente e ajuste:
// $idCurso = $idCurso ?? (isset($_GET['idcurso']) ? (int)$_GET['idcurso'] : 0);
// $idTurma = $idTurma ?? (isset($_GET['idturma']) ? (int)$_GET['idturma'] : 0);
// $idUser = $idUser ?? (isset($_GET['idaluno']) ? (int)$_GET['idaluno'] : 0);

$pdo = config::connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1) Buscar módulos visíveis do curso
$sqlModulos = "
  SELECT m.codigomodulos, m.modulo, COALESCE(m.ordemm, 9999) AS ordemm, COALESCE(m.nraulasm, 0) AS nraulasm
  FROM new_sistema_modulos_PJA m
  WHERE m.codcursos = :idCurso AND m.visivelm = '1'
  ORDER BY ordemm, m.codigomodulos
";
$stmtM = $pdo->prepare($sqlModulos);
$stmtM->execute([':idCurso' => $idCurso]);
$modulos = $stmtM->fetchAll(PDO::FETCH_ASSOC);

// Guardar índice (1., 2., 3. …)
$modIndex = 1;

// 2) Buscar andamento do aluno (assistidas) em lote p/ otimizar
$sqlAssistidas = "
  SELECT DISTINCT idpublicaa, idmoduloaa
  FROM a_aluno_andamento_aula
  WHERE idcursoaa = :idCurso
    AND idturmaaa = :idTurma
    AND idalunoaa = :idAluno
    AND COALESCE(atividadeaa, 1) = 1
";
$stmtA = $pdo->prepare($sqlAssistidas);
$stmtA->execute([
    ':idCurso' => $idCurso,
    ':idTurma' => $idTurma,
    ':idAluno' => $idUser,
]);
$assistidasRaw = $stmtA->fetchAll(PDO::FETCH_ASSOC);

$assistidasByModulo = [];
foreach ($assistidasRaw as $r) {
    $mid = (int)$r['idmoduloaa'];
    $pid = (int)$r['idpublicaa'];
    if (!isset($assistidasByModulo[$mid])) $assistidasByModulo[$mid] = [];
    $assistidasByModulo[$mid][$pid] = true; // set
}

// 3) Pré-carregar totais de lições por módulo (contagem)
$sqlTotais = "
  SELECT idmodulopc AS mid, COUNT(*) AS total
  FROM a_aluno_publicacoes_cursos
  WHERE idcursopc = :idCurso
    AND idturmapc = :idTurma
    AND COALESCE(visivelpc,1) = 1
    AND COALESCE(aulaliberadapc,1) = 1
  GROUP BY idmodulopc
";
$stmtT = $pdo->prepare($sqlTotais);
$stmtT->execute([':idCurso' => $idCurso, ':idTurma' => $idTurma]);
$totaisPorModulo = [];
foreach ($stmtT->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $totaisPorModulo[(int)$t['mid']] = (int)$t['total'];
}

// 4) Função helper para percentuais
$fmtPct = function (int $assistidas, int $total): string {
    if ($total <= 0) return '0%';
    $pct = floor(($assistidas / $total) * 100);
    return $pct . '%';
};

?>

<!-- ESTILOS LEVES (opcional) -->
<style>
    .mod-scroll-x {
        overflow-x: auto;
        white-space: nowrap;
    }

    .mod-btns .btn {
        margin-right: .5rem;
        margin-bottom: .5rem;
    }

    .lesson-item.locked {
        opacity: .6;
    }

    .lesson-item .bi {
        font-size: 1rem;
    }

    .anchor-offset {
        scroll-margin-top: 90px;
    }

    /* ajuste p/ sticky headers */
</style>

<!-- BOTÕES DE MÓDULOS (lado a lado) -->
<div class="mod-scroll-x mb-3">
    <div class="mod-btns d-inline-flex flex-wrap">
        <?php foreach ($modulos as $m):
            $mid   = (int)$m['codigomodulos'];
            $nome  = htmlspecialchars($m['modulo'] ?? ('Módulo ' . $mid));
            $total = $totaisPorModulo[$mid] ?? 0;
            $assistidas = isset($assistidasByModulo[$mid]) ? count($assistidasByModulo[$mid]) : 0;
            $pct = $fmtPct($assistidas, $total);
        ?>
            <a href="#mod-<?= $mid; ?>" class="btn btn-outline-primary btn-sm">
                <?= $nome; ?>
                <span class="badge text-bg-secondary ms-2"><?= $pct; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if (empty($modulos)): ?>
    <div class="alert alert-warning">Nenhum módulo visível para este curso.</div>
<?php endif; ?>

<!-- LISTA DE LIÇÕES POR MÓDULO -->
<div class="d-flex flex-column gap-3">
    <?php
    // Preparar statement para carregar lições por módulo
    $sqlLicoes = "
  SELECT idpublicacaopc, aulapc, ordempc, bloqueadopc, aulaliberadapc
  FROM a_aluno_publicacoes_cursos
  WHERE idcursopc = :idCurso
    AND idturmapc = :idTurma
    AND idmodulopc = :idModulo
    AND COALESCE(visivelpc,1) = 1
  ORDER BY COALESCE(ordempc, 9999), COALESCE(aulapc, 0), idpublicacaopc
";
    $stmtL = $pdo->prepare($sqlLicoes);

    foreach ($modulos as $m):
        $mid   = (int)$m['codigomodulos'];
        $nome  = htmlspecialchars($m['modulo'] ?? ('Módulo ' . $mid));
        $idx   = $modIndex++;

        // Totais/assistidas
        $total = $totaisPorModulo[$mid] ?? 0;
        $assistidas = isset($assistidasByModulo[$mid]) ? count($assistidasByModulo[$mid]) : 0;
        $pct = $fmtPct($assistidas, $total);

        // Carregar lições do módulo
        $stmtL->execute([
            ':idCurso' => $idCurso,
            ':idTurma' => $idTurma,
            ':idModulo' => $mid
        ]);
        $licoes = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        // Set de assistidas p/ checar por publicação
        $setAssistidas = $assistidasByModulo[$mid] ?? [];
    ?>
        <section id="mod-<?= $mid; ?>" class="card anchor-offset">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="m-0"><?= $idx; ?>. <?= $nome; ?></h5>
                    <span class="badge text-bg-info"><?= $pct; ?></span>
                </div>

                <div class="text-end small">
                    <a href="#mod-<?= $mid; ?>" class="me-3 text-decoration-none">
                        <i class="bi bi-journal-text"></i> <?= $total; ?> lições
                    </a>
                    <span class="text-muted">
                        <i class="bi bi-check2-circle"></i>
                        <?= $assistidas; ?> assistidas de <?= $total; ?> lições
                    </span>
                </div>
            </div>

            <div class="card-body">
                <?php if ($total > 0): ?>
                    <div class="mb-2">
                        <div class="progress" role="progressbar" aria-label="Progresso do módulo" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int)floor(($total > 0) ? ($assistidas / $total * 100) : 0); ?>">
                            <div class="progress-bar" style="width: <?= (int)floor(($total > 0) ? ($assistidas / $total * 100) : 0); ?>%"></div>
                        </div>
                    </div>

                    <ol class="list-group list-group-numbered">
                        <?php
                        $n = 1;
                        foreach ($licoes as $L):
                            $pubId   = (int)$L['idpublicacaopc'];
                            $ordem   = $L['ordempc'] !== null ? (int)$L['ordempc'] : $n;
                            $bloq    = (int)($L['bloqueadopc'] ?? 0);
                            $lib     = (int)($L['aulaliberadapc'] ?? 1);
                            $ok      = isset($setAssistidas[$pubId]);
                            $locked  = ($bloq === 1 || $lib === 0);

                            // Título da lição: como não foi fornecida tabela de títulos, usamos um rótulo padrão
                            $titulo  = "Lição " . $ordem;
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center lesson-item <?= $locked ? 'locked' : ''; ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($ok): ?>
                                        <i class="bi bi-check-circle-fill" aria-label="Assistida"></i>
                                    <?php elseif ($locked): ?>
                                        <i class="bi bi-lock-fill" aria-label="Bloqueada"></i>
                                    <?php else: ?>
                                        <i class="bi bi-play-circle" aria-label="Disponível"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($titulo); ?></span>
                                </div>

                                <div class="text-nowrap">
                                    <?php if ($ok): ?>
                                        <span class="badge text-bg-success">Assistida</span>
                                    <?php elseif ($locked): ?>
                                        <span class="badge text-bg-secondary">Bloqueada</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-primary">Disponível</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php
                            $n++;
                        endforeach; ?>
                    </ol>
                <?php else: ?>
                    <div class="text-muted">Este módulo ainda não possui lições visíveis.</div>
                <?php endif; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<!-- JS opcional: rolagem suave para os módulos -->
<script>
    document.querySelectorAll('.mod-btns a[href^="#"]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>