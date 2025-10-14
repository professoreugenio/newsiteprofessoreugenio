<?php define('BASEPATH', true);
include '../conexao/class.conexao.php';
include '../autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>

<?php
//SlU0Q1BwUUNYWHEzdzZGbGc2SFBxRmRReUFXQk4xSk5NOHB1d0ZzcWxrND0=&ts=1744858757 
echo $dec= encrypt("SlU0Q1BwUUNYWHEzdzZGbGc2SFBxRmRReUFXQk4xSk5NOHB1d0ZzcWxrND0=", $action = 'd' );
$exp=explode("&",$dec);
$imdmodulo=$exp[2];
?>
<hr>
<a href="view-pdf.php">pdf</a>
</body>

</html>