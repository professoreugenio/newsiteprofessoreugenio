<!-- Box com lições -->
<div id="licoesBox" class="box-licoes shadow-lg">
    <span class="badge bg-success" onclick="window.location.href='../curso/modulos.php';">
        <i class="bi bi-chevron-left me-2"></i> <i class="bi bi-layers"></i> Módulos
    </span>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div id="nmModuloAula" class="">
            <?= htmlspecialchars($nmmodulo) ?>
        </div>
        <button id="fecharBox" class="btn btn-sm btn-danger">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="box-Menulicoes">
        <?php
        foreach ($fetchTodasLicoes as $key => $value) { ?>
            <?php $codigoaulas = $value['codigopublicacoes']; ?>
            <?php
            $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario  ");
            $query->bindParam(":codigoaula", $codigoaulas);
            $query->bindParam(":codigousuario", $codigousuario);
            $query->execute();
            $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);
            $selecionada = $rwaulavista ? 'lida' : '';
            ?>
            <?php $num = $key + 1; ?>
            <?php $enc = encrypt($value['idpublicacaopc'], $action = 'e'); ?>
            <?php
            ?>
            <div class="licao <?php echo $selecionada; ?>">
                <div class="d-flex justify-content-between align-items-center w-100" id="<?php echo $enc; ?>" onclick="window.location.href='actionCurso.php?lc=<?php echo $enc; ?>';">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <span class="titulo-licao">
                            <?php
                            // $queryUpdate = $con->prepare("UPDATE a_aluno_publicacoes_cursos SET ordempc=:ordem WHERE codigopublicacoescursos  = :id");
                            // $queryUpdate->bindParam(":ordem", $num);
                            // $queryUpdate->bindParam(":id", $value['codigopublicacoescursos']);
                            // $queryUpdate->execute();
                            ?>
                            <?php echo $value['ordempc']; ?>
                            <?php echo $value['titulo']; ?>
                        </span>
                    </div>
                    <button class="btn btn-sm btn-outline-primary btn-marcar">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        <?php } ?>
    </div>
</div>