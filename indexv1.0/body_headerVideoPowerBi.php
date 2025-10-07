<header class="bg-cycle position-relative text-white text-center">
    <!-- Imagens de fundo com escurecimento -->
    <div class="bg-dark-overlay position-absolute w-100 h-100 z-0"></div>
    <div class="bg-images d-flex">
        <img src="https://professoreugenio.com/img/pwbi/bg1.jpg?<?= time(); ?>" alt="Background 1" class="bg-img">
        <img src="https://professoreugenio.com/img/pwbi/bg2.jpg?<?= time(); ?>" alt="Background 2" class="bg-img">
        <img src="https://professoreugenio.com/img/pwbi/bg3.jpg?<?= time(); ?>" alt="Background 3" class="bg-img">
    </div>

    <!-- Conteúdo -->
    <div class="container position-relative py-5 z-1">
        <h1 class="display-4 fw-bold">Curso de Power BI</h1>
        <p class="lead mb-1">Aprenda Power BI do básico ao avançado, com foco em dashboards, inteligência artificial e relatórios profissionais</p>
        <p class="lead mt-2"><i class="bi bi-people-fill me-2"></i> Mais de <strong>380 alunos</strong> satisfeitos</p>
        <p class="lead mt-2"><i class="bi bi-bar-chart-fill me-2"></i> <strong>Acessos hoje</strong></p>
        <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
            <a href="https://professoreugenio.com/pagina_vendas.php?nav=SnlySmlkTXI2NTBVQzJPRGtxYU9UWWVMSUl2bjRkOTY2aFNkMjBKT0ViST0=&ts=<?= time(); ?>"
                class="btn btn-animated-gradient btn-lg px-4">
                <i class="bi bi-cart-fill me-2"></i> Compre Agora
            </a>

            <!-- <button class="btn btn-orange-dark btn-lg px-4" onclick="mostrarVideo()">
                <i class="bi bi-play-circle me-2"></i> Aula 13
            </button> -->
        </div>

        <!-- VÍDEO CENTRALIZADO -->
        <!-- <div id="videoWrapper">
            <button id="fecharVideo" onclick="fecharVideo()" title="Fechar vídeo">&times;</button>

            <div class="text-center">
                <video id="meuVideo" controls poster="img/capaapresentacao.jpg">
                    <source src="videos/publicacoes/169444159820230911/O_QUE_É_O_POWER_BI_682fc7eeee878.mp4" type="video/mp4">
                    <track src="videos/publicacoes/169444159820230911/O_QUE_É_O_POWER_BI_682fc7eeee878.vtt" kind="subtitles" srclang="pt" label="Português" default>
                    Seu navegador não suporta a reprodução de vídeo.
                </video>
                
                <div class="mt-4">
                    <a href="https://professoreugenio.com/action.php?curso=UFRqVGtYbEEwRVROSDBTL25FZElwZz09&ts=<?= time(); ?>"
                        class="btn btn-animated-gradient btn-lg px-5">
                        <i class="bi bi-cart-fill me-2"></i> Compre Agora
                    </a>
                </div>
            </div>
        </div> -->
    </div>
</header>

<!-- <script>
    function mostrarVideo() {
        const wrapper = document.getElementById('videoWrapper');
        wrapper.classList.add('active');
        const video = document.getElementById('meuVideo');
        video.play();
    }

    function fecharVideo() {
        const wrapper = document.getElementById('videoWrapper');
        wrapper.classList.remove('active');
        const video = document.getElementById('meuVideo');
        video.pause();
        video.currentTime = 0;
    }
</script> -->
<?php require 'action_new.php'; ?>