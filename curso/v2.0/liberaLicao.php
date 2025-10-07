<?php
define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';

 $dec = encrypt($_POST['id'], $action = 'd');
$exp = explode('&', $dec);
$idpublicacao = $exp[0];
$idmodulo = $exp[1];
$aulaLiberada = $exp[2];
if ($aulaLiberada == '0') {
    $aulaLiberada = '1';
}


$queryUpdate = $con->prepare("UPDATE  a_aluno_publicacoes_cursos SET aulaliberadapc=:liberada WHERE idpublicacaopc = :id AND idmodulopc = :idmodulo");
$queryUpdate->bindParam(":liberada", $aulaLiberada);
$queryUpdate->bindParam(":id", $idpublicacao);
$queryUpdate->bindParam(":idmodulo", $idmodulo);
$queryUpdate->execute();

if ($queryUpdate->rowCount() >= 1) {
    echo '1';
} else {
    echo '2';
}
