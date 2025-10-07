<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$id = intval($_POST['id'] ?? 0);
$vis = intval($_POST['visivel'] ?? 0);

if ($id <= 0) {
    echo json_encode(['sucesso' => false]);
    exit;
}

$stmt = $con->prepare("UPDATE new_sistema_publicacoes_anexos_PJA SET visivel = :v WHERE codigomanexos = :id");
$ok = $stmt->execute([':v' => $vis, ':id' => $id]);

echo json_encode(['sucesso' => $ok]);
