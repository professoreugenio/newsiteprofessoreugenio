<?php
if (!isset($_GET['md']) || empty($_GET['md'])) {
    header('Location: index.php');
    exit;
}
if (empty($_GET['pub']) and empty($_GET['pub'])) {
    header('Location: index.php');
    exit;
}

$idPublicacao = encrypt($_GET['pub'], $action = 'd');
// Verifica se é um número válido após a descriptografia
if (!is_numeric($idPublicacao)) {
    header('Location: index.php');
    exit;
}
$texto = "não definifo";
if (!isset($_GET['pub']) || !empty($_GET['pub'])) {


    $idPublicacao = encrypt($_GET['pub'], $action = 'd');












    $queryPublicacao = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes   = :idpublicacao");
    $queryPublicacao->bindParam(":idpublicacao", $idPublicacao, PDO::PARAM_INT);
    $queryPublicacao->execute();
    $rwPublicacao = $queryPublicacao->fetch(PDO::FETCH_ASSOC);
    $encIdPublicacao = encrypt($idPublicacao, $action = 'e');
    // Atribuição segura dos valores
    $tituloPublicacao = $rwPublicacao['titulo'] ?? '';
    $linkExterno = $rwPublicacao['linkexterno'] ?? '';
    $Descricao = $rwPublicacao['olho'] ?? '';
    $tags = $rwPublicacao['tag'] ?? '';
    $texto = $rwPublicacao['texto'] ?? '';
    $pastapub = $rwPublicacao['pasta'] ?? '';
    $idModuloPublicacao = $rwPublicacao['codmodulo_sp'] ?? '';
    $idCursoPublicacao = $rwPublicacao['codigocurso_sp'] ?? '';
    $ordem = $rwPublicacao['ordem'] ?? '';

    $visivel = $rwPublicacao['visivel'] ?? '';
    /** para checkbox */
    $chkon   = ($visivel == '1') ? "checked" : "";

    // if ($comercial == 1): $ordem = '1000';
    // endif;
} else {
}
// Se o curso não foi encontrado, redireciona
if (!$rwPublicacao) {
    // header('Location: index.php');
    // exit;
}

// echo "<script> alert('{$idPublicacao}');window.location.href='$paginaatual'</script>";
// exit();

// IDs (ajuste conforme seu contexto)
$idCurso       = $_GET['id']       ?? null;
$idModulo      = $_GET['md']      ?? null;
$idPublicacao  = $idPublicacao  ?? null;

// Encrypts
$encCurso      = $idCurso       ? encrypt($idCurso, 'e')      : '';
$encModulo     = $idModulo      ? encrypt($idModulo, 'e')     : '';
$encPublicacao = $idPublicacao  ? encrypt($idPublicacao, 'e') : '';

// Carrega fotos
$query = $con->prepare("
  SELECT codigomfotos, pasta, foto, favorito_pf, data, hora
  FROM new_sistema_publicacoes_fotos_PJA
  WHERE codpublicacao = :id
  ORDER BY data DESC, hora DESC
");
$query->bindParam(":id", $idPublicacao, PDO::PARAM_INT);
$query->execute();
$fotos = $query->fetchAll(PDO::FETCH_ASSOC);

$qtdFotos = count($fotos);



if ($comercial == 1):

    $idModuloPc = $dec = encrypt($_GET['md'], $action = 'd');
    $idCursoPC = encrypt($_GET['id'], $action = 'd');
    $query = $con->prepare("SELECT * FROM a_aluno_publicacoes_cursos WHERE idpublicacaopc  = :idlicao AND idmodulopc = :idmodulo AND idcursopc = :idcurso");
    $query->bindParam(":idlicao", $idPublicacao, PDO::PARAM_INT);
    $query->bindParam(":idmodulo", $idModuloPc, PDO::PARAM_INT);
    $query->bindParam(":idcurso", $idCursoPC, PDO::PARAM_INT);
    $query->execute();
    $rwNome = $query->fetch(PDO::FETCH_ASSOC);
    // $idPublicacao = $rwNome['idpublicacaopc'] ?? '';
    $ordem = $rwNome['ordempc'] ?? '';
    $visivel = $rwNome['visivelpc'] ?? '';


endif;
