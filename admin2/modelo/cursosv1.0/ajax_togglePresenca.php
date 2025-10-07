<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$idAluno = $_POST['idaluno'] ?? null;
$data = $_POST['data'] ?? null;
$idTurma = $_POST['idturma'] ?? null;
$presente = $_POST['presente'] ?? null;

if (!$idAluno || !$data || !$idTurma || !in_array($presente, ['0', '1'])) {
    echo json_encode(['sucesso' => false, 'msg' => 'Dados inválidos']);
    exit;
}

$con = config::connect();

if ($presente == '1') {
    // Presença existe, então DELETAR
    $stmt = $con->prepare("DELETE FROM a_site_registraacessos WHERE idusuariora = :id AND datara = :data AND idturmara = :turma");
    $stmt->bindParam(":id", $idAluno);
    $stmt->bindParam(":data", $data);
    $stmt->bindParam(":turma", $idTurma);
    $stmt->execute();
    echo json_encode(['sucesso' => true, 'msg' => 'Presença removida']);
} else {
    // Inserir presença
    $hora = date('H:i:s');
    $stmt = $con->prepare("INSERT INTO a_site_registraacessos (idusuariora, datara, horara, idturmara, datregistrora) VALUES (:id, :data, :hora, :turma, :datregistrora)");
    $stmt->bindParam(":id", $idAluno);
    $stmt->bindParam(":data", $data);
    $stmt->bindParam(":hora", $hora);
    $stmt->bindParam(":turma", $idTurma);
    $stmt->bindParam(":datregistrora", $data);
    $stmt->execute();
    echo json_encode(['sucesso' => true, 'msg' => 'Presença registrada']);
}

