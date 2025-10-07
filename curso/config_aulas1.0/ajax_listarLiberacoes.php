<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$data = $_POST['data'] ?? null;
$idturma = $_POST['idturmasam'] ?? null;
$idartigo = $_POST['idartigo_sma'] ?? null;

if (!$data || !$idturma || !$idartigo) {
    echo json_encode([]);
    exit;
}

try {
    $con = config::connect();
    $stmt = $con->prepare("
        SELECT 
            codigomsg,
            msgsam,
            idartigo_sma,
            DATE_FORMAT(datasam, '%d/%m/%Y') AS dataformatada,
            horasam
        FROM new_sistema_msg_alunos
        WHERE datasam = :data
          AND idturmasam = :idturma
        
        ORDER BY horasam ASC
    ");

    $stmt->execute([
        ':data' => $data,
        ':idturma' => $idturma
    ]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}
