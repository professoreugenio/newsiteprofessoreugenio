<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false];

try {
    if (!isset($_POST['idturma'])) {
        throw new Exception("ID da turma não recebido.");
    }

    $idturma = $_POST['idturma'];

    $stmt = config::connect()->prepare("
        DELETE FROM new_sistema_cursos_turma_data 
        WHERE codigoturmactd = :idturma
    ");
    $stmt->bindParam(":idturma", $idturma);
    $stmt->execute();

    $response['sucesso'] = true;
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
