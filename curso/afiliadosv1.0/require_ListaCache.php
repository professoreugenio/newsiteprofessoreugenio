<div class="card-body p-3">
    <!-- Aqui a lista -->

    <?php
    // --- Query das pendências + joins para curso e comissão do produto ---
    $sqlPend = "
    SELECT 
        ac.codigofiliadoscache,
        ac.idafiliadochaveac,
        ac.idprodutoac,
        ac.idclienteac,
        ac.valorac,
        ac.statusac,
        ac.pagamentoac,
        ac.dataac,
        ac.horaac,
        cat.nome AS nomecurso,
        prod.comissaoap
    FROM a_site_afiliados_cache ac
    LEFT JOIN new_sistema_categorias_PJA cat
           ON cat.chaveturmasc = ac.idprodutoac
    LEFT JOIN a_site_afiliados_produto prod
           ON prod.codigoprodutoafiliado = ac.idprodutoac
   WHERE (ac.pagamentoac IS NULL OR ac.pagamentoac = '' OR ac.pagamentoac = 0)
   ORDER BY ac.dataac DESC, ac.horaac DESC
";

    $stmtPend = $con->prepare($sqlPend);
    $stmtPend->execute();
    $itens = $stmtPend->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // --- Função local para calcular o valor da comissão do item (percentual, fração ou fixo) ---
    $calcComItem = function (float $valor, $comissaoap): float {
        $c = ($comissaoap === null) ? 0.0 : (float)$comissaoap;
        if ($c <= 0) return 0.0;
        if ($c > 100) return $c;                // comissão em R$ (fixo)
        if ($c <= 1)  return $valor * $c;       // fração (ex.: 0.4 -> 40%)
        return $valor * ($c / 100.0);           // percentual (ex.: 40 -> 40%)
    };

    // --- Total das comissões ---
    // --- Total das comissões (usando fallback no percentual) ---
    $totalComissao = 0.0;
    foreach ($itens as $r) {
        $valorItem       = (float)($r['valorac'] ?? 0);
        $comissaoLinha   = isset($r['comissaoap']) && $r['comissaoap'] !== null
            ? (float)$r['comissaoap']
            : (float)$comissaoap; // << fallback no comissionamento do produto do topo
        // calcula
        if ($comissaoLinha > 100) {
            $totalComissao += $comissaoLinha;                 // fixo em R$
        } elseif ($comissaoLinha > 1) {
            $totalComissao += $valorItem * ($comissaoLinha / 100); // percentual
        } elseif ($comissaoLinha > 0) {
            $totalComissao += $valorItem * $comissaoLinha;    // fração
        }
    }

    ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 m-0 fw-semibold">Pagamentos pendentes</h2>
        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small">Comissão total</span>
            <span class="badge bg-success-subtle text-success border border-success fw-semibold">
                <?= money_br($totalComissao) ?>
            </span>
        </div>
    </div>

    <?php if (empty($itens)): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-3 m-0">
            Nenhum pagamento pendente encontrado.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Data</th>
                        <th>Status</th>
                        <th>Curso</th>
                        <th class="text-end" style="width: 140px;">Valor</th>
                        <th class="text-end" style="width: 160px;">Comissão</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $r):
                        $valor = (float)($r['valorac'] ?? 0);

                        // fallback do percentual
                        $comissaoLinha = isset($r['comissaoap']) && $r['comissaoap'] !== null
                            ? (float)$r['comissaoap']
                            : (float)$comissaoap;

                        // calcula valor da comissão da linha
                        if ($comissaoLinha > 100) {
                            $comVl = $comissaoLinha;                          // fixo em R$
                            $pctLabel = 'fixo';
                        } elseif ($comissaoLinha > 1) {
                            $comVl = $valor * ($comissaoLinha / 100);         // percentual
                            $pctLabel = rtrim(rtrim(number_format($comissaoLinha, 2, ',', '.'), '0'), ',') . '%';
                        } elseif ($comissaoLinha > 0) {
                            $comVl = $valor * $comissaoLinha;                 // fração
                            $pctLabel = rtrim(rtrim(number_format($comissaoLinha * 100, 2, ',', '.'), '0'), ',') . '%';
                        } else {
                            $comVl = 0.0;
                            $pctLabel = '0%';
                        }

                        // Status
                        $status = (int)($r['statusac'] ?? 0);
                        $statusBadge = ($status === 0)
                            ? '<span class="badge bg-warning-subtle text-warning border border-warning">Pendente</span>'
                            : '<span class="badge bg-success-subtle text-success border border-success">Aprovado</span>';

                        // Data/hora BR
                        $dataFmt = '-';
                        $d = trim((string)($r['dataac'] ?? ''));
                        $t = trim((string)($r['horaac'] ?? ''));
                        if ($d !== '') {
                            $p = explode('-', $d);
                            if (count($p) === 3) $dataFmt = $p[2] . '/' . $p[1] . '/' . $p[0];
                            if ($t !== '') $dataFmt .= ' ' . substr($t, 0, 5);
                        }

                        // Curso
                        echo  $curso = trim((string)($r['nomecurso'] ?? ''));
                        if ($curso === '') {
                            $curso = 'Curso #' . (string)($r['idprodutoac'] ?? '');
                        }

                    ?>
                        <tr>
                            <td class="text-nowrap"><?= h($dataFmt) ?></td>
                            <td><?= $statusBadge ?></td>
                            <td class="text-truncate" style="max-width: 420px;">
                                <?= h($curso) ?>
                                <div class="text-secondary small">Comissão usada: <?= h($pctLabel) ?></div>
                            </td>
                            <td class="text-end"><?= money_br($valor) ?></td>
                            <td class="text-end fw-semibold"><?= money_br($comVl) ?></td>
                        </tr>
                    <?php endforeach; ?>

                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="4" class="text-end">Total de comissões</th>
                        <th class="text-end fw-bold"><?= money_br($totalComissao) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>


</div>