<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $chave = $input['chave'] ?? '';

    if (empty($chave)) {
        throw new Exception("Turma não informada.");
    }

    // Consulta a data final da turma
    $stmt = config::connect()->prepare("SELECT datafimst FROM new_sistema_cursos_turmas WHERE chave = :chave");
    $stmt->bindParam(':chave', $chave);
    $stmt->execute();
    $dataFim = $stmt->fetchColumn();

    if (!$dataFim) {
        throw new Exception("Data final da turma não encontrada.");
    }

    $novaData = (new DateTime($dataFim))->modify('+2 days')->format('Y-m-d');

    // Atualiza todos os alunos da turma com a nova data
    $update = config::connect()->prepare("
        UPDATE new_sistema_inscricao_PJA
        SET dataprazosi = :novaData
        WHERE chaveturma = :chave
    ");
    $update->bindParam(':novaData', $novaData);
    $update->bindParam(':chave', $chave);
    $update->execute();

    echo json_encode([
        'status' => 'ok',
        'msg' => "Prazo atualizado para {$novaData} em " . $update->rowCount() . " aluno(s)."
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'erro',
        'msg' => $e->getMessage()
    ]);
}
