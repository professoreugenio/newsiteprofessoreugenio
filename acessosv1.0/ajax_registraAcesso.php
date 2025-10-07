<?php

/**
 * acessosv1.0/ajax_registraAcesso.php
 * Registra acessos anônimos com cookie (3 meses) e 1 registro/dia por chave.
 * Estratégia: UPSERT atômico (INSERT ... ON DUPLICATE KEY UPDATE) com UNIQUE (chavera, datara).
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

date_default_timezone_set('America/Fortaleza');
header('Content-Type: application/json; charset=utf-8');

// ---------- Helpers ----------
function getClientIP(): string
{
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = is_array($_SERVER[$k]) ? $_SERVER[$k][0] : $_SERVER[$k];
            if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
            return substr($ip, 0, 45);
        }
    }
    return '0.0.0.0';
}

function detectDevice(string $ua): string
{
    $ua = mb_strtolower($ua);
    if (strpos($ua, 'mobile') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'android') !== false) {
        if (strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false) return 'tablet';
        return 'mobile';
    }
    if (strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false) return 'tablet';
    return 'desktop';
}

function currentUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? '';
    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    $url    = $host ? ($scheme . '://' . $host . $uri) : $uri;
    return substr($url, 0, 500);
}

/**
 * Gera/garante cookie 'chavera' sem ponto e imprevisível.
 * Ex.: RA + 24 hex (12 bytes) => "RA3f9c...".
 */
function ensureCookieChavera(): string
{
    $cookieName = 'chavera';
    $expires = time() + (60 * 60 * 24 * 90); // 90 dias
    $path = '/';
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    $httponly = false; // mude para true se quiser bloquear JS
    $samesite = 'Lax'; // se uso cross-site, use 'None' + $secure = true

    if (empty($_COOKIE[$cookieName])) {
        $novaChave = 'RA' . bin2hex(random_bytes(12)); // no dot, alta entropia
        setcookie($cookieName, $novaChave, [
            'expires'  => $expires,
            'path'     => $path,
            'secure'   => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
        $_COOKIE[$cookieName] = $novaChave; // reflete no request atual
        return $novaChave;
    }
    return (string)$_COOKIE[$cookieName];
}

// ---------- Execução ----------
try {
    // Ajuste conforme seu schema (com ou sem underscore)
    // $table = 'a_site_registra_acessos';
    $table = 'a_site_registraacessos';

    $chavera   = ensureCookieChavera();
    $ip        = getClientIP();
    $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $disp      = detectDevice($ua);

    $urlAtual  = currentUrl();


    /**
     * Captura dados detalhados do navegador do visitante.
     */
    /**
     * Retorna apenas o nome do navegador do visitante.
     */
    function getBrowserName(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (stripos($ua, 'Edge') !== false) return 'Microsoft Edge';
        if (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) return 'Opera';
        if (stripos($ua, 'Chrome') !== false) return 'Google Chrome';
        if (stripos($ua, 'Safari') !== false) return 'Safari';
        if (stripos($ua, 'Firefox') !== false) return 'Mozilla Firefox';
        if (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident/') !== false) return 'Internet Explorer';

        return 'Desconhecido';
    }

    $ref = getBrowserName();


    $dataHoje  = date('Y-m-d');
    $horaAgora = date('H:i:s');

    $con = config::connect();

    // Tenta UPSERT com colunas urlra/refra
    try {
        $sql = "INSERT INTO {$table}
                    (ipra, chavera, dispositivora, datara, horara, urlra, refra)
                VALUES
                    (:ip, :ch, :disp, :data, :hora, :url, :ref)
                ON DUPLICATE KEY UPDATE
                    ipra = VALUES(ipra),
                    dispositivora = VALUES(dispositivora),
                    horara = VALUES(horara),
                    urlra = VALUES(urlra),
                    refra = VALUES(refra)";
        $st = $con->prepare($sql);
        $st->execute([
            ':ip'   => $ip,
            ':ch'   => $chavera,
            ':disp' => $disp,
            ':data' => $dataHoje,
            ':hora' => $horaAgora,
            ':url'  => $urlAtual,
            ':ref'  => $ref,
        ]);
        $status = 'upsert_full';
    } catch (Throwable $e1) {
        // Fallback (se tabela não tiver urlra/refra)
        $sql2 = "INSERT INTO {$table}
                    (ipra, chavera, dispositivora, datara, horara)
                 VALUES
                    (:ip, :ch, :disp, :data, :hora)
                 ON DUPLICATE KEY UPDATE
                    ipra = VALUES(ipra),
                    dispositivora = VALUES(dispositivora),
                    horara = VALUES(horara)";
        $st2 = $con->prepare($sql2);
        $st2->execute([
            ':ip'   => $ip,
            ':ch'   => $chavera,
            ':disp' => $disp,
            ':data' => $dataHoje,
            ':hora' => $horaAgora,
        ]);
        $status = 'upsert_min';
    }

    echo json_encode([
        'ok'       => true,
        'status'   => $status,
        'chavera'  => $chavera,
        'today'    => $dataHoje,
        'device'   => $disp
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Falha ao registrar acesso',
        'msg'   => $e->getMessage()
    ]);
}
