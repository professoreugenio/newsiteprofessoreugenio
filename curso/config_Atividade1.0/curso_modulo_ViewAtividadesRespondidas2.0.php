<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="info-curso">
                <h2 class="mb-4 text-white bg-dark p-3 rounded-3 shadow-sm">
                    <i class="bi bi-patch-question-fill me-2 text-warning"></i>
                    Questionário Respondido
                </h2>

                <?php
                $query = $con->prepare("SELECT * FROM a_curso_questionario 
                    WHERE idpublicacaocq = :idpublicacao AND visivelcq = :visivel 
                    ORDER BY ordemcq");
                $query->bindParam(":idpublicacao", $codigoaula);
                $visivel = "1";
                $query->bindParam(":visivel", $visivel);
                $query->execute();
                $questoes = $query->fetchAll();

                foreach ($questoes as $questao):
                    $ordem = $questao['ordemcq'];
                    $titulo = $questao['titulocq'];
                    $tipo = $questao['tipocq'];
                    $respostaSistema = $questao['respostacq'];
                    $idquestionario = $questao['codigoquestionario'];

                    // Busca resposta do aluno
                    $stmt = $con->prepare("SELECT respostaqr FROM a_curso_questionario_resposta 
                        WHERE idalunoqr = :idaluno AND idquestionarioqr = :idquestionario");
                    $stmt->bindParam(":idaluno", $codigousuario);
                    $stmt->bindParam(":idquestionario", $idquestionario);
                    $stmt->execute();
                    $respostaAluno = $stmt->fetchColumn();
                ?>

                    <div class="card mb-4 shadow-sm border-start border-4 border-info bg-light">
                        <div class="card-body">
                            <h6 class="text-muted">Atividade <?= $ordem; ?></h6>
                            <h4 class="card-title fw-bold text-dark"><?= htmlspecialchars($titulo); ?></h4>

                            <?php if ($tipo == 1): ?>
                                <div class="mt-3">
                                    <p class="fw-semibold text-primary mb-1">Resposta do Aluno:</p>
                                    <div class="p-3 bg-white border rounded-2 mb-3 text-dark">
                                        <?= nl2br(htmlspecialchars($respostaAluno ?? 'Não respondido.')); ?>
                                    </div>

                                    <p class="fw-semibold text-success mb-1">Resposta Esperada:</p>
                                    <div class="p-3 rounded-2 text-dark" style="background-color: #e0f3e0;">
                                        <?= nl2br(htmlspecialchars($respostaSistema)); ?>
                                    </div>
                                </div>

                            <?php elseif ($tipo == 2): ?>
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
                                            $isAluno = $respostaAluno === $letra;
                                            $corClasse = '';
                                            if ($isAluno) {
                                                $corClasse = ($respostaAluno === $respostaSistema)
                                                    ? 'bg-success text-white'
                                                    : 'bg-danger text-white';
                                            }
                                            $estiloLabel = "form-check-label d-block p-2 rounded-3 $corClasse";
                                        ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input d-none" type="radio" id="opcao<?= $letra ?>" disabled>
                                                <label class="<?= $estiloLabel ?>" for="opcao<?= $letra ?>">
                                                    <?= $letra ?>. <?= $texto ?>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <!-- Feedback -->
                                    <?php if ($respostaAluno === $respostaSistema): ?>
                                        <div class="alert alert-success rounded-2" role="alert">
                                            <i class="bi bi-check-circle-fill me-2"></i> Resposta correta!
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning rounded-2" role="alert">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                            Resposta correta: <strong><?= $respostaSistema; ?></strong>. <br>
                                            Reveja o conteúdo e tente novamente depois.
                                        </div>
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