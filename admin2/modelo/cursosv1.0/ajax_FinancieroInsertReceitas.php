<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    // Apenas POST permitido
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido.');
    }

    // Dados do formulário
    $idusuarioCF    = $_POST['idusuarioCF'] ?? null;
    $idturma        = $_POST['idturma'] ?? null;
    $idLancamentoCF = $_POST['idLancamentoCF'] ?? null;
    $valorCFBruto   = $_POST['valorCF'] ?? null;
    $dataFC         = $_POST['dataFC'] ?? null;
    $descricaoCF    = trim($_POST['descricaoCF'] ?? '');

    if (!$idusuarioCF || !$idLancamentoCF || !$valorCFBruto || !$dataFC) {
        throw new Exception('Campos obrigatórios não preenchidos.');
    }

    // Conversão do valor para formato de banco (decimal)
    $valorCF = str_replace(['.', ','], ['', '.'], $valorCFBruto);

    // Data atual para registro de envio
    $dataEntradaFC = date('Y-m-d');
    $horaFC = date('H:i:s');

    // Inserção no banco
    $stmt = $con->prepare("INSERT INTO a_curso_financeiro 
        (idusuarioCF, idLancamentoCF, valorCF, dataEntradaFC, dataFC, horaFC, idturmaCF, descricaoCF)
        VALUES 
        (:idusuarioCF, :idLancamentoCF, :valorCF, :dataEntradaFC, :dataFC, :horaFC, :idturma, :descricaoCF)");

    $stmt->execute([
        ':idusuarioCF'    => $idusuarioCF,
        ':idLancamentoCF' => $idLancamentoCF,
        ':valorCF'        => $valorCF,
        ':dataEntradaFC'  => $dataEntradaFC,
        ':dataFC'         => $dataFC,
        ':horaFC'         => $horaFC,
        ':idturma'        => $idturma,
        ':descricaoCF'    => $descricaoCF
    ]);

    echo json_encode([
        'status' => 'ok',
        'mensagem' => 'Pagamento registrado com sucesso.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'mensagem' => $e->getMessage()
    ]);
}
