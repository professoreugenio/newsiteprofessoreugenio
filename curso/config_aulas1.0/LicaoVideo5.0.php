<?php
// 1) Verifica se tem vídeo do YouTube
$queryVideo = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic");
$queryVideo->bindParam(":idpublic", $codigoaula);
$queryVideo->execute();
$fetchVideo = $queryVideo->fetchAll(PDO::FETCH_ASSOC);
$temYoutube = count($fetchVideo) >= 1;

// 2) Se não houver YouTube, verifica vídeo local
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

    <!-- THUMB DO YOUTUBE (abre em nova aba) -->
    <section class="container section-gap">
        <div class="card rounded-4 p-3" data-aos="zoom-in">
            <div class="ratio-16x9 position-relative" style="border-radius:1rem; overflow:hidden">
                <?php
                $chavetube = htmlspecialchars($fetchVideo[0]['chavetube_sy'], ENT_QUOTES);
                $thumbMax = "https://img.youtube.com/vi/{$chavetube}/maxresdefault.jpg";
                $thumbHQ  = "https://img.youtube.com/vi/{$chavetube}/hqdefault.jpg";
                $thumbFallback = "https://professoreugenio.com/img/thumbVinheta.jpg";
                ?>
                <a href="https://www.youtube.com/watch?v=<?= $chavetube ?>" target="_blank" rel="noopener"
                    class="d-block w-100 h-100 js-track-video"
                    data-publicacao="<?= (int)($fetchVideo[0]['idpublicacaocva'] ?? $codigoaula ?? 0) ?>"
                    data-chaveturma="<?= htmlspecialchars($chaveturmaUser ?? '', ENT_QUOTES) ?>"
                    data-chavetube="<?= $chavetube ?>">
                    <img
                        src="<?= $thumbMax ?>"
                        alt="Clique para assistir no YouTube"
                        class="img-fluid w-100 h-100" style="object-fit:cover"
                        loading="lazy" width="1280" height="720"
                        onerror="this.onerror=null;this.src='<?= $thumbHQ ?>'; this.setAttribute('onerror','this.src=&#39;<?= $thumbFallback ?>&#39;');">
                    <div class="position-absolute top-50 start-50 translate-middle">
                        <div class="bg-dark bg-opacity-75 rounded-circle p-3 shadow">
                            <i class="bi bi-play-fill fs-2 text-white"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Lista de views -->
    <div class="px-4 pb-3">
        <h6 class="text-secondary mb-2"><i class="bi bi-people me-1"></i> Quem clicou neste vídeo</h6>
        <div id="listaViewsVideo" class="small"></div>
    </div>

    <script>
        function carregarViewsVideo(idpublicacao, chavetube) {
            $.ajax({
                url: 'publicacoesv1.0/ajax_viewRegistroVideoLista.php',
                method: 'POST',
                data: {
                    idpublicacao,
                    chaveyoutube: chavetube
                },
                success: html => $('#listaViewsVideo').html(html),
                error: () => $('#listaViewsVideo').html('<div class="text-danger small">Falha ao carregar views.</div>')
            });
        }
        $(function() {
            const $a = $('a.js-track-video').first();
            if ($a.length) {
                const idpub = $a.data('publicacao') || 0;
                const chavetube = $a.data('chavetube') || '';
                if (idpub && chavetube) carregarViewsVideo(idpub, chavetube);
            }
        });
        $(document).on('click', 'a.js-track-video', function(e) {
            e.preventDefault();
            const $a = $(this),
                url = $a.attr('href');
            const payload = {
                idpublicacao: $a.data('publicacao') || 0,
                chaveturma: $a.data('chaveturma') || '',
                chaveyoutube: $a.data('chavetube') || ''
            };
            $.ajax({
                    url: 'publicacoesv1.0/ajax_viewRegistroVideo.php',
                    method: 'POST',
                    data: payload,
                    dataType: 'json'
                })
                .always(function() {
                    window.open(url, '_blank');
                    $.ajax({
                        url: 'publicacoesv1.0/ajax_viewRegistroVideoLista.php',
                        method: 'POST',
                        data: {
                            idpublicacao: payload.idpublicacao,
                            chaveyoutube: payload.chaveyoutube
                        }
                    }).done(html => $('#listaViewsVideo').html(html));
                });
        });
    </script>

