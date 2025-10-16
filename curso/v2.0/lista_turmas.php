<div class="row justify-content-center" style="gap: 10px;">

    <?php
    if (!empty($_COOKIE['adminstart'])) {
        $decstart = encrypt($_COOKIE['adminstart'], $action = 'd');
        $exp = explode("&", $decstart);
        $iduser = $exp[0];
        $nomeuser = $exp[2];
        $emailuser = $exp[3];
        $dataanv = "2005-07-01";

        $query = $con->prepare("
            SELECT t.*, h.datasha, h.horasha
            FROM new_sistema_cursos_turmas t
            LEFT JOIN (
                SELECT idturmasha, MAX(datasha) as datasha, MAX(horasha) as horasha
                FROM new_sistema_historico_acessos
                GROUP BY idturmasha
            ) h ON t.codigoturma = h.idturmasha
            ORDER BY h.datasha DESC, t.datast DESC
            LIMIT 0,15
        ");
    } else {
        $decstart = encrypt($_COOKIE['startusuario'], $action = 'd');
        $exp = explode("&", $decstart);
        $iduser = $exp[0];
        $nomeuser = $exp[1];
        $emailuser = $exp[2];
        $dataanv = $exp[3];

        $query = $con->prepare("
            SELECT i.*, t.*, h.datasha, h.horasha
            FROM new_sistema_inscricao_PJA i
            INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
            LEFT JOIN (
                SELECT idturmasha, MAX(datasha) as datasha, MAX(horasha) as horasha
                FROM new_sistema_historico_acessos
                WHERE idusuariosha = :iduser
                GROUP BY idturmasha
            ) h ON t.codigoturma = h.idturmasha
            WHERE i.codigousuario = :iduser
            ORDER BY h.datasha DESC, h.horasha DESC, t.datast DESC, t.horast DESC
        ");
        $query->bindParam(":iduser", $iduser);
    }

    $query->execute();
    $fetch = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($fetch as $key => $value) {
        $encidturma = encrypt($value['codigoturma'], $action = 'e');
        $addtime = 60 * 60 * 4;
        $duracao = time() + $addtime;
        $chaveTurma = $value['chave'];
        $comercial = $value['comercialt'];
        $codTurma = $value['codigoturma'];
        $idCurso = $value['codcursost'];


        if (!empty($_COOKIE['adminstart'])) {
            $dtprazo = "2050-01-01";
            $ativo = $value['andamento'];
            $assinante = "0";
        } else {
            $dtprazo = $value['dataprazosi'];
            $ativo = $value['andamento'];
            $assinante = $value['renovacaosi'];
        }

        $star = ($assinante == "1") ? '<i class="fa fa-star" style="color: gold;" aria-hidden="true"></i>' : '';

        $tokenturma = $iduser . "&" . $nomeuser . "&" . $emailuser . "&" . $dataanv . "&" . $codTurma . "&" . $chaveTurma . "&" . $duracao . "&" . $dtprazo;
        $tokem = encrypt($tokenturma, $action = 'e');

        $ultimadata = (!empty($value['datasha'])) ? databr($value['datasha']) : 'Sem registro';
    ?>

        <?php



        $tipo = "3";

        $query = $con->prepare("
    SELECT 
        categorias.*, fotos.*
    FROM 
        new_sistema_cursos AS categorias
    INNER JOIN 
        new_sistema_midias_fotos_PJA AS fotos
    ON 
        categorias.pasta = fotos.pasta
    WHERE 
        fotos.codpublicacao = :id 
        AND fotos.tipo = :tipo
");

        $query->bindParam(":id", $idCurso);
        $query->bindParam(":tipo", $tipo);
        $query->execute();

        $result = $query->fetch(PDO::FETCH_ASSOC);
        $arquivo = $raizSite . "/img/nocapa.jpg";
        if ($result) {
            $pasta = $result['pasta'];
            $foto = $result['foto'];
            $diretorio = $raizSite . "/fotos/midias/" . $pasta;

            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            $arquivo = $diretorio . "/" . $foto;
        }

        ?>

        <button
            id="chaveRegistraturma"
            type="button"
            class="p-2 d-flex justify-content-center"
            style="background: none; border: none; width: 100%; max-width: 300px; margin: 0 auto;"
            data-id="<?php echo $tokem; ?>">

            <div class="card rounded-4 text-white hover-zoom"
                style="
            width: 280px;
            height: 390px;
            background-image: url('<?php echo htmlspecialchars($arquivo); ?>');
            background-size: cover;
            background-position: center;
            border: none;
         ">
                <div class="card-body text-center">
                    <h6 class="card-title fw-bold mb-3">
                        <?php echo $star; ?> <?php echo $value['nometurma']; ?>*
                    </h6>
                </div>
                <div class="card-footer bg-transparent border-0 rounded-bottom-4 small">
                    <div>Último acesso: <span class="fw-semibold"><?php echo $ultimadata; ?></span></div>
                    <div class="mt-2"><i class="bi bi-arrow-down-circle-fill fs-4 text-white"></i></div>
                </div>
            </div>
        </button>

    <?php } ?>

</div>

<!-- E adicione também este estilo em algum lugar no seu <head> para animação de hover -->
<style>
    .hover-zoom {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hover-zoom:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
</style>