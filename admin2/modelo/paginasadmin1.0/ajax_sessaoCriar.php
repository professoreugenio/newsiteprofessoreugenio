<?php

/**
 * ajax_sessaoCriar.php
 * Cria uma nova sessão em a_site_sessao
 * Entrada (JSON ou form-data):
 *  - nomesessao  (string, máx 20)  [obrigatório]
 *  - iconess     (string, máx 20)  [opcional]
 *  - visivelss   (0|1)             [opcional, default 1]
 * Saída (JSON):
 *  { status: "ok", id: <int>, data: { codigosessao, ordemss, nomesessao, iconess, visivelss } }
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

// Aceita JSON no corpo ou form-data
$raw = file_get_contents('php://input');
$in  = null;
if ($raw) {
    $tmp = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
        $in = $tmp;
    }
}
if (!$in) {
    // fallback para POST comum
    $in = $_POST;
}

// Sanitização e defaults
$nomesessao = isset($in['nomesessao']) ? (string)$in['nomesessao'] : '';
$iconess    = isset($in['iconess'])    ? (string)$in['iconess']    : '';
$visivelss  = isset($in['visivelss'])  ? (string)$in['visivelss']  : '1';

// Normalizações
$nomesessao = trim(strip_tags($nomesessao));
$iconess    = trim(strip_tags($iconess));
$visivelss  = ($visivelss === '1' || $visivelss === 1 || $visivelss === true) ? 1 : 0;

// Limites de tamanho
if (function_exists('mb_substr')) {
    $nomesessao = mb_substr($nomesessao, 0, 20, 'UTF-8');
    $iconess    = mb_substr($iconess, 0, 20, 'UTF-8');
} else {
    $nomesessao = substr($nomesessao, 0, 20);
    $iconess    = substr($iconess, 0, 20);
}

// Validação
$erros = [];
if ($nomesessao === '') {
    $erros[] = 'Informe o nome da sessão.';
}
// if (!empty($iconess)) {
//     // Aceita letras, números, hífen e sublinhado (ex.: bi-folder, bi-speedometer2)
//     if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $iconess)) {
//         $erros[] = 'Ícone inválido. Use apenas letras, números, hífen e underline (ex.: bi-folder).';
//     }
// }
if (!empty($erros)) {
    json_out(422, ['status' => 'erro', 'erros' => $erros]);
}

try {
    if (!$con || !($con instanceof PDO)) {
        json_out(500, ['status' => 'erro', 'msg' => 'Conexão indisponível.']);
    }

    $con->beginTransaction();

    // Calcula próxima ordem
    $proxStmt = $con->query("SELECT COALESCE(MAX(ordemss), 0) + 1 AS prox FROM a_site_sessao");
    $prox = (int)($proxStmt ? ($proxStmt->fetchColumn() ?: 1) : 1);

    // Insert
    $ins = $con->prepare("
        INSERT INTO a_site_sessao (ordemss, nomesessao, iconess, visivelss)
        VALUES (:ordemss, :nomesessao, :iconess, :visivelss)
    ");
    $ins->bindValue(':ordemss',   $prox,       PDO::PARAM_INT);
    $ins->bindValue(':nomesessao', $nomesessao, PDO::PARAM_STR);
    $ins->bindValue(':iconess',   $iconess,    PDO::PARAM_STR);
    $ins->bindValue(':visivelss', $visivelss,  PDO::PARAM_INT);
    $ins->execute();

    $id = (int)$con->lastInsertId();

    // Carrega o registro inserido para devolver ao frontend
    $sel = $con->prepare("
        SELECT codigosessao, ordemss, nomesessao, iconess, visivelss
        FROM a_site_sessao
        WHERE codigosessao = :id
        LIMIT 1
    ");
    $sel->bindValue(':id', $id, PDO::PARAM_INT);
    $sel->execute();
    $row = $sel->fetch(PDO::FETCH_ASSOC);

    $con->commit();

    json_out(200, [
        'status' => 'ok',
        'id'     => $id,
        'data'   => [
            'codigosessao' => (int)($row['codigosessao'] ?? $id),
            'ordemss'      => (int)($row['ordemss']      ?? $prox),
            'nomesessao'   => (string)($row['nomesessao'] ?? $nomesessao),
            'iconess'      => (string)($row['iconess']    ?? $iconess),
            'visivelss'    => (int)($row['visivelss']    ?? $visivelss),
        ]
    ]);
} catch (Throwable $e) {
    if ($con && $con->inTransaction()) {
        $con->rollBack();
    }
    json_out(500, [
        'status' => 'erro',
        'msg'    => 'Falha ao criar sessão.',
        'detail' => $e->getMessage()
    ]);
}
