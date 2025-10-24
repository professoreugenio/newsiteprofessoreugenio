<?php

/**
 * paginasadmin1.0/ajax_paginaAtualizar.php
 * Atualiza os campos de uma página admin (a_site_paginas).
 *
 * Entrada (JSON ou form-data):
 *  - codigopaginas  (int)              [obrigatório]
 *  - idsessaosp     (int)              [obrigatório]
 *  - nomepaginasp   (string, máx 50)   [obrigatório]
 *  - pastasp        (string, máx 100)  [opcional | regex: [a-zA-Z0-9/_\-.]+]
 *  - iconesp        (string, máx 30)   [opcional | regex: [a-zA-Z0-9\-_]+]
 *  - ordemsp        (int >= 1)         [obrigatório]
 *  - visivelsp      (0|1)              [obrigatório]
 *  - manutencaosp   (0|1)              [obrigatório]
 *
 * Saída (JSON):
 *  { status:"ok", id:<int>, data:{...campos atualizados..., enc:<string>} }
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
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) $in = $tmp;
}
if (!$in) $in = $_POST;

/* ===== Coleta / Sanitização ===== */
$codigopaginas = isset($in['codigopaginas']) ? (string)$in['codigopaginas'] : '';
$idsessaosp    = isset($in['idsessaosp'])    ? (string)$in['idsessaosp']    : '';
$nomepaginasp  = isset($in['nomepaginasp'])  ? (string)$in['nomepaginasp']  : '';
$pastasp       = isset($in['pastasp'])       ? (string)$in['pastasp']       : '';
$iconesp       = isset($in['iconesp'])       ? (string)$in['iconesp']       : '';
$ordemsp       = isset($in['ordemsp'])       ? (string)$in['ordemsp']       : '';
$visivelsp     = isset($in['visivelsp'])     ? (string)$in['visivelsp']     : '';
$manutencaosp  = isset($in['manutencaosp'])  ? (string)$in['manutencaosp']  : '';

$codigopaginas = trim($codigopaginas);
$idsessaosp    = trim($idsessaosp);
$nomepaginasp  = trim(strip_tags($nomepaginasp));
$pastasp       = trim($pastasp);   // rota pode conter / - _ .
$iconesp       = trim(strip_tags($iconesp));
$ordemsp       = trim($ordemsp);
$visivelsp     = ($visivelsp === '1' || $visivelsp === 1 || $visivelsp === true) ? 1 : 0;
$manutencaosp  = ($manutencaosp === '1' || $manutencaosp === 1 || $manutencaosp === true) ? 1 : 0;

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

if ($codigopaginas === '' || !ctype_digit($codigopaginas) || (int)$codigopaginas <= 0) {
    $erros[] = 'ID da página inválido.';
}
if ($idsessaosp === '' || !ctype_digit($idsessaosp) || (int)$idsessaosp <= 0) {
    $erros[] = 'Sessão inválida.';
}
if ($nomepaginasp === '') {
    $erros[] = 'Informe o nome da página.';
}
if ($ordemsp === '' || !ctype_digit($ordemsp) || (int)$ordemsp <= 0) {
    $erros[] = 'Ordem inválida (use inteiro >= 1).';
}
if ($iconesp !== '' && !preg_match('/^[a-zA-Z0-9\-_]+$/', $iconesp)) {
    $erros[] = 'Ícone inválido. Use apenas letras, números, hífen e underline (ex.: bi-gear).';
}
if ($pastasp !== '' && !preg_match('#^[a-zA-Z0-9/_\-.]+$#', $pastasp)) {
    $erros[] = 'Pasta/rota inválida. Use apenas letras, números, /, -, _ e .';
}

if (!empty($erros)) {
    json_out(422, ['status' => 'erro', 'erros' => $erros]);
}

$codigopaginas = (int)$codigopaginas;
$idsessaosp    = (int)$idsessaosp;
$ordemsp       = (int)$ordemsp;

