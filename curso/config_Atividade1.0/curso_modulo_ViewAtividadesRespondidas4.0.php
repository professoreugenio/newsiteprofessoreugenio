<?php
// ========= Configuração =========
$mediaPercent = 60;

// Função para normalizar texto (mantida caso queira usar em outras lógicas)
function normaliza_txt($s)
{
    $s = trim((string)$s);
    if ($s === '') return '';
    $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    $s = preg_replace('/[^A-Za-z0-9\s]/', '', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return mb_strtolower(trim($s));
}

// Recebe parâmetros (idaula / idaluno)
if (!empty($_POST['idaula'])):
    $codigoaula = $_POST['idaula'];
    echo " ";
endif;
if (!empty($_POST['idaluno'])):
    $codigousuario = $_POST['idaluno'];
endif;

// (opcional) debug simples:
echo $codigoaula . " " . $codigousuario;

// Busca questões visíveis da aula
$query = $con->prepare("SELECT * FROM a_curso_questionario 
    WHERE idpublicacaocq = :idpublicacao AND visivelcq = :visivel 
    ORDER BY ordemcq");
$query->bindParam(":idpublicacao", $codigoaula);
$visivel = "1";
$query->bindParam(":visivel", $visivel);
$query->execute();
$questoes = $query->fetchAll(PDO::FETCH_ASSOC);

// Contadores
$totalQuestoes   = 0;  // total de questões exibidas
$totalPontosMax  = 0;  // denominador (soma dos pontos possíveis)
$acertos         = 0;  // pontos obtidos

?>
<style>
    /* Compactar cards */
    .card {
        margin-bottom: .75rem !important;
    }

    .card-body {
        padding: .75rem !important;
    }

    .card-title {
        margin-bottom: .25rem !important;
        font-size: 1rem !important;
    }

    h6.text-muted {
        margin-bottom: .25rem !important;
        font-size: .85rem !important;
    }

    .alert {
        padding: .5rem .75rem !important;
        margin-bottom: .5rem !important;
        font-size: .85rem !important;
    }

    .form-check {
        margin-bottom: .0rem !important;
    }

    .form-check-label {
        font-size: .9rem !important;
        padding: .30rem .5rem !important;
        opacity: 1;
    }

    .fw-semibold {
        font-size: .9rem !important;
    }

    /* Resumo fixo compacto */
    .resumo-fixo {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        bottom: 10px;
        z-index: 1090;
        min-width: 280px;
        max-width: 92vw;
        border-radius: .75rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .2);
        font-size: .9rem;
    }

    .resumo-fixo .progress {
        height: .45rem;
    }

    .resumo-fixo .fw-bold {
        font-size: .95rem !important;
    }

    .btn-fechar-resumo {
        padding: .1rem .3rem !important;
        font-size: .8rem !important;
    }
</style>

<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="info-curso">
                <h2 class="mb-3 text-white bg-dark p-2 rounded-3 shadow-sm fs-5">
                    <i class="bi bi-patch-question-fill me-2 text-warning"></i>
                    Questionário Respondido
                </h2>

                <?php foreach ($questoes as $questao):
                    $ordem           = $questao['ordemcq'];
                    $titulo          = $questao['titulocq'];
                    $tipo            = (int)$questao['tipocq'];      // 1 = dissertativa; 2 = múltipla
                    $respostaSistema = $questao['respostacq'];       // p/ tipo 2: letra correta; p/ tipo 1: texto esperado (não usado p/ pontuar)
                    $idquestionario  = (int)$questao['codigoquestionario'];

                    // Busca a resposta do aluno
                    $stmt = $con->prepare("SELECT respostaqr FROM a_curso_questionario_resposta 
                                           WHERE idalunoqr = :idaluno AND idquestionarioqr = :idquestionario");
                    $stmt->bindParam(":idaluno", $codigousuario);
                    $stmt->bindParam(":idquestionario", $idquestionario);
                    $stmt->execute();
                    $respostaAluno = $stmt->fetchColumn();

                    $totalQuestoes++;

                    // --------- Lógica de pontuação ---------
                    if ($tipo === 1) {
                        // Tipo 1: sempre vale 1 ponto (conta no denominador).
                        $totalPontosMax += 1;
                        // Ganha 1 ponto se respondeu (não vazio).
                        if (trim((string)$respostaAluno) !== '') {
                            $acertos += 1;
                        }
                    } elseif ($tipo === 2) {
                        // Tipo 2: só pontua se existe gabarito (respostaSistema).
                        if (!empty($respostaSistema)) {
                            $totalPontosMax += 1;
                            if (!empty($respostaAluno) && strtoupper($respostaAluno) === strtoupper($respostaSistema)) {
                                $acertos += 1;
                            }
                        }
                    }
                    // ---------------------------------------
                ?>

                    <div class="card shadow-sm border-start border-4 border-info bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Atividade <?= htmlspecialchars($ordem); ?></h6>
                            <h4 class="card-title fw-bold text-dark"><?= htmlspecialchars($titulo); ?></h4>

                            <?php if ($tipo === 1): ?>
                                <div class="mt-2">
                                    <p class="fw-semibold text-primary mb-1">Resposta do Aluno <span class="text-muted">(vale 1 ponto se preenchida)</span>:</p>
                                    <div class="p-2 bg-white border rounded-2 mb-2 text-dark small">
                                        <?= nl2br(htmlspecialchars($respostaAluno ?? 'Não respondido.')); ?>
                                    </div>

                                    <?php if ($respostaSistema !== ''): ?>
                                        <p class="fw-semibold text-success mb-1">Resposta Esperada (não usada na pontuação):</p>
                                        <div class="p-2 rounded-2 text-dark small" style="background-color: #e0f3e0;">
                                            <?= nl2br(htmlspecialchars($respostaSistema)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            <?php elseif ($tipo === 2): ?>
                                <div class="mt-2">
                                    <p class="fw-semibold text-primary mb-1">
                                        <i class="bi bi-list-check me-2"></i> Escolha do Aluno:
                                    </p>
                                    <div class="mb-2">
                                        <?php
                                        $opcoes = [
                                            'A' => $questao['opcaoa'],
                                            'B' => $questao['opcaob'],
                                            'C' => $questao['opcaoc'],
                                            'D' => $questao['opcaod']
                                        ];
                                        foreach ($opcoes as $letra => $texto) {
                                            if (!$texto) continue;
                                            $isAluno   = strtoupper((string)$respostaAluno) === $letra;
                                            $isCorreta = strtoupper((string)$respostaSistema) === $letra;

                                            // Sinalização visual apenas para a escolhida pelo aluno
                                            $corClasse = '';
                                            if ($isAluno) {
                                                $corClasse = $isCorreta ? 'bg-success text-white' : 'bg-danger text-white';
                                            }
                                            $estiloLabel = "form-check-label d-block p-1 rounded-2 $corClasse";
                                        ?>
                                            <div class="form-check">
                                                <input class="form-check-input d-none" type="radio" disabled>
                                                <label class="<?= $estiloLabel ?>">
                                                    <?= $letra ?>. <?= htmlspecialchars($texto) ?>
                                                    <?php if ($isCorreta && !$isAluno): ?>
                                                        <span class="badge bg-success ms-2">Correta</span>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>

<?php
$percent = ($totalPontosMax > 0) ? round(($acertos / $totalPontosMax) * 100) : 0;
$aprovado = ($percent >= $mediaPercent);
$bgClass  = $aprovado ? 'bg-success' : 'bg-danger';
$textIcon = $aprovado ? 'bi-emoji-smile-fill' : 'bi-emoji-frown-fill';
?>

<div id="resumoPontuacao" class="resumo-fixo text-white <?= $bgClass ?>">
    <div class="d-flex align-items-center justify-content-between px-2 pt-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi <?= $textIcon ?> fs-5"></i>
            <div>
                <div class="fw-bold">Resultado</div>
                <div class="small">
                    <?= $acertos ?>/<?= $totalPontosMax ?> — <strong><?= $percent ?>%</strong>
                </div>
            </div>
        </div>
        <button class="btn btn-light btn-sm rounded-pill btn-fechar-resumo me-1 mt-1" onclick="document.getElementById('resumoPontuacao')?.remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="px-2 pb-2">
        <div class="progress mt-1">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: <?= $percent ?>%;"></div>
        </div>
    </div>
</div>