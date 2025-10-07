<?php
define('BASEPATH', true);
include('../../conexao/class.conexao.php');
include('../../autenticacao.php');

// === CHAVERA: cookie de rastreio por 3 meses ===
if (empty($_COOKIE['registraacessos'])) {
  // uniqid mais entropia + um pouco de random (tamanho curto, sem ponto)
  $chave = str_replace('.', '', uniqid(bin2hex(random_bytes(2)), true));
  setcookie('registraacessos', $chave, [
    'expires'  => time() + (60 * 60 * 24 * 90), // ~90 dias
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => false, // precisa ser lido pelo JS
    'samesite' => 'Lax'
  ]);
  // Disponibiliza no request atual
  $_COOKIE['registraacessos'] = $chave;
}

// require_once('regexInsereUsuario.php');
// require_once('regexInsereUrl.php');
// require_once('regexInsereAcessos.php');
