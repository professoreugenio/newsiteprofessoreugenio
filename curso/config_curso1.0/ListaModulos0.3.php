<section id="listamodulos" class="py-2">
    <div class="container">
        <!-- Título da Turma -->
        <div class="text-center mb-5">

            <h4 class="mt-4 mb-2 text-white">
                <i class="bi bi-layers"></i> Módulos do Curso
            </h4>
            <p class="text-white">
                Clique nos módulos abaixo para acessar suas aulas.
            </p>
            <p><a class="btn btn-success btn-sm" href="../curso/modulos.php">MEUS CURSOS</a></p>
        </div>

        <!-- Lista de Módulos -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">

            <?php
            // Busca os módulos visíveis do curso
            $queryModulos = $con->prepare("
            SELECT * FROM new_sistema_modulos_PJA 
            WHERE codcursos = :id AND visivelm = '1' 
            ORDER BY ordemm
        ");
            $queryModulos->bindParam(":id", $idCurso);
            $queryModulos->execute();
            $modulos = $queryModulos->fetchAll();

            foreach ($modulos as $modulo):
                $idModulo = $modulo['codigomodulos'];
                $enc = encrypt("$idUser&$idCurso&$idTurma&$idModulo", 'e');

                // Busca quantidade de lições do módulo
                $queryLicoes = $con->prepare("
                SELECT * FROM a_aluno_publicacoes_cursos 
                INNER JOIN new_sistema_publicacoes_PJA 
                ON codigopublicacoes = idpublicacaopc 
                WHERE idmodulopc = :idmodulo AND a_aluno_publicacoes_cursos.visivelpc = '1'
                ORDER BY ordempc ASC
            ");
                $queryLicoes->bindParam(":idmodulo", $idModulo);
                $queryLicoes->execute();
                $quantLicoes = $queryLicoes->rowCount();

                // Busca quantidade de lições assistidas
                $queryAssistidas = $con->prepare("
                SELECT * FROM a_aluno_andamento_aula 
                WHERE idalunoaa = :iduser AND idmoduloaa = :idmodulo
            ");
                $queryAssistidas->bindParam(":iduser", $idUser);
                $queryAssistidas->bindParam(":idmodulo", $idModulo);
                $queryAssistidas->execute();
                $quantAssistidas = $queryAssistidas->rowCount();

                // Calcula progresso do módulo
                $perc = ($quantLicoes > 0) ? ($quantAssistidas / $quantLicoes) * 100 : 0;
                $percFormatado = number_format($perc, 0);
                $corBarra = $perc < 25 ? 'bg-danger' : ($perc < 70 ? 'bg-warning text-dark' : 'bg-success');

                // Busca imagem do módulo
                $arquivo = $raizSite . "/img/nomodulo.png";
                $queryFoto = $con->prepare("
                SELECT categorias.pasta, fotos.foto FROM new_sistema_categorias_PJA AS categorias
                INNER JOIN new_sistema_midias_fotos_PJA AS fotos ON categorias.pasta = fotos.pasta
                WHERE fotos.tipo = 7 AND fotos.codmodulomfp = :idmodulo
            ");
                $queryFoto->bindParam(":idmodulo", $idModulo);
                $queryFoto->execute();
                if ($fotoModulo = $queryFoto->fetch(PDO::FETCH_ASSOC)) {
                    $arquivo = $raizSite . "/fotos/midias/" . $fotoModulo['pasta'] . "/" . $fotoModulo['foto'];
                }

                // Busca tempo total de vídeo
                $queryTempo = $con->prepare("
                SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
                FROM a_curso_videoaulas 
                WHERE idmodulocva = :idmodulo AND online = '1'
            ");
                $queryTempo->bindParam(":idmodulo", $idModulo);
                $queryTempo->execute();
                $totalSeg = (int)($queryTempo->fetchColumn() ?? 0);
                $horas = floor($totalSeg / 3600);
                $min = floor(($totalSeg % 3600) / 60);
                $seg = $totalSeg % 60;
                $tempoTotal = $horas > 0 ? sprintf('%d:%02d:%02d', $horas, $min, $seg) : sprintf('%02d:%02d', $min, $seg);
            ?>

                <!-- Cartão do Módulo -->
                <div class="col" style="position: relative;">
                    <div class="card shadow-lg border-0 rounded-4 text-white module-card"

                        style="background-image: url('<?= $arquivo ?>'); background-size: cover; background-position: center; min-width: 250px; height: 390px; cursor: pointer;">

                        <div onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')" class="card-body p-3" style="background: rgba(0,0,0,0.25); height: 100%; position: relative;">

                            <!-- Progresso -->
                            <?php if ($quantLicoes > 0): ?>
                                <div class="position-absolute top-0 start-0 m-3 d-flex align-items-center justify-content-center <?= $corBarra ?>" style="width: 80px; height: 80px; border-radius: 50%; font-size: 1.4rem; font-weight: bold;">
                                    <?= $percFormatado ?>%
                                </div>
                            <?php endif; ?>

                            <!-- Nome do módulo -->
                            <div class="position-absolute top-50 start-50 translate-middle text-center w-100">
                                <h5 class="fw-bold text-white" style="text-shadow: 0 0 6px #000;">
                                    <i class="bi bi-journal-code me-2"></i><?= $modulo['modulo'] ?>
                                </h5>
                            </div>

                            <!-- Rodapé com informações -->
                            <?php

                            $queryUltimoAcesso = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idalunoaa = :idusuario AND idturmaaa = :idturma AND idmoduloaa = :idmodulo ORDER BY dataaa DESC LIMIT 1 ");
                            $queryUltimoAcesso->bindParam(":idusuario", $idUser);
                            $queryUltimoAcesso->bindParam(":idturma", $idTurma);
                            $queryUltimoAcesso->bindParam(":idmodulo", $idModulo);
                            // Executa a consulta
                            $queryUltimoAcesso->execute();
                            $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
                            $ultimadata   = isset($rwUltAcesso['dataaa'])    ? databr($rwUltAcesso['dataaa'])    : 'Sem registro';
                            $ultihorai   = isset($rwUltAcesso['horaaa'])    ? horabr($rwUltAcesso['horaaa'])    : 'Sem registro';


                            ?>
                            <div class="position-absolute bottom-0 start-0 w-100 p-3" style="font-size: 0.85rem;">
                                <p class="mb-1"><i class="bi bi-clock-history me-1"></i> <strong>Horas aula:</strong> <?= $tempoTotal ?></p>
                                <p class="mb-1"><i class="bi bi-calendar-check me-1"></i> <strong>Último acesso:</strong> <?= $ultimadata ?></p>
                                <p class="mb-1">📚 <strong>Lições:</strong>


                                    <?= $quantLicoes ?> | ✅ <strong>Assistidas:</strong> <?= $quantAssistidas ?>
                                </p>

                                <?php if ($quantLicoes > 0): ?>
                                    <div class="progress" style="height: 10px; background-color: rgba(255,255,255,0.2);">
                                        <div class="progress-bar <?= $corBarra ?>" style="width: <?= $percFormatado ?>%;"></div>
                                    </div>
                                <?php endif; ?>
                                <?php $enc = encrypt($idUser . "&" . $idCurso . "&" . $idModulo, $action = 'e'); ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($codigoUser == 1): ?>
                        <div style="position: absolute;top: 10px; right: 10px;background: rgba(208, 48, 11, 0.5);color:white; padding: 5px; border-radius: 5px;z-index: 9100;">
                            <a target="_blank" class="btn" href="../../pdf/view-pdf.php?var=<?= $enc; ?>">PDF</a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        </div>
    </div>
</section>