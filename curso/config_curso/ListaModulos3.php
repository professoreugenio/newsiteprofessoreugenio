<section id="listamodulos" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h5 class="fw-semibold text-white"><?= $nomeTurma; ?></h5>
            <h4 class="mt-4 mb-2 text-white">
                <i class="bi bi-layers"></i></i> Módulos do Curso
                <br>
            </h4>
            <p class="text-white">
                Clique nos módulos abaixo para acessar suas aulas.
                <!-- Conclua todos os módulos para liberar o certificado de conclusão.<br>
                Recomendamos praticar com as planilhas e realizar os desafios disponíveis ao final de cada módulo. -->
            </p>
        </div>
        <?php
        ?>
        <div class="row justify-content-center g-4">
            <?php
            // $query = $con->prepare("SELECT * FROM new_sistema_modulos_turmas_PJA,new_sistema_modulos_PJA WHERE new_sistema_modulos_turmas_PJA.codcurso = :id AND new_sistema_modulos_PJA.codigomodulos = new_sistema_modulos_turmas_PJA.codmodulo AND visivelm = '1' ORDER BY ordemm");
            $query = $con->prepare("SELECT * FROM new_sistema_modulos_PJA WHERE codcursos = :id AND visivelm = '1' ORDER BY ordemm");
            $query->bindParam(":id", $idCurso);
            $query->execute();
            $fetchmdl = $query->fetchALL();
            foreach ($fetchmdl as $key => $valDropDown) :
                $enc = encrypt($idUser . "&" . $idCurso . "&" . $idTurma . "&" . $valDropDown['codigomodulos'], 'e');
                $bgcolor = $valDropDown['bgcolor'];
                $cor = 'bg-success'; // Exemplo fixo
                $idModulo = $valDropDown['codigomodulos'];
            ?>
                <?php
                $queryLicoes = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos,new_sistema_publicacoes_PJA 
WHERE idmodulopc = :idmodulo 
AND codigopublicacoes = idpublicacaopc AND a_aluno_publicacoes_cursos.visivelpc='1'
ORDER BY ordempc  ASC");
                $queryLicoes->bindParam(":idmodulo", $idModulo);
                $queryLicoes->execute();
                $fetchTodasLicoes = $queryLicoes->fetchALL();
                $quantLicoes = count($fetchTodasLicoes);
                ?>
                <?php
                $queryAssistidas = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
WHERE idalunoaa = :idaluno AND idmoduloaa = :idmodulo");
                $queryAssistidas->bindParam(":idaluno", $idUser);
                $queryAssistidas->bindParam(":idmodulo", $idModulo);
                $queryAssistidas->execute();
                $fetchAssistidas = $queryAssistidas->fetchALL();
                $quantAssisitdas = count($fetchAssistidas);
                $perc = "0";
                if ($quantLicoes > 0) {
                    $perc = ($quantAssisitdas / $quantLicoes) * 100;
                }
                $perc = number_format($perc, 0); // exibe com 2 casas decimai
                if ($perc < 25) {
                    $corBarra = 'bg-danger'; // vermelho
                    $cortext = 'text-white';
                } elseif ($perc < 70) {
                    $corBarra = 'bg-warning text-dark'; // laranja com texto escuro
                    $cortext = 'text-black';
                } else {
                    $corBarra = 'bg-success'; // verde
                    $cortext = 'text-white';
                }
                ?>
                <div class="col-lg-4 col-md-6 d-flex mb-4">
                    <div class="card module-card w-100 shadow-lg border-0 rounded-4"
                        style="background: linear-gradient(135deg,rgb(255, 255, 255) ,rgb(226, 226, 226)); color:rgb(0, 0, 0);"
                        onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')">

                        <div class="card-body d-flex flex-column justify-content-between position-relative px-4 py-4">
                            <?php if ($quantLicoes > $quantAssisitdas): ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-warning text-dark shadow-sm" style="margin-top: -12px;font-size: 18px">
                                    <i class="bi bi-play-circle me-1"></i> Aulas para assistir
                                </span>
                            <?php else: ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-success text-dark shadow-sm text-white" style="margin-top: -12px;font-size: 18px">
                                    <i class="bi bi-trophy-fill me-1"></i> Módulo concluído
                                </span>
                            <?php endif; ?>

                            <?php if ($quantLicoes > 0): ?>
                                <div class="position-absolute top-50 end-0 me-3 mt-3 d-flex align-items-center justify-content-center <?= $corBarra ?> <?= $cortext ?>"
                                    style="width: 64px; height: 64px; border-radius: 50%; font-size: 1.1rem; font-weight: 700; box-shadow: 0 0 8px rgba(0,0,0,0.3);">
                                    <?= $perc; ?>%
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <h5 class="fw-bold mb-3 px-3 py-2 rounded-3 bg-opacity-25 shadow-sm"
                                    style="background: rgba(255, 255, 255, 0.15); font-size: 1.25rem;">
                                    <i class="bi bi-journal-code me-2"></i><?= $valDropDown['modulo'] ?>
                                </h5>

                                <?php
                                $query = $con->prepare("
                    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(totalhoras))) AS somatotal 
                    FROM a_curso_videoaulas 
                    WHERE idmodulocva = :idmodulo
                ");
                                $query->bindParam(":idmodulo", $idModulo);
                                $query->execute();
                                $result = $query->fetch(PDO::FETCH_ASSOC);
                                $tempoTotal = $result['somatotal'] ?? '00:00:00';
                                list($hora, $minuto, $segundo) = explode(':', $tempoTotal);
                                $hora = ltrim($hora, '0');
                                $tempoFormatado = (empty($hora) || $hora == '0') ? "{$minuto}:{$segundo}" : "{$hora}:{$minuto}:{$segundo}";
                                ?>

                                <p class="mb-1 fs-6">
                                    <i class="bi bi-clock-history me-2 text-warning"></i>
                                    <span class="text-light-emphasis">Horas aula:</span> <strong><?= $tempoFormatado; ?></strong>
                                </p>

                                <p class="mb-1 fs-6">
                                    <i class="bi bi-calendar-check me-2 text-info"></i>
                                    <span class="text-light-emphasis">Último acesso:</span>
                                    <strong><?= date('d/m/Y', strtotime($data)); ?></strong>
                                </p>

                                <p class="mb-1 fs-6">
                                    <i class="bi bi-list-task me-2 text-success"></i>
                                    <span class="text-light-emphasis">Lições:</span> <?= $quantLicoes; ?> |
                                    <span class="text-light-emphasis">Assistidas:</span> <?= $quantAssisitdas; ?>
                                </p>
                            </div>

                            <?php if ($quantLicoes > 0): ?>
                                <div class="progress" style="height: 10px; background-color: rgba(136, 136, 136, 0.94);">
                                    <div class="progress-bar <?= $corBarra ?>" role="progressbar" style="width: <?= $perc ?>%;" aria-valuenow="<?= $perc ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($codigoUser) && $codigoUser == 1): ?>
                        <?php $enc = encrypt("327&" . $idCurso . "&" . $idModulo . "&0&0&0", $action = 'e'); ?>
                        <div class="mt-2 ps-2">
                            <a class="btn btn-sm btn-outline-light rounded-pill" target="_blank" href="https://professoreugenio.com/pdf/view-pdf.php?var=<?= $enc; ?>">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver PDF
                            </a>
                            <small class="d-block text-muted"><?= "327&" . $idCurso . "&" . $idModulo . "&0&0&0" ?></small>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</section>