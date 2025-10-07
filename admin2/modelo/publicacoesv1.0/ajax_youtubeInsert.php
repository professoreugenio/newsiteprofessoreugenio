<?php

/** INSERT de vídeo (YouTube/Vimeo) */
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

function limpa($s)
{
    return trim((string)$s);
}

/** Detecta provedor e extrai chavetube_sy + canal_sy */
function parseVideoURL(string $url): array
{
    $u = strtolower($url);
    $canal = '';
    $chave = '';

    // YouTube
    if (strpos($u, 'youtube.com') !== false || strpos($u, 'youtu.be') !== false) {
        $canal = 'youtube';
        // padrões comuns
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            $chave = $m[1];
        } elseif (preg_match('~v=([A-Za-z0-9_-]{6,})~', $url, $m)) {
            $chave = $m[1];
        } elseif (preg_match('~/embed/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            $chave = $m[1];
        }
    }
    // Vimeo
    elseif (strpos($u, 'vimeo.com') !== false) {
        $canal = 'vimeo';
        if (preg_match('~vimeo\.com/(?:video/)?([0-9]+)~', $url, $m)) {
            $chave = $m[1];
        }
    }

    return [$chave, $canal];
}

try {
    $codpublicacao = (int)($_POST['codpublicacao_sy'] ?? 0);
    $url  = limpa($_POST['url_sy'] ?? '');
    $tit  = limpa($_POST['titulo_sy'] ?? '');
    $vis  = (int)($_POST['visivel_sy'] ?? 1);
    $fav  = (int)($_POST['favorito_sy'] ?? 0);

    if ($codpublicacao <= 0) throw new Exception('Publicação inválida.');
    if ($url === '' || $tit === '') throw new Exception('Preencha URL e Título.');

    [$chave, $canal] = parseVideoURL($url);
    if ($chave === '' || $canal === '') throw new Exception('URL não reconhecida como YouTube/Vimeo.');

    $con = config::connect();
    $con->beginTransaction();

    // Se marcar favorito, zera os demais desta publicação
    if ($fav === 1) {
        $qFav = $con->prepare("UPDATE new_sistema_youtube_PJA SET favorito_sy=0 WHERE codpublicacao_sy=:p");
        $qFav->execute([':p' => $codpublicacao]);
    }

    $q = $con->prepare("INSERT INTO new_sistema_youtube_PJA
      (codpublicacao_sy, url_sy, chavetube_sy, titulo_sy, canal_sy, visivel_sy, favorito_sy, data_sy, hora_sy)
      VALUES (:p, :u, :c, :t, :can, :v, :f, CURDATE(), CURTIME())");
    $ok = $q->execute([
        ':p' => $codpublicacao,
        ':u' => $url,
        ':c' => $chave,
        ':t' => $tit,
        ':can' => $canal,
        ':v' => $vis,
        ':f' => $fav
    ]);

    $con->commit();
    echo json_encode(['ok' => $ok]);
} catch (Throwable $e) {
    if (isset($con) && $con->inTransaction()) $con->rollBack();
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
