<?php

/**
 * turmas1.0/ajax_cadastrarTurma.php
 * Cadastra nova turma (new_sistema_cursos_turmas).
 * Sempre responde JSON. Em erro, inclui "debug" quando houver ruído de output.
 */

declare(strict_types=1);

// Garante JSON SEM ruído
header('Content-Type: application/json; charset=UTF-8');
ob_start();
ini_set('display_errors', '0'); // evitar quebrar o JSON por notices/warnings

$respond = function (array $payload, int $http = 200) {
    http_response_code($http);
    // anexa qualquer saída capturada (warnings/echos acidentais)
    $noise = ob_get_clean();
    if ($noise !== '') {
        $payload['debug'] = trim($noise);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
};

try {
    // ===== Cabeçalho/Includes padrão =====
    if (!defined('BASEPATH')) define('BASEPATH', true);
    if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__, 3));
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    date_default_timezone_set('America/Fortaleza');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $respond(['ok' => false, 'msg' => 'Método não permitido.'], 405);
    }

    // Conexão ($con)
    if (!isset($con) || !$con) {
        if (class_exists('config') && method_exists('config', 'connect')) {
            $con = config::connect();
        }
    }
    if (!$con) {
        $respond(['ok' => false, 'msg' => 'Conexão indisponível.']);
    }
    if ($con instanceof PDO) {
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Helper POST
    $post = function (string $k, $def = null) {
        return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $def;
    };

    // ===== Captura =====
    $codcursost    = $post('codcursost', '');
    $ordemct       = $post('ordemct', '1');
    $nometurma     = $post('nometurma', '');
    $nomeprofessor = $post('nomeprofessor', '');
    $linkwhatsapp  = $post('linkwhatsapp', '#');
    $datainiciost  = $post('datainiciost', null);
    $datafimst     = $post('datafimst', null);
    $horainiciost  = $post('horainiciost', null);
    $horafimst     = $post('horafimst', null);
    $ano_turma     = $post('ano_turma', '');
    $texto         = $post('texto', '');
    $chave         = $post('chave', '');

    // Flags
    $visivelst     = (int)($post('visivelst', '0') === '1');
    $comercialt    = (int)($post('comercialt', '0') === '1');
    $institucional = (int)($post('institucional', '0') === '1');

    // ===== Validações mínimas =====
    if ($codcursost === '' || $nometurma === '' || $nomeprofessor === '') {
        $respond(['ok' => false, 'msg' => 'Preencha Curso, Nome da Turma e Nome do Professor.']);
    }

    // Normalizações
    $ordemct = (int)($ordemct !== '' ? $ordemct : 1);
    if ($ordemct < 1) $ordemct = 1;

    if ($ano_turma === '' || !preg_match('/^\d{4}$/', $ano_turma)) {
        if ($datainiciost && preg_match('/^\d{4}-\d{2}-\d{2}$/', $datainiciost)) {
            $ano_turma = substr($datainiciost, 0, 4);
        } else {
            $ano_turma = date('Y');
        }
    }
    $ano_turma = (int)$ano_turma;

    if ($horafimst)  $horafimst = substr($horafimst, 0, 5);
    if ($linkwhatsapp === '') $linkwhatsapp = '#';
    if ($chave === '' || !preg_match('/^\d{8}\d+$/', $chave)) {
        $chave = date('Ymd') . time();
    }
    $datast = date('Y-m-d');
    $horast = date('H:i:s');

    // ===== INSERT =====
    $sql = "INSERT INTO new_sistema_cursos_turmas
            (
              codcursost, ordemct, nometurma, nomeprofessor,
              texto, linkwhatsapp,
              datainiciost, datafimst, horainiciost, horafimst,
              chave, datast, horast, ano_turma,
              visivelst, comercialt, institucional
            )
            VALUES
            (
              :codcursost, :ordemct, :nometurma, :nomeprofessor,
              :texto, :linkwhatsapp,
              :datainiciost, :datafimst, :horainiciost, :horafimst,
              :chave, :datast, :horast, :ano_turma,
              :visivelst, :comercialt, :institucional
            )";

    if (!($con instanceof PDO)) {
        $respond(['ok' => false, 'msg' => 'Driver de conexão não suportado.']);
    }

    $stmt = $con->prepare($sql);

    // Binds
    $stmt->bindValue(':codcursost',    (int)$codcursost, PDO::PARAM_INT);
    $stmt->bindValue(':ordemct',       $ordemct, PDO::PARAM_INT);
    $stmt->bindValue(':nometurma',     $nometurma, PDO::PARAM_STR);
    $stmt->bindValue(':nomeprofessor', $nomeprofessor, PDO::PARAM_STR);
    $stmt->bindValue(':texto',         $texto, PDO::PARAM_STR);
    $stmt->bindValue(':linkwhatsapp',  $linkwhatsapp, PDO::PARAM_STR);

    $stmt->bindValue(':datainiciost',  $datainiciost ?: null, $datainiciost ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':datafimst',     $datafimst ?: null,    $datafimst ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':horainiciost',  $horainiciost ?: null, $horainiciost ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':horafimst',     $horafimst ?: null,    $horafimst ? PDO::PARAM_STR : PDO::PARAM_NULL);

    $stmt->bindValue(':chave',         $chave, PDO::PARAM_STR);
    $stmt->bindValue(':datast',        $datast, PDO::PARAM_STR);
    $stmt->bindValue(':horast',        $horast, PDO::PARAM_STR);
    $stmt->bindValue(':ano_turma',     $ano_turma, PDO::PARAM_INT);

    $stmt->bindValue(':visivelst',     $visivelst, PDO::PARAM_INT);
    $stmt->bindValue(':comercialt',    $comercialt, PDO::PARAM_INT);
    $stmt->bindValue(':institucional', $institucional, PDO::PARAM_INT);

    try {
        $stmt->execute();
    } catch (Throwable $ex) {
        // Erro do SQL com detalhe
        $err = $stmt->errorInfo();
        $respond([
            'ok'  => false,
            'msg' => 'Falha ao inserir a turma.',
            'sql_error' => [
                'code'    => $err[0] ?? null,
                'driver'  => $err[1] ?? null,
                'message' => $err[2] ?? $ex->getMessage(),
            ],
        ], 200);
    }

    $idTurma = (int)$con->lastInsertId();
    if (!$idTurma) {
        $respond(['ok' => false, 'msg' => 'Falha ao obter ID da turma.']);
    }

    $enc_tm = function_exists('encrypt') ? encrypt($idTurma) : (string)$idTurma;

    $respond([
        'ok'      => true,
        'idturma' => $idTurma,
        'enc_tm'  => $enc_tm,
    ]);
} catch (Throwable $e) {
    $respond([
        'ok'  => false,
        'msg' => 'Erro interno ao cadastrar a turma.',
        'err' => $e->getMessage(),
    ], 500);
}
