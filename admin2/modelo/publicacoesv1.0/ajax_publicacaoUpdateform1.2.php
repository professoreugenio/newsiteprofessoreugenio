<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$idPublicacao = encrypt(htmlspecialchars($_POST['idpublicacao']), 'd');
$idModulo = encrypt(htmlspecialchars($_POST['idmodulo']), 'd');
$idCurso = encrypt(htmlspecialchars($_POST['idcurso']), 'd');
$titulo = trim($_POST['titulo']);
$linkexterno = trim($_POST['linkexterno']);
$descricao = trim($_POST['descricao']);
$comercial = trim($_POST['comercial']);
$tags = trim($_POST['tags']);
$ordem = intval($_POST['ordem']);
$visivel = isset($_POST['visivel']) ? 1 : 0;
$visivelhome = isset($_POST['visivelhome']) ? 1 : 0;

 "Modl: {$idModulo} Curso: {$idCurso} Curso: {$comercial} Ordem: {$ordem} Visivel: {$visivel}";

 if($comercial == 1) {

$con = config::connect();
$queryUpdate = $con->prepare("UPDATE a_aluno_publicacoes_cursos SET idmodulopc=:idmodulo,ordempc=:ordem WHERE codigopublicacoescursos = :idpublicacao");
$queryUpdate->bindParam(":idmodulo", $idModulo);
$queryUpdate->bindParam(":ordem", $ordem);
$queryUpdate->bindParam(":idpublicacao", $idPublicacao);
$queryUpdate->execute();

 }


