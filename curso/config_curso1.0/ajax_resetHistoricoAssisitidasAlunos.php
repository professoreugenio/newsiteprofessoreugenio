<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idTurma = $_POST['idturma'] ?? null;

    if (!$idTurma) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos.']);
        exit;
    }

    try {
        $sql = "DELETE FROM a_aluno_andamento_aula 
                WHERE idturmaaa = :idTurma";
        $stmt = config::connect()->prepare($sql);
        $stmt->bindParam(':idTurma', $idTurma);
        $stmt->execute();
        echo json_encode(['sucesso' => true]);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
    }
}
