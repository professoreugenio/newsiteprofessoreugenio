<?php

/**
 * afiliados1.0/ajax_afiliadosProdutoDelete.php
 * Exclui definitivamente o produto e TODAS as imagens/pastas associadas.
 * Espera: POST { id (encrypt ou puro) }
 * Retorna: { ok:true, redirect: "afiliados1.0/sistema_afiliadosProdutos.php" }
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=UTF-8');

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Remove diretório recursivamente com segurança
function rrmdir_safe(string $absTarget, string $absBase): bool
{
    $realBase   = realpath($absBase);
    $realTarget = realpath($absTarget);
    if ($realBase === false) return false;
    // Se diretório não existe, considere ok
    if ($realTarget === false) return true;
    // Garante que o alvo está dentro do base
    if (strpos($realTarget, $realBase) !== 0) return false;

    $it = new RecursiveDirectoryIterator($realTarget, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
        /** @var SplFileInfo $file */
        if ($file->isDir()) {
            @rmdir($file->getRealPath());
        } else {
            @unlink($file->getRealPath());
        }
    }
    return @rmdir($realTarget);
}

try {
    // ===== Autenticação por COOKIE (sem SESSION) =====
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
    $expUser = explode('&', (string)$decUser);
    $idUser  = (int)($expUser[0] ?? 0);
    if ($idUser <= 0) {
        throw new Exception('Usuário inválido.');
    }

    // ===== Entrada =====
    $idParam = (string)($_POST['id'] ?? '');
    if ($idParam === '') {
        throw new InvalidArgumentException('ID não informado.');
    }

    // Decodifica ID (encrypt ou puro)
    $decId = $idParam;
    if (function_exists('encrypt')) {
        try {
            $tryDec = encrypt($idParam, 'd');
            if ($tryDec && is_string($tryDec)) $decId = $tryDec;
        } catch (Throwable $e) {
        }
    }
    $parts = explode('&', (string)$decId);
    $id    = (int)($parts[0] ?? $decId);
    if ($id <= 0) {
        throw new InvalidArgumentException('ID inválido.');
    }

    // ===== DB =====
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca pasta do produto
    $q = $con->prepare("SELECT pastaap FROM a_site_afiliados_produto WHERE codigoprodutoafiliado = :id LIMIT 1");
    $q->bindValue(':id', $id, PDO::PARAM_INT);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new RuntimeException('Produto não encontrado.');
    }
    $pasta = basename((string)$row['pastaap']); // segurança

    // Caminhos
    $baseFotos = APP_ROOT . '/fotos/produtosafiliados';
    $dirProduto = $baseFotos . '/' . $pasta;

    // Remove pasta com todas as imagens/arquivos (se existir)
    rrmdir_safe($dirProduto, $baseFotos);

    // Exclui o registro do produto
    $del = $con->prepare("DELETE FROM a_site_afiliados_produto WHERE codigoprodutoafiliado = :id LIMIT 1");
    $del->bindValue(':id', $id, PDO::PARAM_INT);
    $del->execute();

    json_out([
        'ok' => true,
        'redirect' => 'sistema_afiliadosProdutos.php'
    ], 200);
} catch (Throwable $e) {
    $code = ($e instanceof InvalidArgumentException) ? 400 : 500;
    json_out(['ok' => false, 'msg' => $e->getMessage()], $code);
}
