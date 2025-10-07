<?php

/**
 * afiliados1.0/ajax_afiliadosProdutoDeleteImage.php
 * Exclui a imagem (1080x1920 ou 1024x1024) do produto e zera a coluna no BD.
 * Espera: POST { id (encrypt ou puro), tipo: '1080x1920'|'1024x1024' }
 * Retorna: { ok:true }
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$response = ['ok' => false, 'msg' => ''];

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Auth por COOKIE
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = function_exists('encrypt') ? encrypt($_COOKIE['adminstart'], 'd') : $_COOKIE['adminstart'];
    } elseif (!empty($_COOKIE['startusuario'])) {
        $decUser = function_exists('encrypt') ? encrypt($_COOKIE['startusuario'], 'd') : $_COOKIE['startusuario'];
    } else {
        throw new Exception('Usuário não autenticado (cookies ausentes).');
    }
    if (!$decUser || strpos((string)$decUser, '&') === false) {
        throw new Exception('Token de usuário inválido.');
    }

    // Entrada
    $idParam = (string)($_POST['id'] ?? '');
    $tipo    = (string)($_POST['tipo'] ?? '');
    $allowed = ['1080x1920' => 'img1080x1920', '1024x1024' => 'img1024x1024'];
    if ($idParam === '') throw new InvalidArgumentException('ID não informado.');
    if (!isset($allowed[$tipo])) throw new InvalidArgumentException('Tipo inválido.');

    // Decodifica ID
    $decId = $idParam;
    if (function_exists('encrypt')) {
        try {
            $tryDec = encrypt($idParam, 'd');
            if ($tryDec && is_string($tryDec)) $decId = $tryDec;
        } catch (Throwable $e) {
        }
    }
    $parts = explode('&', (string)$decId);
    $id = (int)($parts[0] ?? $decId);
    if ($id <= 0) throw new InvalidArgumentException('ID inválido.');

    $col = $allowed[$tipo];

    // DB
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca pasta e caminho atual
    $q = $con->prepare("SELECT pastaap, {$col} AS img FROM a_site_afiliados_produto WHERE codigoprodutoafiliado = :id LIMIT 1");
    $q->bindValue(':id', $id, PDO::PARAM_INT);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new RuntimeException('Produto não encontrado.');

    $pasta = basename((string)$row['pastaap']);
    $imgUrl = (string)($row['img'] ?? '');

    // Exclui arquivos possíveis (jpg/png/webp)
    $dirFS = APP_ROOT . '/fotos/produtosafiliados/' . $pasta ;
    foreach (['.jpg', '.png', '.webp'] as $ext) {
        $cand = $dirFS . '/' . $col . $ext;
        if (is_file($cand)) @unlink($cand);
    }

    // Zera coluna
    $u = $con->prepare("UPDATE a_site_afiliados_produto SET {$col} = NULL WHERE codigoprodutoafiliado = :id LIMIT 1");
    $u->bindValue(':id', $id, PDO::PARAM_INT);
    $u->execute();

    $response['ok'] = true;
    json_out($response, 200);
} catch (Throwable $e) {
    $code = ($e instanceof InvalidArgumentException) ? 400 : 500;
    $response['ok'] = false;
    $response['msg'] = $e->getMessage();
    json_out($response, $code);
}
