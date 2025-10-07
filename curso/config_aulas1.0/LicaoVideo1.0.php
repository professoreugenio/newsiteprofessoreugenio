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
    <!-- STYLES -->


    <!-- PLAYER -->
    <div id="videaula">
        <div class="video-content">
            <div class="mb-4 mx-4">
                <div class="video-wrapper" id="videoWrapper">
                    <!-- Botão Play Customizado -->
                    <button class="custom-play-button" id="btnCustomPlay">
                        <i class="bi bi-play-fill"></i>
                    </button>

                    <!-- Botão Fechar Mini -->
                    <button id="btnCloseMini" class="btn-close-mini" title="Fechar mini player" style="display: none;">×</button>

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

    <!-- SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video-aula');
            const wrapper = document.getElementById('videoWrapper');
            const playBtn = document.getElementById('btnCustomPlay');
            const closeMiniBtn = document.getElementById('btnCloseMini');
            const tempoInicial = 1;

            // Define o tempo inicial
            video.addEventListener('loadedmetadata', function() {
                video.currentTime = tempoInicial;
                video.pause();
            });

            // Botão custom play
            playBtn.addEventListener('click', function() {
                playBtn.style.display = 'none';
                video.play();
            });

            video.addEventListener('play', function() {
                playBtn.style.display = 'none';
            });

            // Ativar mini player ao rolar
            window.addEventListener('scroll', function() {
                const videoTop = wrapper.getBoundingClientRect().top;
                const videoHeight = wrapper.offsetHeight;

                if (videoTop + videoHeight < 0 && !wrapper.classList.contains('mini-player')) {
                    wrapper.classList.add('mini-player');
                    closeMiniBtn.style.display = 'block';
                }
            });

            // Fechar mini player
            closeMiniBtn.addEventListener('click', function() {
                wrapper.classList.remove('mini-player');
                closeMiniBtn.style.display = 'none';
                wrapper.scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
<?php endif; ?>