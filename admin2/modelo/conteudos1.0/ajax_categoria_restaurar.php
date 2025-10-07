<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$id = intval($_POST['id']);
$stmt = $con->prepare("UPDATE new_sistema_categorias_PJA SET lixeirasc = 0 WHERE codigocategorias = :id");
$stmt->bindParam(':id', $id);
$ok = $stmt->execute();

echo json_encode(['sucesso' => $ok]);
