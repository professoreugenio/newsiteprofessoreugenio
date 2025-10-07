<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-12">
            <div class="info-curso container mt-4">
                <!-- Título do módulo -->

                <div class="mb-3">
                    <h4 class="text-white">🎯 Atividades - Módulo de <?= htmlspecialchars($nmmodulo); ?>
                        <?= $codigomodulo;  ?>
                    </h4>
                    <h5 class="text-white">📚 Lições ( <?php echo $quantLicoes;  ?>)</h5>
                </div>
                <!-- Lista de lições -->
                <?php
                require 'v2.0/QuestionariosQuestoesCardLista.php';
                ?>

            </div>
        </div>
        <!-- Coluna da direita: Imagem -->
        <!-- <div class="col-md-3 text-center imagem-professor">
            <img src="https://professoreugenio.com/img/mascotes/professor.png" alt="Professor Mascote">
        </div> -->
    </div>
</div>