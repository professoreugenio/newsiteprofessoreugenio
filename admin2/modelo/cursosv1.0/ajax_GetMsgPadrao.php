<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_GET['id'] ?? 0);

$stmt = config::connect()->prepare("SELECT titulomsgPM, textoPM FROM a_admin_padraoalulnosmsg WHERE codigopadraomsg = :id LIMIT 1");
$stmt->bindParam(':id', $id);
$stmt->execute();
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($msg);
exit;
