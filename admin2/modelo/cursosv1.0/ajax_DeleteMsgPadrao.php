<?php define('BASEPATH', true);
// include '../../../conexao/class.conexao.php';
// include '../../../autenticacao.php';
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'msg' => 'ID inválido.']);
    exit;
}

$stmt = config::connect()->prepare("DELETE FROM a_admin_padraoalulnosmsg WHERE codigopadraomsg = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
if ($stmt->execute()) {
    echo json_encode(['status' => 'ok', 'msg' => 'Mensagem excluída com sucesso!']);
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Erro ao excluir mensagem.']);
}
