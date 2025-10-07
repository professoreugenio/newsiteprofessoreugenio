<?php
$query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario = :id");
$query->bindParam(":id", $idUser);
$query->execute();
$rwUser = $query->fetch(PDO::FETCH_ASSOC);

if ($rwUser) {
    $codigoUser = $rwUser['codigousuario'];
    $pastaAdm = $rwUser['pastasu'];
    $fotoAdm = $rwUser['imagem200'];
    $nmUser = nome($rwUser['nome'], $n = "2");
    $mdcad = md5($rwUser['codigousuario']);
    $imgUser = $raizSite . "/fotos/usuarios/" . $pastaAdm . "/" . $fotoAdm;
} else {
    // Tratar caso não encontre o usuário
    $codigoUser = null;
    $pastaAdm = null;
    $fotoAdm = null;
    $nmUser = "Usuário desconhecido";
    $mdcad = null;
    $imgUser = $raizSite . "/fotos/usuarios/usuario_padrao.jpg"; // Um ícone ou imagem padrão
}
