<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php require 'head/head.php' ?>
  <link rel="stylesheet" href="mycss/aulas.css">
  <style>
    .list-group li:hover {
      background-color: #f0f0f0;
    }

    body {
      background-color: #ffffff;
    }

    @media (max-width: 768px) {
      .container-imgflex img {
        display: none;
      }

      .list-group-item {
        padding: 0.5rem 0.3rem;
      }

      .resumo,
      .titulo {
        margin: 0;
      }
    }
  </style>
  </div>
</head>
<?php
if (empty($_GET['var'])) {
  echo ('<meta http-equiv="refresh" content="3; url=index.php">');
  echo ('<body><div class="container"><div class="col-sm-12"><p><h2>Aguarde...</h2></p><p>Dados não processados, novo redirecionamento.</p></div></div></body>');
  exit();
}
?>

<body>
  <!-- Spinner Start -->
  <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>
  <!-- Spinner End -->
  <!-- Navbar Start -->
  <?php $pg = "2";
  require 'modulos/nav.php'; ?>
  <!-- Navbar End -->
  <!-- Header Start -->
  <!-- Header End -->
  <!-- Service Start -->
  <div class="h40"></div>
  <?php if (!empty($_COOKIE['startusuario'])) {
    echo ('<img src="img/logo.png"  id="logopage"  alt="">');
  } ?>
  <!-- Service End -->
  <?php
  // Verifica se existe o parâmetro 'var' e decodifica com segurança
  $decPagina = isset($_GET['var']) ? encrypt($_GET['var'], $action = 'd') : '';
  $exp = explode("&", $decPagina);

  // Inicializa variáveis com valores padrão
  $decPagina = $decPage = $idModulo = null;
  $nomecurso = $descricao = "Não informado";
  $nomeModulo = $color = $idModulos = "Não informado";

  // Garante que existam ao menos 2 elementos no array
  if (count($exp) >= 2) {
    $decPage = $exp[0];
    $decPagina = $exp[1];

    // Consulta categoria
    $queryCat = $con->prepare("SELECT codigocategorias, pasta, nome, descricaosc, bgcolor, ordemsc 
                               FROM new_sistema_categorias_PJA 
                               WHERE codigocategorias = :var AND visivelhomesc = '1'");
    $queryCat->bindParam(":var", $decPagina);
    $queryCat->execute();
    $rwCurso = $queryCat->fetch(PDO::FETCH_ASSOC);

    if ($rwCurso) {
      $nomecurso = $rwCurso['nome'];
      $descricao = $rwCurso['descricaosc'];
    }
  }

  // Consulta módulo se houver terceiro item no array
  if (isset($exp[2])) {
    $idModulo = $exp[2];

    $queryModl = $con->prepare("SELECT * FROM new_sistema_modulos_PJA WHERE codigomodulos = :id");
    $queryModl->bindParam(":id", $idModulo);
    $queryModl->execute();
    $rwMdl = $queryModl->fetch(PDO::FETCH_ASSOC);

    if ($rwMdl) {
      $nomeModulo = $rwMdl['modulo'];
      $color = $rwMdl['bgcolor'];
      $idModulos = $rwMdl['codigomodulos'];
    }
  }
  ?>

  <!-- About Start -->
  <div class="container-xxl py-2">
    <div class="container">
      <div class="row g-5">
        <div class="col-lg-9 wow fadeInUp" data-wow-delay="0.1s">
          <h4><?php echo $nomecurso; ?> | <span style="color: <?php echo $color; ?>;"><?php echo $nomeModulo; ?> <?php echo $idModulos; ?> </span> </h4>
          * <?php
            if (!empty($_COOKIE['adminstart'])) {
              echo ('<a target="_blank" href="pdf/view-pdf.php?var=' . $_GET['var'] . '">Print</a>');
            }
            ?>*
          <h3></h3>
          <?php require 'cursos/aulas.php'; ?>
        </div>
        <div class="col-lg-3 wow fadeInUp" data-wow-delay="0.8s">
          <div class="row justify-content-center">
            <?php $v = "4500"; ?>
            <?php require 'cursos/cards_anunciounico.php' ?>
          </div>
          <h6 class="section-title bg-white text-start text-primary pe-3">Módulos</h6>
          <?php require 'cursos/modulos_lateral.php'; ?>
        </div>
      </div>
    </div>
  </div>
  <!-- About End -->
  <!-- Team Start -->
  <!-- Team End -->
  <!-- Footer Start -->
  <?php require 'footer/footer.php'; ?>
  <!-- Footer End -->
  <!-- Back to Top -->
  <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
  <!-- JavaScript Libraries -->
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="lib/wow/wow.min.js"></script>
  <script src="lib/easing/easing.min.js"></script>
  <script src="lib/waypoints/waypoints.min.js"></script>
  <script src="lib/owlcarousel/owl.carousel.min.js"></script>
  <!-- Template Javascript -->
  <script src="js/main.js"></script>
  <script>
    const queryString = window.location.search;
    // Criando um objeto URLSearchParams a partir da string de consulta
    const params = new URLSearchParams(queryString);
    // Obtendo o valor da variável 'nome' da URL
    const nome = params.get('var');
    // Exibindo o valor obtido
    console.log(nome);
    let num1 = 33;
    let num2 = 12;
    let resto = num1 % num2;
    // console.log("O resto da divisão de " + num1 + " por " + num2 + " é " + resto);
  </script>
</body>


</html>