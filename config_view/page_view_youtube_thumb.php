<?php
$query = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic ");
$query->bindParam(":idpublic", $decPublic);
$query->execute();
$fetch = $query->fetchALL();
$quant = count($fetch);
if ($quant >= 1) {
?>

    <div id="startVideoButton">
        <button class="btn btn-watch" onclick="mostrarVideo()">
            <i class="bi bi-play-circle-fill me-2"></i> Assistir Vídeo
        </button>
    </div>

    <div id="hideVideoButton">
        <button class="btn btn-secondary" onclick="ocultarVideo()">
            <i class="bi bi-x-circle me-2"></i> Ocultar Vídeo
        </button>
    </div>

    <script>
        function mostrarVideo() {
            document.getElementById("startVideoButton").style.display = "none";
            document.getElementById("container-video").style.display = "block";
            document.getElementById("hideVideoButton").style.display = "block";

        }

        function ocultarVideo() {
            document.getElementById("container-video").style.display = "none";
            document.getElementById("hideVideoButton").style.display = "none";
            document.getElementById("startVideoButton").style.display = "flex";

        }
    </script>

    <input type="hidden" id="idurl" value="<?= $quant > 0 ? $fetch[0]['chavetube_sy'] : '' ?>">
    <div class="container mt-5" id="container-video">
        <div class="row justify-content-center">
            <div class="col-sm-8 text-center">
                <div class="thumbnail-container mt-4">
                    <a id="videoLink" href="https://www.youtube.com/watch?v=<?= $fetch[0]['chavetube_sy']; ?>"
                        target="_blank" target=" _blank">
                        <div id="logoyoutube">
                            <img src="img/playYoutube.png" alt="">
                        </div>
                        <img id="thumbnail" src="" alt="Thumbnail do vídeo">

                    </a>
                </div>
                <div class="mini-thumbnails" id="miniThumbs">
                    <?php foreach ($fetch as $key => $rwTubeTumb) :
                        if ($key === 0) continue; // Pula a primeira imagem, pois já está na principal
                    ?>
                        <a href="https://www.youtube.com/watch?v=<?= $rwTubeTumb['chavetube_sy'] ?>" target="_blank"
                            class="mini-thumbnail">
                            <img src="https://img.youtube.com/vi/<?= $rwTubeTumb['chavetube_sy'] ?>/mqdefault.jpg"
                                alt="Miniatura do vídeo">
                            <!-- <img src="https://professoreugenio.com/img/playYoutube.png" class="youtube-logo" alt="YouTube Logo"> -->
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const videoIdInput = document.getElementById("idurl");
            const videoLink = document.getElementById("videoLink");
            const thumbnail = document.getElementById("thumbnail");

            if (videoIdInput && videoLink && thumbnail) {
                const videoId = videoIdInput.value;
                thumbnail.src = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
                videoLink.href = `https://www.youtube.com/watch?v=${videoId}`;
            } else {
                // console.error("Algum dos elementos não foi encontrado no DOM.");
            }
        });
    </script>


<?php } ?>