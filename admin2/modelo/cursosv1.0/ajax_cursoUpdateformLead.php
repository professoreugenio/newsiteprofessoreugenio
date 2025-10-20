<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro ao atualizar.'];

try {
    if (!isset($_POST['curso_id'])) {
        throw new Exception("ID do curso não recebido.");
    }

    $curso_id = encrypt(htmlspecialchars($_POST['curso_id']), 'd');



    $lead         = $_POST['lead'] ?? '';
    $detalhes     = $_POST['detalhes'] ?? '';
    $detalhes     = htmlspecialchars($detalhes, ENT_QUOTES);
    $sobreocurso  = $_POST['sobreocurso'] ?? '';
    $sobreocurso  = htmlspecialchars($sobreocurso, ENT_QUOTES);

    // Validação simples (adicione mais conforme necessário)
    if ($curso_id === '') {
        throw new Exception("O ID do curso é obrigatório.");
    }

    // Atualização
    $query = $con->prepare("UPDATE new_sistema_cursos SET lead = :lead, detalhes = :detalhes, sobreocurso = :sobre WHERE codigocursos = :id");
    $query->bindParam(':lead', $lead);
    $query->bindParam(':detalhes', $detalhes);
    $query->bindParam(':sobre', $sobreocurso);
    $query->bindParam(':id', $curso_id);

    if ($query->execute()) {
        $response['sucesso'] = true;
        $response['mensagem'] = "Curso atualizado com sucesso!";
    } else {
        throw new Exception("Não foi possível atualizar o curso.");
    }
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
