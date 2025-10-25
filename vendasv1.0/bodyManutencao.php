<!-- CONTEÚDO COM IMAGEM -->
<section class="py-5" id="mensagem">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-12 mb-4 mb-md-0">
                <h1 class="fw-bold mb-3"><?php echo $nomeCurso;  ?></h1>
            </div>

        </div>
        <div class="row align-items-center">
            <!-- Imagem -->
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="<?php echo $imgMidiaCurso;  ?>" class="img-fluid rounded shadow"
                    alt="Atualização do Curso" />
            </div>

            <!-- Mensagem -->
            <div class="col-md-6">
                <div class="card p-4 shadow">
                    <h3 class="fw-bold mb-3">Novidades!</h3>
                    <p class="lead mb-4">Logo estaremos disponibilizando novas inscrições para o cursos de <b><?php echo $nomeCurso;  ?></b>  online.</p>
                    <h2 class="mb-3">Deseja ser avisado?</h2>
                    <p class="mb-4">Entre em contato e receba atualizações diretamente no seu WhatsApp ou e-mail.
                    </p>
                    <div id="contato">
                        <a href="https://wa.me/5585996537577" class="btn btn-contato btn-lg mb-3">
                            <i class="bi bi-whatsapp"></i> Falar no WhatsApp
                        </a><br />
                        <a href="mailto:contato@professoreugenio.com" class="btn btn-contato btn-lg">
                            <i class="bi bi-envelope-fill"></i> Enviar E-mail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>