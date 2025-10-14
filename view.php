<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require 'view1.0/viewHead.php' ?>
</head>
<?php
$con = config::connect();
?>

<body>
    <?php require 'view1.0/viewBody.php' ?>
</body>

</html>