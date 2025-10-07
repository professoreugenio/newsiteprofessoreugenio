<?php define('BASEPATH', true);
include('../../conexao/class.conexao.php'); ?>
<?php include('../../autenticacao.php'); ?>
<?php
$tabela = "new_sistema_usuario";
if (!empty($_COOKIE['adminstart'])) {
  $dectoken = encrypt($_COOKIE['adminstart'], $action = 'd');
  $expta = explode('&', $dectoken);
  $id = $expta[0];
  $codigoUser = $id;
  $query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario =:id  ");
  $query->bindParam(":id", $id);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $foto200 = $rwNome['imagem200'];
  $foto50 = $rwNome['imagem50'];
  $pasta = $rwNome['pastasu'];
} else {
  if (!empty($_COOKIE['startusuario'])) {
    $dectoken = encrypt($_COOKIE['startusuario'], $action = 'd');
    $expta = explode('&', $dectoken);
    $id = $expta[0];
    $codigoUser = $id;
    $query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro=:id  ");
    $query->bindParam(":id", $id);
    $query->execute();
    $rwNome = $query->fetch(PDO::FETCH_ASSOC);
    $foto200 = $rwNome['imagem200'];
    $foto50 = $rwNome['imagem50'];
   echo $pasta = $rwNome['pastasc'];
  }
}
$dir0 = "../fotos";
$dir1 = $dir0 . "/usuarios";
$diretorio = $dir1;
if (!(is_dir($diretorio))) {
  mkdir($diretorio, 0777, true);
}

$img = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto200;
$img50 = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto50;
if ($foto200 == "usuario.jpg") {
  $img = $raizSite . "/fotos/usuarios/" . $foto200;
}
// echo $img;
?>
<div>
  <img id="fotouser" style="width:150px; height:150px; border-radius:75px" class="fotouser200" src="<?php echo $img; ?>?ts=<?php echo $ts; ?>" alt="img">
</div>
<?php
if ($codigoUser == '1') { ?>
  <div>
    <img id="fotouser" style="width:50px; border-radius:25px" class="fotouser200" src="<?php echo $img50; ?>?ts=<?php echo $ts; ?>" alt="img">
  </div>
<?php }
?>
