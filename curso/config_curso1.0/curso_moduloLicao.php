<h2 id="titulopagina" class="mb-3"><?php echo $titulo; ?></h2>
<?php $dadosDecodificados;  ?>
<!-- Player de Vídeo Estilizado -->
<!-- <div id="curso-video" class="mb-4 text-center">
    <button id="btn-assistir">Assistir vídeo</button>

    <video id="video-aula" controls poster="aula1.png">
        <source src="aula1.mp4" type="video/mp4">
        Seu navegador não suporta a tag de vídeo.
    </video>
</div> -->
<?php

$queryVideo = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic ");
$queryVideo->bindParam(":idpublic", $codigoaula);
$queryVideo->execute();
$fetchVideo = $queryVideo->fetchALL();
$quantVideo = count($fetchVideo);
if ($quantVideo >= 1) {
?>
    <div id="videaula">
        <button id="btn-toggle-video" title="Fechar ou minimizar vídeo">−</button>

        <div class="video-content">
            <?php


            ?>
            <div class="mb-4 ml-4 mr-4">
                <a href="https://www.youtube.com/watch?v=<?= $fetchVideo[0]['chavetube_sy']; ?>" target="_blank"
                    class="d-block w-100">
                    <img src="https://img.youtube.com/vi/<?= $fetchVideo[0]['chavetube_sy']; ?>/maxresdefault.jpg"
                        alt="Assista no YouTube" class="img-fluid rounded-4 shadow-lg w-100" style="object-fit: cover;">
                </a>
            </div>


            <?php
            $queryVideo = $con->prepare("SELECT * FROM a_curso_videoaulas WHERE idpublicacaocva = :idpublic");
            $queryVideo->bindParam(":idpublic", $codigoaula, PDO::PARAM_INT);
            $queryVideo->execute();
            $fetchVideos = $queryVideo->fetchAll(PDO::FETCH_ASSOC);
            $quantVideo = count($fetchVideos);

            if ($quantVideo >= 1):
                $video = $fetchVideos[0];
                $videoPath = "../videos/publicacoes/{$video['pasta']}/{$video['video']}";
            ?>
                <div class="ratio ratio-16x9 mb-4">
                    <video id="video-aula" controls poster="aula1.png" class="rounded-4 shadow-lg w-100" style="object-fit: cover;">
                        <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
                        Seu navegador não suporta a tag de vídeo.
                    </video>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php } ?>

<hr>

<!-- Descrição da Aula -->
<div class="mb-4">
    <h4>Descrição da Aula</h4>
    <p><?php echo $olho;  ?></p>
</div>
<!-- Atividades -->
<div id="curso-corpotexto">
    <h4>Conteúdo</h4>

    <?php echo $texto;  ?>
</div>