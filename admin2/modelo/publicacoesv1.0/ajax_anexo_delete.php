<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['sucesso' => false]);
    exit;
}

// Busca para apagar arquivo físico (opcional)
$sel = $con->prepare("SELECT anexopa, pastapa FROM new_sistema_publicacoes_anexos_PJA WHERE codigomanexos = :id");
$sel->execute([':id' => $id]);
$ax = $sel->fetch(PDO::FETCH_ASSOC);

$del = $con->prepare("DELETE FROM new_sistema_publicacoes_anexos_PJA WHERE codigomanexos = :id");
$ok = $del->execute([':id' => $id]);

if ($ok && !empty($ax['anexopa']) && !empty($ax['pastapa'])) {
    $path = APP_ROOT . "/../anexos/publicacoes/{$ax['pastapa']}/{$ax['anexopa']}";
    if (is_file($path)) {
        @unlink($path);
    }
}

echo json_encode(['sucesso' => $ok]);
