<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

// Configurações comuns para todos os cookies
$cookieDomain = 'professoreugenio.com'; // sem "https://"
$cookiePath = '/';
$cookieSecure = true;
$cookieHttpOnly = true;

// Lista de cookies a serem removidos
$cookiesParaLimpar = [
    'startusuario',
    'adminstart',
    'adminuserstart',
    'nav',
    'navAdminmaster',
    'cursoOrigem',
    'cursoPara'
];

// Define tempo de expiração passado para forçar remoção
$tempoExpirado = time() - (60 * 60 * 24 * 180); // 180 dias atrás

foreach ($cookiesParaLimpar as $nome) {
    // Remove cookie com domínio
    setcookie($nome, '', $tempoExpirado, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly);
    // Remove cookie sem domínio (caso ele tenha sido setado dessa forma)
    setcookie($nome, '', $tempoExpirado, $cookiePath);

    // Remove da superglobal $_COOKIE para evitar acessos posteriores
    unset($_COOKIE[$nome]);
}
