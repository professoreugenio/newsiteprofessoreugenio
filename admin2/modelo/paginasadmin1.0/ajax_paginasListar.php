<?php

/**
 * paginasadmin1.0/ajax_paginasListar.php
 * Lista páginas de uma sessão (a_site_paginas) por idsessaosp.
 *
 * Entrada (JSON ou form-data):
 *  - idsessaosp (int) [obrigatório]
 *
 * Saída (JSON):
 *  { status: "ok", data: [
 *      {
 *        codigopaginas, idsessaosp, nomepaginasp, pastasp, iconesp,
 *        ordemsp, visivelsp, manutencaosp, enc
 *      }, ...
 *  ]}
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

// Lê JSON do corpo, com fallback para POST
$raw = file_get_contents('php://input');
$in  = null;
if ($raw) {
    $tmp = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
        $in = $tmp;
    }
}
if (!$in) {
    $in = $_POST;
}

// Validação
$idsessaosp = isset($in['idsessaosp']) ? (string)$in['idsessaosp'] : '';
$idsessaosp = trim($idsessaosp);
if ($idsessaosp === '' || !ctype_digit($idsessaosp)) {
    json_out(422, ['status' => 'erro', 'msg' => 'idsessaosp inválido.']);
}
$idsessaosp = (int)$idsessaosp;

try {
    if (!isset($con) || !($con instanceof PDO)) {
        json_out(500, ['status' => 'erro', 'msg' => 'Conexão indisponível.']);
    }

    $sql = "
        SELECT
            codigopaginas,
            idsessaosp,
            nomepaginasp,
            pastasp,
            iconesp,
            ordemsp,
            visivelsp,
            manutencaosp
        FROM a_site_paginas
        WHERE idsessaosp = :s
        ORDER BY ordemsp ASC, codigopaginas ASC
    ";
    $st = $con->prepare($sql);
    $st->bindValue(':s', $idsessaosp, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

    // Monta saída com id encryptado
    $out = [];
    foreach ($rows as $r) {
        $idpg = (int)($r['codigopaginas'] ?? 0);
        $out[] = [
            'codigopaginas' => $idpg,
            'idsessaosp'    => (int)($r['idsessaosp']    ?? $idsessaosp),
            'nomepaginasp'  => (string)($r['nomepaginasp'] ?? ''),
            'pastasp'       => (string)($r['pastasp']      ?? ''),
            'iconesp'       => (string)($r['iconesp']      ?? ''),
            'ordemsp'       => (int)($r['ordemsp']        ?? 0),
            'visivelsp'     => (int)($r['visivelsp']      ?? 0),
            'manutencaosp'  => (int)($r['manutencaosp']   ?? 0),
            'enc'           => encrypt((string)$idpg, 'e'),
        ];
    }

    json_out(200, ['status' => 'ok', 'data' => $out]);
} catch (Throwable $e) {
    json_out(500, [
        'status' => 'erro',
        'msg'    => 'Falha ao listar páginas.',
        'detail' => $e->getMessage()
    ]);
}
