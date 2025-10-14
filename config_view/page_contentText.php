
  <?php
  $query = $con->prepare("SELECT * FROM new_sistema_inscricao_PJA WHERE chaveturma = :chave AND codigousuario=:iduser ");
  $query->bindParam(":chave", $chaveturmaUser);
  $query->bindParam(":iduser", $codigoUser);
  $query->execute();
  $rwIscricao = $query->fetch(PDO::FETCH_ASSOC);
  if ($rwIscricao) {
    $userassin = $rwIscricao['renovacaosi'];
    $andamento = $rwIscricao['andamentosi'];
    $dataprazo = $rwIscricao['dataprazosi'];
    $idCursoInscricao = $rwIscricao['codcurso_ip'];
  }

  ?>

  <?php
  if ($visivel == 1) {
    if ($publico == 1) {
      require 'config_view/page_view_titulo.php';
      require 'config_view/page_view_youtube_thumb.php';
      require 'config_view/page_view_text.php';
      // require 'config_view/floatingActionButton.php';
    } else {
      if (!empty($_COOKIE['adminstart'])) {
        require 'config_view/page_view_titulo.php';
        require 'config_view/page_view_youtube_thumb.php';
        // require 'config_view/page_view_youtube.php';
        require_once 'config_view/page_view_text.php';
        // require 'config_view/floatingActionButton.php';
      } else if (!empty($_COOKIE['startusuario'])) {
        if ($assinante == 1) {
          require 'config_view/page_view_titulo.php';
          require 'config_view/page_view_youtube.php';
          require 'config_view/page_view_text.php';
          // require 'config_view/floatingActionButton.php';
        } else {
          if ($dataprazo >= date("Y-m-d")) {
            require 'config_view/page_view_titulo.php';
            require 'config_view/page_view_youtube_thumb.php';
            require 'config_view/page_view_text.php';
            // require 'config_view/floatingActionButton.php';
          } else {
            require 'config_view/page_view_text_Renovarrestrito.php';
          }
          // require 'config_view/page_view_text_restrito.php';
        }
      } else {
        require 'config_view/page_view_text_restrito.php';
      }
    }
  } else {
    require_once 'config_view/page_view_text_edicao.php';
  }
  ?>
