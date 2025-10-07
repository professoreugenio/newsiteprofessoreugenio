<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
// ajuste o caminho conforme seu projeto
header('Content-Type: application/json; charset=utf-8');

$titulo = trim($_POST['titulo'] ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');
$acao = $_POST['acao'] ?? 'salvar';

if (!$titulo || !$mensagem) {
    echo json_encode(['status' => 'erro', 'msg' => 'Preencha o título e o texto da mensagem!']);
    exit;
}

// Verifica se já existe mensagem com mesmo título
$stmt = config::connect()->prepare("SELECT codigopadraomsg FROM a_admin_padraoalulnosmsg WHERE titulomsgPM = :titulo LIMIT 1");
$stmt->bindParam(':titulo', $titulo);
$stmt->execute();
$existe = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existe) {
    // Atualizar mensagem existente
    $stmtUp = config::connect()->prepare("UPDATE a_admin_padraoalulnosmsg SET textoPM = :texto, dataPM = CURDATE(), horaPM = CURTIME() WHERE codigopadraomsg = :codigo");
    $stmtUp->bindParam(':texto', $mensagem);
    $stmtUp->bindParam(':codigo', $existe['codigopadraomsg']);
    if ($stmtUp->execute()) {
        echo json_encode(['status' => 'ok', 'msg' => 'Mensagem atualizada com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao atualizar.']);
    }
} else {
    // Inserir nova mensagem
    $stmtIns = config::connect()->prepare("INSERT INTO a_admin_padraoalulnosmsg (titulomsgPM, textoPM, dataPM, horaPM) VALUES (:titulo, :texto, CURDATE(), CURTIME())");
    $stmtIns->bindParam(':titulo', $titulo);
    $stmtIns->bindParam(':texto', $mensagem);
    if ($stmtIns->execute()) {
        echo json_encode(['status' => 'ok', 'msg' => 'Mensagem salva com sucesso!']);
    } else {
        echo json_encode(['status' => 'erro', 'msg' => 'Erro ao salvar.']);
    }
}
exit;
