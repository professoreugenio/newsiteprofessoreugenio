<?php

$id = $id ?? $idPub;
$queryYT = $con->prepare("SELECT * FROM  new_sistema_youtube_PJA WHERE codpublicacao_sy  = :idartigo ");
$queryYT->bindParam(":idartigo", $idPub);
$queryYT->execute();
$queryYT->fetch(PDO::FETCH_ASSOC);

$contytube = $queryYT->rowCount();
if ($contytube > 0) {
    $youtube = ('<i class="bi bi-youtube" style="color:#ff0000; font-size: 1.2em;" title="YouTube"></i>'. $id.'');
} else {
    $youtube = ('<i class="bi bi-youtube" style="color:#cccccc; font-size: 1.2em;" title="YouTube"></i>*'. $id.'');
}
