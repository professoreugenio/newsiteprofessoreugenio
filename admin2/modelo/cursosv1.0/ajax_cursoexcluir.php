<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

$response = ['sucesso' => false, 'mensagem' => 'Erro ao excluir o curso.'];

try {
    if (!isset($_POST['idCurso'])) {
        throw new Exception("ID do curso não recebido.");
    }

    $idCurso = encrypt($_POST['idCurso'], 'd');

    if (!is_numeric($idCurso)) {
        throw new Exception("ID inválido.");
    }

    // Verifica se existe
    $check = $con->prepare("SELECT COUNT(*) FROM new_sistema_categorias_PJA WHERE codigocategorias = :id");
    $check->bindParam(':id', $idCurso, PDO::PARAM_INT);
    $check->execute();

    if ($check->fetchColumn() == 0) {
        throw new Exception("Curso não encontrado.");
    }

    // Exclui o curso
    $delete = $con->prepare("DELETE FROM new_sistema_categorias_PJA WHERE codigocategorias = :id");
    $delete->bindParam(':id', $idCurso, PDO::PARAM_INT);
    $delete->execute();
    $delete = $con->prepare("DELETE FROM new_sistema_cursos_turmas WHERE codcursost = :id");
    $delete->bindParam(':id', $idCurso, PDO::PARAM_INT);
    $delete->execute();

    $response['sucesso'] = true;
    $response['mensagem'] = "Curso excluído com sucesso!";
} catch (Exception $e) {
    $response['mensagem'] = $e->getMessage();
}

echo json_encode($response);
