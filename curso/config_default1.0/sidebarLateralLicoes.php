<!-- Box com lições -->
<div id="licoesBox" class="box-licoes shadow-lg">
    <span class="badge bg-success" onclick="window.location.href='../curso/modulos.php';">
        <i class="bi bi-chevron-left me-2"></i> <i class="bi bi-layers"></i> Módulos
    </span>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div id="nmModuloAula" class="">
            <?= htmlspecialchars($nmmodulo) ?>
        </div>
        <button id="fecharBox" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="box-Menulicoes">
        <?php
        foreach ($fetchTodasLicoes as $key => $value) { ?>
            <?php $codigoaulas = $value['codigopublicacoes']; ?>
            <?php $idaulaoriginal = $value['idpublicacaopc']; ?>
            <?php $ordem = $value['ordempc']; ?>
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
            $query = $con->prepare("
    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(totalhoras))) AS somatotal 
    FROM a_curso_videoaulas 
    WHERE idpublicacaocva = :idpublicacao
");
            $query = $con->prepare("
    SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
    FROM a_curso_videoaulas 
    WHERE idpublicacaocva = :idpublicacao
");
            $query->bindParam(":idpublicacao", $idaulaoriginal);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);

            $segundos = (int)($result['totalSegundos'] ?? 0);

            // Converte segundos manualmente
            $hora = floor($segundos / 3600);
            $minuto = floor(($segundos % 3600) / 60);
            $segundo = $segundos % 60;

            // Formata com zero à esquerda
            $tempoTotal = ($hora > 0)
                ? sprintf('<strong>%d:%02d:%02d</strong>', $hora, $minuto, $segundo)
                : sprintf('<strong>%02d:%02d</strong>', $minuto, $segundo);
            ?>
            <div class="licao <?php echo $selecionada; ?>">
                <div class="d-flex justify-content-between align-items-center w-100" id="<?php echo $enc; ?>" onclick="window.location.href='actionCurso.php?lc=<?php echo $enc; ?>';">
                    <div class="d-flex align-items-center">



                        <span style="color: black;font-weight:500" class="badge bg-warning text-dark me-2"><?php echo $tempoTotal; ?></span>
                        <span class="titulo-licao">
                            <?php if ($ordem == $num): echo $ordem;
                            else: echo '<span class="badge bg-danger me-2">' . $num . '</span>';
                            endif; ?> &nbsp;

                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?php echo $value['titulo']; ?>*

                            <?php if (!empty($_COOKIE['adminstart'])):
                                echo $value['codigopublicacoescursos'];
                            endif; ?>
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