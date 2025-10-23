<?php

/**
 * paginasadmin1.0/ajax_paginasReordenar.php
 * Reordena páginas de a_site_paginas.
 *
 * Entrada (JSON ou form-data):
 *  - ordem: [ { idpg: <int>, pos: <int> }, ... ]   [obrigatório]
 *  - idsessaosp: <int>                              [opcional — reforça o escopo]
 *
 * Saída (JSON):
 *  { status: "ok", atualizados: <int> }
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

@header('Content-Type: application/json; charset=UTF-8');

function json_out(int $code, array $payload)
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Lê JSON com fallback para POST
$raw = file_get_contents('php://input');
$in  = null;
if ($raw) {
    $tmp = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
        $in = $tmp;
    }
}
if (!$in) $in = $_POST;

// Coleta parâmetros
$ordem      = $in['ordem'] ?? null;
$idsessRaw  = isset($in['idsessaosp']) ? (string)$in['idsessaosp'] : '';
$idsess     = ($idsessRaw !== '' && ctype_digit($idsessRaw)) ? (int)$idsessRaw : null;

// Validação básica
if (!is_array($ordem) || empty($ordem)) {
    json_out(422, ['status' => 'erro', 'msg' => 'Payload "ordem" inválido ou vazio.']);
}

// Normaliza e valida itens
$map = [];           // idpg => pos
$idList = [];        // ids únicos
$posSet = [];        // checar duplicidade de posições
foreach ($ordem as $item) {
    if (!is_array($item)) continue;
    $idpg = isset($item['idpg']) ? (string)$item['idpg'] : '';
    $pos  = isset($item['pos'])  ? (string)$item['pos']  : '';

    if ($idpg === '' || !ctype_digit($idpg)) {
        json_out(422, ['status' => 'erro', 'msg' => 'idpg inválido na lista.']);
    }
    if ($pos === '' || !ctype_digit($pos)) {
        json_out(422, ['status' => 'erro', 'msg' => 'pos inválida na lista.']);
    }
    $id  = (int)$idpg;
    $ord = (int)$pos;
    if ($id <= 0 || $ord <= 0) {
        json_out(422, ['status' => 'erro', 'msg' => 'Valores não podem ser <= 0.']);
    }

    $map[$id] = $ord;      // se repetir id, última ocorrência prevalece
    $idList[$id] = true;
    if (isset($posSet[$ord])) {
        // Não é bloqueador, mas ajuda a evitar conflitos gritantes
        // Você pode optar por retornar erro aqui se quiser estritamente único:
        // json_out(422, ['status'=>'erro','msg'=>'Posições duplicadas detectadas.']);
    }
    $posSet[$ord] = true;
}
$ids = array_keys($idList);
if (empty($ids)) {
    json_out(422, ['status' => 'erro', 'msg' => 'Nenhum idpg válido informado.']);
}

try {
    if (!isset($con) || !($con instanceof PDO)) {
        json_out(500, ['status' => 'erro', 'msg' => 'Conexão indisponível.']);
    }

    // (Opcional) Verifica se todos os IDs existem (e pertencem à sessão, se fornecida)
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $sqlCheck = "SELECT codigopaginas, idsessaosp FROM a_site_paginas WHERE codigopaginas IN ($ph)";
    $st = $con->prepare($sqlCheck);
    foreach ($ids as $k => $v) $st->bindValue($k + 1, $v, PDO::PARAM_INT);
    $st->execute();
    $exist = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$exist) {
        json_out(404, ['status' => 'erro', 'msg' => 'IDs não encontrados.']);
    }

    // Se idsessaosp foi enviado, restringe a atualização somente aos itens dessa sessão
    if ($idsess !== null) {
        foreach ($exist as $row) {
            if ((int)$row['idsessaosp'] !== $idsess) {
                json_out(403, ['status' => 'erro', 'msg' => 'Itens fora da sessão informada.']);
            }
        }
    }

    // Monta UPDATE em lote via CASE
    $cases = [];
    $params = [];
    foreach ($ids as $id) {
        $cases[] = "WHEN ? THEN ?";
        $params[] = $id;
        $params[] = $map[$id];
    }
    $whereIds = implode(',', array_fill(0, count($ids), '?'));
    $params = array_merge($params, $ids);

    $sql = "UPDATE a_site_paginas
          SET ordemsp = CASE codigopaginas " . implode(' ', $cases) . " END
          WHERE codigopaginas IN ($whereIds)";
    // Se quiser reforçar por sessão:
    if ($idsess !== null) {
        $sql .= " AND idsessaosp = ?";
        $params[] = $idsess;
    }

    $con->beginTransaction();
    $up = $con->prepare($sql);
    foreach ($params as $i => $v) {
        $up->bindValue($i + 1, (int)$v, PDO::PARAM_INT);
    }
    $up->execute();
    $afetados = $up->rowCount();
    $con->commit();

    json_out(200, ['status' => 'ok', 'atualizados' => (int)$afetados]);
} catch (Throwable $e) {
    if ($con && $con->inTransaction()) $con->rollBack();
    json_out(500, [
        'status' => 'erro',
        'msg'    => 'Falha ao reordenar páginas.',
        'detail' => $e->getMessage()
    ]);
}
