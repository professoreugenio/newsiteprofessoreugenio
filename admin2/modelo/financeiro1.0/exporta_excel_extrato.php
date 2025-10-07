<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

// Recupera mês e ano do filtro
$mes = $_GET['mes'] ?? date('m');
$ano = $_GET['ano'] ?? date('Y');
$anoMes = "$ano-$mes";

// Define headers para forçar download em Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=extrato_{$mes}_{$ano}.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Monta o HTML como conteúdo do Excel
echo "<table border='1'>";
echo "<tr style='background:#e6e6e6; font-weight:bold;'>
        <td>Data Pagamento</td>
        <td>Data Lançamento</td>
        <td>Hora</td>
        <td>Tipo</td>
        <td>".utf8_decode('Descrição')."</td>
        <td>Valor (R$)</td>
      </tr>";

// Busca dados do extrato
$stmt = $con->prepare("
    SELECT 
        f.dataEntradaFC,
        f.dataFC,
        f.horaFC,
        f.valorCF,
        f.idLancamentoCF,
        l.nomelancamentosFL,
        l.tipoLancamentos,
        f.descricaoCF
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

// Exibe cada linha
foreach ($dados as $row) {
    $valor = floatval($row['valorCF']);
    $isDebito = $row['tipoLancamentos'] == 2;
    $saldoTotal += $isDebito ? -$valor : $valor;

    echo "<tr>";
    echo "<td>" . date('d/m/Y', strtotime($row['dataEntradaFC'])) . "</td>";
    echo "<td>" . date('d/m/Y', strtotime($row['dataFC'])) . "</td>";
    echo "<td>" . $row['horaFC'] . "</td>";
    echo "<td>" . utf8_decode(htmlspecialchars($row['nomelancamentosFL'])) . "</td>";
    echo "<td>" . utf8_decode(htmlspecialchars($row['descricaoCF'] ?? '-')) . "</td>";

    echo "<td style='color:" . ($isDebito ? 'red' : 'green') . "'>" . number_format($valor, 2, ',', '.') . "</td>";
    echo "</tr>";
}

// Saldo final
echo "<tr style='font-weight:bold; background:#f0f0f0;'>
        <td colspan='5' align='right'>Saldo Final</td>
        <td style='color:" . ($saldoTotal < 0 ? 'red' : 'green') . "'>R$ " . number_format($saldoTotal, 2, ',', '.') . "</td>
      </tr>";

echo "</table>";
exit;
