<<<<<<< HEAD
<?php
// 1) Buscar o último módulo acessado pelo aluno na turma (global)
$queryUltimoGeral = $con->prepare("
    SELECT idmoduloaa 
    FROM a_aluno_andamento_aula
    WHERE idalunoaa = :idusuario AND idturmaaa = :idturma
    ORDER BY dataaa DESC, horaaa DESC
    LIMIT 1
=======
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
                $bgcolor = $valDropDown['bgcolorsm'];
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
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card module-card w-100 shadow-lg border-0 rounded-4"
                        style="background: linear-gradient(135deg,<?= $bgcolor; ?> ,rgb(12, 10, 17) ); color: #f8f9fa;"
                        onclick="abrirPagina('actionCurso.php?mdl=<?= $enc ?>')">
                        <div class="card-body d-flex flex-column justify-content-center position-relative">
                            <?php if ($quantLicoes > $quantAssisitdas): ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-warning text-dark shadow-sm px-4 py-2"
                                    style="margin-top: -12px; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3); letter-spacing: 0.5px;">
                                    <i class="bi bi-play-circle-fill me-2"></i> Aulas para assistir
                                </span>
                            <?php endif; ?>
                            <?php if ($quantLicoes > 0): ?>
                                <div class="d-flex align-items-center justify-content-center <?php echo $corBarra;  ?>" style="width: 80px; height:80px; box-shadow: 0 0 6px rgba(0,0,0,0.2); font-size: 0.9rem; position: absolute;z-index:1000; top: 65px; right: 10px;border-radius:50%;font-size: 1.4rem;font-weight: 600;">
                                    <?php echo $perc;  ?>%
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <h5 class="fw-bold mb-3 px-3 py-2 rounded-3"
                                    style="background: rgba(255, 255, 255, 0.15); font-size: 1.25rem; box-shadow: 0 0 8px rgba(0,0,0,0.2);">
                                    <i class="bi bi-journal-code me-2"></i><?= $valDropDown['modulo'] ?>
                                </h5>
                                <?php
                                $query = $con->prepare("
    SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(totalhoras))) AS somatotal 
    FROM a_curso_videoaulas 
    WHERE idmodulocva = :idmodulo AND online = '1'
>>>>>>> 232351309d3c79e0b4415c59156387067a829324
");
$queryUltimoGeral->bindParam(":idusuario", $idUser, PDO::PARAM_INT);
$queryUltimoGeral->bindParam(":idturma", $idTurma, PDO::PARAM_INT);
$queryUltimoGeral->execute();
$moduloAtualId = (int)($queryUltimoGeral->fetchColumn() ?? 0);

// 2) Buscar os módulos visíveis do curso
$queryModulos = $con->prepare("
    SELECT codigomodulos, modulo 
    FROM new_sistema_modulos_PJA 
    WHERE codcursos = :id AND visivelm = '1' 
    ORDER BY ordemm
");
$queryModulos->bindParam(":id", $idCurso, PDO::PARAM_INT);
$queryModulos->execute();
$modulos = $queryModulos->fetchAll(PDO::FETCH_ASSOC);

// 3) Renderizar APENAS botões com os nomes (links)
?>
<div class="d-flex flex-wrap gap-2" id="lista-modulos">
    <?php foreach ($modulos as $modulo):
        $idModulo = (int)$modulo['codigomodulos'];
        $nomeModulo = trim((string)$modulo['modulo']);
        $enc = encrypt("$idUser&$idCurso&$idTurma&$idModulo", 'e');

        // Destacar o último módulo acessado (opcional)
        $isAtual = ($idModulo === $moduloAtualId);
        $classeBtn = $isAtual ? 'btn-primary' : 'btn-outline-primary';
    ?>
        <a href="actionCurso.php?mdl=<?= $enc ?>"
            class="btn <?= $classeBtn ?> btn-sm"
            title="Ir para: <?= htmlspecialchars($nomeModulo) ?>">
            <i class="bi bi-journal-text me-1"></i><?= htmlspecialchars($nomeModulo) ?>
        </a>
    <?php endforeach; ?>
</div>