<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.');
    }

    $tipolancamento = $_POST['tipolancamento'] ?? null;
    $idusuario = $_POST['idusuario'] ?? null;
    if (!$idusuario) throw new Exception('Usuário não autenticado.');

    $idLancamento = $_POST['idLancamento'] ?? '';
    $valorBruto = $_POST['valor'] ?? '';
    $valor = str_replace(['.', ','], ['', '.'], $valorBruto);
    $dataEntrada = $_POST['dataentrada'] ?? '';
    $descricao = $_POST['descricao'] ?? '';

    if (empty($idLancamento) || empty($valor) || empty($dataEntrada)) {
        throw new Exception('Preencha todos os campos obrigatórios.');
    }

    $dataFC = date('Y-m-d');
    $horaFC = date('H:i:s');

    $stmt = $con->prepare("
        INSERT INTO a_curso_financeiro 
        (idusuarioCF, tipolancamentoCF,  idLancamentoCF, valorCF, descricaoCF, dataEntradaFC, dataFC, horaFC)
        VALUES (:idusuario, :tipolancamento, :idLancamento, :valor, :descricao, :dataEntrada, :dataFC, :horaFC)
    ");

    $stmt->execute([
        ':idusuario'    => $idusuario,
        ':tipolancamento'    => $tipolancamento,
        ':idLancamento' => $idLancamento,
        ':valor'        => $valor,
        ':descricao'        => $descricao,
        ':dataEntrada'  => $dataEntrada,
        ':dataFC'       => $dataFC,
        ':horaFC'       => $horaFC
    ]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Receita lançada com sucesso.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
