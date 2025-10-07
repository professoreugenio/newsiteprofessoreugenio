<?php


/* =========================
   1) LER FILTROS MÊS/ANO
   ========================= */
$mes = isset($_GET['mes']) && ctype_digit($_GET['mes']) ? max(1, min(12, (int)$_GET['mes'])) : (int)date('n');
$ano = isset($_GET['ano']) && ctype_digit($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

/* =========================
   2) INTERVALOS DE DATA
   ========================= */
$inicioMes = new DateTime(sprintf('%04d-%02d-01', $ano, $mes));
$fimMes    = (clone $inicioMes)->modify('last day of this month');
$diasNoMes = (int)$fimMes->format('t');

/* mês anterior (com virada de ano ok) */
$refAnterior = (clone $inicioMes)->modify('-1 month');
$mesAnterior = (int)$refAnterior->format('n');
$anoAnterior = (int)$refAnterior->format('Y');

/* =========================
   3) MAPA DE DIAS (ZERO-FILL)
   ========================= */
$labels = [];
$serie  = [];
$cur = clone $inicioMes;
while ($cur <= $fimMes) {
    $diaStr = $cur->format('Y-m-d');
    $labels[] = $diaStr;
    $serie[$diaStr] = 0;
    $cur->modify('+1 day');
}

/* =========================
   4) BUSCAR DADOS DO MÊS ATUAL
   ========================= */
$sql = "
  SELECT DATE(datara) AS dia, COUNT(DISTINCT chavera) AS total
  FROM a_site_registraacessos
  WHERE datara BETWEEN :ini AND :fim
  GROUP BY DATE(datara)
  ORDER BY dia ASC
";
$stmt = $con->prepare($sql);
$stmt->execute([
    ':ini' => $inicioMes->format('Y-m-d') . ' 00:00:00',
    ':fim' => $fimMes->format('Y-m-d') . ' 23:59:59',
]);

$totalMesAtual = 0;
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $d = $r['dia'];
    $q = (int)$r['total'];
    if (isset($serie[$d])) $serie[$d] = $q;
    $totalMesAtual += $q;
}

/* =========================
   5) TOTAL DO MÊS ANTERIOR
   ========================= */
$iniAnt = new DateTime(sprintf('%04d-%02d-01', $anoAnterior, $mesAnterior));
$fimAnt = (clone $iniAnt)->modify('last day of this month');

$stmtAnt = $con->prepare("
  SELECT DATE(datara) AS dia, COUNT(DISTINCT chavera) AS total
  FROM a_site_registraacessos
  WHERE datara BETWEEN :ini AND :fim
  GROUP BY DATE(datara)
");
$stmtAnt->execute([
    ':ini' => $iniAnt->format('Y-m-d') . ' 00:00:00',
    ':fim' => $fimAnt->format('Y-m-d') . ' 23:59:59',
]);

$totalMesAnterior = 0;
while ($r = $stmtAnt->fetch(PDO::FETCH_ASSOC)) {
    $totalMesAnterior += (int)$r['total'];
}

/* =========================
   6) KPIs: MÉDIA E VARIAÇÃO
   ========================= */
$mediaDiaria = $diasNoMes > 0 ? round($totalMesAtual / $diasNoMes, 2) : 0;
$variacaoPct = $totalMesAnterior > 0
    ? round((($totalMesAtual - $totalMesAnterior) / $totalMesAnterior) * 100, 2)
    : 0;

/* =========================
   7) LABELS E DADOS PARA CHART
   ========================= */

$labelsJson = json_encode($labels, JSON_UNESCAPED_UNICODE);                 // ex.: ["2025-08-01", ...]
$labelsDias = array_map(fn($d) => (int)substr($d, 8, 2), $labels);          // ex.: [1,2,3,...]
$labelsDiasJson = json_encode($labelsDias, JSON_UNESCAPED_UNICODE);
$dadosJson  = json_encode(array_values($serie), JSON_UNESCAPED_UNICODE);


/* Nome do mês pt-BR sem depender de locale */
$nomesMes = [1 => 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
$tituloMes = $nomesMes[$mes] . ' de ' . $ano;
?>

<!-- =========================
     8) FILTRO MÊS/ANO (ACIMA)
     ========================= -->
<form method="get" class="mb-3 mt-4 d-flex flex-wrap align-items-center gap-2" data-aos="fade-up">
    <div class="input-group w-auto">
        <label class="input-group-text" for="mes">Mês</label>
        <select id="mes" name="mes" class="form-select">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $m === $mes ? 'selected' : '' ?>><?= $nomesMes[$m] ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="input-group w-auto">
        <label class="input-group-text" for="ano">Ano</label>
        <select id="ano" name="ano" class="form-select">
            <?php for ($a = (int)date('Y') + 1; $a >= (int)date('Y') - 5; $a--): ?>
                <option value="<?= $a ?>" <?= $a === $ano ? 'selected' : '' ?>><?= $a ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filtrar
    </button>
</form>



<!-- =========================
     9) CARD + GRÁFICO (CHART.JS)
     ========================= -->
