<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'acao' => '', 'total' => 0];

try {
    if (!isset($_POST['data'], $_POST['idturma'])) {
        throw new Exception("Dados incompletos.");
    }

    $data = $_POST['data'];
    $idturma = $_POST['idturma'];

    // Verifica se já existe
    $check = config::connect()->prepare("
        SELECT coidgoturmadata 
        FROM new_sistema_cursos_turma_data 
        WHERE codigoturmactd = :idturma AND dataaulactd = :data
    ");
    $check->bindParam(":idturma", $idturma);
    $check->bindParam(":data", $data);
    $check->execute();

    if ($check->rowCount() > 0) {
        // Excluir
        $del = config::connect()->prepare("
            DELETE FROM new_sistema_cursos_turma_data 
            WHERE codigoturmactd = :idturma AND dataaulactd = :data
        ");
        $del->bindParam(":idturma", $idturma);
        $del->bindParam(":data", $data);
        $del->execute();

        $response['acao'] = 'removido';
    } else {
        // Inserir
        $ins = config::connect()->prepare("
            INSERT INTO new_sistema_cursos_turma_data (codigoturmactd, dataaulactd, datactd, horactd)
            VALUES (:idturma, :data, :datactd, :data)
        ");
        $ins->bindParam(":idturma", $idturma);
        $ins->bindParam(":data", $data);
        $ins->bindParam(":datactd", $data);
        $ins->bindParam(":horactd", $hora);
        $ins->execute();

        $response['acao'] = 'inserido';
    }

    // Contar total após alteração
    $conta = config::connect()->prepare("
        SELECT COUNT(*) FROM new_sistema_cursos_turma_data 
        WHERE codigoturmactd = :idturma
    ");
    $conta->bindParam(":idturma", $idturma);
    $conta->execute();
    $response['total'] = $conta->fetchColumn();

    $response['sucesso'] = true;
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
