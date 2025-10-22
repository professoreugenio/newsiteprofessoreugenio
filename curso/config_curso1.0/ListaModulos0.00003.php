<?php
// 1) Buscar o último módulo acessado pelo aluno na turma (global)
$queryUltimoGeral = $con->prepare("
    SELECT idmoduloaa 
    FROM a_aluno_andamento_aula
    WHERE idalunoaa = :idusuario AND idturmaaa = :idturma
    ORDER BY dataaa DESC, horaaa DESC
    LIMIT 1
");
$queryUltimoGeral->bindParam(":idusuario", $idUser, PDO::PARAM_INT);
$queryUltimoGeral->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
$queryUltimoGeral->execute();
$moduloAtualId = (int)($queryUltimoGeral->fetchColumn() ?? 0);

// Busca os módulos visíveis do curso
$queryModulos = $con->prepare("
    SELECT * FROM new_sistema_modulos_PJA 
    WHERE codcursos = :id AND visivelm = '1' 
    ORDER BY ordemm
");
$queryModulos->bindParam(":id", $idCurso, PDO::PARAM_INT);
$queryModulos->execute();
$modulos = $queryModulos->fetchAll();

foreach ($modulos as $modulo):
    $idModulo = (int)$modulo['codigomodulos'];
    $enc = encrypt("$idUser&$idCurso&$idTurma&$idModulo", 'e');

    // Quantidade de lições do módulo
    $queryLicoes = $con->prepare("
        SELECT 1
        FROM a_aluno_publicacoes_cursos 
        INNER JOIN new_sistema_publicacoes_PJA 
            ON codigopublicacoes = idpublicacaopc 
        WHERE idmodulopc = :idmodulo 
          AND a_aluno_publicacoes_cursos.visivelpc = '1'
        ORDER BY ordempc ASC
    ");
    $queryLicoes->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
    $queryLicoes->execute();
    $quantLicoes = $queryLicoes->rowCount();

    // Lições assistidas
    $queryAssistidas = $con->prepare("
        SELECT 1 FROM a_aluno_andamento_aula 
        WHERE idalunoaa = :iduser AND idmoduloaa = :idmodulo
    ");
    $queryAssistidas->bindParam(":iduser", $idUser, PDO::PARAM_INT);
    $queryAssistidas->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
    $queryAssistidas->execute();
    $quantAssistidas = $queryAssistidas->rowCount();

    // Progresso
    $perc = ($quantLicoes > 0) ? ($quantAssistidas / $quantLicoes) * 100 : 0;
    $percFormatado = number_format($perc, 0);
    $corBarra = $perc < 25 ? 'bg-danger' : ($perc < 70 ? 'bg-warning text-dark' : 'bg-success');
    if ($percFormatado > 100) $percFormatado = 100;
    // Imagem do módulo
    $arquivo = $raizSite . "/img/nomodulo.png";
    $queryFoto = $con->prepare("
        SELECT categorias.pasta, fotos.foto 
        FROM new_sistema_categorias_PJA AS categorias
        INNER JOIN new_sistema_midias_fotos_PJA AS fotos 
            ON categorias.pasta = fotos.pasta
        WHERE fotos.tipo = 7 AND fotos.codmodulomfp = :idmodulo
        LIMIT 1
    ");
    $queryFoto->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
    $queryFoto->execute();
    if ($fotoModulo = $queryFoto->fetch(PDO::FETCH_ASSOC)) {
        $arquivo = $raizSite . "/fotos/midias/" . $fotoModulo['pasta'] . "/" . $fotoModulo['foto'];
    }

    // Tempo total de vídeo
    $queryTempo = $con->prepare("
        SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos
        FROM a_curso_videoaulas 
        WHERE idmodulocva = :idmodulo AND online = '1'
    ");
    $queryTempo->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
    $queryTempo->execute();
    $totalSeg = (int)($queryTempo->fetchColumn() ?? 0);
    $horas = floor($totalSeg / 3600);
    $min = floor(($totalSeg % 3600) / 60);
    $seg = $totalSeg % 60;
    $tempoTotal = $horas > 0 ? sprintf('%d:%02d:%02d', $horas, $min, $seg) : sprintf('%02d:%02d', $min, $seg);

    // Último acesso deste módulo (para exibir data/hora no card)
    $queryUltimoAcesso = $con->prepare("
        SELECT dataaa, horaaa 
        FROM a_aluno_andamento_aula 
        WHERE idalunoaa = :idusuario AND idturmaaa = :idturma AND idmoduloaa = :idmodulo 
        ORDER BY dataaa DESC, horaaa DESC 
        LIMIT 1
    ");
    $queryUltimoAcesso->bindParam(":idusuario", $idUser, PDO::PARAM_INT);
    $queryUltimoAcesso->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
    $queryUltimoAcesso->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
    $queryUltimoAcesso->execute();
    $rwUltAcesso = $queryUltimoAcesso->fetch(PDO::FETCH_ASSOC);
    $ultimadata = isset($rwUltAcesso['dataaa']) ? databr($rwUltAcesso['dataaa']) : 'Sem registro';
    $ultihorai  = isset($rwUltAcesso['horaaa']) ? horabr($rwUltAcesso['horaaa']) : '—';

    $encPdf = encrypt($idUser . "&" . $idCurso . "&" . $idModulo, 'e');

    // É o módulo ATUAL?
    $isAtual = ($idModulo === $moduloAtualId);
?>
    <div class="card-modulo-wrapper position-relative">


        <div class="card-modulo" style="background-image: url('<?= $arquivo; ?>');" data-aos="zoom-in">
            <?php if ($codigoUser == 1): ?>
                <div style="position: absolute; top: 60px; right: 10px; background: rgba(221, 203, 199, 0.5); color: white; padding: 0; border-radius: 5px; z-index: 9100; font-size: 0.8rem;">
                    <a target="_blank" class="btn" href="../../pdf/view-pdf.php?var=<?= $encPdf; ?>">PDF</a>
                    <a target="_blank" class="btn" href="../../phpOffice/viewWord.php?var=<?= $encPdf; ?>">Word</a>
                </div>
            <?php endif; ?>

            <div class="topo" onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')">
                <?= htmlspecialchars($modulo['modulo']) ?>
                <?php if ($quantLicoes > 0): ?>
                    <div class="position-absolute d-flex align-items-center justify-content-center <?= $corBarra ?>" style="width: 80px; height: 80px; border-radius: 50%; font-size: 1.2rem; font-weight: bold;top:-20px; right:-20px">
                        <?= $percFormatado ?>%
                    </div>
                <?php endif; ?>
            </div>

            <div class="rodape" onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')">
                <div class="data">Último acesso: <?= $ultimadata ?> <?= $ultihorai !== '—' ? 'às ' . $ultihorai : '' ?></div>
                <p class="mb-1">📚 <strong>Lições:</strong> <?= $quantLicoes ?> | ✅ <strong>Assistidas:</strong> <?= $quantAssistidas ?></p>
                <!-- Se quiser, pode exibir o tempo total: ⏱ <?= $tempoTotal ?> -->
            </div>

            <button class="btn-abrir-centro" onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')">Abrir</button>
        </div>
    </div>
<?php endforeach; ?>