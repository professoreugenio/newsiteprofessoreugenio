<?php

/**
 * AJAX: Inserir vídeo da publicação
 * Path sugerido: publicacoesv1.0/ajax_publicacaoVideoInsert.php
 * Requer: headers padrão AJAX (item 29)
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
function sanitizeDuration(string $s): string
{
    // aceita hh:mm:ss — normaliza para 00:00:00 se inválido
    return preg_match('/^\d{2}:\d{2}:\d{2}$/', $s) ? $s : '00:00:00';
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
    // whitelist básica para vídeo; ajuste se precisar
    $allow = ['mp4', 'mov', 'm4v', 'webm', 'mkv', 'avi'];
    return in_array($ext, $allow, true) ? $ext : 'mp4';
}
function monthAbbrPT(int $m): string
{
    $arr = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];
    return $arr[max(0, min(11, $m - 1))];
}
function gerarPastaPadrao(): string
{
    $d = new DateTime();
    $stamp = $d->format('Ymd-His');
    return monthAbbrPT((int)$d->format('m')) . '_' . $stamp;
}

try {
    // --- Validações básicas de entrada
    $idpublicacaocva = hnum($_POST['idpublicacaocva'] ?? 0);
    if ($idpublicacaocva <= 0) {
        jexit(['sucesso' => false, 'mensagem' => 'Publicação inválida.'], 400);
    }

    $idmodulocva  = hnum($_POST['idmodulocva'] ?? 0);
    $totalhoras   = sanitizeDuration((string)($_POST['totalhoras'] ?? '00:00:00'));
    $online       = yn($_POST['online'] ?? 0);
    $favorito_pf  = yn($_POST['favorito_pf'] ?? 0);
    $pasta        = trim((string)($_POST['pasta'] ?? ''));

    if ($pasta === '') {
        $pasta = gerarPastaPadrao();
    }

    if (empty($_FILES['arquivo_video']) || !is_uploaded_file($_FILES['arquivo_video']['tmp_name'])) {
        jexit(['sucesso' => false, 'mensagem' => 'Arquivo de vídeo ausente.'], 400);
    }

    $videoFile = $_FILES['arquivo_video'];
    if (!empty($videoFile['error'])) {
        jexit(['sucesso' => false, 'mensagem' => 'Erro no upload do vídeo (código ' . $videoFile['error'] . ').'], 400);
    }

    $origName = (string)$videoFile['name'];
    $ext      = safeExt($origName);
    $base     = slugifyBase($origName);

    // Garante unicidade do nome
    $unique   = $base . '-' . substr(sha1(uniqid((string)mt_rand(), true)), 0, 8);
    $finalFilename = $unique . '.' . $ext;

    // Diretório de destino (FS) e URL pública
    $destDir = APP_ROOT . '/videos/publicacoes/' . $pasta;
    $destUrl = '/videos/publicacoes/' . $pasta;

    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0775, true) && !is_dir($destDir)) {
            jexit(['sucesso' => false, 'mensagem' => 'Falha ao criar a pasta de destino.'], 500);
        }
    }

    $destPath = $destDir . '/' . $finalFilename;

    // Move o vídeo
    if (!move_uploaded_file($videoFile['tmp_name'], $destPath)) {
        jexit(['sucesso' => false, 'mensagem' => 'Falha ao gravar o arquivo de vídeo.'], 500);
    }

    // Tenta preservar permissões razoáveis
    @chmod($destPath, 0664);

    // Tamanho do arquivo (em bytes) convertido para string (a coluna é varchar)
    $sizeStr = (string) (filesize($destPath) ?: $videoFile['size'] ?: 0);

    // Salva legenda VTT (opcional)
    if (!empty($_FILES['legenda_vtt']) && is_uploaded_file($_FILES['legenda_vtt']['tmp_name'])) {
        $cap = $_FILES['legenda_vtt'];
        if (empty($cap['error'])) {
            // Usa o basename do vídeo para a legenda
            $capName = $unique . '.vtt';
            $capPath = $destDir . '/' . $capName;
            if (!move_uploaded_file($cap['tmp_name'], $capPath)) {
                // falha de legenda não deve abortar o vídeo já salvo
                // Apenas log/aviso leve
            } else {
                @chmod($capPath, 0664);
            }
        }
    }

    // Insert no banco
    $pdo = config::connect();
    $sql = "INSERT INTO a_curso_videoaulas
            (idpublicacaocva, idmodulocva, video, tipo, numimg, ext, size, pasta, online, totalhoras, favorito_pf, data, hora)
            VALUES
            (:idpublicacaocva, :idmodulocva, :video, :tipo, :numimg, :ext, :size, :pasta, :online, :totalhoras, :favorito_pf, :data, :hora)";
    $stmt = $pdo->prepare($sql);

    $hoje = (new DateTime())->format('Y-m-d');
    $agora = (new DateTime())->format('H:i:s');

    $stmt->execute([
        ':idpublicacaocva' => $idpublicacaocva,
        ':idmodulocva'     => $idmodulocva,
        ':video'           => $finalFilename,
        ':tipo'            => 0,
        ':numimg'          => '0',
        ':ext'             => $ext,
        ':size'            => $sizeStr,
        ':pasta'           => $pasta,
        ':online'          => $online,
        ':totalhoras'      => $totalhoras,
        ':favorito_pf'     => $favorito_pf,
        ':data'            => $hoje,
        ':hora'            => $agora,
    ]);

    $novoId = (int)$pdo->lastInsertId();

    jexit([
        'sucesso' => true,
        'id'      => $novoId,
        'mensagem' => 'Vídeo inserido com sucesso.',
        'url'     => $destUrl . '/' . $finalFilename
    ], 200);
} catch (Throwable $e) {
    jexit([
        'sucesso' => false,
        'mensagem' => 'Erro inesperado.',
        'erro'   => $e->getMessage()
    ], 500);
}
