<?php
// ----------------------------
// CONTAGENS (mantidas)
// ----------------------------
$totalAssistidas = 0;
$totalNaoAssistidas = 0;

foreach ($fetchTodasLicoes as $value) {
    $q = $con->prepare("SELECT 1 FROM a_aluno_andamento_aula 
        WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario AND idcursoaa = :idcurso LIMIT 1");
    $q->bindParam(":codigoaula", $value['codigopublicacoes']);
    $q->bindParam(":codigousuario", $codigousuario);
    $q->bindParam(":idcurso", $codigocurso);
    $q->execute();
    if ($q->fetch(PDO::FETCH_NUM)) $totalAssistidas++;
    else $totalNaoAssistidas++;
}

// ----------------------------
// MAPA: codigopublicacoes -> dados da lição
// ----------------------------
$mapLicoes = []; // [codigopublicacoes] => ['ordem'=>..., 'titulo'=>..., 'idpubOriginal'=>..., 'liberada'=>...]
foreach ($fetchTodasLicoes as $a) {
    $mapLicoes[(string)$a['codigopublicacoes']] = [
        'ordem'        => $a['ordempc'] ?? null,
        'titulo'       => $a['titulo'] ?? '',
        'idpubOriginal' => $a['idpublicacaopc'] ?? null,
        'liberada'     => $a['aulaliberadapc'] ?? null,
    ];
}

// ----------------------------
// ÚLTIMAS 4 AULAS ASSISTIDAS
// Tente ordenar por data/hora; se não houver, por ID do andamento
// Troque dataaa/horaaa/codigoandamento pelos nomes corretos se necessário
// ----------------------------
$sqlUltimas = "
    SELECT 
        aa.idpublicaa,
        -- concatena data e hora quando existirem; senão usa NULL
        MAX(
            COALESCE(
                STR_TO_DATE(CONCAT(aa.dataaa,' ',aa.horaaa), '%Y-%m-%d %H:%i:%s'),
                NULL
            )
        ) AS ultimo_dt,
        MAX(aa.codigoandamento) AS ultimo_id -- fallback
    FROM a_aluno_andamento_aula aa
    WHERE aa.idalunoaa = :aluno AND aa.idcursoaa = :curso
    GROUP BY aa.idpublicaa
    ORDER BY 
        CASE 
            WHEN MAX(COALESCE(STR_TO_DATE(CONCAT(aa.dataaa,' ',aa.horaaa), '%Y-%m-%d %H:%i:%s'), NULL)) IS NOT NULL 
                THEN MAX(COALESCE(STR_TO_DATE(CONCAT(aa.dataaa,' ',aa.horaaa), '%Y-%m-%d %H:%i:%s'), NULL))
            ELSE MAX(aa.codigoandamento)
        END DESC
    LIMIT 4
";
$stmtUlt = $con->prepare($sqlUltimas);
$stmtUlt->bindParam(":aluno", $codigousuario);
$stmtUlt->bindParam(":curso", $codigocurso);
$stmtUlt->execute();
$ultimasAssistidas = $stmtUlt->fetchAll(PDO::FETCH_ASSOC);

// utilitário para formatar a data
function formatarDataBR($dtStr, $fallbackId)
{
    if (!empty($dtStr)) {
        // Se vier DateTime do MySQL
        $ts = strtotime($dtStr);
        if ($ts) return date('d/m/Y H:i', $ts);
    }
    // fallback: exibe apenas um traço ou algo neutro
    return '—';
}
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
                            <?php if ($perc > 100) $perc = 100; ?>
                            <span class="badge <?= $corBarra ?> ms-2 align-middle"><?= $perc; ?>%</span>
                        </h2>

                        <?php require 'config_curso1.0/require_CountAulas.php'; ?>


                    </div>
                    <div class="d-flex gap-2 mt-3 mt-md-0">
                        <a class="btn btn-warning btn-sm" href="modulo_licoes.php">
                            <i class="bi bi-collection-play me-1"></i> Ver todas as lições
                        </a>
                        <a class="btn btn-outline-light btn-sm" href="./">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> + MÓDULOS
                        </a>
                    </div>
                </div>

                <!-- Resumo de status -->

            </div>

            <!-- ÚLTIMAS 4 AULAS ASSISTIDAS -->
            <div class="row g-3 justify-content-center">
                <?php if (!$ultimasAssistidas): ?>
                    <?php
                    // Ordena por ordempc e pega as 4 primeiras lições do módulo
                    $licoesOrdenadas = $fetchTodasLicoes;
                    usort($licoesOrdenadas, function ($a, $b) {
                        return (int)($a['ordempc'] ?? 0) <=> (int)($b['ordempc'] ?? 0);
                    });
                    $primeirasLicoes = array_slice($licoesOrdenadas, 0, 4);
                    ?>

                    <?php if (empty($primeirasLicoes)): ?>
                        <div class="col-12">
                            <div class="alert alert-secondary border-0">
                                Nenhuma lição encontrada neste módulo.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($primeirasLicoes as $a):
                            $ordem  = $a['ordempc'] ?? null;
                            $titulo = $a['titulo'] ?? '';
                            $idOrig = $a['idpublicacaopc'] ?? null;   // id da aula original
                            $lib    = $a['aulaliberadapc'] ?? '0';     // '1' liberada, '0' bloqueada

                            // link criptografado para a aula
                            $encAula = encrypt($idOrig, 'e');

                            // Verifica existência de questionário
                            $temQuiz = false;
                            if (!empty($idOrig)) {
                                $checkQ = $con->prepare("SELECT 1 FROM a_curso_questionario WHERE idpublicacaocq = :idaula LIMIT 1");
                                $checkQ->bindParam(":idaula", $idOrig);
                                $checkQ->execute();
                                if ($checkQ->fetch()) $temQuiz = true;
                            }
                        ?>
                            <div class="col-12 col-md-6 col-xl-3">
                                <div class="card h-100 bg-body-tertiary border-0 shadow-sm rounded-4 overflow-hidden">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge <?= $lib == '1' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $lib == '1' ? '<i class="bi bi-unlock-fill"></i> Liberada' : '<i class="bi bi-lock-fill"></i> Bloqueada' ?>
                                            </span>
                                            <span class="badge bg-warning text-dark">Lição <?= htmlspecialchars($ordem ?? '—'); ?></span>
                                        </div>

                                        <h6 class="fw-semibold mb-2 text-truncate" title="<?= htmlspecialchars($titulo) ?>">
                                            <?= htmlspecialchars($titulo) ?>
                                        </h6>

                                        <div class="small text-muted mb-3">
                                            <i class="bi bi-stars me-1"></i> Comece por aqui
                                        </div>

                                        <div class="mt-auto d-grid gap-2">
                                            <?php if ($lib == '1'): ?>
                                                <a href="actionCurso.php?lc=<?= $encAula; ?>" class="btn btn-dark">
                                                    <i class="bi bi-play-circle me-1"></i> Ir para a aula *
                                                </a>
                                            <?php else: ?>
                                                <!-- <span class="btn disabled btn-outline-secondary">
                                                    <i class="bi bi-ban me-1"></i> Aula bloqueada
                                                </span> -->
                                            <?php endif; ?>
                                            <!-- 
                                            <?php if ($temQuiz): ?>
                                                <a href="modulo_atividades.php?lc=<?= $encAula; ?>" class="btn btn-outline-warning">
                                                    <i class="bi bi-clipboard-check me-1"></i> Atividades
                                                </a>
                                            <?php else: ?>
                                                <span class="btn disabled btn-outline-secondary">
                                                    <i class="bi bi-clipboard-x me-1"></i> Sem avaliação
                                                </span>
                                            <?php endif; ?>

                                             -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                <?php else: ?>

                    <?php foreach ($ultimasAssistidas as $row):
                        $codPub = (string)$row['idpublicaa']; // corresponde a codigopublicacoes
                        if (!isset($mapLicoes[$codPub])) continue; // segurança

                        $ordem   = $mapLicoes[$codPub]['ordem'];
                        $titulo  = $mapLicoes[$codPub]['titulo'];
                        $idOrig  = $mapLicoes[$codPub]['idpubOriginal'];
                        $lib     = $mapLicoes[$codPub]['liberada'];
                        $encAula = encrypt($idOrig, 'e');

                        $ultimoAcesso = formatarDataBR($row['ultimo_dt'], $row['ultimo_id']);

                        // Verifica existência de questionário e resposta
                        $temQuiz = false;
                        $respondido = false;
                        if (!empty($idOrig)) {
                            $checkQ = $con->prepare("SELECT 1 FROM a_curso_questionario WHERE idpublicacaocq = :idaula LIMIT 1");
                            $checkQ->bindParam(":idaula", $idOrig);
                            $checkQ->execute();
                            if ($checkQ->fetch()) {
                                $temQuiz = true;
                                $checkR = $con->prepare("SELECT 1 FROM a_curso_questionario_resposta 
                                    WHERE idaulaqr = :idaula AND idalunoqr = :aluno LIMIT 1");
                                $checkR->bindParam(":idaula", $idOrig);
                                $checkR->bindParam(":aluno", $codigousuario);
                                $checkR->execute();
                                if ($checkR->fetch()) $respondido = true;
                            }
                        }
                    ?>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card h-100 bg-body-tertiary border-0 shadow-sm rounded-4 overflow-hidden">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span style="display: none;" class="badge <?= $lib == '1' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $lib == '1' ? '<i class="bi bi-unlock-fill"></i> Liberada' : '<i class="bi bi-lock-fill"></i> Bloqueada' ?>
                                        </span>
                                        <span class="badge bg-warning text-dark">Lição <?= htmlspecialchars($ordem ?? '—'); ?></span>
                                    </div>
                                    <h6 class="fw-semibold mb-2 text-truncate" title="<?= htmlspecialchars($titulo) ?>">
                                        <?= htmlspecialchars($titulo) ?>
                                    </h6>
                                    <div class="small text-muted mb-3">
                                        <i class="bi bi-clock-history me-1"></i> Último acesso: <br> <strong><?= $ultimoAcesso; ?></strong>
                                    </div>

                                    <div class="mt-auto d-grid gap-2">
                                        <a href="actionCurso.php?lc=<?= $encAula; ?>" class="btn btn-dark">
                                            <i class="bi bi-play-circle me-1"></i> Ir para a aula
                                        </a>
                                        <?php if ($temQuiz && !$respondido): ?>
                                            <!-- <a href="modulo_atividades.php?lc=<?= $encAula; ?>" class="btn btn-outline-warning">

                                                <i class="bi bi-clipboard-check me-1"></i> Atividades
                                            </a> -->
                                            <!-- <?php elseif ($temQuiz && $respondido): ?>
                                            <span class="btn disabled btn-outline-secondary">
                                                <i class="bi bi-check2-circle me-1"></i> Avaliada
                                            </span>
                                        <?php else: ?>
                                            <span class="btn disabled btn-outline-secondary">
                                                <i class="bi bi-clipboard-x me-1"></i> Sem avaliação
                                            </span>
                                        <?php endif; ?> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- CTA: Ver todas as lições -->
            <div class="text-center my-4">
                <a class="btn btn-warning btn-lg px-4" href="modulo_licoes.php">
                    <i class="bi bi-collection-play me-2"></i> Acessar todas as lições
                </a>
            </div>



        </div>
    </div>
</div>

<!-- JS existente de reset (mantido) -->
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
            .then(r => r.json())
            .then(res => {
                if (res.sucesso) {
                    alert('Lições bloqueadas com sucesso.');
                } else {
                    alert('Erro: ' + res.mensagem);
                }
            })
            .catch(() => alert('Erro na requisição.'));
    }

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
            .then(r => r.json())
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
            .then(r => r.json())
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