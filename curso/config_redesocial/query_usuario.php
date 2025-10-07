<?php
$pastaAdm = $codigoUser = $pasta = $fotoUser = $nmUser = $mdcad = $imgUser = null;

$query = $con->prepare("SELECT * FROM new_sistema_cadastro WHERE codigocadastro = :id");
$query->bindParam(":id", $idUser);
$query->execute();
$rwUser = $query->fetch(PDO::FETCH_ASSOC);

if (is_array($rwUser)) {
    $pastaAdm   = $rwUser['pastasc'] ?? '';
    $codigoUser = $rwUser['codigocadastro'] ?? '';
    $pasta      = $rwUser['pastasc'] ?? '';
    $fotoUser   = trim($rwUser['imagem50'] ?? 'usuario.jpg');
    $nmUser     = nome($rwUser['nome'] ?? '', 2);
    $mdcad      = md5($rwUser['codigocadastro'] ?? '');

    $imgUser = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $fotoUser;
    if ($fotoUser === "usuario.jpg") {
        $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
    }
} else {
    $query = $con->prepare("SELECT * FROM new_sistema_usuario WHERE codigousuario = :id");
    $query->bindParam(":id", $idUser);
    $query->execute();
    $rwUser = $query->fetch(PDO::FETCH_ASSOC);

    if (is_array($rwUser)) {
        $codigoUser = $rwUser['codigousuario'] ?? '';
        $pastaAdm   = $rwUser['pastasu'] ?? '';
        $fotoAdm    = $rwUser['imagem200'] ?? 'usuario.jpg';
        $nmUser     = nome($rwUser['nome'] ?? '', 2);
        $mdcad      = md5($rwUser['codigousuario'] ?? '');

        $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
        if ($fotoAdm !== "usuario.jpg") {
            $imgUser = $raizSite . "/fotos/usuarios/" . $pastaAdm . "/" . $fotoAdm;
        }
    } else {
        // Se nenhum dos dois SELECTs retornar dados, evite acessar variáveis indefinidas
        $nmUser = "Usuário Desconhecido";
        $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
    }
}
