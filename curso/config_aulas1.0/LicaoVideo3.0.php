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
            <div class="mb-4 mx-4 position-relative" style="border-radius: 1rem; overflow: hidden;">
                <a href="https://www.youtube.com/watch?v=<?= $fetchVideo[0]['chavetube_sy']; ?>" target="_blank" class="d-block w-100">
                    <img src="https://img.youtube.com/vi/<?= $fetchVideo[0]['chavetube_sy']; ?>/hqdefault.jpg"
                        alt="Assista no YouTube"
                        class="img-fluid w-100">
                    <!-- Botão de play central -->
                    <div class="play-overlay">
                        <i class="bi bi-play-circle-fill"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>


<?php elseif ($temLocal): ?>

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
            let miniPlayerFechado = false; // FLAG DE CONTROLE

            // Define o tempo inicial do vídeo
            video.addEventListener('loadedmetadata', function() {
                video.currentTime = tempoInicial;
                video.pause();
            });

            // Exibe vídeo ao clicar no botão de play customizado
            playBtn.addEventListener('click', function() {
                playBtn.style.display = 'none';
                video.play();
            });

            // Esconde botão custom play ao iniciar o vídeo
            video.addEventListener('play', function() {
                playBtn.style.display = 'none';
            });

            // Ativa mini player ao rolar para fora da tela
            window.addEventListener('scroll', function() {
                const videoTop = wrapper.getBoundingClientRect().top;
                const videoHeight = wrapper.offsetHeight;

                if (!miniPlayerFechado && videoTop + videoHeight < 0 && !wrapper.classList.contains('mini-player')) {
                    wrapper.classList.add('mini-player');
                    closeMiniBtn.style.display = 'block';
                }

                // Se o vídeo voltou para o viewport, reseta a flag para poder virar mini player de novo depois
                if (miniPlayerFechado && videoTop + videoHeight > 0) {
                    miniPlayerFechado = false;
                }
            });

            // Fecha apenas a miniatura ao clicar no botão fechar mini player
            closeMiniBtn.addEventListener('click', function() {
                wrapper.classList.remove('mini-player');
                closeMiniBtn.style.display = 'none';
                miniPlayerFechado = true; // ATIVA FLAG
            });
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video-aula');
            const wrapper = document.getElementById('videoWrapper');
            const playBtn = document.getElementById('btnCustomPlay');
            const closeMiniBtn = document.getElementById('btnCloseMini');
            const tempoInicial = 1;
            let miniPlayerFechado = false;

            // Drag vars
            let isDragging = false;
            let offsetX = 0;
            let offsetY = 0;

            // Tempo inicial do vídeo
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

            window.addEventListener('scroll', function() {
                const videoTop = wrapper.getBoundingClientRect().top;
                const videoHeight = wrapper.offsetHeight;

                if (!miniPlayerFechado && videoTop + videoHeight < 0 && !wrapper.classList.contains('mini-player')) {
                    wrapper.classList.add('mini-player');
                    closeMiniBtn.style.display = 'block';
                    // Reset position to default when entra no mini player
                    wrapper.style.bottom = '24px';
                    wrapper.style.right = '24px';
                    wrapper.style.left = '';
                    wrapper.style.top = '';
                }
                if (miniPlayerFechado && videoTop + videoHeight > 0) {
                    miniPlayerFechado = false;
                }
            });

            closeMiniBtn.addEventListener('click', function() {
                wrapper.classList.remove('mini-player');
                closeMiniBtn.style.display = 'none';
                miniPlayerFechado = true;
                // Ao fechar, limpa os estilos inline que foram usados para arrastar
                wrapper.style.left = '';
                wrapper.style.top = '';
                wrapper.style.bottom = '';
                wrapper.style.right = '';
                wrapper.style.position = '';
            });


            // ARRASTAR O MINI PLAYER
            wrapper.addEventListener('mousedown', function(e) {
                if (!wrapper.classList.contains('mini-player')) return;

                isDragging = true;
                wrapper.style.cursor = 'grabbing';

                // Pega o deslocamento do clique em relação ao canto do wrapper
                offsetX = e.clientX - wrapper.getBoundingClientRect().left;
                offsetY = e.clientY - wrapper.getBoundingClientRect().top;

                // Impede seleção de texto ao arrastar
                document.body.style.userSelect = 'none';
            });

            document.addEventListener('mousemove', function(e) {
                if (isDragging && wrapper.classList.contains('mini-player')) {
                    // Calcula posição dentro da tela
                    let x = e.clientX - offsetX;
                    let y = e.clientY - offsetY;

                    // Limites (opcional, pode ajustar)
                    x = Math.max(0, Math.min(window.innerWidth - wrapper.offsetWidth, x));
                    y = Math.max(0, Math.min(window.innerHeight - wrapper.offsetHeight, y));

                    wrapper.style.left = x + 'px';
                    wrapper.style.top = y + 'px';
                    wrapper.style.bottom = '';
                    wrapper.style.right = '';
                    wrapper.style.position = 'fixed';
                }
            });

            document.addEventListener('mouseup', function() {
                if (isDragging) {
                    isDragging = false;
                    wrapper.style.cursor = 'grab';
                    document.body.style.userSelect = '';
                }
            });
        });
    </script>


<?php endif; ?>