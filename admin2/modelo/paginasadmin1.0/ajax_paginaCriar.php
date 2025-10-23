<?php

/**
 * paginasadmin1.0/ajax_paginaCriar.php
 * Cria uma nova página admin em a_site_paginas.
 *
 * Entrada (JSON ou form-data):
 *  - idsessaosp     (int)               [obrigatório]
 *  - nomepaginasp   (string, máx 50)    [obrigatório]
 *  - pastasp        (string, máx 100)   [opcional]
 *  - iconesp        (string, máx 30)    [opcional] (ex.: bi-gear, bi-people)
 *  - visivelsp      (0|1)               [opcional, default 1]
 *  - manutencaosp   (0|1)               [opcional, default 0]
 *
 * Saída (JSON):
 *  { status: "ok", id: <int>, data: {
 *      codigopaginas, idsessaosp, nomepaginasp, pastasp, iconesp,
 *      ordemsp, visivelsp, manutencaosp, enc
 *  }}
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

// Lê JSON do corpo, com fallback para POST comum
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

/* ===== Sanitização / Defaults ===== */
$idsessaosp   = isset($in['idsessaosp'])   ? (string)$in['idsessaosp']   : '';
$nomepaginasp = isset($in['nomepaginasp']) ? (string)$in['nomepaginasp'] : '';
$pastasp      = isset($in['pastasp'])      ? (string)$in['pastasp']      : '';
$iconesp      = isset($in['iconesp'])      ? (string)$in['iconesp']      : '';
$visivelsp    = isset($in['visivelsp'])    ? (string)$in['visivelsp']    : '1';
$manutencaosp = isset($in['manutencaosp']) ? (string)$in['manutencaosp'] : '0';

$idsessaosp   = trim($idsessaosp);
$nomepaginasp = trim(strip_tags($nomepaginasp));
$pastasp      = trim($pastasp);   // pode conter "/" e "-", evitar strip_tags para não remover
$iconesp      = trim(strip_tags($iconesp));

$visivelsp    = ($visivelsp === '1' || $visivelsp === 1 || $visivelsp === true) ? 1 : 0;
$manutencaosp = ($manutencaosp === '1' || $manutencaosp === 1 || $manutencaosp === true) ? 1 : 0;

// Limites conforme schema
if (function_exists('mb_substr')) {
    $nomepaginasp = mb_substr($nomepaginasp, 0, 50, 'UTF-8');
    $pastasp      = mb_substr($pastasp,      0, 100, 'UTF-8');
    $iconesp      = mb_substr($iconesp,      0, 30, 'UTF-8');
} else {
    $nomepaginasp = substr($nomepaginasp, 0, 50);
    $pastasp      = substr($pastasp,      0, 100);
    $iconesp      = substr($iconesp,      0, 30);
}

/* ===== Validação ===== */
$erros = [];
if ($idsessaosp === '' || !ctype_digit($idsessaosp)) {
    $erros[] = 'Sessão inválida.';
}
if ($nomepaginasp === '') {
    $erros[] = 'Informe o nome da página.';
}
if ($iconesp !== '' && !preg_match('/^[a-zA-Z0-9\-_]+$/', $iconesp)) {
    $erros[] = 'Ícone inválido. Use apenas letras, números, hífen e underline (ex.: bi-gear).';
}
// Permite caminhos/slug simples em pastasp (letras, números, / - _ .)
if ($pastasp !== '' && !preg_match('#^[a-zA-Z0-9/_\-.]+$#', $pastasp)) {
    $erros[] = 'Pasta/rota inválida. Use apenas letras, números, /, -, _ e .';
}

if (!empty($erros)) {
    json_out(422, ['status' => 'erro', 'erros' => $erros]);
}

$idsessaosp = (int)$idsessaosp;

try {
    if (!isset($con) || !($con instanceof PDO)) {
        json_out(500, ['status' => 'erro', 'msg' => 'Conexão indisponível.']);
    }

    $con->beginTransaction();

    // (Opcional) Verifica duplicidade de nome dentro da sessão
    $dup = $con->prepare("
        SELECT 1 FROM a_site_paginas
        WHERE idsessaosp = :s AND nomepaginasp = :n
        LIMIT 1
    ");
    $dup->execute([':s' => $idsessaosp, ':n' => $nomepaginasp]);
    if ($dup->fetchColumn()) {
        // Não bloqueia totalmente, mas você pode escolher retornar erro:
        // json_out(409, ['status'=>'erro','msg'=>'Já existe uma página com este nome nesta sessão.']);
        // Aqui apenas marcamos um aviso no retorno:
        $duplicado = true;
    } else {
        $duplicado = false;
    }

    // Calcula próxima ordem na sessão
    $proxStmt = $con->prepare("SELECT COALESCE(MAX(ordemsp), 0) + 1 AS prox FROM a_site_paginas WHERE idsessaosp = :s");
    $proxStmt->execute([':s' => $idsessaosp]);
    $prox = (int)($proxStmt->fetchColumn() ?: 1);

    // Insert
    $ins = $con->prepare("
        INSERT INTO a_site_paginas (idsessaosp, nomepaginasp, pastasp, iconesp, ordemsp, visivelsp, manutencaosp)
        VALUES (:ids, :nome, :pasta, :icone, :ordem, :vis, :man)
    ");
    $ins->bindValue(':ids',   $idsessaosp,    PDO::PARAM_INT);
    $ins->bindValue(':nome',  $nomepaginasp,  PDO::PARAM_STR);
    $ins->bindValue(':pasta', $pastasp,       PDO::PARAM_STR);
    $ins->bindValue(':icone', $iconesp,       PDO::PARAM_STR);
    $ins->bindValue(':ordem', $prox,          PDO::PARAM_INT);
    $ins->bindValue(':vis',   $visivelsp,     PDO::PARAM_INT);
    $ins->bindValue(':man',   $manutencaosp,  PDO::PARAM_INT);
    $ins->execute();

    $id = (int)$con->lastInsertId();

    // Seleciona o registro criado
    $sel = $con->prepare("
        SELECT codigopaginas, idsessaosp, nomepaginasp, pastasp, iconesp,
               ordemsp, visivelsp, manutencaosp
        FROM a_site_paginas
        WHERE codigopaginas = :id
        LIMIT 1
    ");
    $sel->bindValue(':id', $id, PDO::PARAM_INT);
    $sel->execute();
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    $con->commit();

    json_out(200, [
        'status'     => 'ok',
        'id'         => $id,
        'duplicado'  => $duplicado, // apenas informativo
        'data'       => [
            'codigopaginas' => (int)($row['codigopaginas'] ?? $id),
            'idsessaosp'    => (int)($row['idsessaosp']    ?? $idsessaosp),
            'nomepaginasp'  => (string)($row['nomepaginasp'] ?? $nomepaginasp),
            'pastasp'       => (string)($row['pastasp']      ?? $pastasp),
            'iconesp'       => (string)($row['iconesp']      ?? $iconesp),
            'ordemsp'       => (int)($row['ordemsp']        ?? $prox),
            'visivelsp'     => (int)($row['visivelsp']      ?? $visivelsp),
            'manutencaosp'  => (int)($row['manutencaosp']   ?? $manutencaosp),
            'enc'           => encrypt((string)($row['codigopaginas'] ?? $id), 'e'),
        ]
    ]);
} catch (Throwable $e) {
    if ($con && $con->inTransaction()) {
        $con->rollBack();
    }
    json_out(500, [
        'status' => 'erro',
        'msg'    => 'Falha ao criar página.',
        'detail' => $e->getMessage(),
    ]);
}
