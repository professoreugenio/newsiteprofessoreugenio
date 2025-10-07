<?php
$con = config::connect();
$quantModulos = 0;
$fetchContModulo = [];

if (!empty($codigocurso)) {
    $queryContAllModulo = $con->prepare("
        SELECT *
        FROM ew_sistema_modulos_PJA 
        WHERE codcursos = :codigo AND visivel = '1'
    ");
    $queryContAllModulo->bindParam(":codigo", $codigocurso);
    $queryContAllModulo->execute();

    $fetchContAllModulo = $queryContAllModulo->fetchAll(PDO::FETCH_ASSOC);
    $quantTotalModulos = count($fetchContAllModulo);


    if ($quantTotalModulos>0) {
        $queryContAnexo = $con->prepare("
        SELECT *
        FROM new_sistema_publicacoes_anexos_PJA 
        WHERE idmodulo_pa = :codigo AND visivel = '1'
    ");
        $queryContAnexo->bindParam(":codigo", $codigoaula);
        $queryContAnexo->execute();

        $fetchContAnexo = $queryContAnexo->fetchAll(PDO::FETCH_ASSOC);
        $quantAnexoLicao = count($fetchContAnexo);
    }
}
