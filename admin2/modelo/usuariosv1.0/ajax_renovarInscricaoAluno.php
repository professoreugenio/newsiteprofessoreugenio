<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

$idUsuarioEnc = $_POST['idUsuario'] ?? '';
$chaveEnc     = $_POST['chaveTurma'] ?? '';
$dias         = isset($_POST['dias']) ? (int)$_POST['dias'] : 0;

$permitidos = [2, 30, 366, 1830];
if ($idUsuarioEnc === '' || $chaveEnc === '' || !in_array($dias, $permitidos, true)) {
    echo json_encode(['ok' => false, 'msg' => 'Parâmetros inválidos.']);
    exit;
}

$idUsuarioDec = encrypt($idUsuarioEnc, $action = 'd');
$chaveTurma   = encrypt($chaveEnc, $action = 'd');

if (!is_numeric($idUsuarioDec) || (int)$idUsuarioDec <= 0 || !$chaveTurma) {
    echo json_encode(['ok' => false, 'msg' => 'Dados inválidos.']);
    exit;
}

$idUsuario = (int)$idUsuarioDec;

try {
    $pdo = config::connect();

    // Garante que exista inscrição antes de atualizar
    $chk = $pdo->prepare("
        SELECT dataprazosi
        FROM new_sistema_inscricao_PJA
        WHERE codigousuario = :uid AND chaveturma = :ch
        LIMIT 1
    ");
    $chk->execute([':uid' => $idUsuario, ':ch' => $chaveTurma]);
    $existe = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$existe) {
        echo json_encode(['ok' => false, 'msg' => 'Inscrição não localizada.']);
        exit;
    }

    // Atualiza dataprazosi somando os dias informados
    $up = $pdo->prepare("
        UPDATE new_sistema_inscricao_PJA
        SET dataprazosi = DATE_ADD(COALESCE(dataprazosi, CURDATE()), INTERVAL :dias DAY)
        WHERE codigousuario = :uid AND chaveturma = :ch
        LIMIT 1
    ");
    $up->bindValue(':dias', $dias, PDO::PARAM_INT);
    $up->bindValue(':uid',  $idUsuario, PDO::PARAM_INT);
    $up->bindValue(':ch',   $chaveTurma, PDO::PARAM_STR);
    $up->execute();

    if ($up->rowCount() > 0) {
        echo json_encode(['ok' => true, 'msg' => 'Prazo renovado com sucesso.']);
    } else {
        // Mesmo valor pode não alterar rowCount; considerar ok se não houve erro
        echo json_encode(['ok' => true, 'msg' => 'Prazo atualizado.']);
    }
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro no banco de dados.']);
}
