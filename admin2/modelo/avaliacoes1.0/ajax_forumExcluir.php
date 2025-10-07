<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

$codigo = isset($_POST['codigoForum']) ? (int)$_POST['codigoForum'] : 0;

try {
    if ($codigo <= 0) throw new Exception('ID inválido');

    $del = config::connect()->prepare("DELETE FROM a_curso_forum WHERE codigoForum = :id LIMIT 1");
    $del->bindParam(':id', $codigo, PDO::PARAM_INT);
    $del->execute();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
