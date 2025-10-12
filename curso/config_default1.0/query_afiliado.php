<?php

if (!empty($_COOKIE['adminstart'])) {
    $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
    require 'config_redesocial/query_cookieStartUsuario.php';
    require 'config_redesocial/query_usuarioAdmin.php';
    require 'config_redesocial/query_fotoAdmin.php';
    require 'config_default/query_turma.php';
} else if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    require 'config_redesocial/query_cookieStartUsuario.php';
    require 'config_redesocial/query_usuario.php';
    require 'config_redesocial/query_fotouser.php';
    require 'config_default/query_turma.php';
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../index.php">');
    exit();
}
$aut1 = "1";
$aut2 = "1";
$aut3 = "1";
$aut4 = "1";
$mascote = "1";
$expUser = explode("&", $decUser);
$idUser =  $expUser['0'];

$queryAfiliado = $con->prepare("SELECT * FROM a_site_afiliados_chave WHERE idusuarioSA = :id ");
$queryAfiliado->bindParam(":id", $idUser);
$queryAfiliado->execute();
$rwAfiliado = $queryAfiliado->fetch(PDO::FETCH_ASSOC);
if (empty($rwAfiliado['chaveafiliadoSA'])) {
    echo ('<meta http-equiv="refresh" content="0; url=../curso/">');
    exit();
}
$chaveAfiliado = $rwAfiliado['chaveafiliadoSA'];
