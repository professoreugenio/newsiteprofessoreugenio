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
    <!-- Card da turma -->
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
    <div class="card-turma" onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')" style="background-image: url('<?= $arquivo; ?>');" data-aos="zoom-in">
        <div class="topo">
            <?= htmlspecialchars($modulo['modulo']) ?>
            <!-- Progresso -->
            <?php if ($quantLicoes > 0): ?>
                <div class="position-absolute m-3 d-flex align-items-center justify-content-center <?= $corBarra ?>" style="width: 70px; height: 70px; border-radius: 50%; font-size:1.2rem; font-weight: bold;left:0">
                    <?= $percFormatado ?>%
                </div>
            <?php endif; ?>
        </div>
        <div class="rodape">
            <div class="data">Horas aula:</strong> <?= $tempoTotal ?> </div>
            <div class="data"><strong>Último acesso:</strong> <?= $ultimadata ?></div>
            <p class="mb-1">📚 <strong>Lições:</strong>
                <?= $quantLicoes ?> | ✅ <strong>Assistidas:</strong> <?= $quantAssistidas ?>
            </p>
            <div class="abrir">Abrir</div>
        </div>
    </div>
<?php endforeach; ?>