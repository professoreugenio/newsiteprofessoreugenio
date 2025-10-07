<div class="row justify-content-center g-4">

    <?php
    $query = $con->prepare("SELECT * FROM new_sistema_modulos_turmas_PJA,new_sistema_modulos_PJA WHERE new_sistema_modulos_turmas_PJA.codcurso = :id AND new_sistema_modulos_PJA.codigomodulos = new_sistema_modulos_turmas_PJA.codmodulo AND visivelm = '1' ORDER BY ordemm");
    $query->bindParam(":id", $idCurso);
    $query->execute();
    $fetchmdl = $query->fetchALL();
    $quant = count($fetchmdl);
    ?>
    <?php
    $i = "1";
    foreach ($fetchmdl as $key => $valDropDown) :
        $enc = encrypt($idUser . "&" . $idCurso . "&" . $idTurma . "&" . $valDropDown['codigomodulos'], $action = 'e');
        $bgcolor = $valDropDown['bgcolor']
    ?>


    <?php

        // Define cor da barra de progresso
        $cor = 'bg-danger';
        // if ($modulo['progresso'] >= 75) $cor = 'bg-success';
        // elseif ($modulo['progresso'] >= 40) $cor = 'bg-warning';
        ?>
    <div class="col-lg-3 col-md-4 d-flex justify-content-center">
        <div class="card border-0 rounded-4 shadow-sm custom-card h-100 w-100"
            style="background: linear-gradient(135deg, <?php echo $bgcolor;  ?>, <?php echo $bgcolor;  ?>62); color: #fff;">
            <div class="card-body d-flex flex-column justify-content-between" style="cursor: pointer;"
                onclick="abrirPagina('actionCurso.php?mdl=<?php echo $enc; ?>')">
                <div>
                    <h5 class="card-title fw-bold">
                        <i class="bi bi-journal-code me-2"></i>
                        <?php echo $valDropDown['modulo']; ?>
                    </h5>
                    <p class="small mb-2">Descrição do módulo</p>
                    <p class="mb-2">Último acesso:
                        <strong><?php echo date('d/m/Y', strtotime($data)); ?></strong>
                    </p>


                </div>
                <div class="text-end mt-3">
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar <?php echo $cor; ?>" role="progressbar" style="width: 75%;"
                            aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small data-bs-toggle="tooltip" title="Progresso baseado nas aulas concluídas">
                        100% concluído
                    </small>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>