<?php define('BASEPATH', true);
include '../../conexao/class.conexao.php';
include '../../autenticacao.php';
$navDec = encrypt($_COOKIE['nav'], 'd');
if (is_string($navDec) && !empty($navDec)) {
    $expnav = explode("&", $navDec);
} else {
    error_log("Falha ao descriptografar o cookie 'nav'.");
}
$iduser = $expnav[0];
$idatv = $expnav[5];
$codigoaula = $expnav[4];
$codigomodulo = $expnav[3];
?>

<?php
/**
 * NOME MÓDULO
 */
$queryModulo = $con->prepare("SELECT * FROM new_sistema_modulos_PJA WHERE codigomodulos = :codigomodulo");
$queryModulo->bindParam(":codigomodulo", $codigomodulo);
$queryModulo->execute();
$rwModulo = $queryModulo->fetch(PDO::FETCH_ASSOC);
if ($rwModulo) {
    $nmmodulo = $rwModulo['modulo'];
    $bgcolor = $rwModulo['bgcolor'];
} else {
    $nmmodulo = 'Módulo não encontrado';
    $bgcolor = '#ccc';
}
?>

<?php
/**
 * TÍTULO DA AULA
 */
$queryAula = $con->prepare("SELECT * FROM  new_sistema_publicacoes_PJA
    WHERE codigopublicacoes = :idpublicaa");
$queryAula->bindParam(":idpublicaa", $codigoaula);
$queryAula->execute();
$rwAulaAtual = $queryAula->fetch(PDO::FETCH_ASSOC);
if ($rwAulaAtual) {
    $tituloAula = $rwAulaAtual['titulo'];
} else {
    $tituloAula = 'Nenhuma publicação disponível.';
}

?>