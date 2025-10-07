<div class="container mt-5">
    <div class="row align-items-center justify-content-between p-4 bg-light rounded shadow-sm">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-lg-8">
            <div class="card border-0 bg-white shadow-sm p-4 rounded-4">
                <div class="row">
                    <!-- Coluna Esquerda: Informações do curso -->
                    <div class="col-md-8">
                        <h5 class="mb-2">👋 Seja muito bem-vindo,<br> <strong class="text-primary"><?php echo $nmUser; ?></strong></h5>
                        <h4 class="fw-bold text-success">🎓 Curso: <?php echo $nomeTurma; ?></h4>
                        <div class="mt-4">
                            <h6 class="fw-bold"> Período do Curso:</h6>
                            <p class="mb-2">📅 Início: <b><?php echo databr($datainicio); ?></b> Fim previsto: <b><?php echo databr($datafim); ?></b> </p>

                            <h6 class="fw-bold">▶️ Dados do curso:</h6>
                            <div class="mb-2"><?php echo $descricao;  ?></div>
                            <h6 class="fw-bold">▶️ Última aula assistida:</h6>
                            <p><a href="actionCurso.php?lc=<?php echo $encUltimaId; ?>" class="text-decoration-underline"><?php echo $tituloultimaaula; ?></a></p>
                            <h6 class="fw-bold">⏱️ Carga horária:</h6>
                            <p class="mb-2"><span class="badge bg-info text-dark"><?php echo $cargahoraria; ?> horas</span> => <span class="badge bg-info text-dark"><?php echo $aulas; ?> Aulas</span> </p>
                            <h6 class="fw-bold">📲 Suporte via WhatsApp:</h6>
                            

                            <!-- Botão WhatsApp -->
                            <a href="<?= $lkwhats ?>" target="_blank" class="btn btn-success btn-sm shadow-sm">
                                <i class="bi bi-whatsapp"></i><?php echo $rotuloWats;  ?>
                            </a>


                        </div>
                    </div>
                    <!-- Coluna Direita: Apostilas -->
                    <div class="col-md-4">
                        <h6 class="fw-bold">📘 Apostilas disponíveis:</h6>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?php
                            // $dec = encrypt($_GET['curso'], $action = 'd');
                            $query = $con->prepare("SELECT * FROM new_sistema_modulos_turmas_PJA,new_sistema_modulos_PJA WHERE
                new_sistema_modulos_turmas_PJA.codcurso = :id AND new_sistema_modulos_PJA.codigomodulos =
                new_sistema_modulos_turmas_PJA.codmodulo AND visivelm = '1' ORDER BY ordemm");
                            $query->bindParam(":id", $idCurso);
                            $query->execute();
                            $fetch = $query->fetchALL();
                            $quant = count($fetch);
                            $i = "1";
                            foreach ($fetch as $key => $rwModulo) {
                                $ord = $key;
                                $pasta = $rwModulo['chavem'];
                                $codigomodulo = $rwModulo['codigomodulos'];
                                $tipo = "3";
                                $dir0 = "../apostilas/modulos";
                                $diretorio = $dir0 . "/" . $pasta;
                                $vis = "1";
                                $query = $con->prepare("SELECT * FROM new_sistema_midias_fotos_PJA 
                WHERE pasta = :pasta AND tipo = :tipo AND visivel =:visivel");
                                $query->bindParam(":pasta", $pasta);
                                $query->bindParam(":tipo", $tipo);
                                $query->bindParam(":visivel", $vis);
                                $query->execute();
                                $rwImagem = $query->fetch(PDO::FETCH_ASSOC);
                                $countRwImagem = $query->rowCount();
                                if ($rwImagem && isset($rwImagem['visivel']) && $rwImagem['visivel'] == 1) {
                                    $enc = encrypt($rwImagem['codigomidiasfotos'], $action = 'e');
                                    $foto = $rwImagem['foto'];
                                    $visivel = $rwImagem['visivel'];
                                    $arquivo = $diretorio . "/" . $foto;
                            ?>
                                    <a href="<?php echo $arquivo; ?>" target="_blank" class="btn btn-outline-secondary btn-sm shadow-sm">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i> <?php echo $ord;  ?> -
                                        <?php echo $rwModulo['modulo']; ?>
                                    </a>
                            <?php }
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Coluna da direita: Imagem -->
        <div class="col-lg-3 text-center">
            <img src="https://professoreugenio.com/img/mascotes/professor.png"
                alt="Professor Mascote"
                class="img-fluid rounded-circle shadow-sm"
                style="max-width: 220px;">
        </div>
    </div>
</div>