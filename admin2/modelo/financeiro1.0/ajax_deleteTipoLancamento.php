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

    // Validação do ID
    $id = $_POST['id'] ?? null;
    if (empty($id) || !is_numeric($id)) {
        throw new Exception('ID inválido.');
    }

    // Verifica se o tipo está sendo usado em lançamentos
    $verifica = $con->prepare("SELECT COUNT(*) FROM a_curso_financeiro WHERE idLancamentoCF = :id");
    $verifica->bindValue(':id', $id, PDO::PARAM_INT);
    $verifica->execute();
    $usado = $verifica->fetchColumn();

    if ($usado > 0) {
        throw new Exception('Este tipo está vinculado a lançamentos e não pode ser excluído.');
    }

    // Exclusão
    $delete = $con->prepare("DELETE FROM a_curso_financeiroLancamentos WHERE codigolancamentos = :id");
    $delete->bindValue(':id', $id, PDO::PARAM_INT);
    $delete->execute();

    echo json_encode(['sucesso' => true, 'mensagem' => 'Tipo excluído com sucesso.']);
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => $e->getMessage()]);
}
