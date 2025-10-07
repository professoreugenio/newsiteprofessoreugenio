<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

$idUsuarioEnc = $_POST['idUsuario'] ?? '';
$chaveEnc     = $_POST['chaveTurma'] ?? '';

if ($idUsuarioEnc === '' || $chaveEnc === '') {
    echo json_encode(['ok' => false, 'msg' => 'Parâmetros insuficientes.']);
    exit;
}

// Decrypt dos parâmetros
$idUsuarioDec = encrypt($idUsuarioEnc, $action = 'd');
$chaveTurma   = encrypt($chaveEnc, $action = 'd');

if (!is_numeric($idUsuarioDec) || (int)$idUsuarioDec <= 0 || empty($chaveTurma)) {
    echo json_encode(['ok' => false, 'msg' => 'Dados inválidos.']);
    exit;
}

$idUsuario = (int)$idUsuarioDec;

try {
    $pdo = config::connect();

    $st = $pdo->prepare("
        DELETE FROM new_sistema_inscricao_PJA
        WHERE codigousuario = :uid AND chaveturma = :ch
        LIMIT 1
    ");
    $st->execute([
        ':uid' => $idUsuario,
        ':ch'  => $chaveTurma
    ]);

    if ($st->rowCount() > 0) {
        echo json_encode(['ok' => true, 'msg' => 'Inscrição excluída com sucesso.']);
    } else {
        echo json_encode(['ok' => false, 'msg' => 'Nenhuma inscrição encontrada.']);
    }
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro no banco de dados.']);
}
