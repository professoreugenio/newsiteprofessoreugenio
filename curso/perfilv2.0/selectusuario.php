<?php
if (!empty($_COOKIE['adminstart'])) {
  $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
  $expUser = explode("&", $decUser);
  $idUser =  $expUser['0'];
  $query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario  = :id  ");
  $query->bindParam(":id", $idUser);
  $query->execute();
  $rwNome = $query->fetch(PDO::FETCH_ASSOC);
  $imgoriginal = $rwNome['imagem'];
  $pasta = $rwNome['pastasu'];
} else {
  if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    $expUser = explode("&", $decUser);
    $idUser =  $expUser['0'];
    $query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro = :id  ");
    $query->bindParam(":id", $idUser);
    $query->execute();
    $rwNome = $query->fetch(PDO::FETCH_ASSOC);
    $imgoriginal = $rwNome['imagemsc'];
    $pasta = $rwNome['pastasc'];
  }
}

echo "id: " . $idUser . " pasta" . $pasta . "<br>";
?>
<?php
$foto = "usuario";
$numfoto = "numfoto";

$imagematual = $rwNome['imagem200'];

if ($imgoriginal != "usuario.jpg") {
  $imgoriginal = $imgoriginal;
} else {
  $imgoriginal = "usuario.jpg";
}
if (empty($pasta)) {
  $pasta = date("Y") . date("m") . date("d") . $ts;
}
$diretorio = "../../fotos/usuarios";
echo $pastathub = $diretorio . "/" . $pasta;
if (!(is_dir($pastathub))) {
  mkdir($pastathub, 0777, true);
}
$file = $diretorio . "/" . $pasta . "/" . $imagematual;
if (is_file($file)) {
  unlink($file);
}

$imagem200 = $rwNome['imagem200'];
$imagem50 = $rwNome['imagem50'];
if ($imgoriginal == "usuario.jpg") {

  $imagem200 = "usuario.jpg";
  $imagem50 = "usuario.jpg";
}
