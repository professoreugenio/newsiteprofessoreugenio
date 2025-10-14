<div id="canvas1" class="canvas">
    <div class="canvas-header">
        <h3>Lições ***</h3>
        <button class="close-btn-canvas" data-target="canvas1">&times;</button>
    </div>
    <div class="canvas-content">
        <div class="box-listaMaislicoes" style="height: 84vh; overflow-y: auto;">
            <ul>
                <?php
                $lm_con = config::connect();
                $lm_decModulo = encrypt($_GET['var'], $lm_action = 'd');
                $lm_exp = explode("&", $lm_decModulo);
                $lm_decModulo = $lm_exp[2];
                $lm_decCurso = $lm_exp[1];
                $lm_decCategoria = $lm_exp[0];
                $lm_ordemant = $lm_exp[4] - 4;
                $lm_decPagina = $lm_exp[0];
                ?>
                <?php
                $lm_con = config::connect();
                $lm_query = $lm_con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codmodulo_sp = :id AND visivel='1' AND ordem>:ordem ORDER BY ordem LIMIT 0,50 ");
                $lm_query->bindParam(":id", $lm_decModulo);
                $lm_query->bindParam(":ordem", $lm_ordemant);
                $lm_query->execute();
                $lm_fetch = $lm_query->fetchALL();
                $lm_quant = count($lm_fetch);
                foreach ($lm_fetch as $lm_key => $lm_valuepub) {
                    $lm_titulo = $lm_valuepub['titulo'];
                    $lm_olho = $lm_valuepub['olho'];
                    $lm_ordem = $lm_valuepub['ordem'];
                    $lm_ordempub = $lm_valuepub['ordem'];
                    $lm_assinante = $lm_valuepub['assinante'];
                    $lm_idpublic = $lm_valuepub['codigopublicacoes'];
                ?>
                    <?php
                    $lm_var = $lm_decPagina . "&" . $lm_decCurso . "&" . $lm_decModulo . "&" . $lm_valuepub['codigopublicacoes'] . "&" . $lm_valuepub['ordem'];
                    $lm_encIdPublic = encrypt($lm_var, $lm_action = 'e');
                    ?>
                    <?php
                    if ($lm_valuepub['idpubliccopia'] > "0") {
                        $lm_idcopia = $lm_valuepub['idpubliccopia'];
                        $lm_query = $lm_con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes =:idcopia");
                        $lm_query->bindParam(":idcopia", $lm_idcopia);
                        $lm_query->execute();
                        $lm_rwCopia = $lm_query->fetch(PDO::FETCH_ASSOC);
                        /**
                         * lista
                         */
                        $lm_enc = encrypt($lm_valuepub['idpubliccopia'], $lm_action = 'e');
                        $lm_titulo = $lm_rwCopia['titulo'];
                        $lm_olho = $lm_rwCopia['olho'];
                        $lm_ordempub = $lm_rwCopia['ordem'];
                        $lm_idpublic = $lm_rwCopia['codigopublicacoes'];
                        $lm_var = $lm_decPagina . "&" . $lm_decCurso . "&" . $lm_decModulo . "&" . $lm_valuepub['codigopublicacoes'] . "&" . $lm_valuepub['ordem'];
                        $lm_encIdPublic = encrypt($lm_var, $lm_action = 'e');
                    }
                    ?>
                    <?php
                    $lm_queryImgFav = $lm_con->prepare("SELECT * FROM new_sistema_publicacoes_fotos_PJA WHERE codpublicacao = :id AND favorito_pf = '1' ");
                    $lm_queryImgFav->bindParam(":id", $lm_idpublic);
                    $lm_queryImgFav->execute();
                    $lm_rwimagen = $lm_queryImgFav->fetch(PDO::FETCH_ASSOC);
                    $lm_imgMidia = $raizSite . "/fotos/publicacoes/site/img.jpg";
                    if ($lm_rwimagen) {
                        $lm_pasta = $lm_rwimagen['pasta'];
                        $lm_foto = $lm_rwimagen['foto'];
                        $lm_imgMidia = $raizSite . "/fotos/publicacoes/" . $lm_pasta . "/" . $lm_foto;
                    }
                    if ($lm_assinante == "1") {
                        $lm_star = ('<i class="bi bi-star-fill" style="color: orange;" aria-hidden="true"></i>');
                    } else {
                        $lm_star = ('');
                    }
                    $active = ('');
                    if ($lm_ordempub == $ordempub) {
                        $active = (' active ');
                    }

                    ?>
                    <?php
                    $lm_style = ('');
                    if ($lm_ordem == $lm_exp[4]) {
                        $lm_style = (' style="background-color:#ffffff;" ');
                    }
                    ?>
                    <li class="<?php echo $active;  ?>">
                        <a href="action.php?pub=<?php echo $lm_encIdPublic; ?>"><i class="bi bi-book"></i>
                            <?php echo $lm_ordem; ?>::<?php echo $lm_star; ?>
                            <?php $lm_titulo; ?>
                            <?php echo (strlen($lm_titulo) > 30) ? substr($lm_titulo, 0, 21) . '...' : $lm_titulo; ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <!-- <div style="text-align: center; margin-top: 20px;">
            <a href="#" class="btn btn-warning blinkbutton">
                Conteúdo completo
            </a>
        </div> -->
    </div>
</div>