try {
    if (!isset($con) || !($con instanceof PDO)) {
        json_out(500, ['status' => 'erro', 'msg' => 'Conexão indisponível.']);
    }

    $con->beginTransaction();

    // Verifica se a página existe
    $chk = $con->prepare("SELECT codigopaginas FROM a_site_paginas WHERE codigopaginas = :id LIMIT 1");
    $chk->bindValue(':id', $codigopaginas, PDO::PARAM_INT);
    $chk->execute();
    if (!$chk->fetchColumn()) {
        $con->rollBack();
        json_out(404, ['status' => 'erro', 'msg' => 'Página não encontrada.']);
    }

    // (Opcional) Verifica se a sessão existe
    $chkS = $con->prepare("SELECT codigosessao FROM a_site_sessao WHERE codigosessao = :s LIMIT 1");
    $chkS->bindValue(':s', $idsessaosp, PDO::PARAM_INT);
    $chkS->execute();
    if (!$chkS->fetchColumn()) {
        $con->rollBack();
        json_out(404, ['status' => 'erro', 'msg' => 'Sessão informada não existe.']);
    }

    // Atualiza registro
    $up = $con->prepare("
    UPDATE a_site_paginas
       SET idsessaosp   = :ids,
           nomepaginasp = :nome,
           pastasp      = :pasta,
           iconesp      = :icone,
           ordemsp      = :ordem,
           visivelsp    = :vis,
           manutencaosp = :man
     WHERE codigopaginas = :id
     LIMIT 1
  ");
    $up->bindValue(':ids',   $idsessaosp,   PDO::PARAM_INT);
    $up->bindValue(':nome',  $nomepaginasp, PDO::PARAM_STR);
    $up->bindValue(':pasta', $pastasp,      PDO::PARAM_STR);
    $up->bindValue(':icone', $iconesp,      PDO::PARAM_STR);
    $up->bindValue(':ordem', $ordemsp,      PDO::PARAM_INT);
    $up->bindValue(':vis',   $visivelsp,    PDO::PARAM_INT);
    $up->bindValue(':man',   $manutencaosp, PDO::PARAM_INT);
    $up->bindValue(':id',    $codigopaginas, PDO::PARAM_INT);
    $up->execute();

    // Seleciona registro atualizado
    $sel = $con->prepare("
    SELECT codigopaginas, idsessaosp, nomepaginasp, pastasp, iconesp,
           ordemsp, visivelsp, manutencaosp
      FROM a_site_paginas
     WHERE codigopaginas = :id
     LIMIT 1
  ");
    $sel->bindValue(':id', $codigopaginas, PDO::PARAM_INT);
    $sel->execute();
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    $con->commit();

    json_out(200, [
        'status' => 'ok',
        'id'     => $codigopaginas,
        'data'   => [
            'codigopaginas' => (int)($row['codigopaginas'] ?? $codigopaginas),
            'idsessaosp'    => (int)($row['idsessaosp']    ?? $idsessaosp),
            'nomepaginasp'  => (string)($row['nomepaginasp'] ?? $nomepaginasp),
            'pastasp'       => (string)($row['pastasp']      ?? $pastasp),
            'iconesp'       => (string)($row['iconesp']      ?? $iconesp),
            'ordemsp'       => (int)($row['ordemsp']        ?? $ordemsp),
            'visivelsp'     => (int)($row['visivelsp']      ?? $visivelsp),
            'manutencaosp'  => (int)($row['manutencaosp']   ?? $manutencaosp),
            'enc'           => encrypt((string)($row['codigopaginas'] ?? $codigopaginas), 'e'),
        ]
    ]);
} catch (Throwable $e) {
    if ($con && $con->inTransaction()) $con->rollBack();
    json_out(500, [
        'status' => 'erro',
        'msg'    => 'Falha ao atualizar página.',
        'detail' => $e->getMessage()
    ]);
}
