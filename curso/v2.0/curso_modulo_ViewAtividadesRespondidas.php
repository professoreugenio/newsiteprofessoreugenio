<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-12">
            <div class="info-curso container mt-4">
                <h3 class="mb-4">Questionário Respondido</h3>
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

                                    <label class="fw-semibold text-success">Análise comparativa:</label>
                                    <div class="p-2 bg-white border rounded">
                                        <?php echo nl2br(htmlspecialchars($respostaSistema)); ?>
                                    </div>
                                </div>

                            <?php elseif (in_array($tipo, [2, 3])): ?>
                                <div class="mt-3">
                                    <label class="fw-semibold text-success">Resposta do Sistema:</label>
                                    <div class="p-2 bg-white border rounded">
                                        <?php echo nl2br(htmlspecialchars($respostaSistema)); ?>
                                    </div>
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