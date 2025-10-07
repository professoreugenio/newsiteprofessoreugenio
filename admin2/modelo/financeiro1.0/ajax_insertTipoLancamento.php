<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    // Validação do método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.');
    }

    // Captura e valida os dados
    $nome = trim($_POST['novoTipo'] ?? '');
    $tipo = $_POST['tipoLancamentos'] ?? '';
    $valorBruto = $_POST['valor'] ?? '';
    $valorSanitizado = str_replace('.', '', $valorBruto); // remove milhar
    $valorConvertido = str_replace(',', '.', $valorSanitizado); // troca vírgula por ponto

    if (empty($nome)) {
        throw new Exception('Informe o nome do tipo.');
    }

    if (!in_array($tipo, ['1', '2'])) {
        throw new Exception('Tipo inválido.');
    }

    // Verifica duplicidade
    $verifica = $con->prepare("SELECT COUNT(*) FROM a_curso_financeiroLancamentos WHERE nomelancamentosFL = :nome AND tipoLancamentos = :tipo");
    $verifica->execute([
        ':nome' => $nome,
        ':tipo' => $tipo
    ]);
    if ($verifica->fetchColumn() > 0) {
        throw new Exception('Este tipo já está cadastrado.');
    }

    // Inserção
    $stmt = $con->prepare("INSERT INTO a_curso_financeiroLancamentos (nomelancamentosFL, tipoLancamentos, valorFL) VALUES (:nome, :tipo, :valor)");
    $stmt->execute([
        ':nome' => $nome,
        ':valor' => $valorConvertido,
        ':tipo' => $tipo
    ]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Tipo de lançamento inserido com sucesso.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
