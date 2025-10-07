<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
// Segurança básica
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_POST['nav'])) {
    echo json_encode(['status' => 'error', 'msg' => 'Parâmetro nav ausente']);
    exit;
}


$navDec = encrypt($_POST['nav'], 'd'); // decrypt
$af     = isset($_POST['af']) ? trim($_POST['af']) : '';

// Transforma em array
$parts = explode('&', $navDec);

// Adiciona o af na última posição
$parts[] = $af !== '' ? $af : 0;

// Reconstrói a string
$navFinal = implode('&', $parts);

// Recria o token criptografado
$enc = encrypt($navFinal, 'e');
$duracao = 60 * 60 * 24 * 7; // 7 dias

// Registrar cookie
// setcookie('nav', $enc, time() + $duracao, '/', '', false, true);

setcookie('nav', $enc, [
    'expires'  => time() + $duracao, // validade
    'path'     => '/',               // disponível em todo o site
    'domain'   => '',                // vazio = domínio atual
    'secure'   => false,             // true se só HTTPS
    'httponly' => true,              // bloqueia acesso via JS
    'samesite' => 'Lax'              // None, Lax ou Strict
]);

echo json_encode(['status' => 'ok', 'cookie' => $enc]);
