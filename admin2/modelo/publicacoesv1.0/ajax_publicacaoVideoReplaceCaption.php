<?php

/**
 * AJAX: Substituir legenda (caption) de um vídeo de publicação
 * Path: publicacoesv1.0/ajax_publicacaoVideoReplaceCaption.php
 * POST:
 *  - codigovideos (int)  [obrigatório]
 *  - legenda_vtt  (file) [obrigatório] — aceita .vtt ou .srt (conversão para .vtt)
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

/** Verifica se a coluna existe na tabela */
function columnExists(PDO $pdo, string $table, string $column): bool
{
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c";
    $st = $pdo->prepare($sql);
    $st->execute([':t' => $table, ':c' => $column]);
    return (int)$st->fetchColumn() > 0;
}

/** Conversor simples de SRT para conteúdo VTT */
function srtToVtt(string $srt): string
{
    // Normaliza EOL, adiciona cabeçalho WEBVTT e troca vírgulas por pontos nos timestamps
    $txt = str_replace("\r", "", $srt);
    // Substitui "HH:MM:SS,mmm" por "HH:MM:SS.mmm"
    $txt = preg_replace('/(\d{2}:\d{2}:\d{2}),(\d{3})/', '$1.$2', $txt);
    // Garante uma linha em branco entre blocos (robustez)
    // (opcional) $txt = preg_replace("/\n{3,}/", "\n\n", $txt);
    return "WEBVTT\n\n" . $txt;
}

try {
    $id = hnum($_POST['codigovideos'] ?? 0);
    if ($id <= 0) {
        jexit(['sucesso' => false, 'mensagem' => 'ID do vídeo inválido.'], 400);
    }

    if (empty($_FILES['legenda_vtt']) || !is_uploaded_file($_FILES['legenda_vtt']['tmp_name'])) {
        jexit(['sucesso' => false, 'mensagem' => 'Arquivo de legenda ausente.'], 400);
    }
    if (!empty($_FILES['legenda_vtt']['error'])) {
        jexit(['sucesso' => false, 'mensagem' => 'Erro no upload da legenda (código ' . $_FILES['legenda_vtt']['error'] . ').'], 400);
    }

    $pdo = config::connect();

    // Busca pasta e nome do vídeo para montar o basename
    $st = $pdo->prepare("SELECT pasta, video FROM a_curso_videoaulas WHERE codigovideos = :id LIMIT 1");
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        jexit(['sucesso' => false, 'mensagem' => 'Vídeo não encontrado.'], 404);
    }

    $pasta    = (string)$row['pasta'];
    $video    = (string)$row['video'];
    $basename = pathinfo($video, PATHINFO_FILENAME); // legenda deve seguir o basename do vídeo
    if ($basename === '') $basename = 'video';

    $destDir = APP_ROOT . '/videos/publicacoes/' . $pasta;
    $destUrl = '/videos/publicacoes/' . $pasta;
    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            jexit(['sucesso' => false, 'mensagem' => 'Falha ao criar a pasta de destino.'], 500);
        }
    }

    $upload = $_FILES['legenda_vtt'];
    $origName = (string)$upload['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    $finalPath = $destDir . '/' . $basename . '.vtt';
    $finalUrl  = $destUrl . '/' . $basename . '.vtt';

    // Se já existir uma legenda, apaga antes (opcional)
    if (is_file($finalPath)) {
        @unlink($finalPath);
    }

    // Se for SRT, converte; se for VTT, apenas move
    if ($ext === 'srt') {
        $srt = file_get_contents($upload['tmp_name']);
        if ($srt === false) {
            jexit(['sucesso' => false, 'mensagem' => 'Falha ao ler o arquivo SRT.'], 500);
        }
        $vtt = srtToVtt($srt);
        if (file_put_contents($finalPath, $vtt) === false) {
            jexit(['sucesso' => false, 'mensagem' => 'Falha ao gravar a legenda VTT convertida.'], 500);
        }
        @chmod($finalPath, 0664);
    } elseif ($ext === 'vtt') {
        if (!move_uploaded_file($upload['tmp_name'], $finalPath)) {
            jexit(['sucesso' => false, 'mensagem' => 'Falha ao gravar a legenda VTT.'], 500);
        }
        @chmod($finalPath, 0664);
    } else {
        // Alguns navegadores podem enviar sem extensão correta; tenta content-type
        $ctype = strtolower((string)($upload['type'] ?? ''));
        if (str_contains($ctype, 'srt')) {
            $srt = file_get_contents($upload['tmp_name']);
            if ($srt === false) {
                jexit(['sucesso' => false, 'mensagem' => 'Falha ao ler a legenda (SRT).'], 500);
            }
            $vtt = srtToVtt($srt);
            if (file_put_contents($finalPath, $vtt) === false) {
                jexit(['sucesso' => false, 'mensagem' => 'Falha ao gravar a legenda VTT convertida.'], 500);
            }
            @chmod($finalPath, 0664);
        } elseif (str_contains($ctype, 'vtt') || str_contains($ctype, 'text/plain')) {
            if (!move_uploaded_file($upload['tmp_name'], $finalPath)) {
                jexit(['sucesso' => false, 'mensagem' => 'Falha ao gravar a legenda VTT.'], 500);
            }
            @chmod($finalPath, 0664);
        } else {
            jexit(['sucesso' => false, 'mensagem' => 'Formato de legenda não suportado. Envie .srt ou .vtt.'], 400);
        }
    }

    // Atualiza coluna de legenda se existir (opcional)
    $table = 'a_curso_videoaulas';
    if (columnExists($pdo, $table, 'legendavtt')) {
        $up = $pdo->prepare("UPDATE {$table} SET legendavtt = :legendavtt WHERE codigovideos = :id LIMIT 1");
        $up->execute([
            ':legendavtt' => $basename . '.vtt',
            ':id'         => $id
        ]);
    }

    jexit([
        'sucesso'  => true,
        'mensagem' => 'Legenda atualizada com sucesso.',
        'url'      => $finalUrl
    ], 200);
} catch (Throwable $e) {
    jexit([
        'sucesso' => false,
        'mensagem' => 'Erro inesperado ao substituir a legenda.',
        'erro'   => $e->getMessage()
    ], 500);
}
