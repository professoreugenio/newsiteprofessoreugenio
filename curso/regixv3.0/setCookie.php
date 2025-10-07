<?php
// regixv3.0/setCookie.php
declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 2));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

@header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('America/Fortaleza');

const COOKIE_NAME = 'registraacessosREGIX';
const COOKIE_MONTHS = 3;

function regix_cookie_set(string $name, string $value, int $months = COOKIE_MONTHS): bool
{
    $expire   = strtotime("+{$months} months");
    $secure   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $params   = [
        'expires'  => $expire,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    return setcookie($name, $value, $params);
}

function regix_generate_chavera(): string
{
    // uniqid + random bytes -> 32~40 chars (cabe em varchar(50))
    $rand = bin2hex(random_bytes(8));
    return strtoupper($rand . dechex(time()));
}

function regix_user_info_from_cookies(): array
{
    // Tenta mapear usuário/turma a partir dos cookies padrão do seu projeto
    $idUsuario = 0;
    $idTurma   = 0;

    if (!function_exists('encrypt')) {
        return ['idUsuario' => $idUsuario, 'idTurma' => $idTurma, 'logado' => false];
    }

    $decUser = '';
    if (!empty($_COOKIE['adminstart'])) {
        $decUser = encrypt($_COOKIE['adminstart'], 'd');
    } elseif (!empty($_COOKIE['startusuario'])) {
        $decUser = encrypt($_COOKIE['startusuario'], 'd');
    }

    if ($decUser) {
        $exp = explode('&', $decUser);
        // Evita notices e segue seu padrão conhecido
        $idUsuario = (int)($exp[0] ?? 0);
        $idTurma   = (int)($exp[4] ?? 0); // conforme seu padrão mais comum
        return ['idUsuario' => $idUsuario, 'idTurma' => $idTurma, 'logado' => ($idUsuario > 0)];
    }

    return ['idUsuario' => $idUsuario, 'idTurma' => $idTurma, 'logado' => false];
}

try {
    $chavera = $_COOKIE[COOKIE_NAME] ?? '';
    if (empty($chavera)) {
        $chavera = regix_generate_chavera();
        regix_cookie_set(COOKIE_NAME, $chavera);
    }

    $user = regix_user_info_from_cookies();

    echo json_encode([
        'ok'       => true,
        'chavera'  => $chavera,
        'logado'   => $user['logado'],
        'idusuario' => $user['idUsuario'],
        'idturma'  => $user['idTurma'],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'Erro interno']);
}
