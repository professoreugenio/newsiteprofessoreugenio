<?php

$sqlUltimas = "
    SELECT 
        p.codigopublicacoes,
        p.titulo,
        p.olho,
        apc.idmodulopc,
        apc.datapc,
        m.modulo AS nome_modulo
    FROM a_aluno_publicacoes_cursos apc
    INNER JOIN new_sistema_publicacoes_PJA p
        ON p.codigopublicacoes = apc.idpublicacaopc
    LEFT JOIN new_sistema_modulos_PJA m
        ON m.codigomodulos = apc.idmodulopc
    WHERE 
        apc.visivelpc = 1
        AND apc.idcursopc = :idCurso
        AND apc.datapc IS NOT NULL
    ORDER BY apc.datapc DESC, apc.ordempc DESC, p.codigopublicacoes DESC
    LIMIT 3
";


$qUlt = $con->prepare($sqlUltimas);
$qUlt->bindParam(':idCurso', $idCurso, PDO::PARAM_INT);
$qUlt->execute();
$ultimas = $qUlt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($ultimas)):
?>
    <section id="ultimas-aulas" class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h4 class="mb-0" style="color:#00BB9C;">Últimas aulas adicionadas</h4>
            <!-- <span class="badge bg-warning text-dark">Últimos 30 dias</span> -->
        </div>

        <div class="row g-3">
            <?php foreach ($ultimas as $row):
                $idPub     = (int)$row['codigopublicacoes'];
                $idModulo  = (int)$row['idmodulopc'];
                $titulo    = trim((string)$row['titulo']);
                $olho      = trim((string)($row['olho'] ?? ''));
                $moduloNm  = trim((string)($row['nome_modulo'] ?? 'Módulo'));
                $dataPub   = $row['datapc'] ? databr($row['datapc']) : '—';

                // Link criptografado (mantendo padrão)
                $encModulo = encrypt($idModulo, 'e');
                $var = $idUser . "&" . $idCurso . "&" . $idTurma . "&" . $idModulo . "&" . $idPub;
                $encPub = encrypt($var, 'e');
                $hrefAbrir = "actionCurso.php?pubult={$encPub}";

                // ===== Capa da publicação (SEM visivel) =====
                $capa = $raizSite . "/img/nomodulo.png";

                // 1) Tenta favorito
                $qCapa = $con->prepare("
                SELECT pasta, foto
                FROM new_sistema_publicacoes_fotos_PJA
                WHERE codpublicacao = :idpub AND favorito_pf = 1
                ORDER BY codigomfotos DESC
                LIMIT 1
            ");
                $qCapa->bindParam(':idpub', $idPub, PDO::PARAM_INT);
                $qCapa->execute();
                $rwCapa = $qCapa->fetch(PDO::FETCH_ASSOC);

                // 2) Fallback: qualquer registro (prioriza favorito, numimg='1', tipo, mais recente)
                if (!$rwCapa) {
                    $qCapa2 = $con->prepare("
                    SELECT pasta, foto
                    FROM new_sistema_publicacoes_fotos_PJA
                    WHERE codpublicacao = :idpub
                    ORDER BY favorito_pf DESC, (numimg = '1') DESC, tipo DESC, codigomfotos DESC
                    LIMIT 1
                ");
                    $qCapa2->bindParam(':idpub', $idPub, PDO::PARAM_INT);
                    $qCapa2->execute();
                    $rwCapa = $qCapa2->fetch(PDO::FETCH_ASSOC);
                }

                if ($rwCapa && !empty($rwCapa['foto'])) {
                    $capa = $raizSite . "/fotos/publicacoes/" . $rwCapa['pasta'] . "/" . $rwCapa['foto'];
                }
                // ===== fim capa =====
            ?>
                <div class="col-12 col-md-4" data-aos="fade-up">

                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="ratio ratio-16x9" style="background:#0d1324; cursor:pointer;"
                            onclick="window.location.href='<?= $hrefAbrir ?>'">
                            <div class="w-100 h-100"
                                title="<?= htmlspecialchars($capa) ?>"
                                style="background:url('<?= htmlspecialchars($capa) ?>') center/cover no-repeat;"></div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-dark-subtle text-dark">Módulo : <i class="bi bi-diagram-3 me-1"></i><?= htmlspecialchars($moduloNm) ?></span>
                                <small class="text-white">Publicado: <strong><?= $dataPub ?></strong></small>
                            </div>

                            <h6 class="fw-semibold mb-1 text-white" title="<?= htmlspecialchars($titulo) ?>">
                                <?= htmlspecialchars($titulo) ?>
                            </h6>



                            <?php if ($olho == '1000'): ?>
                                <p class="small text-secondary mb-3 text-white" title="<?= htmlspecialchars($olho) ?>">
                                    <?= htmlspecialchars($olho) ?>
                                </p>
                            <?php endif; ?>

                            <div class="mt-auto d-grid">
                                <a href="<?= $hrefAbrir ?>" class="btn btn-warning fw-semibold">
                                    Assistir agora <i class="bi bi-play-fill ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>