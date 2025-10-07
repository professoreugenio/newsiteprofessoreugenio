<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->

        <div class="col-md-12">
            <div id="cabecalhoAulas" class="p-4 bg-dark text-light rounded-4 shadow-lg mb-4 border border-secondary">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <!-- Informações da Turma e Módulo -->
                    <div>
                        <h6 class="mb-1 text-uppercase text-muted"><?= $nomeTurma; ?></h6>

                        <h2 class="fw-bold text-white mb-2">
                            <?= $nmmodulo; ?>
                            <span class="badge <?= $corBarra ?> ms-2 align-middle"><?= $perc; ?>%</span>
                        </h2>

                        <div class="mt-3">
                            <h6 class="text-light-50 mb-1">📌 Última aula assistida:</h6>
                            <p class="mb-0 fw-medium text-info" style="cursor: pointer;" onclick="window.location.href='actionCurso.php?lc=<?= $encUltimaId; ?>';">
                                <i class="bi bi-arrow-return-right me-1"></i> <?= $tituloultimaaula; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Botão de voltar para Módulos -->
                    <a class="btn btn-warning btn-sm mt-3 mt-md-0" href="./">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> + MÓDULOS
                    </a>
                </div>

                <!-- Área de Aulas -->
                <div class="d-flex justify-content-between align-items-center flex-wrap border-top pt-3">
                    <h4 class="mb-3 text-light"><i class="bi bi-journal-text me-2"></i>Lições</h4>
                    <div class="mb-3 d-flex">
                        <button id="btnNaoAssistidas" class="btn btn-outline-light me-2">
                            <i class="bi bi-play-circle me-1"></i> Aulas
                        </button>
                        <button id="btnAssistidas" class="btn btn-outline-success">
                            <i class="bi bi-check-circle me-1"></i> Aulas Assistidas
                        </button>
                    </div>
                </div>
            </div>


            <!-- Início da Lista -->
            <div class="listaLicoes">
                <!-- Aulas NÃO assistidas -->
                <div id="licoesNaoAssistidas">
                    <?php
                    $temNaoAssistidas = false;

                    foreach ($fetchTodasLicoes as $key => $value):
                        $codigoaulas = $value['codigopublicacoes'];
                        $idaulaoriginal = $value['idpublicacaopc'];
                        $ordem = $value['ordempc'];

                        $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
            WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario");
                        $query->bindParam(":codigoaula", $codigoaulas);
                        $query->bindParam(":codigousuario", $codigousuario);
                        $query->execute();
                        $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);

                        if ($rwaulavista) continue;

                        $temNaoAssistidas = true; // encontrou pelo menos uma não assistida

                        $num = $key + 1;
                        $enc = encrypt($idaulaoriginal, 'e');

                        $query = $con->prepare("
            SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
            FROM a_curso_videoaulas 
            WHERE idpublicacaocva = :idpublicacao
        ");
                        $query->bindParam(":idpublicacao", $idaulaoriginal);
                        $query->execute();
                        $result = $query->fetch(PDO::FETCH_ASSOC);

                        $segundos = (int)($result['totalSegundos'] ?? 0);
                        $hora = floor($segundos / 3600);
                        $minuto = floor(($segundos % 3600) / 60);
                        $segundo = $segundos % 60;
                        $tempoTotal = ($hora > 0)
                            ? sprintf('<strong>%d:%02d:%02d</strong>', $hora, $minuto, $segundo)
                            : sprintf('<strong>%02d:%02d</strong>', $minuto, $segundo);
                    ?>
                        <div class="licao">
                            <div class="d-flex justify-content-between align-items-center w-100" id="<?= $enc; ?>"
                                onclick="window.location.href='actionCurso.php?lc=<?= $enc; ?>';">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning text-dark me-2"><?= $tempoTotal; ?></span>
                                    <span class="titulo-licao">
                                        <span class="badge bg-danger me-2"><?= $num; ?></span>
                                        <i class="bi bi-play-circle me-2"></i>
                                        <?= $value['titulo']; ?>
                                        <?php if (!empty($_COOKIE['adminstart'])): echo $value['codigopublicacoescursos'];
                                        endif; ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!$temNaoAssistidas): ?>
                        <div class="licao">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success text-white me-2"><i class="bi bi-check-circle-fill"></i></span>
                                    <span class="titulo-licao text-muted">
                                        <strong>Todas as lições foram assistidas.</strong>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary disabled"><i class="bi bi-check2"></i></button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- Aulas assistidas -->
                <div id="licoesAssistidas" style="display: none;">
                    <?php foreach ($fetchTodasLicoes as $key => $value): ?>
                        <?php
                        $codigoaulas = $value['codigopublicacoes'];
                        $idaulaoriginal = $value['idpublicacaopc'];

                        $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                    WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario");
                        $query->bindParam(":codigoaula", $codigoaulas);
                        $query->bindParam(":codigousuario", $codigousuario);
                        $query->execute();
                        $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);

                        if (!$rwaulavista) continue; // pula as não assistidas aqui

                        $num = $key + 1;
                        $enc = encrypt($idaulaoriginal, 'e');

                        $query = $con->prepare("
                    SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
                    FROM a_curso_videoaulas 
                    WHERE idpublicacaocva = :idpublicacao
                ");
                        $query->bindParam(":idpublicacao", $idaulaoriginal);
                        $query->execute();
                        $result = $query->fetch(PDO::FETCH_ASSOC);

                        $segundos = (int)($result['totalSegundos'] ?? 0);
                        $hora = floor($segundos / 3600);
                        $minuto = floor(($segundos % 3600) / 60);
                        $segundo = $segundos % 60;
                        $tempoTotal = ($hora > 0)
                            ? sprintf('<strong>%d:%02d:%02d</strong>', $hora, $minuto, $segundo)
                            : sprintf('<strong>%02d:%02d</strong>', $minuto, $segundo);
                        ?>
                        <div class="licao lida">
                            <div class="d-flex justify-content-between align-items-center w-100" id="<?= $enc; ?>"
                                onclick="window.location.href='actionCurso.php?lc=<?= $enc; ?>';">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary text-white me-2"><?= $tempoTotal; ?></span>
                                    <span class="titulo-licao text-muted">
                                        <i class="bi bi-check2-circle text-success me-2"></i>
                                        <?= $value['titulo']; ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Script jQuery para alternar -->
        <script>
            $(document).ready(function() {
                $("#btnNaoAssistidas").click(function() {
                    $("#licoesNaoAssistidas").show();
                    $("#licoesAssistidas").hide();
                    $(this).addClass("btn-primary").removeClass("btn-outline-primary");
                    $("#btnAssistidas").addClass("btn-outline-success").removeClass("btn-success");
                });

                $("#btnAssistidas").click(function() {
                    $("#licoesNaoAssistidas").hide();
                    $("#licoesAssistidas").show();
                    $(this).addClass("btn-success").removeClass("btn-outline-success");
                    $("#btnNaoAssistidas").addClass("btn-outline-primary").removeClass("btn-primary");
                });
            });
        </script>

        <!-- Coluna da direita: Imagem -->
        <!-- <div class="col-md-3 text-center imagem-professor">
            <img src="https://professoreugenio.com/img/mascotes/professor.png" alt="Professor Mascote">
        </div> -->
    </div>
</div>