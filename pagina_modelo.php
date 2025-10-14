<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<s>
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
  <style>
    .container .texto {
      font-size: 1.1rem;
      color: #BDC3C7;
    }


    .container h3,
    .container h2,
    .container h1 {
      font-size: 1.7rem;
      color: #BDC3C7;
    }
  </style>
  </head>

  <body>
    <!-- Navbar -->
    <?php include 'config_default/body_navall.php'; ?>
    <!-- Header -->
    <section class="container">


      <section>
        <div class="container">
          <div class="row">
            <div class="col-sm-12">
              <div style="min-height: 100vh;">
                <section id="conteudo" class="content-section">
                  <div class="container">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="d-flex justify-content-center flex-wrap">

                          <?php require 'config_anuncios/carousel_um_100.php' ?>
                          <?php require 'config_anuncios/carousel_dois_100.php' ?>
                          <?php require 'config_anuncios/carousel_tres_100.php' ?>
                          <?php require 'config_anuncios/carousel_quatro_100.php' ?>


                        </div>
                      </div>
                      <div class="col-md-9">
                        <h3>Página</h3>
                        <p class="texto">Lorem ipsum dolor sit amet consectetur adipisicing elit. Distinctio eius aut sed qui. Delectus obcaecati molestias ea temporibus accusamus totam corrupti inventore quisquam et ut pariatur similique, eius ipsa cumque repellendus sed voluptatibus modi nobis quod quia cum quibusdam numquam quasi! Sunt dolor voluptas omnis accusantium adipisci corporis a explicabo repellat necessitatibus? Et officia perferendis nobis quo fugiat sequi officiis, cumque saepe nulla ex voluptatum itaque harum eligendi consequuntur maiores voluptatem delectus fugit necessitatibus! Mollitia, sunt. Explicabo, praesentium cupiditate voluptas labore ratione maiores adipisci dignissimos quaerat quidem quia iusto optio nisi iste reprehenderit tempore deleniti. Necessitatibus natus vitae iure molestias.</p>
                      </div>

                    </div>
                  </div>

              </div>
      </section>
      </div>
      </div>
      </div>
      </div>
    </section>
    </section>




    <!-- Contato Section -->

    <button id="scrollToTopBtn" class="scrollToTopBtn"><i class="bi bi-arrow-up"></i></button>

    <?php require 'config_default/link_adm.php'; ?>
    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
      <p class="mb-0">&copy;Professor Eugênio 2025 Cursos Online. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="config_index_js/scrollToTop.js"></script>
  </body>

</html>