<section id="cursoslivres" class="text-center bg-black text-white">
    <div class="container">
        <h2>Cursos Livres</h2>
        <p class="lead">Aprenda novas tecnologias </p>
        <i class="bi bi-star-fill text-warning"></i>
        <i class="bi bi-star-fill text-warning"></i>
        <i class="bi bi-star-fill text-warning"></i>
        <i class="bi bi-star-fill text-warning"></i>
        <i class="bi bi-star-fill text-warning"></i>

    </div>
</section>
<section class="container-fluid my-5">
    <div class="row justify-content-center g-4">

        <?php
        if (empty($_GET['idpage'])) {
            $_GET['idpage'] = 'ZlVRcUU5UC9ENDRvWTE0aGNNSVEvUT09';
        }
        $decPagina = encrypt($_GET['idpage'], $action = 'd');
        $exp = explode("&", $decPagina);
        $decPagina = $exp[0];
        $query = $con->prepare("SELECT nomepaginapa FROM new_sistema_paginasadmin WHERE codigopaginasadmin = :cod  ");
        $query->bindParam(":cod", $decPagina);
        $query->execute();
        $rwPage = $query->fetch(PDO::FETCH_ASSOC);
        $tituloPagina = $rwPage['nomepaginapa'];
        ?>
        <?php
        $tipoc = "0";
        $queryCat = $con->prepare("SELECT codigocategorias,pasta,nome,externosc,descricaosc,bgcolor,ordemsc FROM new_sistema_categorias_PJA WHERE codpagesadminsc = :var AND visivelhomesc ='1' AND comercialsc=:tipoc ORDER BY ordemsc ");
        $queryCat->bindParam(":var", $decPagina);
        $queryCat->bindParam(":tipoc", $tipoc);
        $queryCat->execute();
        $fetchCat = $queryCat->fetchALL();
        $quant = count($fetchCat);
        foreach ($fetchCat as $key => $value) {
            $pasta = $value['pasta'];
            $externo = $value['externosc'];
            $tipo = "1";
            $dir0 = "fotos/midias";
            $diretorio = $dir0 . "/" . $pasta;
            $query = $con->prepare("SELECT * FROM new_sistema_midias_fotos_PJA WHERE pasta = :pasta AND tipo = :tipo ");
            $query->bindParam(":pasta", $pasta);
            $query->bindParam(":tipo", $tipo);
            $query->execute();
            $rwImagem = $query->fetch(PDO::FETCH_ASSOC);
            $foto = $rwImagem['foto']?? '';
            $tipof = $rwImagem['tipo']?? '';
            $idpublic = $rwImagem['codigomidiasfotos']?? '';
            $enc = encrypt($decPagina . "&" . $value['codigocategorias'] . "&" . $idpublic, $action = 'e');
            $arquivo = $raizSite . "/" . $diretorio . "/" . $foto;
            if ($foto == "") {
                $arquivo = $raizSite . "/" . $dir0 . "/bgmidia.jpg";
            }
            $link = ('action.php?curso=') . $enc;
            if ($externo == 1) {
                $link = ('action.php?cursoexterno=') . $enc;
            }
            $decxx = encrypt("MUF6NExVeDY0Mjh4dkl3QzYyMmppWkV4Z2xoWHlCSWxEbklwSlhMSm50ND0=", $action = 'd');
        ?>
            <div class="col-md-3">
                <div class="card" style="background-image: url('<?php echo $arquivo; ?>');">

                    <div class="card-body">
                        <h5 class="card-title"><?php echo $value['nome']; ?></h5>


                        <div class="card-buttons-container">
                            <div class="card-buttons">
                                <a href="<?php echo $link;  ?>" class=" btn-sm btn-saibamais btn-custom">Saiba Mais <i
                                        class="bi bi-eye"></i></a>
                                <a href="#" class=" btn-sm btn-login btn-custom">Login <i class="bi bi-person"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>



    </div>
</section>