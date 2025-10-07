<?php

// Calcular saldo do mês atual
$anoMesAtual = date('Y-m');
$stmtSaldo = $con->prepare("
    SELECT 
        SUM(CASE WHEN l.tipoLancamentos = 1 THEN f.valorCF ELSE 0 END) AS total_credito,
        SUM(CASE WHEN l.tipoLancamentos = 2 THEN f.valorCF ELSE 0 END) AS total_debito
    FROM a_curso_financeiro f
    INNER JOIN a_curso_financeiroLancamentos l ON f.idLancamentoCF = l.codigolancamentos
    WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes
");
$stmtSaldo->bindValue(':anoMes', $anoMesAtual);
$stmtSaldo->execute();
$saldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);
$credito = floatval($saldo['total_credito']);
$debito = floatval($saldo['total_debito']);
$saldoAtual = $credito - $debito;
?>
<div id="cardSaldoFixo" class="position-fixed" style="top: 80px; right: 0; z-index: 1055; width: 260px;">
    <div class="bg-white shadow rounded p-3 border-start border-4">
        <div class="text-center">
            <h6 class="text-secondary mb-1">Saldo Atual (<?= date('m/Y') ?>)</h6>
            <h4 id="valorSaldoCard" class="fw-bold <?= $saldoAtual >= 0 ? 'text-success' : 'text-danger' ?>">
                R$ <?= number_format($saldoAtual, 2, ',', '.') ?>
            </h4>

        </div>
    </div>
</div>