<style>
    /* Wrapper padrão para gráficos: controla altura sem “esticar” o canvas */
    .chart-box {
        height: 360px;
        /* ajuste fino aqui */
        max-height: 55vh;
        /* evita ficar gigante em telas menores */
        position: relative;
        /* necessário para o Chart ocupar 100% */
    }

    .chart-box>canvas {
        width: 100% !important;
        height: 100% !important;
        /* usa a altura do wrapper */
    }
</style>

<div class="card shadow-sm mb-4" data-aos="fade-up">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <h5 class="card-title mb-0">Acessos — <?= htmlspecialchars($tituloMes) ?></h5>
            <span class="small text-muted">Fonte: professoreugenio.com</span>
        </div>

        <div class="d-flex flex-wrap gap-3 mb-3 small">
            <span>Total no mês: <strong><?= $totalMesAtual ?></strong></span>
            <span>Total no mês Anterior: <strong><?= $totalMesAnterior ?></strong></span>
            <span>Média diária: <strong><?= $mediaDiaria ?></strong></span>
            <span>Variação vs. mês anterior:
                <strong class="<?= $variacaoPct >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= ($variacaoPct >= 0 ? '+' : '') . $variacaoPct ?>%
                </strong>
            </span>
        </div>

        <div class="chart-box">
            <canvas id="graficoAcessos"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js (mantenha UM include global no layout) -->
<!-- Chart.js + DataLabels -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
    (function() {
        const ctx = document.getElementById('graficoAcessos');

        if (window.__chartAcessos) window.__chartAcessos.destroy();

        // Labels: números do dia (1..31) no eixo X
        const labelsDias = <?= $labelsDiasJson ?>;
        // Datas completas para tooltip
        const labelsFull = <?= $labelsJson ?>;
        // Série de acessos
        const dataVals = <?= $dadosJson ?>;

        window.__chartAcessos = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsDias, // eixo X com 1..31
                datasets: [{
                    label: 'Acessos únicos por dia',
                    data: dataVals,
                    borderWidth: 2,
                    borderColor: '#00BB9C',
                    backgroundColor: 'rgba(0,187,156,0.15)',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                devicePixelRatio: Math.min(window.devicePixelRatio || 1, 1.5),
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            // Mostra a data completa no tooltip
                            title: (items) => {
                                const idx = items[0].dataIndex;
                                const d = labelsFull[idx]; // "YYYY-MM-DD"
                                const [Y, M, D] = d.split('-');
                                return `${D}/${M}/${Y}`;
                            },
                            label: (item) => `Acessos: ${item.formattedValue}`
                        }
                    },
                    // >>> DATALABELS para exibir valor + dia sobre o ponto <<<
                    datalabels: {
                        display: true,
                        align: 'top',
                        anchor: 'end',
                        offset: 4,
                        font: {
                            weight: '600',
                            size: 10
                        },
                        color: '#333',
                        formatter: (valor, ctx) => {
                            const dia = ctx.chart.data.labels[ctx.dataIndex]; // número do dia
                            return `${valor} · ${dia}`;
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Dia do mês'
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 15
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grace: '5%',
                        ticks: {
                            precision: 0
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    })();
</script>