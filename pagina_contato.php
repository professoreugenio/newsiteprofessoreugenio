<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">


<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Professor Eugênio</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="config_index2/body_index.css">
<link rel="stylesheet" href="mycss/default.css">
<link rel="stylesheet" href="mycss/config.css">
<link rel="stylesheet" href="mycss/nav.css">
<link rel="stylesheet" href="mycss/anuncio.css">
<link rel="stylesheet" href="mycss/animate.min.css">
<link rel="stylesheet" href="config_default/config.css">
<link rel="stylesheet" href="config_default/whatsapp.css">
<link rel="stylesheet" href="config_contato/contato.css">
<style>

</style>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body>
    <?php
    $var = "ZFZDdkVwN2RDa0plVWdienNUQTRqdz09";
    require 'query_paginas/query.php'; ?>
    <!-- Navbar -->
    <?php include 'config_default/body_navall.php'; ?>
    <!-- Header -->
    <section class="container" style="min-height: 100vh;">

        <div class="row justify-content-center">
            <div class="col-md-3">
                <h4 style="color: #ffffff;"><?php echo $tituloPublicacao ?? '';  ?>/SERVIÇOS</h4>
                <h5 class="mb-4" style="color: #ffffff;">Fale diretamente pelo WhatsApp:</h5>
                <div class="row">

                    <div class="col-md-12 mb-4">
                        <a href="https://wa.me/5585996537577?text=Olá,<?= $saudacao; ?>. Sobre *Designer Gráfico*: %20gostaria%20de%20mais%20informações!" class="whatsapp-button" target="_blank">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Icon">
                            Designer Gráfico <i class="bi bi-chevron-compact-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12 mb-4">
                        <a href="https://wa.me/5585996537577?text=Olá,<?= $saudacao; ?>. *Sobre Power Bi*:%20gostaria%20de%20mais%20informações!" class="whatsapp-button" target="_blank">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Icon">
                            Power BI <i class="bi bi-chevron-compact-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12 mb-4">
                        <a href="https://wa.me/5585996537577?text=Olá, . *Sobre anunciar no site*:%20gostaria%20de%20mais%20informações!" class="whatsapp-button" target="_blank">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Icon">
                            Anunciar no Site <i class="bi bi-chevron-compact-right"></i>
                        </a>
                    </div>

                </div>
                <div class="row">

                </div>
            </div>


            <div class="col-md-6">
                <div class="form-container p-4 rounded-4 shadow-lg bg-dark text-white">

                    <?php require 'config_contato/tratamento_get.php'; ?>
                    <div id="mensagemStatus" class="mb-3"></div>

                    <h2 class="mb-4 text-center">Fale com a Gente</h2>

                    <form method="post" id="formcontato" role="form">
                        <div class="mb-3">
                            <label for="assunto" class="form-label">📌 Assunto *</label>
                            <input type="text" class="form-control form-control-lg" name="assunto" id="assunto" placeholder="Assunto do contato" value="<?php echo $assunto; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="nome" class="form-label">👤 Nome *</label>
                            <input type="text" class="form-control form-control-lg" name="nome" id="nome" placeholder="Seu nome completo" value="<?php echo $nome; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">✉️ E-mail *</label>
                            <input type="email" class="form-control form-control-lg" name="email" id="email" placeholder="seuemail@exemplo.com" value="<?php echo $email; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="celular" class="form-label">📱 Celular (opcional)</label>
                            <input type="text" class="form-control form-control-lg" name="celular" id="celular" placeholder="(DDD) 99999-9999" value="<?php echo $celular; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="dadospc" class="form-label">💻 Dados do PC para aulas on-line *</label>
                            <input type="text" class="form-control form-control-lg" name="dadospc" id="dadospc" placeholder="Ex: Windows 10, 8GB RAM..." required>
                        </div>

                        <div class="mb-4">
                            <label for="mensagem" class="form-label">📝 Mensagem *</label>
                            <textarea class="form-control form-control-lg" name="mensagem" id="mensagem" rows="4" placeholder="Escreva sua mensagem aqui..." required><?php echo $msg; ?></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="btContato">
                                <span id="retorno">📨 Enviar Contato</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </section>





    <!-- Contato Section -->

    <button id="scrollToTopBtn" class="scrollToTopBtn"><i class="bi bi-arrow-up"></i></button>

    <?php require 'config_default/link_adm.php'; ?>
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">&copy;Professor Eugênio 2025 Cursos Online. Todos os direitos reservados.</p>
    </footer>
    <script src="acessosv1.0/ajax_registraAcesso.js"></script>
    <script src="config_contato/envia_contato.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="config_index_js/scrollToTop.js"></script>
</body>

</html>