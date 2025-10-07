<?php define('BASEPATH', true);
include('../../conexao/class.conexao.php');
include('../../autenticacao.php');
?>
<?php
$ip = $_POST['ip'];
$url = $_POST['pagina'];
$nome = "Anonimo";
if (!empty($_COOKIE['startusuario'])) {
    $dec = encrypt($_COOKIE['startusuario'], $action = 'd');
    $expuser = explode("&", $dec);
    $nome = $expuser[1];
}
if (!empty($_COOKIE['adminstart'])) {
    $dec = encrypt($_COOKIE['adminstart'], $action = 'd');
    $expuser = explode("&", $dec);
    $nome = $expuser[1];
}
if (empty($_COOKIE['registraacessos'])) {
    $chave = $ip . "&" . time() . "&" . $nome . "&" . uniqid();
    $key = encrypt($chave, $action = 'e');
    $addtime = 60 * 60 * 24 * 1;
    $duracao = time() + $addtime;
    setcookie('registraacessos', $key, time() + $duracao, '/');
    echo "Não registrado";
} else {
    $dec = encrypt($_COOKIE['registraacessos'], $action = 'd');
    $exp = explode("&", $dec);
    $ipRegistro = $exp[0];
    $timeInicial = $exp[1];
    $chave = $ipRegistro . " " . $timeInicial;
    $datahora = $data . " " . $hora;

    $query = $con->prepare("SELECT * FROM a_site_registrausuario WHERE chaveru = :chave ");
    $query->bindParam(":chave", $chave);
    $query->execute();
    $rwRegistro = $query->fetch(PDO::FETCH_ASSOC);

    if (!$rwRegistro) {

        $queryInsert = $con->prepare("INSERT INTO a_site_registrausuario (
  chaveru,
  dispositivoru,
  usuarioru,
  dataru
  )VALUES (
  :chave,
  :dispositivo,
  :usuario,
  :dataru
  )");
        $queryInsert->bindParam(":chave", $chave);
        $queryInsert->bindParam(":dispositivo", $dispositoAcesso);
        $queryInsert->bindParam(":usuario", $nome);
        $queryInsert->bindParam(":dataru", $data);
        $queryInsert->execute();
    } else {


        $queryUpdate = $con->prepare("UPDATE a_site_registrausuario SET dataru=:dataru, usuarioru = :usuario WHERE chaveru = :chave");
        $queryUpdate->bindParam(":dataru", $data);
        $queryUpdate->bindParam(":usuario", $nome);
        $queryUpdate->bindParam(":chave", $chave);
        $queryUpdate->execute();

         $rwRegistro['idregistrausuario'];

        $queryInsert = $con->prepare("INSERT INTO a_site_registraacessos (
  chavera,
  urlra,
  datara
  )VALUES (
  :chave,
  :url,
  :datahora
  )");
        $queryInsert->bindParam(":chave", $chave);
        $queryInsert->bindParam(":url", $url);
        $queryInsert->bindParam(":datahora", $data);
        $queryInsert->execute();
    }
}

?>