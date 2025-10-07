<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

if (!isset($_POST['chaveturma'])) {
  echo json_encode(['erro' => 'Chave da turma não informada']);
  exit;
}

$chaveTurma = $_POST['chaveturma'];
$data = date('Y-m-d');
$hora = date('H:i:s');

// Função para gerar chave


// Excluir chave anterior
$delete = $con->prepare("DELETE FROM new_sistema_chave WHERE chaveturmasc = :chaveturma");
$delete->bindParam(":chaveturma", $chaveTurma);
$delete->execute();

// Gerar e inserir nova chave
$chave = gerachave();

$insert = $con->prepare("INSERT INTO new_sistema_chave (chavesc, chaveturmasc, datasc, horasc)
VALUES (:chave, :chaveturmasc, :datasc, :horasc)");
$insert->bindParam(":chave", $chave);
$insert->bindParam(":chaveturmasc", $chaveTurma);
$insert->bindParam(":datasc", $data);
$insert->bindParam(":horasc", $hora);
$insert->execute();

echo json_encode(['sucesso' => true, 'chave' => $chave]);
