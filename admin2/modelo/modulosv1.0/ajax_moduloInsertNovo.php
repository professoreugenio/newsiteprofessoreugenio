<?php

/**
 * modulosv1.0/ajax_moduloInsertNovo.php
 * Insere um novo módulo em new_sistema_modulos_PJA.
 * Retorna JSON.
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

    // Helpers
    $asInt = static fn($v) => (int)(is_numeric($v) ? $v : 0);
    $asNullOrStr = static function ($v) {
        $v = trim((string)$v);
        return ($v === '') ? null : $v;
    };
    // "000.000,00" -> 000000.00
    $moneyToFloat = static function ($v) {
        $v = trim((string)$v);
        if ($v === '') return null;
        $v = str_replace(['.', ' '], '', $v);
        $v = str_replace(',', '.', $v);
        return (float)$v;
    };

    // Coleta dos campos
    $codcursos       = $asInt($_POST['codcursos'] ?? 0);
    $nomemodulosm    = trim((string)($_POST['nomemodulosm'] ?? ''));
    $bgcolorsm       = substr(trim((string)($_POST['bgcolorsm'] ?? '')), 0, 10);
    $imagem          = trim((string)($_POST['imagem'] ?? 'padrao.jpg'));
    $descricao       = $asNullOrStr($_POST['descricao'] ?? null);
    $valordocursosm  = $moneyToFloat($_POST['valordocursosm'] ?? '');
    $valordahorasm   = $moneyToFloat($_POST['valordahorasm'] ?? '');
    $cargahorariasm  = ($tmp = $_POST['cargahorariasm'] ?? '') === '' ? null : $asInt($tmp);
    $ordemm          = ($tmp = $_POST['ordemm'] ?? '') === '' ? null : $asInt($tmp);
    $visivelm        = ((int)($_POST['visivelm'] ?? 0)) ? '1' : '0'; // varchar(1)
    $visivelhome     = $asInt($_POST['visivelhome'] ?? 0);           // int(11)
    $datam           = $asNullOrStr($_POST['datam'] ?? null);        // YYYY-MM-DD
    $horam           = $asNullOrStr($_POST['horam'] ?? null);        // HH:MM

    // Validações mínimas
    if ($codcursos <= 0) {
        throw new Exception('Selecione um curso válido.');
    }
    if ($nomemodulosm === '') {
        throw new Exception('Informe o nome do módulo.');
    }
    if ($imagem === '') {
        $imagem = 'padrao.jpg';
    }

    // SQL de inserção
    $sql = "
        INSERT INTO new_sistema_modulos_PJA
            (codcursos, modulo, bgcolorsm, imagem, descricao,
             valordocursosm, valordahorasm, cargahorariasm, ordem m,
             visivelm, visivelhome, datam, horam)
        VALUES
            (:codcursos, :nomemodulosm, :bgcolorsm, :imagem, :descricao,
             :valordocursosm, :valordahorasm, :cargahorariasm, :ordemm,
             :visivelm, :visivelhome, :datam, :horam)
    ";
    // Garantir nome correto da coluna "ordemm"
    $sql = str_replace('ordem m', 'ordemm', $sql);

    $stmt = $con->prepare($sql);
    $stmt->bindValue(':codcursos',       $codcursos, PDO::PARAM_INT);
    $stmt->bindValue(':nomemodulosm',    $nomemodulosm, PDO::PARAM_STR);
    $stmt->bindValue(':bgcolorsm',       $bgcolorsm !== '' ? $bgcolorsm : null, $bgcolorsm !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':imagem',          $imagem, PDO::PARAM_STR);
    $stmt->bindValue(':descricao',       $descricao, $descricao === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    // Para floats, use PARAM_STR para evitar issues de locale
    $stmt->bindValue(':valordocursosm',  $valordocursosm, $valordocursosm === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':valordahorasm',   $valordahorasm,  $valordahorasm  === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':cargahorariasm',  $cargahorariasm, $cargahorariasm === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':ordemm',          $ordemm,         $ordemm         === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindValue(':visivelm',        $visivelm, PDO::PARAM_STR);
    $stmt->bindValue(':visivelhome',     $visivelhome, PDO::PARAM_INT);
    $stmt->bindValue(':datam',           $datam, $datam === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':horam',           $horam, $horam === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

    if (!$stmt->execute()) {
        $err = $stmt->errorInfo();
        throw new Exception('Falha ao inserir. ' . ($err[2] ?? ''));
    }

    $out['success'] = true;
    $out['message'] = 'Módulo criado com sucesso!';
    echo json_encode($out);
    exit;
} catch (Throwable $e) {
    $out['success'] = false;
    $out['message'] = $e->getMessage();
    echo json_encode($out);
    exit;
}