<?php elseif ($temLocal): ?>

    <!-- PLAYER LOCAL (centralizado + mini flutuante quando rolar) -->
    <section id="videoaula" class="container section-gap">
        <div class="video-shell mx-auto">
            <!-- Sentinela e mount -->
            <div id="videoSentinel" aria-hidden="true" style="height:1px;"></div>
            <div id="videoMount"><!-- wrapper volta aqui quando sair do mini --></div>

            <div class="card rounded-4 shadow-lg border-0">
                <div class="p-3 p-md-4">
                    <div class="video-wrapper" id="videoWrapper" style="position:relative">
                        <!-- Botão Play Customizado -->
                        <button class="custom-play-button" id="btnCustomPlay"
                            style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);z-index:3;border:none;border-radius:50%;width:64px;height:64px;background:rgba(0,0,0,.65);color:#fff;display:flex;align-items:center;justify-content:center">
                            <i class="bi bi-play-fill fs-2"></i>
                        </button>

                        <!-- Botão Fechar Mini -->
                        <button id="btnCloseMini" class="btn-close-mini" title="Fechar mini player"
                            style="display:none;position:absolute;right:8px;top:8px;z-index:3;background:rgba(0,0,0,.65);border:none;border-radius:6px;color:#fff;padding:.35rem .5rem">×</button>

                        <!-- Área de arrasto (aparece no mini) -->
                        <div id="miniDragHandle" class="mini-drag-handle" title="Arraste para mover"></div>

                        <!-- Vídeo -->
                        <div class="ratio ratio-16x9 rounded-4 overflow-hidden">
                            <video id="video-aula" controls preload="auto" class="w-100 h-100" style="object-fit:cover;">
                                <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
                                <track src="<?= preg_replace('/\.mp4$/', '.vtt', $videoPath); ?>" kind="subtitles" srclang="pt" label="Português" default>
                                Seu navegador não suporta a tag de vídeo.
                            </video>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CSS -->
    <style>
        /* largura/centralização do bloco principal */
        #videoaula .video-shell {
            max-width: 960px;
            width: 100%;
        }

        @media (max-width: 576px) {
            #videoaula .video-shell {
                max-width: 100%;
            }
        }

        /* Dock global que recebe o vídeo em modo mini (apendado no <body>) */
        #floatingVideoDock {
            position: fixed;
            inset: 0 auto auto 0;
            /* não usado para posicionar, quem posiciona é #videoWrapper.mini-player */
            z-index: 2147483647;
            /* ACIMA DE TUDO */
            pointer-events: none;
            /* eventos só no wrapper/vídeo */
        }

        /* Wrapper em mini: fixo no viewport SEMPRE acima de tudo */
        #videoWrapper.mini-player {
            position: fixed;
            width: 360px;
            /* ajuste fino */
            aspect-ratio: 16 / 9;
            top: 16px;
            right: 16px;
            left: auto;
            bottom: auto;
            z-index: 2147483647;
            /* redundância: mantém sobre qualquer overlay */
            cursor: grab;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .35), 0 2px 8px rgba(0, 0, 0, .25);
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            pointer-events: auto;
            /* recebe cliques normalmente */
        }

        #videoWrapper.mini-player.dragging {
            cursor: grabbing;
            user-select: none;
        }

        #videoWrapper.mini-player .ratio {
            width: 100%;
            height: 100%;
        }

        #videoWrapper.mini-player video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Handle de arrasto (camada superior, não cobre controles) */
        .mini-drag-handle {
            display: none;
            position: absolute;
            left: 0;
            right: 40px;
            top: 0;
            height: 28px;
            z-index: 4;
            cursor: grab;
            /* toque leve visual (quase invisível): */
            background: linear-gradient(to bottom, rgba(0, 0, 0, .18), rgba(0, 0, 0, 0));
        }

        #videoWrapper.mini-player .mini-drag-handle {
            display: block;
        }

        @media (max-width: 480px) {
            #videoWrapper.mini-player {
                width: min(86vw, 360px);
                top: 12px;
                right: 12px;
            }
        }
    </style>

    <!-- JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video-aula');
            const wrapper = document.getElementById('videoWrapper');
            const playBtn = document.getElementById('btnCustomPlay');
            const closeBtn = document.getElementById('btnCloseMini');
            const handle = document.getElementById('miniDragHandle');
            const sentinel = document.getElementById('videoSentinel');
            const mount = document.getElementById('videoMount');

            const tempoInicial = 1;

            // Estado drag/mini
            let isDragging = false,
                offsetX = 0,
                offsetY = 0;
            let miniClosed = false; // clicou no X
            let userDragged = false; // moveu manualmente
            let lastPos = null; // {left, top}
            let lastSize = null; // largura em mini
            let dock = null; // container no <body>

            function clamp(n, min, max) {
                return Math.max(min, Math.min(max, n));
            }

            function ensureDock() {
                if (!dock) {
                    dock = document.getElementById('floatingVideoDock');
                    if (!dock) {
                        dock = document.createElement('div');
                        dock.id = 'floatingVideoDock';
                        document.body.appendChild(dock);
                    }
                }
                return dock;
            }

            function enterMini(initial = false) {
                if (wrapper.classList.contains('mini-player')) return;

                // move wrapper para o <body> (fora de qualquer stacking/transform do layout)
                ensureDock().appendChild(wrapper);

                wrapper.classList.add('mini-player');
                closeBtn.style.display = 'block';

                // largura inicial
                const w = Math.min(420, Math.round(window.innerWidth * 0.45));
                const useW = lastSize ?? w;
                wrapper.style.width = useW + 'px';
                if (!lastSize) lastSize = useW;

                // posiciona
                if (lastPos && userDragged) {
                    wrapper.style.left = lastPos.left + 'px';
                    wrapper.style.top = lastPos.top + 'px';
                    wrapper.style.right = '';
                    wrapper.style.bottom = '';
                } else {
                    // topo-direito padrão
                    wrapper.style.top = initial ? '16px' : (wrapper.style.top || '16px');
                    wrapper.style.right = '16px';
                    wrapper.style.left = '';
                    wrapper.style.bottom = '';
                }
            }

            function exitMini() {
                if (!wrapper.classList.contains('mini-player')) return;

                wrapper.classList.remove('mini-player', 'dragging');
                closeBtn.style.display = 'none';

                // limpa posicionamento inline ANTES de voltar
                wrapper.removeAttribute('style');

                // devolve wrapper para o mount original
                mount.appendChild(wrapper);
            }

            // Vídeo
            video.addEventListener('loadedmetadata', function() {
                video.currentTime = tempoInicial;
                video.pause();
            });
            playBtn.addEventListener('click', function() {
                this.style.display = 'none';
                video.play();
            });
            video.addEventListener('play', () => playBtn.style.display = 'none');

            // Observa o sentinela: saiu da viewport -> entra mini; voltou -> sai mini
            const io = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // bloco visível (voltou)
                        if (!miniClosed) exitMini();
                        miniClosed = false; // reabilita mini futuramente após retornar
                    } else {
                        // bloco saiu da viewport (rolou para cima)
                        if (!miniClosed) enterMini(true);
                    }
                });
            }, {
                root: null,
                threshold: 0.01
            });
            io.observe(sentinel);

            // Botão fechar mini
            closeBtn.addEventListener('click', function() {
                // guarda posição atual para caso precise
                if (wrapper.classList.contains('mini-player')) {
                    const r = wrapper.getBoundingClientRect();
                    lastPos = {
                        left: r.left,
                        top: r.top
                    };
                    userDragged = true;
                }
                miniClosed = true;
                exitMini();
            });

            // Drag via handle (garante arrasto mesmo com controles do vídeo)
            function startDrag(e) {
                if (!wrapper.classList.contains('mini-player')) return;
                isDragging = true;
                wrapper.classList.add('dragging');

                const r = wrapper.getBoundingClientRect();
                offsetX = e.clientX - r.left;
                offsetY = e.clientY - r.top;

                // usar left/top
                wrapper.style.left = r.left + 'px';
                wrapper.style.top = r.top + 'px';
                wrapper.style.right = '';
                wrapper.style.bottom = '';

                document.body.style.userSelect = 'none';
            }
            handle.addEventListener('mousedown', startDrag);
            // fallback: permitir arrastar clicando no wrapper (exceto sobre o <video> com controles)
            wrapper.addEventListener('mousedown', function(e) {
                if (!wrapper.classList.contains('mini-player')) return;
                if (e.target && e.target.tagName === 'VIDEO') return; // não sobre controles
                if (e.target === handle || e.target === closeBtn) return;
                startDrag(e);
            });

            document.addEventListener('mousemove', function(e) {
                if (!isDragging || !wrapper.classList.contains('mini-player')) return;

                const w = wrapper.offsetWidth,
                    h = wrapper.offsetHeight;
                let x = e.clientX - offsetX;
                let y = e.clientY - offsetY;

                x = clamp(x, 0, window.innerWidth - w);
                y = clamp(y, 0, window.innerHeight - h);

                wrapper.style.left = x + 'px';
                wrapper.style.top = y + 'px';
            });

            document.addEventListener('mouseup', function() {
                if (!isDragging) return;
                isDragging = false;
                wrapper.classList.remove('dragging');
                document.body.style.userSelect = '';

                // guarda posição para reaparecer onde o usuário deixou
                if (wrapper.classList.contains('mini-player')) {
                    const r = wrapper.getBoundingClientRect();
                    lastPos = {
                        left: r.left,
                        top: r.top
                    };
                    userDragged = true;
                }
            });

            // Ajusta posição se redimensionar a janela
            window.addEventListener('resize', function() {
                if (!wrapper.classList.contains('mini-player')) return;

                const w = wrapper.offsetWidth,
                    h = wrapper.offsetHeight;
                const r = wrapper.getBoundingClientRect();

                let x = clamp(r.left, 0, window.innerWidth - w);
                let y = clamp(r.top, 0, window.innerHeight - h);

                wrapper.style.left = x + 'px';
                wrapper.style.top = y + 'px';
            });
        });
    </script>

<?php endif; ?>