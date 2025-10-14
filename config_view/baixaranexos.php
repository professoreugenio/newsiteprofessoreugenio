<?php define('BASEPATH', true);
include('../conexao/class.conexao.php'); ?>
<?php include('../autenticacao.php'); ?>
<?php
$chave = gerachave() . "-" . gerachave();
$var = $_POST['variavel'];
$email = $_POST['email'];
$nome = $_POST['nome'];
$emailpara = $email;
$nomepara = $nome;
$assunto = "CHAVE DOWNLOAD " . $chave;
$subject = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
?>
<?php
$horaprazo = date('H:i:s', strtotime($hora) + 350);
$querychave = $con->prepare("SELECT * FROM new_sistema_chave_download WHERE emailcd = :email ");
$querychave->bindParam(":email", $email);
$querychave->execute();
$rwChave = $querychave->fetch(PDO::FETCH_ASSOC);
if ($rwChave) {
    $horacd = $rwChave['horacd'];
    $horacd = date('H:i:s', strtotime($horacd) + 60);
    if ($horacd > $hora) {
        echo '1-já cadastrado ';
        echo " dentro do prazo.";
        echo $rwChave['horacd'] . " - ";
        echo $horacd . " ";
        echo $hora;
        $con = config::connect();
        $queryUpdate = $con->prepare("UPDATE new_sistema_chave_download SET chavecd=:chave, varcd=:var, datacd=:datacd WHERE emailcd = :email");
        $queryUpdate->bindParam(":chave", $chave);
        $queryUpdate->bindParam(":email", $var);
        $queryUpdate->bindParam(":datacd", $data);
        $queryUpdate->execute();
        if ($queryUpdate->rowCount() >= 1) {
            echo '1-atualizado.';
        } else {
            echo '2-sem alterações.';
        }
        // include '../modulos_mail/modulo_mail_headers.php';
        // include '../modulos_mail/modulo_mail_body_enviacodigo.php';
        // include '../modulos_mail/modulo_mail_send.php';
    } else {
        echo '1-já cadastrado ';
        echo " fora do prazo.";
    }
} else {
    $con = config::connect();
    $queryInsert = $con->prepare("INSERT INTO new_sistema_chave_download (
  chavecd,
  emailcd,
  varcd,
  datacd,
  horacd,
  horaprazo
  )VALUES (
    :chave,
    :email,
    :var,
    :datacd,
    :horacd,
    :horaprazo
    )");
    $queryInsert->bindParam(":chave", $chave);
    $queryInsert->bindParam(":email", $email);
    $queryInsert->bindParam(":var", $var);
    $queryInsert->bindParam(":datacd", $data);
    $queryInsert->bindParam(":horacd", $hora);
    $queryInsert->bindParam(":horaprazo", $horaprazo);
    $queryInsert->execute();
    if ($queryInsert) {
        echo "2";
        // include '../modulos_mail/modulo_mail_headers.php';
        // include '../modulos_mail/modulo_mail_body_enviacodigo.php';
        // include '../modulos_mail/modulo_mail_send.php';
    }
}
?>