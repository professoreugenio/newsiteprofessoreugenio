<?php
// 1) Último módulo acessado
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

// 2) Módulos visíveis
$queryModulos = $con->prepare("
    SELECT * 
    FROM new_sistema_modulos_PJA 
    WHERE codcursos = :id AND visivelm = '1' 
    ORDER BY ordemm
");
$queryModulos->bindParam(":id", $idCurso, PDO::PARAM_INT);
$queryModulos->execute();
$modulos = $queryModulos->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Orientação -->
<p class="text-center lead mb-4 mt-4">
    <i class="bi bi-mouse3"></i> Clique no <strong>nome do módulo</strong> para acessar as aulas.
</p>

<!-- Grade de cards -->
<div class="row g-3 justify-content-center mb-4" id="lista-modulos">

    <?php
    foreach ($modulos as $modulo):
        $idModulo   = (int)$modulo['codigomodulos'];
        $nomeModulo = trim((string)$modulo['modulo']);
        $enc        = encrypt("$idUser&$idCurso&$idTurma&$idModulo", 'e');

        // Quantidade de lições
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
        SELECT 1 
        FROM a_aluno_andamento_aula 
        WHERE idalunoaa = :iduser AND idmoduloaa = :idmodulo
    ");
        $queryAssistidas->bindParam(":iduser", $idUser, PDO::PARAM_INT);
        $queryAssistidas->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
        $queryAssistidas->execute();
        $quantAssistidas = $queryAssistidas->rowCount();

        // Percentual
        $perc = ($quantLicoes > 0) ? ($quantAssistidas / $quantLicoes) * 100 : 0;
        $percFormatado = (int)number_format($perc, 0);
        if ($percFormatado > 100) $percFormatado = 100;

        // Cor do gradiente: preto → bgcolorsm
        $rawColor  = trim((string)($modulo['bgcolorsm'] ?? ''));
        $safeColor = (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $rawColor)) ? $rawColor : '#0d6efd';

        // Destaque (opcional) se for o módulo atual
        $isAtual = ($idModulo === $moduloAtualId);
        $bordaClasse = $isAtual ? 'ring-current' : '';
    ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <!-- Card com gradiente e posição relativa para ancorar o círculo -->
            <div class="card h-100 shadow-sm border-0 position-relative <?= $bordaClasse ?>"
                style="
           border-radius: 1rem;
           overflow: hidden;
           background: linear-gradient(135deg, #000 0%, <?= htmlspecialchars($safeColor) ?> 100%);
         ">

                <a href="actionCurso.php?mdl=<?= $enc ?>" class="text-decoration-none text-white">
                    <div class="card-body text-white p-3 pe-5 position-relative" style="cursor:pointer;">
                        <!-- Nome do módulo -->
                        <div class="fw-semibold d-inline-block mb-2" style="font-size:1.06rem;">
                            <?= htmlspecialchars($nomeModulo) ?>
                        </div>

                        <!-- Totais -->
                        <div class="small opacity-75">
                            <span class="me-3">📚 <strong><?= (int)$quantLicoes ?></strong> <?= ($quantLicoes === 1 ? 'aula' : 'aulas') ?></span>
                            <span>✅ <strong><?= (int)$quantAssistidas ?></strong> <?= ($quantAssistidas === 1 ? 'assistida' : 'assistidas') ?></span>
                        </div>

                        <!-- Link invisível para estender a área clicável -->
                        <span class="stretched-link"></span>
                    </div>
                </a>


                <!-- Círculo laranja menor com percentual, alinhado à direita -->
                <?php
                // Determinar cor do círculo de acordo com o percentual
                if ($percFormatado < 25) {
                    $circleColor = '#dc3545'; // vermelho
                    $textColor   = '#fff';
                } elseif ($percFormatado < 70) {
                    $circleColor = '#ffc107'; // amarelo
                    $textColor   = '#000';
                } else {
                    $circleColor = '#198754'; // verde
                    $textColor   = '#fff';
                }
                ?>
                <!-- Círculo colorido dinâmico -->
                <div class="percent-badge"
                    style="
       background: <?= $circleColor ?>;
       color: <?= $textColor ?>;
     ">
                    <?= $percFormatado ?>%
                </div>

            </div>
        </div>
    <?php endforeach; ?>

</div>

<!-- Estilos -->
<style>
    #lista-modulos .card {
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }

    #lista-modulos .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 22px rgba(0, 0, 0, .22);
        filter: brightness(1.02);
    }

    /* “Aura” sutil para o módulo atual (classe aplicada condicionalmente) */
    #lista-modulos .ring-current {
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.28);
    }

    /* Círculo menor, fixado no lado direito e centralizado verticalmente */
    #lista-modulos .percent-badge {
        position: absolute;
        bottom: -23px;
        right: 5px;
        /* distancia da borda direita */
        transform: translateY(-50%);
        width: 56px;
        /* círculo menor */
        height: 56px;
        border-radius: 50%;
        background: #FF9C00;
        /* laranja */
        color: #fff;
        font-weight: 700;
        font-size: .95rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            inset 0 2px 6px rgba(0, 0, 0, .18),
            0 6px 12px rgba(0, 0, 0, .20);
        pointer-events: none;
        /* não intercepta o clique do stretched-link */
    }
</style>