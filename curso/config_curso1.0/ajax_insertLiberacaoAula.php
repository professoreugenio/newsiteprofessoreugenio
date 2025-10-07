<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

// var_dump([
//     'idturma'  => $_POST['idturmasam'] ?? null,
//     'idmodulo' => $_POST['idmodulosam'] ?? null,
//     'titulo' => $_POST['titulo'] ?? null,
//     'idartigo' => $_POST['idartigo_sma'] ?? null,
//     'data'     => $_POST['datasam'] ?? null,
//     'hora'     => date('H:i:s')
// ]);

header('Content-Type: application/json');

try {
    $idturma   = $_POST['idturmasam'] ?? null;
    $idmodulo  = $_POST['idmodulosam'] ?? null;
    $idartigo  = $_POST['idartigo_sma'] ?? null;
    $titulo  = $_POST['titulo'] ?? null;
    $idde  = $_POST['idde'] ?? null;
    $datasam      = $_POST['datasam'] ?? null;
    $pastasam      = date('Ymd') . '_' . time() ?? null;
    $horasam      = date('H:i:s');

    if (!$idturma || !$idmodulo || !$idartigo || !$datasam) {
        throw new Exception("Dados incompletos.");
    }

    $con = config::connect();
    $stmt = $con->prepare("INSERT INTO new_sistema_msg_alunos 
        (iddesam,msgsam,pastasam, idturmasam, tiposam, idmodulosam, idartigo_sma, datasam, horasam,dataatualizasam,horaatualizasam)
        VALUES (:idde, :msg, :pasta, :idturma, 'liberacao', :idmodulo, :idartigo, :data, :hora, :dataatualz, :horaatualz)");

    $stmt->bindParam(":idde", $idde);
    $stmt->bindParam(":msg", $titulo);
    $stmt->bindParam(":pasta", $pastasam);
    $stmt->bindParam(":idturma", $idturma);
    $stmt->bindParam(":idmodulo", $idmodulo);
    $stmt->bindParam(":idartigo", $idartigo);
    $stmt->bindParam(":data", $datasam);
    $stmt->bindParam(":hora", $horasam);
    $stmt->bindParam(":dataatualz", $data);
    $stmt->bindParam(":horaatualz", $hora);
    $stmt->execute();

    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
}
