<?php

/**
 * modulosv1.0/ajax_moduloUpdate.php
 * Atualiza os dados de um módulo existente.
 * Campo renomeado: 'nomemodulosm' → 'nomemodulo'
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

$out = ['success' => false, 'message' => 'Erro desconhecido.'];

try {
    // Garante conexão ($con)
    if (!isset($con) || !$con instanceof PDO) {
        if (class_exists('config') && method_exists('config', 'connect')) {
            $con = config::connect();
        }
    }
    if (!$con instanceof PDO) {
        throw new Exception('Conexão indisponível.');
    }

    // Aceita apenas POST
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new Exception('Método inválido.');
    }

    // --- Helpers ---
    $toInt = static fn($v) => (int)(is_numeric($v) ? $v : 0);
    $toStr = static fn($v) => trim((string)$v);
    $toNull = static fn($v) => ($v === '' ? null : $v);
    $moneyToFloat = static function ($v) {
        $v = trim((string)$v);
        if ($v === '') return null;
        $v = str_replace(['.', ' '], '', $v);
        $v = str_replace(',', '.', $v);
        return (float)$v;
    };

    // --- Coleta dos campos ---
    $idModulo      = $toInt($_POST['codigomodulos'] ?? 0);
    $codcursos     = $toInt($_POST['codcursos'] ?? 0);
    $nomemodulo    = $toStr($_POST['nomemodulo'] ?? '');
    $descricao     = $toStr($_POST['descricao'] ?? '');
    $bgcolorsm     = substr($toStr($_POST['bgcolorsm'] ?? ''), 0, 10);
    $imagem        = $toStr($_POST['imagem'] ?? 'padrao.jpg');
    $ordemm        = $toInt($_POST['ordemm'] ?? 0);
    $visivelm      = ($_POST['visivelm'] ?? '0') === '1' ? '1' : '0';
    $visivelhome   = $toInt($_POST['visivelhome'] ?? 0);

    // --- Validações ---
    if ($idModulo <= 0) {
        throw new Exception('ID do módulo inválido.');
    }
    if ($codcursos <= 0) {
        throw new Exception('Selecione um curso válido.');
    }
    if ($nomemodulo === '') {
        throw new Exception('Informe o nome do módulo.');
    }

    // --- Atualização ---
    $sql = "
        UPDATE new_sistema_modulos_PJA
        SET 
            codcursos     = :codcursos,
            nomemodulo    = :nomemodulo,
            descricao     = :descricao,
            bgcolorsm     = :bgcolorsm,
            imagem        = :imagem,
            ordemm        = :ordemm,
            visivelm      = :visivelm,
            visivelhome   = :visivelhome
        WHERE codigomodulos = :id
        LIMIT 1
    ";

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':codcursos',   $codcursos, PDO::PARAM_INT);
    $stmt->bindValue(':nomemodulo',  $nomemodulo, PDO::PARAM_STR);
    $stmt->bindValue(':descricao',   $toNull($descricao), $descricao === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':bgcolorsm',   $toNull($bgcolorsm), $bgcolorsm === '' ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':imagem',      $imagem, PDO::PARAM_STR);
    $stmt->bindValue(':ordemm',      $ordemm, PDO::PARAM_INT);
    $stmt->bindValue(':visivelm',    $visivelm, PDO::PARAM_STR);
    $stmt->bindValue(':visivelhome', $visivelhome, PDO::PARAM_INT);
    $stmt->bindValue(':id',          $idModulo, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        $err = $stmt->errorInfo();
        throw new Exception('Falha ao atualizar módulo. ' . ($err[2] ?? ''));
    }

    $out['success'] = true;
    $out['message'] = 'Módulo atualizado com sucesso!';
    echo json_encode($out);
    exit;
} catch (Throwable $e) {
    $out['success'] = false;
    $out['message'] = $e->getMessage();
    echo json_encode($out);
    exit;
}
