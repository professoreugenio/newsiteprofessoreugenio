<div class="container">
    <div class="row align-items-center">
        <!-- Coluna da esquerda: Texto -->
        <div class="col-md-8">
            <div class="info-curso container mt-4">
                <div id="formContainer" class="form-card">
                    <?php
                    $con = config::connect();

                    // Busca todas as questões ordenadas
                    $queryQuestoes = $con->prepare("SELECT * FROM a_curso_questionario 
    WHERE idpublicacaocq = :idpublic ORDER BY ordemcq ASC");
                    $queryQuestoes->bindParam(":idpublic", $codigoaula);
                    $queryQuestoes->execute();
                    $questoes = $queryQuestoes->fetchAll(PDO::FETCH_ASSOC);
                    $quantQuestoes = count($questoes);

                    $encIdQues = encrypt($questoes['codigoquestionario'], $action = 'e');

                    // Suponha que você receba ou defina o código atual da questão:
                    $codigoAtual = $questoes[0]['codigoquestionario']; // ou defina manualmente
                    $proximoCodigo = null;

                    // Localiza o próximo na ordem
                    for ($i = 0; $i < $quantQuestoes; $i++) {
                        if ($questoes[$i]['codigoquestionario'] == $codigoAtual && isset($questoes[$i + 1])) {
                            $proximoCodigo = $questoes[$i + 1]['codigoquestionario'];
                            break;
                        }
                    }

                    // Encripta IDs
                    $idatual   = encrypt($codigoAtual, 'e');
                    $proximoid = $proximoCodigo ? encrypt($proximoCodigo, 'e') : null;
                    ?>

                    <form id="formResposta">
                        <input type="hidden" name="proximoid" id="proximoid" value="<?php echo $proximoid; ?>">
                        <input type="hidden" name="idatividade" id="idatividade" value="<?php echo $encIdQues; ?>">
                        <h3>Um total de <?php echo $quantQuestoes;  ?> questões</h3>
                        <?php if ($questoes[0]['tipocq'] == 1): ?>
                            <h5 class="mb-4"><i class="bi bi-question-circle-fill text-primary"></i> <?php echo $questoes[0]['titulocq']; ?></h5>
                            <p class="mb-2">Explique com suas palavras o conceito de aprendizagem ativa.</p>
                            <div class="form-floating mb-3">
                                <textarea class="form-control" id="respostaAluno" placeholder="Digite sua resposta aqui" style="height: 120px" required></textarea>
                                <label for="respostaAluno">Sua resposta</label>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" id="btnEnviar" class="btn btn-success">
                                <i class="bi bi-send"></i> Enviar resposta
                            </button>
                            <?php if ($proximoid): ?>
                                <button type="button" id="btnProxima" class="btn btn-primary" style="display: none;">
                                    <i class="bi bi-arrow-right-circle"></i> Próxima pergunta
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                </div>

            </div>
        </div>
        <!-- Coluna da direita: Imagem -->
    </div>
</div>

<!-- jQuery e Bootstrap Toast (se ainda não incluído) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(document).ready(function() {
        $('#formResposta').on('submit', function(e) {
            e.preventDefault();

            // Botões e Loading
            var $btnEnviar = $('#btnEnviar');
            var originalHtml = $btnEnviar.html();
            $btnEnviar.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...');
            $btnEnviar.prop('disabled', true);

            // Coleta de dados
            var resposta = $('#respostaAluno').val();
            var proximoid = $('#proximoid').val();
            var idatividade = $('#idatividade').val();

            $.ajax({
                type: 'POST',
                url: 'config_Atividades/AtividadeInsert.php',
                data: {
                    resposta: resposta,
                    proximoid: proximoid,
                    idatividade: idatividade
                },
                success: function(response) {
                    showToast('✅ Sua resposta foi enviada para avaliação do professor.');

                    // Oculta o botão enviar e mostra o botão próxima
                    $btnEnviar.hide();
                    $('#btnProxima').show();
                },
                error: function() {
                    showToast('❌ Erro ao enviar a atividade. Tente novamente.', true);
                },
                complete: function() {
                    $btnEnviar.prop('disabled', false).html(originalHtml);
                }
            });
        });

        function showToast(mensagem, erro = false) {
            const toastClass = erro ? 'bg-danger' : 'bg-success';

            const toast = $(`
            <div class="toast align-items-center text-white ${toastClass} border-0" role="alert" aria-live="assertive" aria-atomic="true" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">
                        ${mensagem}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

            $('body').append(toast);
            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();

            // Remove toast após 5 segundos
            setTimeout(() => toast.remove(), 5000);
        }
    });
</script>