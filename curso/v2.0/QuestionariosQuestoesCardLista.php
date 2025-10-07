<div class="row row-cols-1 g-3" id="cards-curso">
    <?php foreach ($fetchTodasLicoes as $key => $value): ?>
        <?php
        $ordem = $key + 1;
        $codigoaulas = $value['codigopublicacoes'];

        // Verifica progresso da aula
        $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                                WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario");
        $query->bindParam(":codigoaula", $codigoaulas);
        $query->bindParam(":codigousuario", $codigousuario);
        $query->execute();
        $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);

        $percentual = $rwaulavista['percentual'] ?? 0;
        $nota = $rwaulavista['nota'] ?? '-';
        $tentativas = $rwaulavista['tentativas'] ?? 0;

        // Verifica respostas de questionário
        $query = $con->prepare("SELECT * FROM a_curso_questionario_resposta 
                                WHERE idalunoqr = :idaluno AND idaulaqr = :idpublicacao");
        $query->bindParam(":idaluno", $codigousuario);
        $query->bindParam(":idpublicacao", $codigoaulas);
        $query->execute();
        $rwQuest = $query->fetch(PDO::FETCH_ASSOC);
        $qtdRespostas = $query->rowCount();

        $respondida = (!empty($rwQuest))
            ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Respondida</span>'
            : '<a href="#" class="btn btn-outline-primary btn-sm">Responder</a>';

        $Tentativas = ($qtdRespostas > 0)
            ? '<span class="badge bg-secondary">Tentativas: ' . $qtdRespostas . '</span>'
            : '';

        $encAtividade = !empty($rwQuest)
            ? encrypt($rwQuest['idquestionarioqr'], 'e')
            : "0";

        $encAula = encrypt($value['idpublicacaopc'], 'e');
        ?>

        <?php

        $query = $con->prepare("SELECT * FROM a_curso_questionario WHERE idpublicacaocq = :idpublicacao AND visivelcq = '1' ORDER BY ordemcq ASC");
        $query->bindParam(":idpublicacao", $codigoaulas);
        $query->execute();
        $rwQuest = $query->fetch(PDO::FETCH_ASSOC);
        $idQuest = $rwQuest['codigoquestionario'] ?? '';
        $encPublic = encrypt($idQuest, $action = 'e');
        $qtdPerguntas = $query->rowCount();
        if (!empty($rwQuest)) {
        ?>

            <div class="col">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-semibold">
                                <?= $ordem; ?>. <?= htmlspecialchars($value['titulo']); ?>
                                <small style="color:rgb(157, 157, 157);"><i>Perguntas:<?= $qtdPerguntas; ?></i></small>

                            </h6>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </div>

                        <div class="mb-3 text-muted small">
                            Código decodificado: <code><?= encrypt($encPublic, 'd'); ?></code>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <?= $respondida ?>
                            <?= $Tentativas ?>
                        </div>

                        <a href="actionCurso.php?Atvs=<?= $encPublic; ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>

        <?php } ?>
    <?php endforeach; ?>
</div>