<?php
$imgUser = $raizSite . "/fotos/usuarios/usuario.jpg"; // valor padrão

if (is_array($rwUser)) {
    // Define prioridade: usa imagem200 se existir, senão imagem50
    $foto = $rwUser['imagem200'] ?? $rwUser['imagem50'] ?? 'usuario.jpg';
    $mdcad = md5($rwUser['codigocadastro'] ?? $rwUser['codigousuario'] ?? '');

    if ($foto !== "usuario.jpg") {
        $pasta = $rwUser['pastasc'] ?? $rwUser['pastasu'] ?? '';
        $expimg = explode(".", $foto);
        $imgUser = $raizSite . "/fotos/usuarios/" . $pasta . "/" . $foto;
    } else {
        $imgUser = $raizSite . "/fotos/usuarios/usuario.jpg";
    }
}
