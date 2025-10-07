<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php'; ?>
<?php
$timestampAtual = time();
if ($dispositivo == '1') {
  $timestampAdiantado = $timestampAtual + 4320 * 3600; // 360 horas em segundos
  $addtime = 60 * 60 * 24 * 280;
} else {
  $timestampAdiantado = $timestampAtual + 5 * 3600; // 360 horas em segundos
  $addtime = 60 * 60 * 5;
}
$dec2 = encrypt($_GET['tokenchave'], $action = 'd');
$exp = explode("&", $dec2);
$iduser = $exp[0];
$querySelect = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro=:idusuario");
$querySelect->bindParam(":idusuario", $iduser);
$querySelect->execute();
$rwUser = $querySelect->fetch(PDO::FETCH_ASSOC);
$dataFormatada = date('Y-m-d H:i:s', $timestampAdiantado);
$duracao = time() + $addtime;
$dec2 = encrypt($_GET['tokenchave'], $action = 'd');
$newtokenturma = $dec2 . "&0";
$cookie_name = "userstart";
$cookie_value = "";
$cookie_expiration = time() - 3600;
$cookie_path = "/";
$cookie_domain = ""; // Se vazio, o cookie será válido apenas para o domínio atual
$cookie_secure = true; // Defina como true para enviar apenas por HTTPS
$cookie_http_only = true;
setcookie($cookie_name, $cookie_value, $cookie_expiration, $cookie_path, $cookie_domain, $cookie_secure, $cookie_http_only);
$newkey = encrypt($newtokenturma, $action = 'e');
if (!empty($_COOKIE['adminstart'])) {
  setcookie('adminstart', $newkey,  $duracao, '/');
} else {
  setcookie('startusuario', $newkey,  $duracao, '/');
}
setcookie('timeduracao', $dataFormatada,  $duracao, '/');
?>
