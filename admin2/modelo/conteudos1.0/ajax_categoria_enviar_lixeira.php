<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$id = intval($_POST['id']);

$stmt = $con->prepare("UPDATE new_sistema_cursos SET lixeirasc = 1 WHERE codigocursos = :id");
$stmt->bindParam(':id', $id);
$ok = $stmt->execute();

echo json_encode(['sucesso' => $ok]);
