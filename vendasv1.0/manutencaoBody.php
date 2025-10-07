<!-- CONTEÚDO COM IMAGEM -->
<section class="py-5 mt-5" id="mensagem">

    <div class="container">
        <div class="row align-items-center">
            <!-- Imagem -->
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="https://professoreugenio.com/img/manutencao.jpg" class="img-fluid rounded shadow"
                    alt="Atualização do Curso" />
            </div>
            <!-- Mensagem -->
            <div class="col-md-6">
                <div class="card p-4 shadow">
                    <h1 class="fw-bold mb-3">Curso de <?php echo $nomeCurso;  ?></h1>
                    <p class="lead mb-4">O conteúdo deste curso está passando por melhorias e estará disponível em
                        breve.</p>

                    <h2 class="mb-3">Deseja ser avisado?</h2>
                    <p class="mb-4">Entre em contato e receba atualizações diretamente no seu WhatsApp ou e-mail.
                    </p>
                    <div id="contato">
                        <?php
                        $mensagemWhats = rawurlencode(
                            saudacao() . " tudo bem? 😊\n\nTenho interesse no curso: *" . $nomeCurso . "*.\nPoderia me enviar mais informações, por favor?"
                        );
                        $linkWhatsApp = "https://wa.me/5585996537577?text={$mensagemWhats}";
                        ?>
                        <br />
                        <a target="_blank" href="<?= $linkWhatsApp ?>" class="btn btn-contato btn-lg mb-3">
                            <i class="bi bi-whatsapp"></i> Falar no WhatsApp
                        </a><br />
                        <?php
                        $assuntoEmail = rawurlencode("Interesse no curso de: $nomeCurso");
                        $corpoEmail = rawurlencode(
                            saudacao() . ", tudo bem?\n\nGostaria de obter mais informações sobre o curso: \"$nomeCurso\".\n\nAguardo seu retorno.\n\nAtenciosamente,"
                        );
                        $linkEmail = "mailto:professoreugeniomls@gmail.com?subject=$assuntoEmail&body=$corpoEmail";
                        ?>
                        <a target="_blank" href="<?= $linkEmail ?>" class="btn btn-contato btn-lg">
                            <i class="bi bi-envelope-fill"></i> Enviar E-mail
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>