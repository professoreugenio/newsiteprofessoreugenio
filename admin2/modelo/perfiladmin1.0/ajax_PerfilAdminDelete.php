<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

$decadm = encrypt($_COOKIE['adminuserstart'], 'd');
$expadm = explode("&", $decadm);
$niveladm = $expadm[1] ?? '';
$codadm   = $expadm[0] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!function_exists('temPermissao')) {
    function temPermissao($nivelUsuario, $permitidos = [])
    {
        return in_array($nivelUsuario, $permitidos);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido.']);
        exit;
    }

    $niveladm = isset($niveladm) ? (int)$niveladm : 0; // vem do autenticacao.php
    if (!temPermissao($niveladm, [1])) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Permissão negada.']);
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'ID inválido.']);
        exit;
    }

    if ($id == $codadm) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'O usuário não pode se excluir.']);
        exit;
    }

    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verifica existência
    $s = $con->prepare("SELECT codigousuario FROM new_sistema_usuario WHERE codigousuario = :id LIMIT 1");
    $s->bindValue(':id', $id, PDO::PARAM_INT);
    $s->execute();
    if ($s->rowCount() === 0) {
        echo json_encode(['status' => 'erro', 'mensagem' => 'Usuário não encontrado.']);
        exit;
    }

    try {
        // Exclusão definitiva
        $del = $con->prepare("DELETE FROM new_sistema_usuario WHERE codigousuario = :id LIMIT 1");
        $del->bindValue(':id', $id, PDO::PARAM_INT);
        $del->execute();

        echo json_encode(['status' => 'ok', 'mensagem' => 'Usuário excluído com sucesso.']);
        exit;
    } catch (Throwable $e) {
        // Fallback: se houver restrições de FK, apenas desativar
        $upd = $con->prepare("UPDATE new_sistema_usuario SET liberado = 0, onlinesu = 0, timestampsu = :t WHERE codigousuario = :id LIMIT 1");
        $upd->bindValue(':t', date('Y-m-d H:i:s'));
        $upd->bindValue(':id', $id, PDO::PARAM_INT);
        $upd->execute();

        echo json_encode([
            'status' => 'ok',
            'mensagem' => 'Usuário vinculado a registros. Conta desativada (liberado=0).'
        ]);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro ao excluir: ' . $e->getMessage()]);
}
