<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2)); // Ajuste para refletir sua estrutura
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$codigo = $_POST['id'] ?? null;

if (!$codigo) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Código não informado']);
    exit;
}

try {
    $con = config::connect();
    $stmt = $con->prepare("DELETE FROM new_sistema_msg_alunos WHERE codigomsg = :codigo");
    $stmt->bindParam(':codigo', $codigo, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['sucesso' => true]);
} catch (PDOException $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
