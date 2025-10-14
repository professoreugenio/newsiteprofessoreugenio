<?php

/**
 * acessosv1.0/ajax_registraAcesso.php
 * Registra acessos (anônimos ou com usuário) com cookie (3 meses) e 1 registro/dia por chave.
 * Se, em algum momento, o id do usuário for detectado, faz backfill dos registros dessa chavera sem id.
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

date_default_timezone_set('America/Fortaleza');
header('Content-Type: application/json; charset=utf-8');

/* ================= Helpers ================= */

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
    if (strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false) return 'tablet';
    if (strpos($ua, 'mobile') !== false || strpos($ua, 'iphone') !== false || (strpos($ua, 'android') !== false && strpos($ua, 'mobile') !== false)) return 'mobile';
    return 'desktop';
}

function ensureCookieChavera(): string
{
    $cookieName = 'chavera';
    $expires = time() + 60 * 60 * 24; // 90 dias
    $opts = [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
        'httponly' => false,
        'samesite' => 'Lax'
    ];
    if (empty($_COOKIE[$cookieName])) {
        $nova = 'RA' . uniqid('', true);
        setcookie($cookieName, $nova, $opts);
        $_COOKIE[$cookieName] = $nova;
    }
    return $_COOKIE[$cookieName];
}

/**
 * Tenta obter id do usuário pelos cookies padrão do seu projeto.
 * Retorna int>0 quando válido; do contrário, 0.
 */
function resolveCurrentUserId(): int
{
    try {
        $token = '';
        if (!empty($_COOKIE['adminstart']))       $token = (string)$_COOKIE['adminstart'];
        elseif (!empty($_COOKIE['startusuario'])) $token = (string)$_COOKIE['startusuario'];

        if ($token === '') return 0;

        // decrypt com sua função utilitária
        $dec = encrypt($token, 'd');
        if (!$dec || strpos($dec, '&') === false) return 0;

        $parts = explode('&', $dec);
        $id = (int)($parts[0] ?? 0);
        return $id > 0 ? $id : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

/* ================= Execução ================= */

try {
    $chavera   = ensureCookieChavera();
    $ip        = getClientIP();
    $ua        = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $disp      = detectDevice($ua);
    $dataHoje  = date('Y-m-d');
    $horaAgora = date('H:i:s');

    // Detecta usuário (se houver)
    $idUsuario = resolveCurrentUserId(); // 0 quando anônimo

    // Conexão PDO
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica última data registrada para essa chave
    $st = $con->prepare("
        SELECT MAX(datara) AS ultima_data
        FROM a_site_registraacessos
        WHERE chavera = :ch
    ");
    $st->execute([':ch' => $chavera]);
    $ultimaData = (string)($st->fetchColumn() ?: '');


    // Verifica última data registrada para essa chave
    $st = $con->prepare("
    SELECT MAX(datara) AS ultima_data
    FROM a_site_registraacessos
    WHERE chavera = :ch
");
    $st->execute([':ch' => $chavera]);
    $ultimaData = (string)($st->fetchColumn() ?: '');

    // ===============================
    // LIMPA COOKIE CHAVERA SE DATA EXPIRADA
    // ===============================
    if ($ultimaData !== '' && $ultimaData < date('Y-m-d')) {
        $cookieName = 'chavera';
        // Remove cookie do navegador (define expiração no passado)
        setcookie($cookieName, '', time() - 3600, '/');
        unset($_COOKIE[$cookieName]);

        // Também pode, opcionalmente, apagar registros antigos dessa chave:
        // $del = $con->prepare("DELETE FROM a_site_registraacessos WHERE chavera = :ch");
        // $del->execute([':ch' => $chavera]);

        // Cria nova chave imediatamente
        $chavera = 'RA' . uniqid('', true);
        setcookie($cookieName, $chavera, [
            'expires'  => time() + 60 * 60 * 24,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
            'httponly' => false,
            'samesite' => 'Lax'
        ]);
        $_COOKIE[$cookieName] = $chavera;
    }
    // ===============================


    // Só insere se não houver registro hoje
    $podeInserir = ($ultimaData === '' || $dataHoje > $ultimaData);

    $status = 'skipped';
    if ($podeInserir) {
        if ($idUsuario > 0) {
            // Insere com idusuario
            $sql = "
                INSERT INTO a_site_registraacessos
                    (ipra, chavera, dispositivora, datara, horara, idusuariora)
                VALUES (:ip, :ch, :disp, :dt, :hr, :idu)
            ";
            $params = [
                ':ip'  => $ip,
                ':ch'  => $chavera,
                ':disp' => $disp,
                ':dt'  => $dataHoje,
                ':hr'  => $horaAgora,
                ':idu' => $idUsuario
            ];
        } else {
            // Insere anônimo (sem idusuario)
            $sql = "
                INSERT INTO a_site_registraacessos
                    (ipra, chavera, dispositivora, datara, horara)
                VALUES (:ip, :ch, :disp, :dt, :hr)
            ";
            $params = [
                ':ip'  => $ip,
                ':ch'  => $chavera,
                ':disp' => $disp,
                ':dt'  => $dataHoje,
                ':hr'  => $horaAgora
            ];
        }
        $ins = $con->prepare($sql);
        $ins->execute($params);
        $status = 'inserted';
    }

    // BACKFILL: se agora temos idUsuario, atualizar registros antigos desta chavera que ainda não têm usuário
    $backfill = false;
    $bfCount  = 0;
    if ($idUsuario > 0) {
        $up = $con->prepare("
            UPDATE a_site_registraacessos
               SET idusuariora = :idu
             WHERE chavera = :ch
               AND (idusuariora IS NULL OR idusuariora = 0)
        ");
        $up->execute([':idu' => $idUsuario, ':ch' => $chavera]);
        $bfCount = $up->rowCount();
        $backfill = ($bfCount > 0);
    }

    echo json_encode([
        'ok'         => true,
        'status'     => $status,           // inserted | skipped
        'backfill'   => $backfill,
        'bf_count'   => $bfCount,
        'user_id'    => $idUsuario,
        'chavera'    => $chavera,
        'today'      => $dataHoje,
        'last'       => $ultimaData
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Falha ao registrar acesso',
        'msg'   => $e->getMessage()
    ]);
}
