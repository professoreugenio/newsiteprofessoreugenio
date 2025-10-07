<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro ao inserir módulo.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Coleta e sanitiza os dados
        $chavem       = trim($_POST['chavem'] ?? '');
        $modulo       = trim($_POST['modulo'] ?? '');
        $descricao    = trim($_POST['descricao'] ?? '');
        $valorm       = floatval($_POST['valorm'] ?? 0);
        $valorh       = floatval($_POST['valorh'] ?? 0);
        $nraulasm     = intval($_POST['nraulasm'] ?? 0);
        $ordemm       = intval($_POST['ordemm'] ?? 1);
        $bgcolor      = $_POST['bgcolor'] ?? '#ffffff';
        $visivelm     = isset($_POST['visivelm']) ? 1 : 0;
        $visivelhome  = isset($_POST['visivelhome']) ? 1 : 0;

        if (empty($modulo) || empty($chavem)) {
            throw new Exception('Preencha todos os campos obrigatórios.');
        }

        // Geração de chave única para o módulo
        $chaveModulo = md5(uniqid('mod', true));

        // Inserção no banco
        $sql = "INSERT INTO new_sistema_modulos_PJA 
                    (chavem, modulo, descricao, valorm, valorh, nraulasm, ordemm, bgcolor, visivelm, visivelhome, chavemodulo)
                VALUES
                    (:chavem, :modulo, :descricao, :valorm, :valorh, :nraulasm, :ordemm, :bgcolor, :visivelm, :visivelhome, :chavemodulo)";

        $stmt = config::connect()->prepare($sql);
        $stmt->bindParam(':chavem', $chavem);
        $stmt->bindParam(':modulo', $modulo);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':valorm', $valorm);
        $stmt->bindParam(':valorh', $valorh);
        $stmt->bindParam(':nraulasm', $nraulasm, PDO::PARAM_INT);
        $stmt->bindParam(':ordemm', $ordemm, PDO::PARAM_INT);
        $stmt->bindParam(':bgcolor', $bgcolor);
        $stmt->bindParam(':visivelm', $visivelm, PDO::PARAM_INT);
        $stmt->bindParam(':visivelhome', $visivelhome, PDO::PARAM_INT);
        $stmt->bindParam(':chavemodulo', $chaveModulo);

        if ($stmt->execute()) {
            $response = [
                'sucesso' => true,
                'mensagem' => 'Módulo inserido com sucesso!'
            ];
        } else {
            $response['mensagem'] = 'Falha ao inserir no banco.';
        }
    } catch (Exception $e) {
        $response['mensagem'] = $e->getMessage();
    }
} else {
    $response['mensagem'] = 'Requisição inválida.';
}

echo json_encode($response);
