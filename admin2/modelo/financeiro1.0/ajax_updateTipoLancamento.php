<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    // Verifica o método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método inválido.');
    }

    // Captura os dados
    $id = $_POST['id'] ?? null;
    $nome = trim($_POST['nome'] ?? '');
    $valorBruto = $_POST['valor'] ?? null;

    if (empty($id) || !is_numeric($id)) {
        throw new Exception('ID inválido.');
    }

    if (empty($nome)) {
        throw new Exception('O nome não pode estar vazio.');
    }

    // Normaliza valor (transforma "1.234,56" em "1234.56")
    $valorBruto = $_POST['valor'] ?? '';
    $valorSanitizado = str_replace('.', '', $valorBruto); // remove milhar
    $valorConvertido = str_replace(',', '.', $valorSanitizado); // troca vírgula por ponto



    // Verifica duplicidade de nome com outro ID
    $verifica = $con->prepare("SELECT COUNT(*) FROM a_curso_financeiroLancamentos WHERE nomelancamentosFL = :nome AND codigolancamentos != :id");
    $verifica->execute([
        ':nome' => $nome,
        ':id' => $id
    ]);
    if ($verifica->fetchColumn() > 0) {
        throw new Exception('Já existe um tipo com esse nome.');
    }

    // Atualização
    $stmt = $con->prepare("UPDATE a_curso_financeiroLancamentos 
    SET nomelancamentosFL = :nome, valorFL = :valor 
    WHERE codigolancamentos = :id");

    $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
    $stmt->bindValue(':valor', $valorConvertido, PDO::PARAM_STR); // ou PDO::PARAM_STR para DECIMAL
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    $stmt->execute();


    echo json_encode(['sucesso' => true, 'mensagem' => 'Tipo atualizado com sucesso.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
