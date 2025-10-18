<?php

/**
 * BodyListaAulas.php (módulo incluído)
 *
 * Espera antes deste include:
 * - $idCurso (int)
 * - $idUser  (int)  -> id do aluno
 * - PDO via config::connect()
 *
 * Importante: por solicitação, NÃO considerar filtro por turma nas consultas.
 */

if (!isset($idCurso, $idUser)) {
    echo '<div class="alert alert-warning">Parâmetros ausentes (idCurso, idUser).</div>';
    return;
}

$pdo = config::connect();

// ---------- Consulta módulos visíveis do curso ----------
$sqlMod = "
    SELECT codigomodulos, modulo, COALESCE(bgcolor,'#112240') AS bgcolor
    FROM new_sistema_modulos_PJA
    WHERE codcursos = :idCurso
      AND visivelm = '1'
    ORDER BY codigomodulos
";
$stMod = $pdo->prepare($sqlMod);
$stMod->execute([':idCurso' => $idCurso]);
$modulos = $stMod->fetchAll(PDO::FETCH_ASSOC);

if (!$modulos) {
    echo '<div class="alert alert-info">Nenhum módulo visível para este curso.</div>';
    return;
}

// ---------- Helpers ----------
function textColorForBg(string $hex): string
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) return '#ffffff';
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $l = (0.299 * $r + 0.587 * $g + 0.114 * $b);
    return ($l > 160) ? '#111827' : '#ffffff';
}

// Lições do módulo (SEM filtrar por turma)
$sqlLessons = "
    SELECT p.codigopublicacoes   AS idpub,
           COALESCE(p.titulo,'(Sem título)') AS titulo,
           COALESCE(apc.ordempc, 999999)     AS ordempc
    FROM a_aluno_publicacoes_cursos apc
    JOIN new_sistema_publicacoes_PJA p
      ON p.codigopublicacoes = apc.idpublicacaopc
    WHERE apc.idcursopc  = :idCurso
      AND apc.idmodulopc = :idModulo
      AND apc.visivelpc  = '1'
    ORDER BY ordempc, titulo
";
$stLessons = $pdo->prepare($sqlLessons);

// Publicações assistidas pelo aluno no módulo
$sqlWatched = "
    SELECT DISTINCT idpublicaa AS idpub
    FROM a_aluno_andamento_aula
    WHERE idalunoaa  = :idAluno
      AND idcursoaa  = :idCurso
      AND idmoduloaa = :idModulo
";
$stWatched = $pdo->prepare($sqlWatched);

$modulosResumo = [];
foreach ($modulos as $m) {
    $stLessons->execute([
        ':idCurso'  => $idCurso,
        ':idModulo' => $m['codigomodulos'],
    ]);
    $lessons = $stLessons->fetchAll(PDO::FETCH_ASSOC);

    $total = count($lessons);

    $stWatched->execute([
        ':idAluno'  => $idUser,
        ':idCurso'  => $idCurso,
        ':idModulo' => $m['codigomodulos'],
    ]);
    $watchedIds = $stWatched->fetchAll(PDO::FETCH_COLUMN, 0);

    // Evita warnings no array_flip (nulos/não escalares)
    $watchedIds = array_filter($watchedIds, function ($v) {
        return is_scalar($v) && $v !== null && $v !== '';
    });
    $watchedSet = $watchedIds ? array_flip($watchedIds) : [];

    $assistidas = 0;
    foreach ($lessons as $l) {
        if (isset($watchedSet[$l['idpub']])) $assistidas++;
    }
    $percent = ($total > 0) ? (int)round(($assistidas / $total) * 100) : 0;

    $modulosResumo[] = [
        'id'         => (int)$m['codigomodulos'],
        'nome'       => $m['modulo'],
        'bg'         => $m['bgcolor'],
        'txt'        => textColorForBg($m['bgcolor']),
        'total'      => $total,
        'assistidas' => $assistidas,
        'percent'    => $percent,
        'lessons'    => $lessons,
        'watchedSet' => $watchedSet,
    ];
}
?>



<!-- Botões de módulos (lado a lado) -->
<div class="d-flex flex-wrap gap-2 modules-toolbar mb-3">
    <?php foreach ($modulosResumo as $mod): ?>
        <?php
        $bg  = htmlspecialchars($mod['bg']);
        $txt = htmlspecialchars($mod['txt']);
        $rot = htmlspecialchars($mod['nome']);
        $pid = (int)$mod['id'];
        $pct = (int)$mod['percent'];
        ?>
        <a href="#mod-<?= $pid ?>" class="btn mod-btn"
            style="background: <?= $bg ?>; color: <?= $txt ?>;">
            <span class="fw-semibold"><?= $rot ?></span>
            <span class="badge ms-2 badge-soft"><?= $pct ?>%</span>
        </a>
    <?php endforeach; ?>
</div>

<!-- Blocos por módulo com lista de lições -->
<div class="sections-stack vstack">
    <?php foreach ($modulosResumo as $i => $mod): ?>
        <?php
        $bg  = htmlspecialchars($mod['bg']);
        $txt = htmlspecialchars($mod['txt']);
        $pid = (int)$mod['id'];
        $rot = htmlspecialchars($mod['nome']);
        $total = (int)$mod['total'];
        $assistidas = (int)$mod['assistidas'];
        $pct = (int)$mod['percent'];

        $qtdeLabel = $total === 1 ? '1 lição' : $total . ' lições';
        $assistLabel = $assistidas . ' assistida' . ($assistidas === 1 ? '' : 's');
        ?>

        <section id="mod-<?= $pid ?>" class="anchor-offset card mod-card">
            <div class="mod-header d-flex flex-wrap align-items-center justify-content-between"
                style="background: <?= $bg ?>; color: <?= $txt ?>;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-soft"><?= $pct ?>%</span>
                    <h5 class="m-0"><?= $rot ?></h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="#mod-<?= $pid ?>" class="link-light text-decoration-underline">
                        <?= $qtdeLabel ?>
                    </a>
                    <span class="opacity-75">
                        <a href="#mod-<?= $pid ?>" class="link-light text-decoration-underline">
                            <?= $assistLabel ?>
                        </a>
                        <span class="ms-1">de <?= $total ?> lições</span>
                    </span>
                </div>
            </div>

            <div class="section-body">
                <div class="lesson-list list-group list-group-flush rounded-bottom">
                    <?php
                    $n = 0;
                    foreach ($mod['lessons'] as $l):
                        $n++;
                        $isDone = isset($mod['watchedSet'][$l['idpub']]);
                        $rowClass = $isDone ? 'lesson-done' : '';
                        $title = htmlspecialchars($l['titulo']);
                    ?>
                        <div class="list-group-item <?= $rowClass ?>">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="lesson-index"><?= $n ?>.</span>
                                    <span><?= $title ?></span>
                                </div>
                                <?php if ($isDone): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Assistida</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Não assistida</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if ($total === 0): ?>
                        <div class="list-group-item">
                            <em class="text-muted">Nenhuma lição visível neste módulo.</em>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</div>