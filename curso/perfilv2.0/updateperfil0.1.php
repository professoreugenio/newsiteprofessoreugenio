<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';
?>
<?php

if (!empty($_COOKIE['adminstart'])) {
    $desUser = $_COOKIE['adminstart'];
} else if (!empty($_COOKIE['startusuario'])) {
    $desUser = $_COOKIE['startusuario'];
} else {
    echo ('<meta http-equiv="refresh" content="0; url=../">');
    exit();
}

$dectoken = encrypt($desUser, $action = 'd');
$expta = explode('&', $dectoken);
$id = $expta[0];

$nome = $_POST['nome'];
$datanasc = $_POST['datanasc'];
$email = trim(strip_tags($_POST['email']));
$celular = $_POST['celular'];
$senhaatual = trim(strip_tags($_POST['senhaatual']));
$senhanova =  trim(strip_tags($_POST['senhanova']));
$datanasc = $_POST['datanasc'];

$senhaenc = encrypt($email . "&" . $senhanova, $action = 'e');

$chaveenc = strtoupper(md5($email . "&" . $senhanova));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "6";
} else {
    if (!empty($senhaatual && $senhanova)) {

        $senhaatual = encrypt($email . "&" . $senhaatual, $action = 'e');

        $queryUser = $con->prepare("SELECT * FROM  new_sistema_cadastro WHERE senha = :senha ");
        $queryUser->bindParam(":senha", $senhaatual);
        $queryUser->execute();
        $rwUser = $queryUser->fetch(PDO::FETCH_ASSOC);
        if (!$rwUser) {
            echo "4";
        } else {

            $senhaenc = encrypt($email . "&" . $senhanova, $action = 'e');
            $chaveenc = strtoupper(md5($email . "&" . $senhanova));

            $queryUpdate = $con->prepare("UPDATE new_sistema_cadastro SET senha=:senha, chave=:chave, nome=:nome, datanascimento_sc=:datanasc, celular=:celular, dataedita=:dataedita, horaedita=:horaedita WHERE codigocadastro = :id ");
            $queryUpdate->bindParam(":senha", $senhaenc);
            $queryUpdate->bindParam(":chave", $chaveenc);
            $queryUpdate->bindParam(":nome", $nome);
            $queryUpdate->bindParam(":datanasc", $datanasc);
            $queryUpdate->bindParam(":celular", $celular);
            $queryUpdate->bindParam(":dataedita", $data);
            $queryUpdate->bindParam(":horaedita", $hora);
            $queryUpdate->bindParam(":id", $id);
            $queryUpdate->execute();
            if ($queryUpdate->execute()) {
                echo "3";
            } else {
                echo "2";
            }
        }
    } else {
        $queryUpdate = $con->prepare("UPDATE new_sistema_cadastro SET nome=:nome, datanascimento_sc=:datanasc, celular=:celular WHERE codigocadastro = :id ");
        $queryUpdate->bindParam(":nome", $nome);
        $queryUpdate->bindParam(":datanasc", $datanasc);
        $queryUpdate->bindParam(":celular", $celular);
        $queryUpdate->bindParam(":id", $id);
        $queryUpdate->execute();
        if ($queryUpdate->execute()) {
            echo "1";
        } else {
            echo "2";
        }
    }
}
