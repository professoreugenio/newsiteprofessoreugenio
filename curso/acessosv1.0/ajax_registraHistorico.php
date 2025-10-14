<?php

/**
 * acessosv1.0/ajax_registraHistorico.php
 * Registra histórico de páginas acessadas (path + query) sem domínio.
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php'; // se seu bootstrap precisar

date_default_timezone_set('America/Recife');
header('Content-Type: application/json; charset=utf-8');

// ---------------- Helpers ----------------
function ensureCookieChavera(): string
{
    $cookieName = 'chavera';
    $expires = time() + (60 * 60 * 24 * 90); // 90 dias
    $path = '/';
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $httponly = false;  // mude para true se quiser bloquear acesso JS
    $samesite = 'Lax';

    if (empty($_COOKIE[$cookieName])) {
        $novaChave = 'RA' . uniqid('', true);
        setcookie($cookieName, $novaChave, [
            'expires'  => $expires,
            'path'     => $path,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
        $_COOKIE[$cookieName] = $novaChave;
        return $novaChave;
    }
    return $_COOKIE[$cookieName];
}

function deviceToInt(?string $ua): int
{
    $ua = mb_strtolower((string)$ua);
    // 1=desktop, 2=mobile, 3=tablet
    if (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false) return 3;
    if (strpos($ua, 'mobile') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'android') !== false) return 2;
    return 1;
}

function getUserFromCookie(): array
{
    // Retorna [idUser|null, idTurma|null]
    if (empty($_COOKIE['startusuario'])) return [null, null];
    $decUser = encrypt($_COOKIE['startusuario'], 'd');
    $expUser = explode("&", (string)$decUser);
    if (count($expUser) < 5) return [null, null];
    $idUser  = (int)($expUser[0] ?? 0);
    $idTurma = (int)($expUser[4] ?? 0);
    if ($idUser <= 0) $idUser = null;
    if ($idTurma <= 0) $idTurma = null; // não está no schema, mas mantemos se quiser usar depois
    return [$idUser, $idTurma];
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

// ---------------- Execução ----------------
try {
    $con = config::connect();

    $chavera = ensureCookieChavera();
    [$idUser, $idTurma] = getUserFromCookie(); // $idTurma não está na tabela de histórico

    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $dispInt = deviceToInt($ua);

    $data = readJsonBody();
    $url = trim((string)($data['url'] ?? ''));
    if (preg_match('#https?://[^/]+(/.*)$#i', $url, $m)) {
        $url = $m[1];
    }
    // Garante que começa com "/"
    if ($url !== '' && $url[0] !== '/') {
        $url = '/' . $url;
    }

    // Limita a 300 chars conforme coluna
    if (mb_strlen($url) > 300) {
        $url = mb_substr($url, 0, 300);
    }


    if (strpos($url, 'modulo_licao.php') !== false) {
        $navCookie = isset($_COOKIE['nav']) ? $_COOKIE['nav'] : '';

        // Acrescenta o parâmetro logo após "modulo_licao.php"
        // Exemplo: modulo_licao.php → modulo_licao.php&NAVCOOKIE&
        $url = preg_replace(
            '/(modulo_licao\.php)(?!&)/',     // encontra "modulo_licao.php" que não tem & logo após
            'modulo_licao.php?var=' . $navCookie . '&',
            $url,
            1
        );
    }

    $dataHoje  = date('Y-m-d');
    $horaAgora = date('H:i:s');

    

    // Inserção
    $sqlIns = "INSERT INTO a_site_registraacessoshistorico
               (idusuariorah, chaverah, dispositivorah, urlrah, datarah, horarah)
               VALUES (:idu, :ch, :disp, :url, :data, :hora)";
    $ins = $con->prepare($sqlIns);
    if ($idUser === null) {
        $ins->bindValue(':idu', null, PDO::PARAM_NULL);
    } else {
        $ins->bindValue(':idu', $idUser, PDO::PARAM_INT);
    }
    $ins->bindValue(':ch', $chavera, PDO::PARAM_STR);
    $ins->bindValue(':disp', $dispInt, PDO::PARAM_INT);
    $ins->bindValue(':url', $url, PDO::PARAM_STR);
    $ins->bindValue(':data', $dataHoje, PDO::PARAM_STR);
    $ins->bindValue(':hora', $horaAgora, PDO::PARAM_STR);
    $ins->execute();

    echo json_encode([
        'ok' => true,
        'status' => 'inserted',
        'idUser' => $idUser,
        'chavera' => $chavera,
        'device' => $dispInt,
        'url' => $url,
        'date' => $dataHoje,
        'time' => $horaAgora
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'FalhaAoRegistrarHistorico',
        'msg' => $e->getMessage()
    ]);
}
