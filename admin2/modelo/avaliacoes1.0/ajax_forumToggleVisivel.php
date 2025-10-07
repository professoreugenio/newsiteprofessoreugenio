<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

$codigo = isset($_POST['codigoForum']) ? (int)$_POST['codigoForum'] : 0;

try {
    if ($codigo <= 0) throw new Exception('ID inválido');

    // Lê estado atual
    $st = config::connect()->prepare("SELECT visivelCF FROM a_curso_forum WHERE codigoForum = :id LIMIT 1");
    $st->bindParam(':id', $codigo, PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) throw new Exception('Registro não encontrado');

    $novo = ((int)$row['visivelCF'] === 1) ? 0 : 1;

    $up = config::connect()->prepare("UPDATE a_curso_forum SET visivelCF = :novo WHERE codigoForum = :id LIMIT 1");
    $up->bindParam(':novo', $novo, PDO::PARAM_INT);
    $up->bindParam(':id', $codigo, PDO::PARAM_INT);
    $up->execute();

    echo json_encode(['ok' => true, 'visivel' => $novo]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
