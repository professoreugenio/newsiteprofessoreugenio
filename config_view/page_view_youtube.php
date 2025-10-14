<div class="video-wrapper">
    <?php
    $queryTube = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic ");
    $queryTube->bindParam(":idpublic", $decPublic);
    $queryTube->execute();
    $rwTube = $queryTube->fetch(PDO::FETCH_ASSOC);
    ?>
    <?php if ($rwTube) {  ?>
    <div class="container">

        <div id="startVideoButton">
            <button class="btn btn-watch" onclick="mostrarVideo()">
                <i class="bi bi-play-circle-fill me-2"></i> Assistir Vídeo
            </button>
        </div>

        <div id="videoCarousel" class="carousel slide" data-bs-ride="carousel">
            <!-- Indicadores -->


            <div class="carousel-indicators justify-content-center">

                <?php
                    $query = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic ");
                    $query->bindParam(":idpublic", $decPublic);
                    $query->execute();
                    $fetch = $query->fetchALL();
                    $quant = count($fetch);
                    foreach ($fetch as $key => $rwTubeTumb) {
                        $n = $key + 1;
                        $act = "";
                        if ($key == "0") {
                            $act = ('class="active"');
                        }
                    ?>
                <button type="button" data-bs-target="#videoCarousel" data-bs-slide-to="<?php echo $key;  ?>"
                    <?php echo $act;  ?> aria-current="true"
                    aria-label="Vídeo <?php echo $n;  ?>"><?php echo $n;  ?></button>

                <?php } ?>



            </div>

            <!-- Slides -->

            <div class="carousel-inner">
                <?php
                    $query = $con->prepare("SELECT * FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = :idpublic ");
                    $query->bindParam(":idpublic", $decPublic);
                    $query->execute();
                    $fetch = $query->fetchALL();
                    $quant = count($fetch);
                    foreach ($fetch as $key => $rwTubeTumb) {
                        $n = $key + 1;
                        $act = "";
                        if ($key == "0") {
                            $act = (' active');
                        }
                    ?>
                <div class="carousel-item <?php echo $act;  ?>">
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.youtube.com/embed/<?php echo $rwTubeTumb['chavetube_sy']; ?>"
                            title="Vídeo <?php echo $n; ?>" allowfullscreen></iframe>
                    </div>
                </div>
                <?php } ?>

            </div>

            <!-- Controles -->
            <button class="carousel-control-prev" type="button" data-bs-target="#videoCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#videoCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Próximo</span>
            </button>
        </div>
    </div>
    <?php } ?>
</div>