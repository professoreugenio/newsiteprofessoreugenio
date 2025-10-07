<?php

/**
 * AJAX: Excluir vídeo de uma publicação
 * Path: publicacoesv1.0/ajax_publicacaoVideoDelete.php
 * POST:
 *  - codigovideos (int) [obrigatório]
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

/** Verifica se a coluna existe na tabela destino */
function columnExists(PDO $pdo, string $table, string $column): bool
{
    $sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c";
    $st = $pdo->prepare($sql);
    $st->execute([':t' => $table, ':c' => $column]);
    return (int)$st->fetchColumn() > 0;
}

/** Remove diretório se estiver vazio */
function tryRemoveEmptyDir(string $dir): bool
{
    if (!is_dir($dir)) return false;
    $scan = @scandir($dir);
    if ($scan === false) return false;
    // diretório vazio se só tiver . e ..
    if (count($scan) <= 2) {
        return @rmdir($dir);
    }
    return false;
}

try {
    $id = hnum($_POST['codigovideos'] ?? 0);
    if ($id <= 0) {
        jexit(['sucesso' => false, 'mensagem' => 'ID do vídeo inválido.'], 400);
    }

    $pdo = config::connect();
    $table = 'a_curso_videoaulas';
    $hasLegend = columnExists($pdo, $table, 'legendavtt');

    // Busca registro (pasta, arquivo de vídeo e, se existir, legenda)
    $sql = "SELECT pasta, video" . ($hasLegend ? ", legendavtt" : "") . " 
            FROM {$table} WHERE codigovideos = :id LIMIT 1";
    $st = $pdo->prepare($sql);
    $st->execute([':id' => $id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        jexit(['sucesso' => false, 'mensagem' => 'Vídeo não encontrado.'], 404);
    }

    $pasta = (string)$row['pasta'];
    $video = (string)$row['video'];
    $legendFromDb = $hasLegend ? (string)($row['legendavtt'] ?? '') : '';

    $destDir = APP_ROOT . '/videos/publicacoes/' . $pasta;
    $destUrl = '/videos/publicacoes/' . $pasta;

    $remocoes = [
        'video'         => false,
        'caption_bybase' => false,
        'caption_bydb'  => false,
        'pasta_removida' => false,
        'avisos'        => []
    ];

    // Remove vídeo físico
    $videoPath = $destDir . '/' . $video;
    if (is_file($videoPath)) {
        $remocoes['video'] = @unlink($videoPath);
        if (!$remocoes['video']) {
            $remocoes['avisos'][] = 'Não foi possível remover o arquivo de vídeo (permissão).';
        }
    }

    // Remove legenda por basename do vídeo (basename.vtt)
    $basename = pathinfo($video, PATHINFO_FILENAME);
    if ($basename) {
        $byBasePath = $destDir . '/' . $basename . '.vtt';
        if (is_file($byBasePath)) {
            $remocoes['caption_bybase'] = @unlink($byBasePath);
            if (!$remocoes['caption_bybase']) {
                $remocoes['avisos'][] = 'Não foi possível remover a legenda VTT (basename).';
            }
        }
    }

    // Se existir coluna legendavtt e ela apontar para outro nome, remove também
    if ($hasLegend && $legendFromDb) {
        $byDbPath = $destDir . '/' . $legendFromDb;
        if (is_file($byDbPath) && (empty($basename) || $legendFromDb !== ($basename . '.vtt'))) {
            $remocoes['caption_bydb'] = @unlink($byDbPath);
            if (!$remocoes['caption_bydb']) {
                $remocoes['avisos'][] = 'Não foi possível remover a legenda VTT (por coluna legendavtt).';
            }
        }
    }

    // Exclui o registro do banco
    $del = $pdo->prepare("DELETE FROM {$table} WHERE codigovideos = :id LIMIT 1");
    $del->execute([':id' => $id]);
    $apagadas = $del->rowCount();

    if ($apagadas <= 0) {
        jexit(['sucesso' => false, 'mensagem' => 'Falha ao excluir o registro no banco.'], 500);
    }

    // Tenta remover a pasta se ficar vazia (opcional)
    $remocoes['pasta_removida'] = tryRemoveEmptyDir($destDir);

    jexit([
        'sucesso'  => true,
        'mensagem' => 'Vídeo excluído com sucesso.',
        'url_base' => $destUrl,
        'remocoes' => $remocoes
    ], 200);
} catch (Throwable $e) {
    jexit([
        'sucesso' => false,
        'mensagem' => 'Erro inesperado ao excluir o vídeo.',
        'erro'   => $e->getMessage()
    ], 500);
}
