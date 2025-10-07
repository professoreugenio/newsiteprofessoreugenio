<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    $anoMesAtual = date('Y-m');

    $stmt = $con->prepare("
        SELECT 
            SUM(CASE WHEN l.tipoLancamentos = 1 THEN f.valorCF ELSE 0 END) AS total_credito,
            SUM(CASE WHEN l.tipoLancamentos = 2 THEN f.valorCF ELSE 0 END) AS total_debito
        FROM a_curso_financeiro f
        INNER JOIN a_curso_financeiroLancamentos l ON f.idLancamentoCF = l.codigolancamentos
        WHERE DATE_FORMAT(f.dataFC, '%Y-%m') = :anoMes
    ");
    $stmt->bindValue(':anoMes', $anoMesAtual);
    $stmt->execute();
    $saldo = $stmt->fetch(PDO::FETCH_ASSOC);

    $credito = floatval($saldo['total_credito']);
    $debito = floatval($saldo['total_debito']);
    $saldoAtual = $credito - $debito;

    $valorFormatado = 'R$ ' . number_format($saldoAtual, 2, ',', '.');
    $classe = $saldoAtual >= 0 ? 'text-success' : 'text-danger';

    echo json_encode([
        'sucesso' => true,
        'valor' => $valorFormatado,
        'classe' => $classe
    ]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
