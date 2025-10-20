<?php
$user = $_COOKIE['startusuario'] ?? '';
$admin = $_COOKIE['adminstart'] ?? '';
$autorizado = !empty($user) || !empty($admin);
?>
<?php $permissao = "0"; ?>
<?php
$idTurma = "0";
$codigoUser = "0";
$comercial = "0";
$aut1 = "0";
$aut2 = "0";
$aut3 = "0";
$aut4 = "0";
if (!empty($_COOKIE['adminstart'])) {
  $decUser = encrypt($_COOKIE['adminstart'], $action = 'd');
  $exp = explode("&", $decUser);
  $idusuario = $exp[0];
  $codigoUser = $exp[0];
  $comercial = "1";
  $dataprazo = $data;
} else {
  if (!empty($_COOKIE['startusuario'])) {
    $decUser = encrypt($_COOKIE['startusuario'], $action = 'd');
    $exp = explode("&", $decUser);
    $codigoUser = $exp[0];
    $idusuario = $exp[0];
    $nomeUser = $exp[1] ?? '';
    $idTurma = $exp[4] ?? '';
    $chaveturmaUser = "";
    $nomeTurma = "Não definido";
    $idCursoInscricao = "0";
    if (!empty($exp[4])) {
      $idTurma = $exp[4];
      $chaveturmaUser = $exp[5];
      $queryTurma = $con->prepare("SELECT * FROM  new_sistema_cursos_turmas WHERE codigoturma = :idsubcat ");
      $queryTurma->bindParam(":idsubcat", $idTurma);
      $queryTurma->execute();
      $rwTurma = $queryTurma->fetch(PDO::FETCH_ASSOC);
      $nomeTurma = $rwTurma['nometurma'] ?? '';
      $chaveTurma = $rwTurma['chave'] ?? '';
      $idcurso = $rwTurma['codcursost'] ?? '';
      $comercial = $rwTurma['comercialt'] ?? '';
      $masterclass = $rwTurma['masterclass'] ?? '';
      $query = $con->prepare("SELECT * FROM new_sistema_inscricao_PJA WHERE chaveturma = :chave AND codigousuario=:iduser ");
      $query->bindParam(":chave", $chaveturmaUser);
      $query->bindParam(":iduser", $codigoUser);
      $query->execute();
      $rwIscricao = $query->fetch(PDO::FETCH_ASSOC);
      $userassin = $rwIscricao['renovacaosi'] ?? '';
      $andamento = $rwIscricao['andamentosi'] ?? '';
      $dataprazo = $rwIscricao['dataprazosi'] ?? '';
      $idCursoInscricao = $rwIscricao['codcurso_ip'] ?? '';
    }
  } else {
    // echo "<script> alert('Não');window.location.href='$paginaatual'</script>";
    // exit();
  }
}
