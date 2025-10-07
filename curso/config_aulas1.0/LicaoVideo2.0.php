<?php $dadosDecodificados; ?>

<?php
// 1. Verifica se tem vídeo do YouTube
$queryVideo = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic");
$queryVideo->bindParam(":idpublic", $codigoaula);
$queryVideo->execute();
$fetchVideo = $queryVideo->fetchAll();
$temYoutube = count($fetchVideo) >= 1;

// 2. Se não houver YouTube, verifica vídeo local
$temLocal = false;
$videoPath = '';
if (!$temYoutube) {
    $queryVideo = $con->prepare("SELECT * FROM a_curso_videoaulas WHERE idpublicacaocva = :idpublic");
    $queryVideo->bindParam(":idpublic", $codigoaula, PDO::PARAM_INT);
    $queryVideo->execute();
    $fetchVideos = $queryVideo->fetchAll(PDO::FETCH_ASSOC);
    $temLocal = count($fetchVideos) >= 1;

    if ($temLocal) {
        $video = $fetchVideos[0];
        $videoPath = "../videos/publicacoes/{$video['pasta']}/{$video['video']}";
    }
}
?>

<?php if ($temYoutube): ?>
    <!-- PLAYER DO YOUTUBE -->
    <div id="videaulaYoutube">
        <div class="video-content-youtube">
            <div class="mb-4 mx-4">
                <a href="https://www.youtube.com/watch?v=<?= $fetchVideo[0]['chavetube_sy']; ?>" target="_blank" class="d-block w-100">
                    <img src="https://img.youtube.com/vi/<?= $fetchVideo[0]['chavetube_sy']; ?>/hqdefault.jpg"
                        alt="Assista no YouTube"
                        class="img-fluid w-100">
                </a>
            </div>
        </div>
    </div>

<?php elseif ($temLocal): ?>
    <!-- PLAYER LOCAL -->
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
            const tempoInicial = 1;

            video.addEventListener('loadedmetadata', function() {
                video.currentTime = tempoInicial;
                video.pause();
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