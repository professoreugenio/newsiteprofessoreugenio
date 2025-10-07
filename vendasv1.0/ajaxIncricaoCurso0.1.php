<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 1));

require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

// Verificação do método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Método inválido']);
    exit;
}

$con = config::connect();

// Capturar e limpar os dados
$nome     = trim($_POST['nome'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$chaveCript = trim($_POST['Codigochave'] ?? '');

if (!$nome || !$email || !$telefone || !$chaveCript) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

// Descriptografar chave
$chave = encrypt($chaveCript, 'd');

// Verificar validade da chave
$stmtChave = $con->prepare("SELECT chavesc, chaveturmasc FROM new_sistema_chave WHERE chavesc = :chavesc");
$stmtChave->bindParam(":chavesc", $chave);
$stmtChave->execute();
$rwChave = $stmtChave->fetch(PDO::FETCH_ASSOC);

if (!$rwChave) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Chave do curso inválida ou expirada.']);
    exit;
}

$chaveTurma = $rwChave['chaveturmasc'];

try {
    // Verificar se já existe aluno com esse e-mail nesta turma
    $queryCad = $con->prepare("
        SELECT nc.codigocadastro 
        FROM new_sistema_cadastro AS nc
        JOIN new_sistema_inscricao_PJA AS ni ON ni.codigousuario = nc.codigocadastro
        WHERE nc.email = :email AND ni.chaveturma = :chaveturma
    ");
    $queryCad->bindParam(":email", $email);
    $queryCad->bindParam(":chaveturma", $chaveTurma);
    $queryCad->execute();
    $existe = $queryCad->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        echo json_encode(['status' => 'ok', 'mensagem' => 'Você já possui inscrição nesta turma.']);
        exit;
    }

    // Criar nome da pasta do aluno
    $pasta = mesabreviado(date('Y-m-d')) . "_" . date("Ymd") . time();

    // Inserir aluno
    $stmt = $con->prepare("
        INSERT INTO new_sistema_cadastro (nome, pastasc, email, celular, data_sc)
        VALUES (:nome, :pasta, :email, :telefone, NOW())
    ");
    $stmt->execute([
        ':nome'     => $nome,
        ':pasta'    => $pasta,
        ':email'    => $email,
        ':telefone' => $telefone
    ]);

    $novoId = $con->lastInsertId();

    // Vincular aluno à turma
    $stmtInscricao = $con->prepare("
        INSERT INTO new_sistema_inscricao_PJA (codigousuario, chaveturma, data_ins)
        VALUES (:iduser, :chaveturma, NOW())
    ");
    $stmtInscricao->execute([
        ':iduser'     => $novoId,
        ':chaveturma' => $chaveTurma
    ]);

    echo json_encode(['status' => 'ok', 'id' => $novoId]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Erro no banco de dados.']);
}
