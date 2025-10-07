<div class="container mt-5">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-12">
            <div class="info-curso">
                <div class="row">
                    <!-- Coluna Esquerda: Boas-vindas e informações -->
                    <div class="col-md-8">
                        <div class="mb-2">Seja muito bem-vindo <strong><?php echo $nmUser; ?></strong></div>
                        <div class="titulo mb-4">Você está no módulo de <br><strong><?php echo $nmmodulo; ?></strong>!</div>

                        <h5 class="mt-3">📚 Última aula assistida</h5>
                        <p><?php echo $tituloultimaaula; ?></p>

                        <h5 class="mt-3">📄 Descrição da Aula</h5>
                        <p><?php echo $olhoAaula; ?></p>
                    </div>

                    <!-- Coluna Direita: Barra de Progresso -->
                    <div class="col-md-4 mb-3">
                        <div id="barraprogressoAulas" class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <h6 class="card-title text-center mb-4">📊 Progresso</h6>

                                <!-- Círculo com percentual -->
                                <div class="progress-circle <?php echo $corBarra; ?> ">
                                    <?php echo $perc; ?>%
                                </div>

                                <!-- Barra de progresso abaixo do círculo -->
                                <div class="progress w-75" style="height: 20px;">
                                    <div class="progress-bar <?php echo $corBarra; ?> text-dark fw-bold"
                                        role="progressbar"
                                        style="width: <?php echo $perc; ?>%;"
                                        aria-valuenow="<?php echo $perc; ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linha de cards -->
                <div class="row mt-4" id="cards-curso">
                    <!-- Card: Link da Aula -->
                    <?php if ($rwUltimaaula): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card card-custom h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center text-center" style="cursor: pointer;" onclick="window.location.href='actionCurso.php?lc=<?php echo $encUltimaId; ?>';">
                                    <h6 class="card-title">🔗 Link da aula</h6>
                                    <p class="card-text">Clique aqui para assistir</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Atividade -->
                        <div class="col-md-6 mb-3">
                            <div class="card card-custom h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center text-center" style="cursor: pointer;" onclick="window.location.href='modulo_atividades.php';">
                                    <h6 class="card-title">📌 Atividade</h6>
                                    <p class="card-text"><strong>Status:</strong> Em análise</p>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="col-md-12 mb-3">
                            <div class="card card-custom h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h6 class="card-title">📌Primeiros passos</h6>
                                    <p class="card-text"><strong>Lições:</strong>Acesse as lições na barra lateral no lado direito de sua página</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <div class="col-md-12">
            <h3>Lições</h3>

            <div class="listaLicoes">
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
        <!-- Coluna da direita: Imagem -->
        <!-- <div class="col-md-3 text-center imagem-professor">
            <img src="https://professoreugenio.com/img/mascotes/professor.png" alt="Professor Mascote">
        </div> -->
    </div>
</div>