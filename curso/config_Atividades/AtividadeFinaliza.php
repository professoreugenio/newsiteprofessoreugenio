<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

if (isset($_COOKIE['nav'])) {
    $navDescriptografado = encrypt($_COOKIE['nav'], 'd');
    if (is_string($navDescriptografado) && !empty($navDescriptografado)) {
        $expnav = explode("&", $navDescriptografado);
    } else {
        error_log("Falha ao descriptografar o cookie 'nav'.");
    }
} else {
    error_log("Cookie 'nav' não encontrado.");
}

$decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
$expUser = explode("&", $decUser);
/**
 * dados
 */
echo $iduser = $expUser[0];
echo $idquest = $expnav[5];
$idpublicacao = $expnav[4];

$queryUpdate = $con->prepare("UPDATE  a_curso_questionario_resposta 
SET visivel='1' WHERE idalunoqr = :iduser AND idaulaqr = :idaulaqr");
$queryUpdate->bindParam(":iduser", $iduser);
$queryUpdate->bindParam(":idaulaqr", $idpublicacao);
$queryUpdate->execute();

if ($queryUpdate->rowCount() >= 1) {
    echo '1';
} else {
    echo '2';
}
