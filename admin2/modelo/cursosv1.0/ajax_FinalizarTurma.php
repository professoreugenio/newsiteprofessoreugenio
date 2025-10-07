<?php

/**
 * cursosv1.0/ajax_FinalizarTurma.php
 * Atualiza andamento = 1 (finalizar) ou andamento = 0 (reativar)
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

try {
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3));
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    $input = json_decode(file_get_contents('php://input'), true);
    $chave = trim((string)($input['chave'] ?? ''));
    $acao  = trim((string)($input['acao'] ?? ''));

    if ($chave === '' || !in_array($acao, ['finalizar', 'reativar'], true)) {
        echo json_encode(['status' => 'erro', 'msg' => 'Parâmetros inválidos.']);
        exit;
    }

    $novoStatus = $acao === 'finalizar' ? 1 : 0;

    $pdo = config::connect();
    $stmt = $pdo->prepare("UPDATE new_sistema_cursos_turmas SET andamento = :novo WHERE chave = :chave LIMIT 1");
    $stmt->bindParam(':novo', $novoStatus, PDO::PARAM_INT);
    $stmt->bindParam(':chave', $chave);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $msg = $acao === 'finalizar' ? 'Turma finalizada com sucesso.' : 'Turma reativada com sucesso.';
        echo json_encode(['status' => 'ok', 'msg' => $msg]);
    } else {
        echo json_encode(['status' => 'alerta', 'msg' => 'Nenhuma alteração realizada.']);
    }
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'msg' => 'Erro interno.']);
}
