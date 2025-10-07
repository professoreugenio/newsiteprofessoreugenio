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

try {
    $stmt = config::connect()->prepare("
        UPDATE new_sistema_contato
        SET lixeiraSC = 0,
            datalixeira = NULL,
            horalixeira = NULL
        WHERE codigocontato = :id
    ");
    $stmt->execute([':id' => $id]);

    echo json_encode(['status' => 'sucesso']);
} catch (Exception $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao restaurar a mensagem.']);
}
