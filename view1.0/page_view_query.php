<?php
function safeRedirect($url, $delay = 0)
{
    echo ('<meta http-equiv="refresh" content="0; url=../">');
    exit();
}
if (empty($_GET['var'])) {
    safeRedirect('index.php', 20);
}
if (!empty($_GET['permissaoanexo'])) {
    $decVar = encrypt($_GET['permissaoanexo'], 'd');
    $expVar = explode("&", $decVar);
} elseif (!empty($_GET['var'])) {
    $decVar = encrypt($_GET['var'], 'd');
    $expVar = explode("&", $decVar);
} elseif (!empty($_COOKIE['nav'])) {
    $decVar = encrypt($_COOKIE['nav'], 'd');
    $expVar = explode("&", $decVar);
} else {
    safeRedirect('index.php', 0);
}
?>
<?php
$con = config::connect();
if (!empty($_GET['permissaoanexo'])) {
    $decVar = encrypt($_GET['permissaoanexo'], $action = 'd');
    $expVar = explode("&", $decVar);
}
$titulocurso = "";
$titulomodulo = "";
?>
<?php
$idCurso = $expVar[1];
if (!empty($expVar[1])) {
    $queryCatPJA = $con->prepare("SELECT nome,descricaosc,bgcolor,valorsc,pasta,codpagesadminsc FROM new_sistema_categorias_PJA WHERE codigocategorias = :cod  ");
    $queryCatPJA->bindParam(":cod", $expVar[1]);
    $queryCatPJA->execute();
    $rwPageCurso = $queryCatPJA->fetch(PDO::FETCH_ASSOC);
    $titulocurso = $rwPageCurso['nome']??'Curso não definido';
}
?>
<?php
$idmodulo = "0";
if (!empty($expVar[2])) {
    $query = $con->prepare("SELECT modulo,bgcolorsm,codcursos,codigomodulos,visivelm, ordemm  FROM new_sistema_modulos_PJA WHERE codigomodulos = :id AND visivelm = '1' ORDER BY ordemm");
    $query->bindParam(":id", $expVar[2]);
    $query->execute();
    $rwModulo = $query->fetch(PDO::FETCH_ASSOC);
    $titulomodulo = $rwModulo['modulo']??'Módulo não definido';
    $bgcolor = $rwModulo['bgcolorsn']??'';
    $decModulo = $expVar[2]??'';
    $idmodulo = $rwModulo['codigomodulos']??'';
}
?>
<?php
$decPublic = $expVar[3]??'';
$tituloPublicacao = "";
$olho = "";
$idcopia = "0";
$idoriginal = "";
$visivel = "0";
$ordempub = "0";
$assinante = "0";

// Consulta da publicação original
$queryPublic = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :id ");
$queryPublic->bindParam(":id", $decPublic);
$queryPublic->execute();
$rwPublic = $queryPublic->fetch(PDO::FETCH_ASSOC);

if ($rwPublic) {
    $tituloPublicacao = $rwPublic['titulo'];
    $olho = $rwPublic['olho']??'';
    // Validação dos campos antes de atribuir
    $assinante = isset($rwPublic['assinante']) ? $rwPublic['assinante'] : '0';
    $idmodulo = isset($rwPublic['codmodulo_sp']) ? $rwPublic['codmodulo_sp'] : '0';
    $ordempub = isset($rwPublic['ordem']) ? $rwPublic['ordem'] : '0';
    $visivel = isset($rwPublic['visivel']) ? $rwPublic['visivel'] : '0';
    $publico = isset($rwPublic['publico']) ? $rwPublic['publico'] : '0';
    $atividade = isset($rwPublic['atividadesp']) ? $rwPublic['atividadesp'] : '';
    $textoPublicacao = isset($rwPublic['texto']) ? $rwPublic['texto'] : '';
    $decPublic = isset($rwPublic['codigopublicacoes']) ? $rwPublic['codigopublicacoes'] : '';
    $idoriginal = isset($rwPublic['codigopublicacoes']) ? $rwPublic['codigopublicacoes'] : '';

    // Verifica se é uma cópia de outra publicação
    if (!empty($rwPublic['idpubliccopia']) && $rwPublic['idpubliccopia'] > 0) {
        $idcopia = $rwPublic['idpubliccopia'];

        $query = $con->prepare("SELECT * FROM new_sistema_publicacoes_PJA WHERE codigopublicacoes = :idcopia");
        $query->bindParam(":idcopia", $idcopia);
        $query->execute();
        $rwCopia = $query->fetch(PDO::FETCH_ASSOC);

        if ($rwCopia) {
            $tituloPublicacao = $rwCopia['titulo']??'';
            $olho = $rwCopia['olho']??'';
            $idmodulo = $rwCopia['codmodulo_sp']??'';
            $atividade = $rwCopia['atividadesp']??'';
            $textoPublicacao = $rwCopia['texto']??'';
            $decPublic = $rwCopia['codigopublicacoes']??'';
            $assinante = $rwCopia['assinante']??'';
        }
    }
} else {
    // Defina um fallback ou mensagem de erro se a publicação não for encontrada
    $tituloPublicacao = "Publicação não encontrada.";
}
?>
<?php
$con = config::connect();
$query = $con->prepare("SELECT * FROM new_sistema_publicacoes_fotos_PJA WHERE codpublicacao = :id AND favorito_pf='1' ORDER BY data DESC, hora DESC ");
$query->bindParam(":id", $decPublic);
$query->execute();
$rwImg = $query->fetch(PDO::FETCH_ASSOC);
$imgMidia = ('https://professoreugenio.com/img/capasite.jpg');
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
if (!empty($_GET['permissaoanexo'])) {
    $decVar = encrypt($_GET['permissaoanexo'], $action = 'd');
} else {
    $decvar = encrypt($_GET['var'], $action = 'd');
}
$expv = explode("&", $decvar);
$encMdls = encrypt($expv[0] . "&" . $expv[1] . "&0&0&0", $action = 'e');
?>
<?php
$con = config::connect();
$query = $con->prepare("SELECT * FROM new_sistema_msg_alunos WHERE tiposam='0' 
AND idmodulosam = :idmdl AND idartigo_sma = :id ORDER BY codigomsg DESC");
$query->bindParam(":idmdl", $lm_decModulo);
$query->bindParam(":id", $idoriginal);
$query->execute();
$rwNome = $query->fetch(PDO::FETCH_ASSOC);
if ($rwNome && is_array($rwNome)) {
    $idmsg = $rwNome['codigomsg'];
    $idpublicmsg = $rwNome['idartigo_sma'];
    $encId = encrypt($idmsg . "&" . $decPublic, $action = 'e');
} else {
    // Tratamento caso a consulta não retorne dados
    $idmsg = null;
    $idpublicmsg = null;
    $encId = null;
    // Log de erro ou mensagem opcional
    // error_log("Nenhuma mensagem encontrada para os parâmetros fornecidos.");
}
?>

<?php
$nrAula = numerodaaula($decModulo, $idTurma, $data);
?>

<?php
$query = $con->prepare("SELECT * FROM a_aluno_permissoes WHERE idalunop = :id ");
$query->bindParam(":id", $codigoUser);
$query->execute();
$rwPerm = $query->fetch(PDO::FETCH_ASSOC);
$aut = !empty($rwPerm['autorize1']) ? $rwPerm['autorize1'] : '0';
$showmodal = ($aut == 1) ? 'show' : '';
?>