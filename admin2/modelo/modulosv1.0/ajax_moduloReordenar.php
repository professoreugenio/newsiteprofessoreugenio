<?php

/**
 * modulosv1.0/ajax_moduloReordenar.php
 * Atualiza a coluna 'ordemm' conforme a nova ordem enviada.
 * Espera POST: ordem[] (ids criptografados), idcurso (opcional), filtro (opcional).
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

    $ordem = $_POST['ordem'] ?? [];
    if (!is_array($ordem) || count($ordem) === 0) {
        throw new Exception('Lista de itens inválida.');
    }

    // (opcional) validar curso e filtro
    $idcurso = isset($_POST['idcurso']) ? (int)$_POST['idcurso'] : 0;
    $filtro  = isset($_POST['filtro']) ? (string)$_POST['filtro'] : null;

    // Decrypt IDs
    $ids = [];
    foreach ($ordem as $encId) {
        $id = (int) encrypt((string)$encId, $action = 'd');
        if ($id > 0) $ids[] = $id;
    }
    if (count($ids) === 0) {
        throw new Exception('IDs inválidos.');
    }

    // Atualiza em transação
    $con->beginTransaction();

    $sql = "UPDATE new_sistema_modulos_PJA SET ordemm = :ordem WHERE codigomodulos = :id";
    $stmt = $con->prepare($sql);

    $pos = 1;
    foreach ($ids as $id) {
        $stmt->bindValue(':ordem', $pos, PDO::PARAM_INT);
        $stmt->bindValue(':id',    $id,  PDO::PARAM_INT);
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            throw new Exception('Erro ao atualizar ordem: ' . ($err[2] ?? ''));
        }
        $pos++;
    }

    $con->commit();

    $out['success'] = true;
    $out['message'] = 'Ordem salva com sucesso.';
    echo json_encode($out);
    exit;
} catch (Throwable $e) {
    if (isset($con) && $con instanceof PDO && $con->inTransaction()) {
        $con->rollBack();
    }
    $out['success'] = false;
    $out['message'] = $e->getMessage();
    echo json_encode($out);
    exit;
}
