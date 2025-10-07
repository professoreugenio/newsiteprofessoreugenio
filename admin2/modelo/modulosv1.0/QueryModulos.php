<?php
if (!isset($_GET['md']) || empty($_GET['md'])) {
    header('Location: index.php');
    exit;
}
require_once APP_ROOT . '/conexao/class.conexao.php'; // caso ainda não tenha sido incluído
require_once APP_ROOT . '/autenticacao.php'; // idem
$idModulo = encrypt($_GET['md'], $action = 'd');
// Verifica se é um número válido após a descriptografia
if (!is_numeric($idModulo)) {
    header('Location: index.php');
    exit;
}
$queryModulo = $con->prepare("SELECT * FROM new_sistema_modulos_PJA WHERE codigomodulos  = :idmodulo");
$queryModulo->bindParam(":idmodulo", $idModulo, PDO::PARAM_INT);
$queryModulo->execute();
$rwModulo = $queryModulo->fetch(PDO::FETCH_ASSOC);
// Se o curso não foi encontrado, redireciona
if (!$rwModulo) {
    header('Location: index.php');
    exit;
}
$encIdModulo= encrypt($idModulo, $action = 'e' );
// Atribuição segura dos valores
$Bocolor = $rwModulo['bgcolor'];
$Nomemodulo = $rwModulo['modulo'] . $idModulo;
$Descricao = $rwModulo['descricao'];
$Valor = $rwModulo['valorm'];
$ValorHora = $rwModulo['valorh'];
$NumeroOrdemClassificacao = $rwModulo['ordemm'];
$QuantidadedeAulas = $rwModulo['nraulasm'];
$ChaveModulo = $rwModulo['chavem'];
/** para checkbox */
$chkon   = ($rwModulo['visivelm'] == '1') ? "checked" : "";
$chkvh   = ($rwModulo['visivelhome'] == '1') ? "checked" : "";
