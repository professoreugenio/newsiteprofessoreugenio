<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-12">
            <div class="info-curso container mt-4">
                <h3 class="mb-4">Questionário Respondido <?php  $codigoaula;  ?></h3>

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

                    <div class="card mb-4 shadow-sm border-start border-4 border-primary">
                        <div class="card-body">
                            <h6 class="text-muted">Atividade <?php echo $ordem; ?></h6>
                            <h5 class="card-title"><?php echo htmlspecialchars($titulo); ?></h5>

                            <?php if ($tipo == 1): ?>
                                <div class="mt-3">
                                    <label class="fw-semibold text-primary">Resposta do Aluno:</label>
                                    <div class="p-2 bg-light border rounded mb-3">
                                        <?php echo nl2br(htmlspecialchars($respostaAluno ?? 'Não respondido.')); ?>
                                    </div>

                                    <label class="fw-semibold text-success">Resposta comparativa :</label>
                                    <div class="p-2 border rounded" style="background-color:rgb(205, 221, 199);">
                                        <?php echo nl2br(htmlspecialchars($respostaSistema)); ?>
                                    </div>
                                </div>

                            <?php elseif ($tipo == 2): ?>
                                <div class="mt-3">
                                    <div class="card shadow-sm border-0 rounded p-4 mb-4">
                                        <h5 class="mb-3 text-primary">
                                            <i class="bi bi-question-circle-fill me-2"></i> <?php echo $titulo; ?>
                                        </h5>
                                        <p class="mb-4 fs-5 text-secondary">Resposta do Aluno:</p>

                                        <div class="mb-3">
                                            <?php
                                            // Array de opções
                                            $opcoes = [
                                                'A' => $questao['opcaoa'],
                                                'B' => $questao['opcaob'],
                                                'C' => $questao['opcaoc'],
                                                'D' => $questao['opcaod']
                                            ];

                                            foreach ($opcoes as $letra => $texto) {
                                                // Verifica se é a resposta do aluno
                                                $isAluno = $respostaAluno === $letra;

                                                // Define a classe de cor de fundo
                                                $corClasse = '';
                                                if ($isAluno) {
                                                    $corClasse = ($respostaAluno === $respostaSistema) ? 'bg-success text-white' : 'bg-danger text-white';
                                                }

                                                // Classes extras para deixar o label mais estilizado
                                                $estiloLabel = "form-check-label d-block p-2 rounded $corClasse";
                                            ?>
                                                <div class="form-check mb-2">
                                                    <!-- Input oculto -->
                                                    <input class="form-check-input d-none" type="radio" name="quest" id="opcao<?= $letra ?>" value="<?= $letra ?>">

                                                    <!-- Label visível com destaque de fundo -->
                                                    <label class="<?= $estiloLabel ?>" for="opcao<?= $letra ?>">
                                                        <?= $letra ?>. <?= $texto ?>
                                                    </label>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    

                                    <!-- Mensagem de feedback -->
                                    <?php if ($respostaAluno === $respostaSistema): ?>
                                        <div class="alert alert-success" role="alert">
                                            <i class="bi bi-check-circle-fill me-2"></i> Resposta correta!
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning" role="alert">
                                            <i class="bi bi-exclamation-circle-fill me-2"></i> 
                                            A resposta correta é a <b><?php echo $respostaSistema;  ?></b>. <br> Vamos revisar o conteúdo e tentar na próxima?
                                        </div>
                                    <?php endif; ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        </div>

        <!-- Coluna da direita: Imagem -->
    </div>
</div>
<!-- jQuery e Bootstrap Toast (se ainda não incluído) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#atividade').load('config_Atividades/AtividadeLoad.php');
    });
</script>