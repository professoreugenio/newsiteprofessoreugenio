<?php
// Consulta se existe vídeo relacionado à publicação
$queryVd = $con->prepare(
    "SELECT idpublicacaocva FROM a_curso_videoaulas WHERE idpublicacaocva = :idartigo"
);
$queryVd->bindParam(":idartigo", $idPublic, PDO::PARAM_INT);
$queryVd->execute();

// Conta quantos vídeos existem para o artigo
$contVideo = $queryVd->rowCount();

// Ajuste para garantir que $id esteja definido, caso não, utilize $idPublic
$idExibir = isset($id) ? $id : $idPublic;

// Gera o HTML com ícone colorido ou cinza conforme existência do vídeo
if ($contVideo > 0) {
    $video = '<i class="bi bi-camera-video-fill" style="color:#0080c0; font-size: 1.2em;" title="YouTube"></i> '
        . $contVideo . ' - ' . $idExibir;
} else {
    $video = '<i class="bi bi-camera-video-fill" style="color:#cccccc; font-size: 1.2em;" title="YouTube"></i> '
        . $contVideo . ' - ' . $idExibir;
}
