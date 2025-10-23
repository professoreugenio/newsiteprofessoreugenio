<?php

/**
 * paginasadmin1.0/ajax_paginaToggleManutencao.php
 * Liga/Desliga a flag de manutenção de uma página (a_site_paginas.manutencaosp).
 *
 * Entrada (JSON ou form-data):
 *  - idpg          (int)   [obrigatório]
 *  - manutencaosp  (0|1)   [obrigatório]
 *
 * Saída (JSON):
 *  { status:"ok", idpg:<int>, manutencaosp:<int> }
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
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) $in = $tmp;
}
if (!$in) $in = $_POST;

// Captura e valida
$idpgRaw  = isset($in['idpg']) ? (string)$in['idpg'] : '';
$manRaw   = isset($in['manutencaosp']) ? (string)$in['manutencaosp'] : '';

$idpg = (ctype_digit($idpgRaw) ? (int)$idpgRaw : 0);
$man  = ($manRaw === '1' || $manRaw === 1 || $manRaw === true) ? 1 : 0;

if ($idpg <= 0) {
    json_out(422, ['status' => 'erro', 'msg' => 'idpg inválido.']);
}

try {
    if (!isset($con) || !($con instanceof PDO)) {
        json_out(500, ['status' => 'erro', 'msg' => 'Conexão indisponível.']);
    }

    $con->beginTransaction();

    // Garante existência do registro
    $chk = $con->prepare("SELECT codigopaginas FROM a_site_paginas WHERE codigopaginas = :id LIMIT 1");
    $chk->bindValue(':id', $idpg, PDO::PARAM_INT);
    $chk->execute();
    if (!$chk->fetchColumn()) {
        $con->rollBack();
        json_out(404, ['status' => 'erro', 'msg' => 'Página não encontrada.']);
    }

    // Atualiza manutenção
    $up = $con->prepare("UPDATE a_site_paginas SET manutencaosp = :m WHERE codigopaginas = :id");
    $up->bindValue(':m',  $man,  PDO::PARAM_INT);
    $up->bindValue(':id', $idpg, PDO::PARAM_INT);
    $up->execute();

    $con->commit();

    json_out(200, [
        'status'        => 'ok',
        'idpg'          => $idpg,
        'manutencaosp'  => $man
    ]);
} catch (Throwable $e) {
    if ($con && $con->inTransaction()) $con->rollBack();
    json_out(500, [
        'status' => 'erro',
        'msg'    => 'Falha ao alterar manutenção.',
        'detail' => $e->getMessage()
    ]);
}
