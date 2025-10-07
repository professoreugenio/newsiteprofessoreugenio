<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

try {
  $pdo = config::connect();

  $id = isset($_POST['idUsuario']) ? (int)$_POST['idUsuario'] : 0;
  if ($id <= 0) {
    echo json_encode(['status' => 'erro', 'msg' => 'ID inválido.']);
    exit;
  }

  // Campos permitidos
  $nome   = trim($_POST['nome'] ?? '');
  $email  = trim($_POST['email'] ?? '');
  $emailAnterior = trim($_POST['emailanterior'] ?? '');
  $nasc   = $_POST['datanascimento_sc'] ?? null;
  $tel    = trim($_POST['telefone'] ?? '');
  $cel    = trim($_POST['celular'] ?? '');
  $estado = trim($_POST['estado'] ?? '');
  $possuipc = ($_POST['possuipc'] ?? '') === '' ? null : (string)$_POST['possuipc'];
  $emailbloq = (string)($_POST['emailbloqueio'] ?? '0');
  $bloqpost  = (string)($_POST['bloqueiopost'] ?? '0');
  $pastasc   = trim($_POST['pastasc'] ?? '');
  $imagem200 = trim($_POST['imagem200'] ?? '');

  $sql = "
      UPDATE new_sistema_cadastro SET
        nome = :nome,
        email = :email,
        emailanterior = :emailanterior,
        datanascimento_sc = :nasc,
        telefone = :tel,
        celular = :cel,
        estado = :estado,
        possuipc = :possuipc,
        emailbloqueio = :emailbloq,
        bloqueiopost = :bloqpost,
        pastasc = :pastasc,
        imagem200 = :imagem200
      WHERE codigocadastro = :id
      LIMIT 1
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ':nome' => $nome,
    ':email' => $email,
    ':emailanterior' => $emailAnterior,
    ':nasc' => $nasc ?: null,
    ':tel' => $tel,
    ':cel' => $cel,
    ':estado' => strtoupper($estado),
    ':possuipc' => $possuipc,
    ':emailbloq' => $emailbloq,
    ':bloqpost' => $bloqpost,
    ':pastasc' => $pastasc,
    ':imagem200' => $imagem200,
    ':id' => $id
  ]);







  echo json_encode(['status' => 'ok', 'msg' => 'Dados atualizados com sucesso.']);
} catch (Exception $e) {
  echo json_encode(['status' => 'erro', 'msg' => 'Erro: ' . $e->getMessage()]);
}
