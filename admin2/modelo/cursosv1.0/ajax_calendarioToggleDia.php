<?php
// ajax_calendarioToggleDia.php

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['data'], $_POST['idturma'])) {
        throw new Exception("Parâmetros obrigatórios ausentes.");
    }

    $data = $_POST['data'];
    $idTurma = $_POST['idturma'];

    // Verifica se o dia já está cadastrado
    $check = config::connect()->prepare("SELECT COUNT(*) FROM new_sistema_cursos_turma_data WHERE codigoturmactd = :idturma AND dataaulactd = :data");
    $check->bindParam(':idturma', $idTurma);
    $check->bindParam(':data', $data);
    $check->execute();

    $existe = $check->fetchColumn();

    if ($existe) {
        // Remover
        $delete = config::connect()->prepare("DELETE FROM new_sistema_cursos_turma_data WHERE codigoturmactd = :idturma AND dataaulactd = :data");
        $delete->bindParam(':idturma', $idTurma);
        $delete->bindParam(':data', $data);
        $delete->execute();
        echo json_encode(['sucesso' => true, 'acao' => 'removido', 'mensagem' => 'Dia removido.']);
    } else {
        // Inserir
        $insert = config::connect()->prepare("INSERT INTO new_sistema_cursos_turma_data (codigoturmactd, dataaulactd, datactd, horactd) VALUES (:idturma, :data, :datactd, :horactd)");
        $insert->bindParam(':idturma', $idTurma);
        $insert->bindParam(':data', $data);
        $insert->bindParam(':datactd', $data);
        $insert->bindParam(':horactd', $hora);
        $insert->execute();
        echo json_encode(['sucesso' => true, 'acao' => 'inserido', 'mensagem' => 'Dia adicionado.']);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
}
