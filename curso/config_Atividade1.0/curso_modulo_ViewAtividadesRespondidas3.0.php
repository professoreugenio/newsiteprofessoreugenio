<?php
// ========= Configuração =========
$mediaPercent = 60; // média mínima para ficar verde

// Função para normalizar texto (comparação de dissertativa)
function normaliza_txt($s)
{
    $s = trim((string)$s);
    if ($s === '') return '';
    // Remover acentos
    $s = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
    $s = preg_replace('/[^A-Za-z0-9\s]/', '', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return mb_strtolower(trim($s));
}

// ========= Consulta das questões =========
$query = $con->prepare("SELECT * FROM a_curso_questionario 
    WHERE idpublicacaocq = :idpublicacao AND visivelcq = :visivel 
    ORDER BY ordemcq");
$query->bindParam(":idpublicacao", $codigoaula);
$visivel = "1";
$query->bindParam(":visivel", $visivel);
$query->execute();
$questoes = $query->fetchAll(PDO::FETCH_ASSOC);

// Contadores
$totalQuestoes = 0;              // total de questões (visíveis)
$totalCorrigiveis = 0;           // total de questões passíveis de correção automática
$acertos = 0;                    // total de acertos

?>
<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="info-curso">
                <h2 class="mb-4 text-white bg-dark p-3 rounded-3 shadow-sm">
                    <i class="bi bi-patch-question-fill me-2 text-warning"></i>
                    Questionário Respondido
                </h2>

                <?php foreach ($questoes as $questao):
                    $ordem           = $questao['ordemcq'];
                    $titulo          = $questao['titulocq'];
                    $tipo            = (int)$questao['tipocq']; // 1=dissertativa, 2=múltipla
                    $respostaSistema = $questao['respostacq'];  // para MC: letra; para dissertativa: texto
                    $idquestionario  = (int)$questao['codigoquestionario'];

                    // Busca resposta do aluno (ATENÇÃO: nome da tabela conforme seu código: a_curso_questionario_resposta)
                    $stmt = $con->prepare("SELECT respostaqr FROM a_curso_questionario_resposta 
                                   WHERE idalunoqr = :idaluno AND idquestionarioqr = :idquestionario");
                    $stmt->bindParam(":idaluno", $codigousuario);
                    $stmt->bindParam(":idquestionario", $idquestionario);
                    $stmt->execute();
                    $respostaAluno = $stmt->fetchColumn();

                    // Atualiza contadores
                    $totalQuestoes++;

                    $contouComoCorrigivel = false;
                    $contouComoAcerto = false;

                    if ($tipo === 2) {
                        // Múltipla escolha: respostaSistema deve ser A/B/C/D
                        $contouComoCorrigivel = !empty($respostaSistema);
                        if ($contouComoCorrigivel && !empty($respostaAluno) && strtoupper($respostaAluno) === strtoupper($respostaSistema)) {
                            $contouComoAcerto = true;
                        }
                    } elseif ($tipo === 1) {
                        // Dissertativa: comparação simples (pode ajustar a regra)
                        if (!empty($respostaSistema)) {
                            $contouComoCorrigivel = true;
                            if (normaliza_txt($respostaAluno) !== '' && normaliza_txt($respostaAluno) === normaliza_txt($respostaSistema)) {
                                $contouComoAcerto = true;
                            }
                        }
                    }

                    if ($contouComoCorrigivel) $totalCorrigiveis++;
                    if ($contouComoAcerto)     $acertos++;
                ?>

                    <div class="card mb-4 shadow-sm border-start border-4 border-info bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Atividade <?= htmlspecialchars($ordem); ?></h6>
                            <h4 class="card-title fw-bold text-dark"><?= htmlspecialchars($titulo); ?></h4>

                            <?php if ($tipo === 1): // Dissertativa 
                            ?>
                                <div class="mt-3">
                                    <p class="fw-semibold text-primary mb-1">Resposta do Aluno:</p>
                                    <div class="p-3 bg-white border rounded-2 mb-3 text-dark">
                                        <?= nl2br(htmlspecialchars($respostaAluno ?? 'Não respondido.')); ?>
                                    </div>

                                    <p class="fw-semibold text-success mb-1">Resposta Esperada:</p>
                                    <div class="p-3 rounded-2 text-dark" style="background-color: #e0f3e0;">
                                        <?= nl2br(htmlspecialchars($respostaSistema)); ?>
                                    </div>

                                    <?php if (!empty($respostaSistema)): ?>
                                        <?php if (normaliza_txt($respostaAluno) !== '' && normaliza_txt($respostaAluno) === normaliza_txt($respostaSistema)): ?>
                                            <div class="alert alert-success rounded-2 mt-3" role="alert">
                                                <i class="bi bi-check-circle-fill me-2"></i> Resposta considerada correta.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning rounded-2 mt-3" role="alert">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                Esta questão é dissertativa; a correção automática pode não refletir avaliação do professor.
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>

                            <?php elseif ($tipo === 2): // Múltipla escolha 
                            ?>
                                <div class="mt-4">
                                    <p class="fw-semibold text-primary mb-2">
                                        <i class="bi bi-list-check me-2"></i> Escolha do Aluno:
                                    </p>

                                    <div class="mb-3">
                                        <?php
                                        $opcoes = [
                                            'A' => $questao['opcaoa'],
                                            'B' => $questao['opcaob'],
                                            'C' => $questao['opcaoc'],
                                            'D' => $questao['opcaod']
                                        ];
                                        foreach ($opcoes as $letra => $texto) {
                                            $isAluno = (!empty($respostaAluno) && strtoupper($respostaAluno) === $letra);
                                            $corClasse = '';
                                            if ($isAluno) {
                                                $corClasse = (strtoupper($respostaAluno) === strtoupper($respostaSistema))
                                                    ? 'bg-success text-white'
                                                    : 'bg-danger text-white';
                                            }
                                            $estiloLabel = "form-check-label d-block p-2 rounded-3 $corClasse";
                                            $idFor = "opcao{$letra}_{$idquestionario}";
                                        ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input d-none" type="radio" id="<?= $idFor ?>" disabled>
                                                <label class="<?= $estiloLabel ?>" for="<?= $idFor ?>">
                                                    <?= $letra ?>. <?= htmlspecialchars($texto) ?>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <?php if (!empty($respostaSistema)): ?>
                                        <?php if (strtoupper((string)$respostaAluno) === strtoupper((string)$respostaSistema)): ?>
                                            <div class="alert alert-success rounded-2" role="alert">
                                                <i class="bi bi-check-circle-fill me-2"></i> Resposta correta!
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning rounded-2" role="alert">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                Resposta correta: <strong><?= htmlspecialchars($respostaSistema); ?></strong>.
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
// ========= RESUMO FIXO =========
$percent = ($totalCorrigiveis > 0) ? round(($acertos / $totalCorrigiveis) * 100) : 0;
$aprovado = ($percent >= $mediaPercent);
$bgClass  = $aprovado ? 'bg-success' : 'bg-danger';
$textIcon = $aprovado ? 'bi-emoji-smile-fill' : 'bi-emoji-frown-fill';
?>

<style>
    .resumo-fixo {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        bottom: 20px;
        z-index: 1090;
        min-width: 320px;
        max-width: 92vw;
        border-radius: 1rem;
        box-shadow: 0 6px 24px rgba(0, 0, 0, .25);
    }

    .resumo-fixo .progress {
        height: .6rem;
        background: rgba(255, 255, 255, .25);
    }

    .btn-fechar-resumo {
        opacity: .9;
    }
</style>

<div id="resumoPontuacao" class="resumo-fixo text-white <?= $bgClass ?>">
    <div class="d-flex align-items-center justify-content-between px-3 pt-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi <?= $textIcon ?> fs-4"></i>
            <div>
                <div class="fw-bold">Resultado do Questionário</div>
                <div class="small">
                    Acertos: <strong><?= $acertos ?></strong> /
                    Corrigíveis: <strong><?= $totalCorrigiveis ?></strong> —
                    Nota: <strong><?= $percent ?>%</strong> (Média: <?= $mediaPercent ?>%)
                </div>
            </div>
        </div>
        <button class="btn btn-light btn-sm rounded-pill btn-fechar-resumo me-1 mt-1" onclick="document.getElementById('resumoPontuacao')?.remove()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="px-3 pb-3">
        <div class="progress mt-2">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    </div>
</div>