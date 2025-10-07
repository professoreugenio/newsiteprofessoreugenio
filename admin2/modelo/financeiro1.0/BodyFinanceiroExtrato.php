<?php require 'financeiro1.0/botoesFinanceiro.php' ?>
<?php


// Filtro de mês e ano (padrão = mês atual)
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$anoMes = "$ano-$mes";

// Buscar lançamentos
$stmt = $con->prepare("
    SELECT 
        f.valorCF,
        f.dataEntradaFC,
        f.descricaoCF,
        f.dataFC,
        f.horaFC,
        f.idLancamentoCF,
        l.nomelancamentosFL,
        l.tipoLancamentos
    FROM a_curso_financeiro f
    INNER JOIN a_curso_financeiroLancamentos l 
        ON f.idLancamentoCF = l.codigolancamentos
    WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes
    ORDER BY f.dataFC ASC, f.horaFC ASC
");
$stmt->bindValue(':anoMes', $anoMes);
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$saldoTotal = 0;
?>

<div class="container mt-4">

    <?php require 'financeiro1.0/CardSaldoTotal.php'; ?>
    <h4 class="mb-4 text-primary"><i class="bi bi-journal-text me-2"></i>Extrato Financeiro</h4>

    <!-- Filtro mês/ano -->
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-md-2">
            <label class="form-label">Mês</label>
            <select name="mes" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++):
                    $selected = $mes == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '';
                ?>
                    <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $selected ?>>
                        <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Ano</label>
            <select name="ano" class="form-select">
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= ($ano == $y ? 'selected' : '') ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search"></i> Filtrar
            </button>
        </div>
    </form>
    <!-- Botão para gerar Excel -->
    <div class="d-flex justify-content-end mb-3">
        <a href="financeiro1.0/exporta_excel_extrato.php?mes=<?= $mes ?>&ano=<?= $ano ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel"></i> Gerar Planilha Excel
        </a>
    </div>

    <!-- Lista de lançamentos -->
    <div class="list-group shadow-sm">
        <?php if (count($dados) > 0): ?>
            <!-- Cabeçalho da lista -->
            <div class="list-group-item bg-light fw-semibold d-none d-md-block">
                <div class="row">
                    <div class="col-md-2">Data Pagamento</div>
                    <div class="col-md-2">Data/Hora Lançamento</div>
                    <div class="col-md-2">Tipo</div>
                    <div class="col-md-4">Descrição</div>
                    <div class="col-md-2 text-end">Valor</div>
                </div>
            </div>

            <!-- Lista dos lançamentos -->
            <?php foreach ($dados as $row):
                $valor = floatval($row['valorCF']);
                $isDebito = $row['tipoLancamentos'] == 2;
                $saldoTotal += $isDebito ? -$valor : $valor;
                $classe = $isDebito ? 'text-danger' : 'text-success';
                $valorFormatado = number_format($valor, 2, ',', '.');
                $dataPagamento = date('d/m/Y', strtotime($row['dataEntradaFC']));
                $dataLancamento = date('d/m/Y', strtotime($row['dataFC'])) . ' ' . $row['horaFC'];
            ?>
                <div class="list-group-item">
                    <div class="row align-items-center">
                        <div class="col-md-2"><?= $dataPagamento ?></div>
                        <div class="col-md-2"><?= $dataLancamento ?></div>
                        <div class="col-md-2"><?= htmlspecialchars($row['nomelancamentosFL']) ?></div>
                        <div class="col-md-4 text-muted"><?= htmlspecialchars($row['descricaoCF'] ?? '-') ?></div>
                        <div class="col-md-2 text-end fw-bold <?= $classe ?>">R$ <?= $valorFormatado ?></div>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <div class="list-group-item text-center text-muted">Nenhum lançamento encontrado neste período.</div>
        <?php endif; ?>
    </div>

    <!-- Saldo Total -->
    <div class="mt-4 text-end">
        <h5>Saldo Total:
            <span class="<?= $saldoTotal < 0 ? 'text-danger' : 'text-success' ?>">
                R$ <?= number_format($saldoTotal, 2, ',', '.') ?>
            </span>
        </h5>
    </div>
</div>