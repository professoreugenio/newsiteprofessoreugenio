<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

$stmt = config::connect()->query("SELECT codigopadraomsg, titulomsgPM FROM a_admin_padraoalulnosmsg ORDER BY dataPM DESC, codigopadraomsg DESC");
$msgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($msgs);
exit;
