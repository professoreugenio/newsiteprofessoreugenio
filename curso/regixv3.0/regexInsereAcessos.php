<?php
// regixv3.0/regexInsereAcessos.php
declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

@header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Fortaleza');

const COOKIE_NAME = 'registraacessosREGIX';

function cli_ip(): string
{
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            return substr($ip, 0, 30); // cabe em varchar(30)
        }
    }
    return '0.0.0.0';
}

function device_from_ua(string $ua): string
{
    $ua = strtolower($ua);
    $isTablet = (str_contains($ua, 'tablet') || str_contains($ua, 'ipad'));
    $isMobile = (str_contains($ua, 'mobi') || str_contains($ua, 'android') || str_contains($ua, 'iphone'));
    if ($isTablet) return 'tablet';
    if ($isMobile) return 'mobile';
    return 'desktop';
}

function regix_user_info_from_cookies(): array
{
    $idUsuario = 0;
    $idTurma = 0;
    if (function_exists('encrypt')) {
        $decUser = '';
        if (!empty($_COOKIE['adminstart'])) {
            $decUser = encrypt($_COOKIE['adminstart'], 'd');
        } elseif (!empty($_COOKIE['startusuario'])) {
            $decUser = encrypt($_COOKIE['startusuario'], 'd');
        }
        if ($decUser) {
            $exp = explode('&', $decUser);
            $idUsuario = (int)($exp[0] ?? 0);
            $idTurma   = (int)($exp[4] ?? 0);
        }
    }
    return [$idUsuario, $idTurma];
}

try {
    if (empty($_COOKIE[COOKIE_NAME])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'msg' => 'Cookie ausente']);
        exit;
    }

    $chavera = $_COOKIE[COOKIE_NAME];
    $ip      = cli_ip();
    $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $disp    = substr(device_from_ua($ua), 0, 10);

    [$idUsuario, $idTurma] = regix_user_info_from_cookies();
    $hoje = date('Y-m-d');
    $agora = date('H:i:s');

    // 1) Existe registro hoje?
    $sqlSel = "SELECT idregistraacessos FROM a_site_registraacessos WHERE chavera=:c AND datara=:d LIMIT 1";
    $s = $con->prepare($sqlSel);
    $s->bindValue(':c', $chavera);
    $s->bindValue(':d', $hoje);
    $s->execute();
    $rowHoje = $s->fetch(PDO::FETCH_ASSOC);

    // 2) Se logado e NÃO existe registro para hoje:
    //    - Se o último registro da mesma chavera tiver datara < hoje, promova esse registro para hoje
    if (!$rowHoje && $idUsuario > 0) {
        $sqlUlt = "SELECT idregistraacessos, datara FROM a_site_registraacessos 
                   WHERE chavera=:c ORDER BY datara DESC, horara DESC LIMIT 1";
        $u = $con->prepare($sqlUlt);
        $u->bindValue(':c', $chavera);
        $u->execute();
        $ult = $u->fetch(PDO::FETCH_ASSOC);

        if ($ult && $ult['datara'] < $hoje) {
            $sqlPromove = "UPDATE a_site_registraacessos
                           SET datara=:d, horara=:h, ipra=:ip, dispositivora=:dv,
                               idusuariora=:idu, idturmara=:idt
                           WHERE idregistraacessos=:id";
            $p = $con->prepare($sqlPromove);
            $p->bindValue(':d', $hoje);
            $p->bindValue(':h', $agora);
            $p->bindValue(':ip', $ip);
            $p->bindValue(':dv', $disp);
            $p->bindValue(':idu', $idUsuario ?: null, PDO::PARAM_INT);
            $p->bindValue(':idt', $idTurma   ?: null, PDO::PARAM_INT);
            $p->bindValue(':id', $ult['idregistraacessos'], PDO::PARAM_INT);
            $p->execute();

            echo json_encode(['ok' => true, 'acao' => 'promovido', 'id' => $ult['idregistraacessos']]);
            exit;
        }
    }

    if ($rowHoje) {
        // 3) Update de hoje (mantém um único registro diário)
        $sqlUpd = "UPDATE a_site_registraacessos
                   SET ipra=:ip, dispositivora=:dv, horara=:h,
                       idusuariora=COALESCE(idusuariora, :idu),
                       idturmara=COALESCE(idturmara, :idt)
                   WHERE idregistraacessos=:id";
        $u = $con->prepare($sqlUpd);
        $u->bindValue(':ip', $ip);
        $u->bindValue(':dv', $disp);
        $u->bindValue(':h',  $agora);
        $u->bindValue(':idu', $idUsuario ?: null, PDO::PARAM_INT);
        $u->bindValue(':idt', $idTurma   ?: null, PDO::PARAM_INT);
        $u->bindValue(':id', $rowHoje['idregistraacessos'], PDO::PARAM_INT);
        $u->execute();

        echo json_encode(['ok' => true, 'acao' => 'atualizado', 'id' => $rowHoje['idregistraacessos']]);
        exit;
    }

    // 4) Insert novo (hoje)
    $sqlIns = "INSERT INTO a_site_registraacessos 
               (ipra, chavera, dispositivora, idusuariora, idturmara, datara, horara)
               VALUES (:ip, :c, :dv, :idu, :idt, :d, :h)";
    $i = $con->prepare($sqlIns);
    $i->bindValue(':ip',  $ip);
    $i->bindValue(':c',   $chavera);
    $i->bindValue(':dv',  $disp);
    $i->bindValue(':idu', $idUsuario ?: null, PDO::PARAM_INT);
    $i->bindValue(':idt', $idTurma   ?: null, PDO::PARAM_INT);
    $i->bindValue(':d',   $hoje);
    $i->bindValue(':h',   $agora);
    $i->execute();

    echo json_encode(['ok' => true, 'acao' => 'inserido', 'id' => $con->lastInsertId()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Erro interno']);
}
