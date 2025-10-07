<div class="container mt-5">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-8">
            <div class="info-curso">
                <div class="">Seja muito bem-vindo <?php echo $nmUser;  ?></div>
                <div class="titulo mb-3">Você está no módulo de <br> <?php echo $nmmodulo;  ?>!</div>
                <h5>📚 Última aula assistida</h5>
                <p><?php echo $tituloultimaaula;  ?></p>
                <h5>📄 Descrição da Aula</h5>
                <p><?php echo $olhoAaula;  ?></p>


                <div class="row mt-4" id="cards-curso">
                    <!-- Card: Link da Aula -->
                    <div class="col-md-4 mb-3">
                        <div class="card card-custom h-100">
                            <div class="card-body">
                                <h6 class="card-title">🔗 Link da aula</h6>
                                <p class="card-text"><a href="#">Clique aqui</a></p>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Atividade -->
                    <div class="col-md-4 mb-3">
                        <div class="card card-custom h-100">
                            <div class="card-body">
                                <h6 class="card-title">📌 Atividade</h6>
                                <p class="card-text"><strong>Status:</strong> Em análise</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Progresso -->
                    <?php echo $barra;  ?>

                </div>


            </div>
        </div>
        <!-- Coluna da direita: Imagem -->
        <div class="col-md-3 text-center imagem-professor">
            <img src="https://professoreugenio.com/img/mascotes/professor.png" alt="Professor Mascote">
        </div>
    </div>
</div>