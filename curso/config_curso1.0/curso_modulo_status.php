<div class="container mt-5">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-9">
            <div class="info-curso">
                <div class="row">
                    <!-- Coluna Esquerda: Boas-vindas e informações -->
                    <div class="col-md-8">
                        <div class="mb-2">Seja muito bem-vindo <strong><?php echo $nmUser; ?></strong></div>
                        <div class="titulo mb-4">Você está no módulo de <br><strong><?php echo $nmmodulo; ?></strong>!</div>

                        <h5 class="mt-3">📚 Última aula assistida</h5>
                        <p><?php echo $tituloultimaaula; ?></p>

                        <h5 class="mt-3">📄 Descrição da Aula</h5>
                        <p><?php echo $olhoAaula; ?></p>
                    </div>

                    <!-- Coluna Direita: Barra de Progresso -->
                    <div class="col-md-4 mb-3">
                        <div id="barraprogressoAulas" class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <h6 class="card-title text-center mb-4">📊 Progresso</h6>

                                <!-- Círculo com percentual -->
                                <div class="progress-circle <?php echo $corBarra; ?> ">
                                    <?php echo $perc; ?>%
                                </div>

                                <!-- Barra de progresso abaixo do círculo -->
                                <div class="progress w-75" style="height: 20px;">
                                    <div class="progress-bar <?php echo $corBarra; ?> text-dark fw-bold"
                                        role="progressbar"
                                        style="width: <?php echo $perc; ?>%;"
                                        aria-valuenow="<?php echo $perc; ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Linha de cards -->
                <div class="row mt-4" id="cards-curso">
                    <!-- Card: Link da Aula -->
                    <?php if ($rwUltimaaula): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card card-custom h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center text-center" style="cursor: pointer;" onclick="window.location.href='actionCurso.php?lc=<?php echo $encUltimaId; ?>';">
                                    <h6 class="card-title">🔗 Link da aula</h6>
                                    <p class="card-text">Clique aqui para assistir</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Atividade -->
                        <div class="col-md-6 mb-3">
                            <div class="card card-custom h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h6 class="card-title">📌 Atividade</h6>
                                    <p class="card-text"><strong>Status:</strong> Em análise</p>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="col-md-12 mb-3">
                            <div class="card card-custom h-100 shadow-sm">
                                <div class="card-body d-flex flex-column justify-content-center text-center">
                                    <h6 class="card-title">📌Primeiros passos</h6>
                                    <p class="card-text"><strong>Lições:</strong>Acesse as lições na barra lateral no lado direito de sua página</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <!-- Coluna da direita: Imagem -->
        <!-- <div class="col-md-3 text-center imagem-professor">
            <img src="https://professoreugenio.com/img/mascotes/professor.png" alt="Professor Mascote">
        </div> -->
    </div>
</div>