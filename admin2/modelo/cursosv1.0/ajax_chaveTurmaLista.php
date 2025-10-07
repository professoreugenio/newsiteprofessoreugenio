<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php'; ?>
<?php
$decAdm = encrypt($_COOKIE['navAdminmaster'], $action = 'd');
$exp = explode("&", $decAdm);
$idTurma = $exp[9];
$chaveTurma = $exp[10];
?>
<?php

if (!empty($chaveTurma)) {
    $querychave = $con->prepare("SELECT * FROM new_sistema_chave WHERE chaveturmasc = :campo ");
    $querychave->bindParam(":campo", $chaveTurma);
    $querychave->execute();
    $rwChave = $querychave->fetch(PDO::FETCH_ASSOC);
    if ($rwChave) {
        echo $rwChave['chavesc'];
    }
}
?>
