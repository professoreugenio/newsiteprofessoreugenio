<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php require 'head/head.php'; ?>
  <link rel="stylesheet" href="mycss/home.css">
  <link rel="stylesheet" href="popupatendimento/popupatendimento.css">
  <?php require 'head/head_midiassociais.php'; ?>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
</head>

<body>
  <?php
  $queryacess = $con->prepare("SELECT * FROM new_sistema_historico_acessos ");
  $queryacess->execute();
  $fetch = $queryacess->fetchALL();
  $quantacess = count($fetch);
  $num_acessos = number_format($quantacess, 0, ',', '.');
  $num_acessos = ('<span style="color:#ffff00;font-size: 16px;"> <i class="fa fa-users" aria-hidden="true"></i>  ') . $num_acessos . (' Acessos.</span>');
  ?>
  <div class="scroll-down-wrapper">
    <div class="scroll-down">
    </div>
  </div>
  <!-- Spinner Start -->
  <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>
  <!-- Spinner End -->
  <!-- Navbar Start -->
  <?php $pg = "1";
  require 'modulos/nav.php'; ?>
  <!-- Navbar End -->
  <!-- Carousel Start -->
  <div class="container-fluid p-0 mt-5">
    <?php require 'cursos/cursos.php'; ?>

    <div class="text-center wow fadeInUp mt-5" data-wow-delay="0.1s">
      <h5 class="section-title bg-white text-center text-primary px-3">
        PARA QUEM TEM UM COMPUTADOR</h5>
      <p>
        PARTICIPAR DE CURSOS ONLINE
        <br>
        <?php
        $queryacess = $con->prepare("SELECT * FROM new_sistema_historico_acessos ");
        $queryacess->execute();
        $fetch = $queryacess->fetchALL();
        $quantacess = count($fetch);
        $num_acessos = number_format($quantacess, 0, ',', '.');
        echo $num_acessos = ('<span style="color:#ff0080;font-size: 16px;"> <i class="fa fa-users" aria-hidden="true"></i>  ') . $num_acessos . (' Acessos.</span>');
        ?>
      </p>
    </div>
  </div>
  <!-- Carousel End -->
  <!-- Service Start -->
  <!-- Service End -->
  <!-- About Start -->
  <!-- About End -->
  <!-- Categories Start -->
  <div class="container-xxl py-5 category">

    <div class="row g-3">
      <div class="col-lg-12 col-md-12 text-center">
        <a href="https://professoreugenio.com/pagina_cursos_online.php?v=0" class="btn btn-success py-3 px-5 mt-2">CURSOS ONLINE</a>
      </div>
    </div>
  </div>
  <?php require 'footer/footer.php'; ?>
  <!-- Footer End -->
  <!-- Back to Top -->
  <!-- <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a> -->
  <!-- JavaScript Libraries -->
  <?php
  // require 'scripts/contador_sistem.php';
  ?>
  <?php require 'scripts/registraacessos.php' ?>
  <?php require 'popupatendimento/blocopopupatendimento.php' ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="lib/wow/wow.min.js"></script>
  <script src="popupatendimento/script_popupatendimento.js"></script>
  <sc ript src="lib/easing/easing.min.js">
    </script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const scrollDown = document.querySelector(".scroll-down");
        const scrollDownMove = document.querySelector(".scroll-down-wrapper");
        scrollDownMove.addEventListener("click", function() {
          window.scrollTo({
            top: window.innerHeight,
            behavior: "smooth"
          });
          scrollDownMove.style.display = "none";
        });
      });
    </script>
</body>

</html>