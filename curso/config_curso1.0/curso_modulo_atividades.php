<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-12">
            <div class="info-curso container mt-4">

                <!-- Título do módulo -->
                <div class="mb-3">
                    <h4 class="text-white">🎯 Atividades - Módulo de <?= htmlspecialchars($nmmodulo); ?></h4>
                    <h5 class="text-white">📚 Lições</h5>
                </div>

                <!-- Lista de lições -->
                <div class="row" id="cards-curso">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($fetchTodasLicoes as $key => $value): ?>
                                        <?php
                                        $codigoaulas = $value['codigopublicacoes'];
                                        $query = $con->prepare("SELECT * FROM a_aluno_andamento_aula 
                            WHERE idpublicaa = :codigoaula AND idalunoaa = :codigousuario");
                                        $query->bindParam(":codigoaula", $codigoaulas);
                                        $query->bindParam(":codigousuario", $codigousuario);
                                        $query->execute();
                                        $rwaulavista = $query->fetch(PDO::FETCH_ASSOC);

                                        $selecionada = $rwaulavista ? 'lida' : '';
                                        $enc = encrypt($value['idpublicacaopc'], 'e');

                                        // Exemplo de dados fictícios — substitua com dados reais
                                        $percentual = $rwaulavista['percentual'] ?? 0;
                                        $nota = $rwaulavista['nota'] ?? '-';
                                        $tentativas = $rwaulavista['tentativas'] ?? 0;
                                        $status = ($percentual >= 100) ? '✔ Concluída' : '❌ Em análise';
                                        ?>
                                        <li class="list-group-item p-3">
                                            <div class="d-flex justify-content-between align-items-center"
                                                onclick="window.location.href='actionCurso.php?Atv=<?= $enc; ?>';"
                                                style="cursor: pointer;">
                                                <span class="fw-medium"><?= htmlspecialchars($value['titulo']); ?></span>
                                                <i class="bi bi-chevron-right text-muted"></i>
                                            </div>

                                            <!-- Informações adicionais com estilo -->
                                            <div class="mt-3 d-flex flex-wrap gap-2">
                                                <div class="info-badge">
                                                    <strong>Status:</strong> <?= $status; ?>
                                                </div>
                                                <div class="info-badge">
                                                    <strong>Percentual:</strong> <?= $percentual; ?>%
                                                </div>
                                                <div class="info-badge">
                                                    <strong>Nota:</strong> <?= $nota; ?>/10
                                                </div>
                                                <div class="info-badge">
                                                    <strong>Tentativas:</strong> <?= $tentativas; ?>
                                                </div>
                                            </div>
                                        </li>

                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </div>
        <!-- Coluna da direita: Imagem -->
        <!-- <div class="col-md-3 text-center imagem-professor">
            <img src="https://professoreugenio.com/img/mascotes/professor.png" alt="Professor Mascote">
        </div> -->
    </div>
</div>