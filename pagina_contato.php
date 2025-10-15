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
    /* ---- Formulário escuro, compacto e com floating labels ---- */
    .dark-form .form-control,
    .dark-form .form-select,
    .dark-form textarea {
        background-color: #0f1b2d;
        border-color: #2b3a55;
        color: #fff;
    }

    .dark-form .form-control::placeholder,
    .dark-form textarea::placeholder {
        color: rgba(255, 255, 255, .35);
    }

    .dark-form .form-control:focus,
    .dark-form .form-select:focus,
    .dark-form textarea:focus {
        border-color: #00BB9C;
        box-shadow: 0 0 0 .2rem rgba(0, 187, 156, .18);
        background-color: #12203a;
        color: #fff;
    }

    .dark-form .form-floating>label {
        color: rgba(255, 255, 255, .6);
    }

    .form-compact .form-floating {
        margin-bottom: .55rem;
    }

    /* menos espaço entre campos */
    .form-compact .btn {
        padding: .6rem 1rem;
    }

    /* botão mais enxuto */

    /* Alturas dos campos (compactas) */
    .form-compact .form-control,
    .form-compact .form-select {
        min-height: 2.8rem;
        padding-top: .9rem;
        padding-bottom: .9rem;
    }

    /* Altura do textarea em floating */
    .form-compact .form-floating textarea.form-control {
        height: 140px;
        /* ajuste fino de altura */
        min-height: 140px;
    }

    /* Container do form */
    .form-container {
        background: #0b152b;
        border: 1px solid rgba(255, 255, 255, .06);
    }
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
                            Serviços Designer Gráfico <i class="bi bi-chevron-compact-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12 mb-4">
                        <a href="https://wa.me/5585996537577?text=Olá,<?= $saudacao; ?>. *Sobre Power Bi*:%20gostaria%20de%20mais%20informações!" class="whatsapp-button" target="_blank">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp Icon">
                            Consultoria em Planilhas e Power BI <i class="bi bi-chevron-compact-right"></i>
                        </a>
                    </div>
                    <div class="col-md-12 mb-4">
                        <a href="https://wa.me/5585996537577?text=Olá,<?= $saudacao; ?>. *Desejo infromações sobre anunciar no site*:%20gostaria%20de%20mais%20informações!" class="whatsapp-button" target="_blank">
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

                    <h2 class="mb-4 text-center">Fale com o professor</h2>

                    <form method="post" id="formcontato" role="form" class="dark-form form-compact">
                        <!-- Assunto -->
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="assunto" name="assunto"
                                placeholder="" value="<?php echo $assunto; ?>" required>
                            <label for="assunto">📌 Assunto *</label>
                        </div>

                        <!-- Nome -->
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="nome" name="nome"
                                placeholder="" value="<?php echo $nome; ?>" required>
                            <label for="nome">👤 Nome *</label>
                        </div>

                        <!-- E-mail -->
                        <div class="form-floating mb-2">
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="" value="<?php echo $email; ?>" required>
                            <label for="email">✉️ E-mail *</label>
                        </div>

                        <!-- Celular (opcional) -->
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="celular" name="celular"
                                placeholder="" value="<?php echo $celular; ?>">
                            <label for="celular">📱 (DDD) 999999999</label>
                        </div>

                        <!-- Dados do PC -->
                        <div class="form-floating mb-2">
                            <input type="text" class="form-control" id="dadospc" name="dadospc"
                                placeholder="" required>
                            <label for="dadospc">💻 Dados do PC para aulas on-line *</label>
                        </div>

                        <!-- Mensagem -->
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="mensagem" name="mensagem"
                                placeholder="" required><?php echo $msg; ?></textarea>
                            <label for="mensagem">📝 Mensagem *</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="btContato">
                                <span id="retorno">📨 Enviar</span>
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