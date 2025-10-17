<?php

/**
 * modulosv1.0/ajax_moduloAlterarStatus.php
 * Altera o status (visivelm) de um módulo.
 * Aceita: status = 1 (visível), 0 (oculto), 9 (lixeira).
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

$out = ['success' => false, 'message' => 'Erro desconhecido.'];

try {
    // Conexão
    if (!isset($con) || !$con instanceof PDO) {
        if (class_exists('config') && method_exists('config', 'connect')) {
            $con = config::connect();
        }
    }
    if (!$con instanceof PDO) {
        throw new Exception('Conexão indisponível.');
    }

    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new Exception('Método inválido.');
    }

    $encId  = $_POST['id']     ?? '';
    $status = $_POST['status'] ?? '';

    if ($encId === '' || $status === '') {
        throw new Exception('Parâmetros inválidos.');
    }

    if (!in_array((string)$status, ['0', '1', '9'], true)) {
        throw new Exception('Status não permitido.');
    }

    // Decrypt do ID
    $idModulo = (int) encrypt($encId, $action = 'd');
    if ($idModulo <= 0) {
        throw new Exception('ID do módulo inválido.');
    }

    // Atualiza visivelm
    $sql = "UPDATE new_sistema_modulos_PJA SET visivelm = :status WHERE codigomodulos = :id LIMIT 1";
    $stmt = $con->prepare($sql);
    $stmt->bindValue(':status', (string)$status, PDO::PARAM_STR);
    $stmt->bindValue(':id', $idModulo, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        $err = $stmt->errorInfo();
        throw new Exception('Falha ao atualizar status. ' . ($err[2] ?? ''));
    }

    $out['success'] = true;
    $out['message'] = ($status === '9') ? 'Módulo movido para a lixeira.' : (($status === '1') ? 'Módulo definido como visível.' : 'Módulo ocultado.');
    echo json_encode($out);
    exit;
} catch (Throwable $e) {
    $out['success'] = false;
    $out['message'] = $e->getMessage();
    echo json_encode($out);
    exit;
}
