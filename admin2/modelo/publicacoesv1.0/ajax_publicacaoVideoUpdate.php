<?php

/**
 * AJAX: Atualizar metadados do vídeo da publicação
 * Path sugerido: publicacoesv1.0/ajax_publicacaoVideoUpdate.php
 * Campos aceitos (POST):
 * - codigovideos (int) [obrigatório]
 * - totalhoras (hh:mm:ss)
 * - online (0/1)
 * - favorito_pf (0/1)
 * - idmodulocva (int)
 * - titulo (string) -> só atualiza se a coluna existir na tabela
 */
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('America/Fortaleza');

function jexit(array $arr, int $code = 200)
{
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}
function hnum($v)
{
    return is_numeric($v) ? (int)$v : 0;
}
function yn($v)
{
    return (!empty($v) && (string)$v === '1') ? 1 : 0;
}
function sanitizeDuration(?string $s): ?string
{
    if ($s === null || $s === '') return null;
    return preg_match('/^\d{2}:\d{2}:\d{2}$/', $s) ? $s : null;
}

/** Verifica se a coluna existe na tabela destino */
function columnExists(PDO $pdo, string $table, string $column): bool
{
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c";
    $st = $pdo->prepare($sql);
    $st->execute([':t' => $table, ':c' => $column]);
    return (int)$st->fetchColumn() > 0;
}

try {
    $id = hnum($_POST['codigovideos'] ?? 0);
    if ($id <= 0) {
        jexit(['sucesso' => false, 'mensagem' => 'ID do vídeo inválido.'], 400);
    }

    // Coleta e normaliza os campos opcionais
    $totalhoras  = isset($_POST['totalhoras']) ? sanitizeDuration((string)$_POST['totalhoras']) : null;
    $online      = isset($_POST['online']) ? yn($_POST['online']) : null;
    $favorito_pf = isset($_POST['favorito_pf']) ? yn($_POST['favorito_pf']) : null;
    $idmodulo    = isset($_POST['idmodulocva']) ? hnum($_POST['idmodulocva']) : null;
    $titulo      = isset($_POST['titulo']) ? trim((string)$_POST['titulo']) : null;

    // Nada para atualizar?
    if ($totalhoras === null && $online === null && $favorito_pf === null && $idmodulo === null && $titulo === null) {
        jexit(['sucesso' => false, 'mensagem' => 'Nenhum campo para atualizar.'], 400);
    }

    $pdo = config::connect();

    // Monta SET dinâmico
    $sets = [];
    $params = [':id' => $id];

    if ($totalhoras !== null) {
        $sets[] = 'totalhoras = :totalhoras';
        $params[':totalhoras'] = $totalhoras;
    }
    if ($online !== null) {
        $sets[] = 'online = :online';
        $params[':online'] = $online;
    }
    if ($favorito_pf !== null) {
        $sets[] = 'favorito_pf = :favorito_pf';
        $params[':favorito_pf'] = $favorito_pf;
    }
    if ($idmodulo !== null) {
        $sets[] = 'idmodulocva = :idmodulocva';
        $params[':idmodulocva'] = $idmodulo;
    }

    // Atualiza título apenas se a coluna existir
    $table = 'a_curso_videoaulas';
    if ($titulo !== null && columnExists($pdo, $table, 'titulo')) {
        $sets[] = 'titulo = :titulo';
        $params[':titulo'] = mb_substr($titulo, 0, 200, 'UTF-8');
    }

    if (!$sets) {
        // Caso só tenham enviado "titulo" mas a coluna não exista
        jexit(['sucesso' => false, 'mensagem' => 'Nenhum campo válido para atualizar (colunas não existentes).'], 400);
    }

    $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE codigovideos = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $rows = $stmt->rowCount();

    jexit([
        'sucesso'  => true,
        'mensagem' => $rows > 0 ? 'Atualizado com sucesso.' : 'Nada alterado (valores idênticos).',
        'afetadas' => $rows
    ], 200);
} catch (Throwable $e) {
    jexit([
        'sucesso' => false,
        'mensagem' => 'Erro inesperado ao atualizar.',
        'erro'   => $e->getMessage()
    ], 500);
}
