<?php

/**
 * afiliados1.0/ajax_afiliadosProdutoUpload.php
 * Upload de imagem do produto de afiliado.
 * Espera: POST { id (encrypt ou puro), tipo: '1080x1920'|'1024x1024', arquivo: FILE }
 * Retorna JSON: { ok: true, url: 'fotos/produtosafiliados/<pasta>/i<arquivo>' }
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
    $tipo    = (string)($_POST['tipo'] ?? '');
    if ($idParam === '') {
        throw new InvalidArgumentException('ID não informado.');
    }
    $allowedTipos = ['1080x1920' => 'img1080x1920', '1024x1024' => 'img1024x1024'];
    if (!isset($allowedTipos[$tipo])) {
        throw new InvalidArgumentException('Tipo de imagem inválido.');
    }
    if (empty($_FILES['arquivo']) || !is_uploaded_file($_FILES['arquivo']['tmp_name'])) {
        throw new InvalidArgumentException('Arquivo não enviado.');
    }

    // Decodifica ID (encrypt ou puro)
    $decId = $idParam;
    if (function_exists('encrypt')) {
        try {
            $tryDec = encrypt($idParam, 'd');
            if ($tryDec && is_string($tryDec)) {
                $decId = $tryDec;
            }
        } catch (Throwable $e) { /* mantém idParam */
        }
    }
    $parts = explode('&', (string)$decId);
    $id    = (int)($parts[0] ?? $decId);
    if ($id <= 0) {
        throw new InvalidArgumentException('ID inválido.');
    }

    // Arquivo
    $f = $_FILES['arquivo'];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no upload (código ' . $f['error'] . ').');
    }
    // Limite de 12MB (ajuste conforme necessidade)
    if ($f['size'] > 12 * 1024 * 1024) {
        throw new InvalidArgumentException('Arquivo excede 12MB.');
    }

    // Validação de MIME/Extensão
    $mimeFromPHP = mime_content_type($f['tmp_name']);
    $mime = $mimeFromPHP ?: ($f['type'] ?? '');
    $ext = '';
    switch ($mime) {
        case 'image/jpeg':
        case 'image/pjpeg':
            $ext = '.jpg';
            break;
        case 'image/png':
            $ext = '.png';
            break;
        case 'image/webp':
            $ext = '.webp';
            break;
        default:
            throw new InvalidArgumentException('Formato de imagem não suportado (use JPG, PNG ou WEBP).');
    }

    // DB
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Busca pasta do produto
    $q = $con->prepare("SELECT pastaap FROM a_site_afiliados_produto WHERE codigoprodutoafiliado = :id LIMIT 1");
    $q->bindValue(':id', $id, PDO::PARAM_INT);
    $q->execute();
    $row = $q->fetch(PDO::FETCH_ASSOC);
    if (!$row || empty($row['pastaap'])) {
        throw new RuntimeException('Pasta do produto não localizada.');
    }
    $pasta = basename((string)$row['pastaap']); // segurança

    // Diretórios (FS) e caminhos (URL)
    $dirFS  = APP_ROOT . '/fotos/produtosafiliados/' . $pasta . '/';
    if (!is_dir($dirFS) && !@mkdir($dirFS, 0775, true)) {
        throw new RuntimeException('Não foi possível criar o diretório de imagens.');
    }

    $baseName = $allowedTipos[$tipo]; // img1080x1920 | img1024x1024
    $fileFS   = $dirFS . '/' . $baseName . $ext;
    // depois (caminho root-relative)
    $urlRel   = '/fotos/produtosafiliados/' . $pasta . '/' . $baseName . $ext;
    



    // Remove arquivo anterior se extensão diferente (limpeza)
    foreach (['.jpg', '.png', '.webp'] as $e) {
        $cand = $dirFS . '/' . $baseName . $e;
        if ($e !== $ext && is_file($cand)) {
            @unlink($cand);
        }
    }
    if (is_file($fileFS)) {
        @unlink($fileFS);
    }
    
    // Move o arquivo
    if (!@move_uploaded_file($f['tmp_name'], $fileFS)) {
        throw new RuntimeException('Falha ao mover o arquivo para o destino.');
    }

    // Proteção básica de permissões (opcional)
    @chmod($fileFS, 0644);

    // Atualiza coluna correspondente
    $coluna = $baseName; // mesmo nome no BD (img1080x1920 | img1024x1024)
    $sql = "UPDATE a_site_afiliados_produto SET {$coluna} = :url WHERE codigoprodutoafiliado = :id LIMIT 1";
    $up = $con->prepare($sql);
    $up->bindValue(':url', $urlRel, PDO::PARAM_STR);
    $up->bindValue(':id',  $id, PDO::PARAM_INT);
    $up->execute();

    $response['ok']  = true;
    $response['url'] = $urlRel;
    json_out($response, 200);
} catch (Throwable $e) {
    $code = ($e instanceof InvalidArgumentException) ? 400 : 500;
    $response['ok']  = false;
    $response['msg'] = $e->getMessage();
    json_out($response, $code);
}
