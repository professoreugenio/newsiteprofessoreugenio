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
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 justify-content-center g-4">


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
                } elseif ($perc < 70) {
                    $corBarra = 'bg-warning text-dark'; // laranja com texto escuro
                } else {
                    $corBarra = 'bg-success'; // verde
                }
                ?>
                <?php
                $tipo = "7";
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
                    fotos.tipo = :tipo
                    AND fotos.codmodulomfp = :idmodulo
                ");
                $query->bindParam(":idmodulo", $idModulo);
                $query->bindParam(":tipo", $tipo);
                $query->execute();
                $result = $query->fetch(PDO::FETCH_ASSOC);
                $arquivo = $raizSite . "/img/nomodulo,png";
                if ($result) {
                    $pasta = $result['pasta'];
                    $foto = $result['foto'];
                    $diretorio = $raizSite . "/fotos/midias/" . $pasta;
                    if (!is_dir($diretorio)) {
                        mkdir($diretorio, 0777, true);
                    }
                    $arquivo = $diretorio . "/" . $foto;
                } else {
                }
                ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card module-card shadow-lg border-0 rounded-4 text-white"
                        style="width: 250px; height: 390px;
            background-image: url('<?= $arquivo ?>');
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;"
                        onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')">
                        <div class="card-body p-3" style="background: rgba(0,0,0,0.2); height: 100%; position: relative;">
                            <?php if ($quantLicoes > 0): ?>
                                <div class="d-flex align-items-center justify-content-center <?php echo $corBarra;  ?>" style="width: 80px; height:80px; box-shadow: 0 0 6px rgba(0,0,0,0.2); font-size: 0.9rem; position: absolute;z-index:1000; top: 10px; left: 10px;border-radius:50%;font-size: 1.4rem;font-weight: 600;">
                                    <?php echo $perc;  ?>%
                                </div>
                            <?php endif; ?>
                            <!-- Título totalmente centralizado -->
                            <div id="nomedoModulo"
                                class="text-center px-2"
                                style="position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 100%;">
                                <h5 class="fw-bold text-white" style="font-size: 1.1rem; text-shadow: 0 0 4px #000;">
                                    <i class="bi bi-journal-code me-2"></i><?= $valDropDown['modulo'] ?>
                                </h5>
                            </div>
                            <?php
                            $query = $con->prepare("
    SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
    FROM a_curso_videoaulas 
    WHERE  idmodulocva = :idmodulo AND online = '1'
");
                            $query->bindParam(":idmodulo", $idModulo);
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
                            <!-- Informações na parte inferior -->
                            <div class="text-start position-absolute bottom-0 start-0 w-100 p-3" style="font-size: 0.85rem;">
                                <p class="mb-1">
                                    <i class="bi bi-clock-history me-1"></i> <strong>Horas aula:</strong> <?= $tempoTotal; ?>
                                </p>

                                
                                <p class="mb-1">
                                    <i class="bi bi-calendar-check me-1"></i> <strong>Último acesso:</strong> <?= date('d/m/Y', strtotime($data)); ?>
                                </p>
                                <p class="mb-1">
                                    📚 <strong>Lições:</strong> <?= $quantLicoes ?> |
                                    ✅ <strong>Assistidas:</strong> <?= $quantAssisitdas ?>
                                </p>
                                <div>
                                    <?php if ($quantLicoes > 0): ?>
                                        <div class="progress mb-2" style="height: 10px; background-color: rgba(255,255,255,0.2);">
                                            <div class="progress-bar <?= $corBarra ?>" role="progressbar" style="width: <?php echo $perc;  ?>%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- 
                    <?php if (!empty($codigoUser) && $codigoUser == 1): ?>
                        <?php $enc = encrypt("327&" . $idCurso . "&" . $idModulo . "&0&0&0", $action = 'e'); ?>
                        <a target="_blank" href="https://professoreugenio.com/pdf/view-pdf.php?var=<?php echo $enc;  ?>">Pdf</a>
                        <?php echo $var = "327&" . $idCurso . "&" . $idModulo . "&0&0&0"; ?>
                    <?php endif; ?>
 -->
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>