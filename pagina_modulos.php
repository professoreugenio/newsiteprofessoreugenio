<?php define('BASEPATH', true);
include 'conexao/class.conexao.php';
include 'autenticacao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <?php require 'paginasv1.0/paginamodulosHead.php'; ?>
</head>
<?php
if (empty($_GET['var'])) {
  echo ('<meta http-equiv="refresh" content="3; url=index.php">');
  echo ('<body><div class="container"><div class="col-sm-12"><p><h2>Aguarde...</h2></p><p>Dados não processados, novo redirecionamento.</p></div></div></body>');
  exit();
}
?>

<body>
  <?php require 'paginasv1.0/paginamodulosBody.php'; ?>
</body>

</html>