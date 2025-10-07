<?php

if (temPermissao($niveladm, [1])):
    $idCurso = isset($idCurso) ? htmlspecialchars($idCurso) : 'N/A';
    $idModulo = isset($idModulo) ? htmlspecialchars($idModulo) : 'N/A';
    $idPublicacao = isset($idPublicacao) ? htmlspecialchars($idPublicacao) : 'N/A';
   

    if(!empty($_GET['id'])): $idCurso= encrypt($_GET['id'], $action = 'd' ); endif;
    if(!empty($_GET['md'])): $idModulo= encrypt($_GET['md'], $action = 'd' ); endif;
    if(!empty($_GET['pub'])): $idPublicacao= encrypt($_GET['pub'], $action = 'd' ); endif;

    $mod = "<h3>{ C: $idCurso * M: $idModulo }";
    $mod .= "{ P: $idPublicacao }";
    $mod .= "</h3>";
endif;
echo $mod;
