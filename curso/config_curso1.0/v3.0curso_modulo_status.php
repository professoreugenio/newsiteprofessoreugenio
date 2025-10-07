<?php
// Contagem de aulas
$totalAssistidas = 0;
$totalNaoAssistidas = 0;

foreach ($fetchTodasLicoes as $value) {
    $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
        WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario AND idcursoaa = :idcurso");
    $query->bindParam(":codigoaula", $value['codigopublicacoes']);
    $query->bindParam(":codigousuario", $codigousuario);
    $query->bindParam(":idcurso", $codigocurso);
    $query->execute();

    if ($query->fetch(PDO::FETCH_ASSOC)) {
        $totalAssistidas++;
    } else {
        $totalNaoAssistidas++;
    }
}
?>
<?php

?>
<div class="container">
    <div class="row align-items-center">
        <div class="col-md-12">
            <div id="cabecalhoAulas" class="p-4 bg-dark text-light rounded-4 shadow-lg mb-4 border border-secondary">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                    <div>
                        <h6 class="mb-1 text-uppercase text-muted"><?= $nomeTurma; ?></h6>
                        <h2 class="fw-bold text-white mb-2">
                            <?= $nmmodulo; ?>
                            <span class="badge <?= $corBarra ?> ms-2 align-middle"><?= $perc; ?>%</span>
                        </h2>
                        <div class="mt-3">
                            <h6 class="text-light-50 mb-1">📌 Última aula assistida:</h6>
                            <p style="background-color: #fec041; padding: 10px 20px; border-radius: 25px;color: #000000; cursor: pointer;" class="mb-0 fw-medium" style="cursor: pointer;" onclick="window.location.href='actionCurso.php?lc=<?= $encUltimaId; ?>';">
                                <i class="bi bi-arrow-return-right me-1"></i> <?= $tituloultimaaula; ?>
                            </p>

                            <?php require 'config_curso1.0/require_CountAulas.php'; ?>
                        </div>
                    </div>
                    <a class="btn btn-warning btn-sm mt-3 mt-md-0" href="./">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> + MÓDULOS
                    </a>
                </div>

                <!-- Botões de filtro -->
                <div class="d-flex justify-content-start align-items-center flex-wrap border-top pt-3">
                    <div class="btn-group" role="group">
                        <button id="btnNaoAssistidas" class="btn btn-sm btn-outline-primary me-2 active">
                            <i class="bi bi-play-circle me-1"></i> Aulas <span class="badge bg-roxo"><?= $totalNaoAssistidas; ?></span>
                        </button>
                        <button id="btnAssistidas" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-check-circle me-1"></i> Aulas Assistidas <span class="badge bg-roxo"><?= $totalAssistidas; ?></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- AQUI A LISTA NUMÉRICA -->

            <?php
            // Gerar a sequência numérica de lições
            echo '<div class="mb-4 d-flex flex-wrap gap-2">';

            foreach ($fetchTodasLicoes as $aula) {
                $ordem = $aula['ordempc'];
                $titulo = $aula['titulo'];
                $idAula = $aula['idpublicacaopc'];
                $link = 'actionCurso.php?lc=' . encrypt($idAula, 'e');



                // Verifica se essa é a última aula assistida
                $isUltima = ($rwUltimaaula && $rwUltimaaula['idpublicaa'] == $aula['codigopublicacoes']);

                // Estilo para a bolinha
                $circleClass = $isUltima
                    ? 'bg-warning text-dark fw-bold border border-dark'
                    : 'bg-secondary text-white';

                echo '
    <a href="' . $link . '" class="text-decoration-none">
        <div class="rounded-circle d-flex justify-content-center align-items-center ' . $circleClass . '" 
             style="width: 40px; height: 40px;" 
             title="' . htmlspecialchars($titulo) . '">
            ' . $ordem . '
        </div>
    </a>';
            }

            echo '</div>';
            ?>


            <div class="listaLicoes">

                <!-- AULAS NÃO ASSISTIDAS -->
                <div id="licoesNaoAssistidas">
                    <?php
                    $temNaoAssistidas = false;
                    foreach ($fetchTodasLicoes as $key => $value):
                        $codigoaulas = $value['codigopublicacoes'];
                        $idaulaoriginal = $value['idpublicacaopc'];
                        $ordem = $value['ordempc'];

                        // Verifica se já assistiu
                        $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario AND idcursoaa = :idcurso");
                        $query->bindParam(":codigoaula", $codigoaulas);
                        $query->bindParam(":codigousuario", $codigousuario);
                        $query->bindParam(":idcurso", $codigocurso);
                        $query->execute();
                        if ($query->fetch()) continue;

                        $temNaoAssistidas = true;

                        // Tempo da aula
                        $query = $con->prepare("SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos 
                FROM a_curso_videoaulas WHERE idpublicacaocva = :idpublicacao");
                        $query->bindParam(":idpublicacao", $idaulaoriginal);
                        $query->execute();
                        $segundos = (int)($query->fetchColumn() ?? 0);
                        $tempoTotal = sprintf('<strong>%02d:%02d</strong>', floor($segundos / 60), $segundos % 60);

                        // Status da atividade
                        $atividadeStatus = '<span class="text-secondary ms-2 small">Nenhuma atividade</span>';
                        $checkAtividade = $con->prepare("SELECT * FROM a_curso_questionario WHERE idpublicacaocq = :idaula");
                        $checkAtividade->bindParam(":idaula", $idaulaoriginal);
                        $checkAtividade->execute();
                        if ($checkAtividade->fetch()) {
                            $checkResposta = $con->prepare("SELECT * FROM a_curso_questionario_resposta 
                    WHERE idaulaqr = :idaula AND idalunoqr = :idaluno");
                            $checkResposta->bindParam(":idaula", $idaulaoriginal);
                            $checkResposta->bindParam(":idaluno", $codigousuario);
                            $checkResposta->execute();
                            $atividadeStatus = $checkResposta->fetch()
                                ? '<span class="text-success ms-2 small">Atividade OK</span>'
                                : '<span class="text-danger ms-2 small">Atividade pendente</span>';
                        }

                        $enc = encrypt($idaulaoriginal, 'e');

                        $aulaLiberada = $aula['aulaliberadapc'];
                        if ($aulaLiberada == '1'):
                            $lock = ' <i class="bi bi-lock-fill text-success"></i>';

                        else:
                            $lock = '<i class="bi bi-unlock-fill text-danger"></i>';;
                        endif;
                    ?>
                        <div class="licao">
                            <div class="d-flex justify-content-between align-items-center w-100" onclick="window.location.href='actionCurso.php?lc=<?= $enc; ?>';">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-warning text-dark me-2"><?= $tempoTotal; ?></span>
                                    <span class="titulo-licao">
                                        <span class="badge bg-danger me-2">*<?= $ordem; ?></span>
                                        <i class="bi bi-play-circle me-2"></i>
                                        <?= $lock ?>
                                        <?= $value['titulo'] . $atividadeStatus; ?>
                                        <?php if ($codigoUser == '1'): ?>
                                            <?= $idaulaoriginal; ?>-
                                            <?= $codigomodulo; ?>
                                        <?php endif; ?>
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

                <!-- AULAS ASSISTIDAS -->
                <div id="licoesAssistidas" style="display: none;">
                    <?php
                    foreach ($fetchTodasLicoes as $key => $value):
                        $codigoaulas = $value['codigopublicacoes'];
                        $idaulaoriginal = $value['idpublicacaopc'];
                        $ordem = $value['ordempc'];

                        // Verifica se a aula foi assistida
                        $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
            WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario AND idcursoaa = :idcurso");
                        $query->bindParam(":codigoaula", $codigoaulas);
                        $query->bindParam(":codigousuario", $codigousuario);
                        $query->bindParam(":idcurso", $codigocurso);
                        $query->execute();
                        if (!$query->fetch()) continue;

                        // Tempo total da aula
                        $query = $con->prepare("SELECT SUM(TIME_TO_SEC(totalhoras)) AS totalSegundos 
            FROM a_curso_videoaulas WHERE idpublicacaocva = :idpublicacao");
                        $query->bindParam(":idpublicacao", $idaulaoriginal);
                        $query->execute();
                        $segundos = (int)($query->fetchColumn() ?? 0);
                        $tempoTotal = sprintf('<strong>%02d:%02d</strong>', floor($segundos / 60), $segundos % 60);

                        // Verificação de atividade
                        $atividadeStatus = '<span class="atividade-status atividade-nenhuma">Nenhuma atividade</span>';
                        $checkAtividade = $con->prepare("SELECT * FROM a_curso_questionario WHERE idpublicacaocq = :idaula");
                        $checkAtividade->bindParam(":idaula", $idaulaoriginal);
                        $checkAtividade->execute();
                        if ($checkAtividade->fetch()) {
                            $checkResposta = $con->prepare("SELECT * FROM a_curso_questionario_resposta 
                WHERE idaulaqr = :idaula AND idalunoqr = :idaluno");
                            $checkResposta->bindParam(":idaula", $idaulaoriginal);
                            $checkResposta->bindParam(":idaluno", $codigousuario);
                            $checkResposta->execute();
                            $atividadeStatus = $checkResposta->fetch()
                                ? '<span class="atividade-status atividade-ok">Atividade OK</span>'
                                : '<span class="atividade-status atividade-pendente">Atividade Pendente</span>';
                        }

                        $enc = encrypt($idaulaoriginal, 'e');

                        $aulaLiberada = $value['aulaliberadapc'];
                        if ($aulaLiberada == '1'):
                            $lock = ' <i class="bi bi-lock-fill text-success"></i>';

                        else:
                            $lock = '<i class="bi bi-unlock-fill text-danger"></i>';;
                        endif;
                    ?>
                        <div class="licao lida">
                            <div class="d-flex justify-content-between align-items-center w-100" onclick="window.location.href='actionCurso.php?lc=<?= $enc; ?>';">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-secondary text-white me-2"><?= $tempoTotal; ?></span>
                                    <span class="titulo-licao text-muted d-flex flex-wrap align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-success me-2"></i>
                                        <?= $lock; ?><?= $ordem; ?>. <?= $value['titulo'] . ' ' . $atividadeStatus; ?>
                                        <?php if ($codigoUser == '1'): ?>
                                            <?= $idaulaoriginal; ?>
                                            -<?= $codigomodulo; ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>


            </div>

        </div>
    </div>
</div>

<!-- Script para alternar os filtros -->
<script>
    $(document).ready(function() {
        $("#btnNaoAssistidas").click(function() {
            $("#licoesNaoAssistidas").show();
            $("#licoesAssistidas").hide();
            $(this).addClass("active btn-primary").removeClass("btn-outline-primary");
            $("#btnAssistidas").removeClass("active btn-success").addClass("btn-outline-success");
        });

        $("#btnAssistidas").click(function() {
            $("#licoesNaoAssistidas").hide();
            $("#licoesAssistidas").show();
            $(this).addClass("active btn-success").removeClass("btn-outline-success");
            $("#btnNaoAssistidas").removeClass("active btn-primary").addClass("btn-outline-primary");
        });
    });
</script>



<button class="btn btn-danger btn-sm" onclick="limparAndamentoAluno(<?= $codigousuario ?>, <?= $idTurma ?>)">
    <i class="bi bi-trash3-fill me-1"></i> Assisitir Novamente
</button>
<?php if ($codigoUser == 1): ?>
    <button class="btn btn-danger btn-sm" onclick="limparAndamentoTurma(<?= $codigousuario ?>, <?= $idTurma ?>)">
        <i class="bi bi-trash3-fill me-1"></i> Resetar Acesso da Turma <?= $codigousuario ?>, <?= $idTurma ?>
    </button>
    <button class="btn btn-danger btn-sm" onclick="BloquearLicoesdoModulo(<?= $codigomodulo ?>)">
        <i class="bi bi-trash3-fill me-1"></i> Bloquear lições do módulo <?= $codigomodulo ?>
    </button>

    <script>
        function BloquearLicoesdoModulo(idModulo) {
            if (!confirm('Tem certeza que deseja limpar todos os registros de andamento desta turma?')) return;

            fetch('config_curso1.0/ajax_BloquearTodasasAulas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        idmodulo: idModulo
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.sucesso) {
                        alert('Lições bloqueadas com sucesso.');
                        // location.reload();
                    } else {
                        alert('Erro: ' + res.mensagem);
                    }
                })
                .catch(() => alert('Erro na requisição.'));
        }
    </script>
    <script>
        function limparAndamentoTurma(idAluno, idTurma) {
            if (!confirm('Tem certeza que deseja limpar todos os registros de andamento desta turma?')) return;

            fetch('config_curso1.0/ajax_resetHistoricoAssisitidasAlunos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        idaluno: idAluno,
                        idturma: idTurma
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (res.sucesso) {
                        alert('Andamentos apagados com sucesso.');
                        location.reload();
                    } else {
                        alert('Erro: ' + res.mensagem);
                    }
                })
                .catch(() => alert('Erro na requisição.'));
        }
    </script>


<?php endif; ?>
<script>
    function limparAndamentoAluno(idAluno, idTurma) {
        if (!confirm('Tem certeza que deseja limpar todos os registros de andamento desta turma?')) return;

        fetch('config_curso1.0/ajax_resetHistoricoAssisitidas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    idaluno: idAluno,
                    idturma: idTurma
                })
            })
            .then(res => res.json())
            .then(res => {
                if (res.sucesso) {
                    alert('Andamentos apagados com sucesso.');
                    location.reload();
                } else {
                    alert('Erro: ' + res.mensagem);
                }
            })
            .catch(() => alert('Erro na requisição.'));
    }
</script>

<?php require 'config_curso1.0/require_ModalAtividadesPendentes.php' ?>