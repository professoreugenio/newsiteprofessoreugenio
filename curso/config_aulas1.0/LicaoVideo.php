<?php $dadosDecodificados;  ?>

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



        </div>
    </div>

<?php } ?>
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
    <div id="videaula">
        <button id="btn-toggle-video" title="Fechar ou minimizar vídeo">−</button>
        <div class="video-content">
            <div class="mb-4 mx-4">
                <div class="video-wrapper">
                    <!-- Botão de play central -->
                    <button class="custom-play-button" id="btnCustomPlay">
                        <i class="bi bi-play-fill"></i>
                    </button>
                    <!-- Vídeo -->
                    <video id="video-aula" controls preload="auto" class="rounded-4 shadow-lg w-100">
                        <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
                        <track src="<?= preg_replace('/\.mp4$/', '.vtt', $videoPath); ?>" kind="subtitles" srclang="pt" label="Português" default>
                        Seu navegador não suporta a tag de vídeo.
                    </video>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video-aula');
            const playBtn = document.getElementById('btnCustomPlay');
            const tempoInicial = 1; // segundos
            video.addEventListener('loadedmetadata', function() {
                if (video.readyState >= 2) {
                    video.currentTime = tempoInicial;
                    video.pause();
                } else {
                    video.addEventListener('canplay', function() {
                        video.currentTime = tempoInicial;
                        video.pause();
                    }, {
                        once: true
                    });
                }
            });
            playBtn.addEventListener('click', function() {
                playBtn.style.display = 'none';
                video.play();
            });
            video.addEventListener('play', function() {
                playBtn.style.display = 'none';
            });
        });
    </script>
<?php endif; ?>