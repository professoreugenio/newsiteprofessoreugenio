<?php
$con = config::connect();
if (!empty($_GET['var'])) {
  $decVar = encrypt($_GET['var'], $action = 'd');
  $expVar = explode("&", $decVar);
} else {
  if (!empty($_COOKIE['nav'])) {
    $decVar = encrypt($_COOKIE['nav'], $action = 'd');

    $expVar = explode("&", $decVar);
  }
}
$titulocurso = "";
$titulomodulo = "";
?>

<?php
if (!empty($expVar[1])) {
  $queryCatPJA = $con->prepare("SELECT nome,descricaosc,bgcolor,valorsc,pasta,codpagesadminsc FROM new_sistema_cursos WHERE codigocursos = :cod  ");
  $queryCatPJA->bindParam(":cod", $expVar[1]);
  $queryCatPJA->execute();
  $rwPageCurso = $queryCatPJA->fetch(PDO::FETCH_ASSOC);
  $titulocurso = $rwPageCurso['nome'];
  $idCurso = $expVar[1];
}
?>

<?php

if (!empty($expVar[2])) {

  $query = $con->prepare("SELECT modulo,bgcolor,codcursos,codigomodulos,visivelm, ordemm  FROM new_sistema_modulos_PJA WHERE codigomodulos = :id AND visivelm = '1' ORDER BY ordemm");
  $query->bindParam(":id", $expVar[2]);
  $query->execute();
  $rwModulo = $query->fetch(PDO::FETCH_ASSOC);
  $titulomodulo = $rwModulo['modulo'];
  $bgcolor = $rwModulo['bgcolor'];
  $decModulo = $expVar[2];
}

?>

<?php
$decPublic = $expVar[3];
$tituloPublicacao = "";
$olho = "";
$queryPublic = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
$queryPublic->bindParam(":id", $expVar[3]);
$queryPublic->execute();
$rwPublic = $queryPublic->fetch(PDO::FETCH_ASSOC);
$tituloPublicacao = $rwPublic['titulo'];
$olho = $rwPublic['olho'];
$assinante = $rwPublic['assinante'];
$ordempub = $rwPublic['ordem'];
$visivel = $rwPublic['visivel'];
$publico = $rwPublic['publico'];
$atividade = $rwPublic['atividadesp'];
$textoPublicacao = $rwPublic['texto'];
$decPublic = $rwPublic['codigopublicacoes'];
$idcopia = "0";
$idoriginal = $rwPublic['codigopublicacoes'];
?>

<?php
if ($rwPublic['idpubliccopia'] > "0") {
  $idcopia = $rwPublic['idpubliccopia'];
  $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes =:idcopia");
  $query->bindParam(":idcopia", $idcopia);
  $query->execute();
  $rwCopia = $query->fetch(PDO::FETCH_ASSOC);
  $tituloPublicacao = $rwCopia['titulo'];
  $olho = $rwCopia['olho'];
  $atividade = $rwCopia['atividadesp'];
  $textoPublicacao = $rwCopia['texto'];
  $decPublic = $rwCopia['codigopublicacoes'];
  // $ordempub = $rwCopia['ordem'] . "*";
}
?>

<?php
$con = config::connect();
$query = $con->prepare("SELECT * FROM new_sistema_publicacoes_fotos_PJA WHERE codpublicacao = :id AND favorito_pf='1' ORDER BY data DESC, hora DESC ");
$query->bindParam(":id", $decPublic);
$query->execute();
$rwImg = $query->fetch(PDO::FETCH_ASSOC);
if ($rwImg) {
  $imgMidia = $raizSite . "/fotos/publicacoes/" . $rwImg['pasta'] . "/" . $rwImg['foto'];
}

?>

<?php
if ($assinante == "1") {
  $star = ('<i class="fa fa-star" style="color: orange;" aria-hidden="true"></i>');
} else {
  $star = ('');
}
?>

<?php

$query = $con->prepare("SELECT * FROM new_sistema_publicacoes_url WHERE url_pu = :urlpu ");
$query->bindParam(":urlpu", $paginaatual);
$query->execute();
$rwUrl = $query->fetch(PDO::FETCH_ASSOC);
if (!$rwUrl) {

  $con = config::connect();
  $encpasta = encrypt($pastats, $action = 'e');
  $queryInsert = $con->prepare("INSERT INTO new_sistema_publicacoes_url (url_pu,chave_pu,key_pu)VALUES (:urlpu,:chavepu,:keypu)");
  $queryInsert->bindParam(":urlpu", $paginaatual);
  $queryInsert->bindParam(":chavepu", $pastats);
  $queryInsert->bindParam(":keypu", $encpasta);
  $queryInsert->execute();

  // if ($queryInsert->rowCount() >= 1) {
  //   echo '1';
  // } else {
  //   echo '2';
  // }
}
?>

<?php
$decvar = encrypt($_GET['var'], $action = 'd');
$expv = explode("&", $decvar);
$encMdls = encrypt($expv[0] . "&" . $expv[1] . "&0&0&0", $action = 'e');
?>

<?php

$con = config::connect();
$query = $con->prepare("SELECT * FROM new_sistema_msg_alunos WHERE tiposam='0' 
AND idmodulosam=:idmdl AND idartigo_sma = :id ORDER BY codigomsg DESC  ");
$query->bindParam(":idmdl", $lm_decModulo);
$query->bindParam(":id", $idoriginal);
$query->execute();
$rwNome = $query->fetch(PDO::FETCH_ASSOC);
$idmsg = $rwNome['codigomsg'];
$idpublicmsg = $rwNome['idartigo_sma'];
$encId = encrypt($idmsg . "&" . $decPublic, $action = 'e');

?>