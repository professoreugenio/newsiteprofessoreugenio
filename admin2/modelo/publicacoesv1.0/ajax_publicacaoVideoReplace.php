<?php

/**
 * AJAX: Substituir arquivo de vídeo de uma publicação
 * Path: publicacoesv1.0/ajax_publicacaoVideoReplace.php
 * POST:
 *  - codigovideos (int) [obrigatório]
 *  - novo_video (file)  [obrigatório]
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
function slugifyBase(string $name): string
{
    $base = pathinfo($name, PATHINFO_FILENAME);
    $base = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $base);
    $base = preg_replace('/[^a-zA-Z0-9\-_\.\s]/', '', $base);
    $base = preg_replace('/\s+/', '-', $base);
    $base = preg_replace('/-+/', '-', $base);
    $base = trim($base, '-');
    return $base !== '' ? strtolower($base) : 'video';
}
function safeExt(string $name): string
{
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allow = ['mp4', 'mov', 'm4v', 'webm', 'mkv', 'avi'];
    return in_array($ext, $allow, true) ? $ext : 'mp4';
}

try {
    $id = hnum($_POST['codigovideos'] ?? 0);
    if ($id <= 0) {
        jexit(['sucesso' => false, 'mensagem' => 'ID do vídeo inválido.'], 400);
    }
    if (empty($_FILES['novo_video']) || !is_uploaded_file($_FILES['novo_video']['tmp_name'])) {
        jexit(['sucesso' => false, 'mensagem' => 'Arquivo de vídeo ausente.'], 400);
    }
    if (!empty($_FILES['novo_video']['error'])) {
        jexit(['sucesso' => false, 'mensagem' => 'Erro no upload do vídeo (código ' . $_FILES['novo_video']['error'] . ').'], 400);
    }

    $pdo = config::connect();

    // Busca registro atual para obter pasta e vídeo antigo
    $st = $pdo->prepare("SELECT pasta, video FROM a_curso_videoaulas WHERE codigovideos = :id LIMIT 1");
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        jexit(['sucesso' => false, 'mensagem' => 'Vídeo não encontrado.'], 404);
    }

    $pasta = (string)$row['pasta'];
    $oldVideo = (string)$row['video'];
    $oldBase  = pathinfo($oldVideo, PATHINFO_FILENAME);

    $file = $_FILES['novo_video'];
    $origName = (string)$file['name'];
    $ext = safeExt($origName);
    $base = slugifyBase($origName);

    // Gera novo nome único para evitar cache e colisões
    $unique = $base . '-' . substr(sha1(uniqid((string)mt_rand(), true)), 0, 8);
    $newFilename = $unique . '.' . $ext;

    $destDir = APP_ROOT . '/videos/publicacoes/' . $pasta;
    $destUrl = '/videos/publicacoes/' . $pasta;
    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            jexit(['sucesso' => false, 'mensagem' => 'Falha ao criar a pasta de destino.'], 500);
        }
    }

    $newPath = $destDir . '/' . $newFilename;

    // Move novo vídeo
    if (!move_uploaded_file($file['tmp_name'], $newPath)) {
        jexit(['sucesso' => false, 'mensagem' => 'Falha ao gravar o novo arquivo de vídeo.'], 500);
    }
    @chmod($newPath, 0664);

    // Remove vídeo antigo (se existir)
    $oldPath = $destDir . '/' . $oldVideo;
    if (is_file($oldPath)) {
        @unlink($oldPath);
    }

    // Se existir legenda .vtt com o basename antigo, renomeia para o novo basename
    $oldVtt = $destDir . '/' . $oldBase . '.vtt';
    $newVtt = $destDir . '/' . $unique . '.vtt';
    if (is_file($oldVtt)) {
        @rename($oldVtt, $newVtt);
        @chmod($newVtt, 0664);
    }

    // Atualiza banco: arquivo, extensão e size
    $sizeStr = (string) (filesize($newPath) ?: $file['size'] ?: 0);
    $up = $pdo->prepare("UPDATE a_curso_videoaulas
                         SET video = :video, ext = :ext, size = :size
                         WHERE codigovideos = :id LIMIT 1");
    $up->execute([
        ':video' => $newFilename,
        ':ext'   => $ext,
        ':size'  => $sizeStr,
        ':id'    => $id
    ]);

    jexit([
        'sucesso'  => true,
        'mensagem' => 'Vídeo substituído com sucesso.',
        'url'      => $destUrl . '/' . $newFilename
    ], 200);
} catch (Throwable $e) {
    jexit([
        'sucesso' => false,
        'mensagem' => 'Erro inesperado ao substituir o vídeo.',
        'erro'   => $e->getMessage()
    ], 500);
}
