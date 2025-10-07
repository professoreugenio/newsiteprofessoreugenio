<?php
$con = config::connect();
$quantAnexoLicao = 0;
$fetchContAnexo = [];

if (!empty($codigoaula)) {
    $queryContAnexo = $con->prepare("
        SELECT extpa, titulopa, urlpa, pastapa, anexopa, codigomanexos, visivel
        FROM new_sistema_publicacoes_anexos_PJA 
        WHERE codpublicacao = :codigo AND visivel = '1'
    ");
    $queryContAnexo->bindParam(":codigo", $codigoaula);
    $queryContAnexo->execute();

    $fetchContAnexo = $queryContAnexo->fetchAll(PDO::FETCH_ASSOC);
    $quantAnexoLicao = count($fetchContAnexo);
}
