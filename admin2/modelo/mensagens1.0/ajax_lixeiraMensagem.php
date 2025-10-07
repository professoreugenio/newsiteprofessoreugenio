<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';

header('Content-Type: application/json');

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
    exit;
}

$id = intval($_POST['id']);
$dataHoje = date('Y-m-d');
$horaAgora = date('H:i:s');

try {
    $stmt = config::connect()->prepare("
        UPDATE new_sistema_contato
        SET lixeiraSC = 1,
            datalixeira = :data,
            horalixeira = :hora
        WHERE codigocontato = :id
    ");
    $stmt->execute([
        ':data' => $dataHoje,
        ':hora' => $horaAgora,
        ':id' => $id
    ]);

    echo json_encode(['status' => 'sucesso']);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao mover para a lixeira.']);
}
