<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idModulo = $_POST['idmodulo'] ?? null;

    if (!$idModulo) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos.']);
        exit;
    }

    try {
        $novoStatus="0";
        $sql = "UPDATE a_aluno_publicacoes_cursos 
                SET aulaliberadapc = :novo 
                WHERE idmodulopc = :idModulo";
        $stmt = config::connect()->prepare($sql);
        $stmt->bindParam(":novo", $novoStatus);
        $stmt->bindParam(':idModulo', $idModulo);
        $stmt->execute();
        echo json_encode(['sucesso' => true]);
    } catch (PDOException $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
    }
}
