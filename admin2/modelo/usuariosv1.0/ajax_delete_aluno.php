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

    // Transação
    $pdo->beginTransaction();

    // Apaga registros relacionados nas tabelas indicadas
    // a_curso_questionario_resposta.idalunoqr
    $pdo->prepare("DELETE FROM a_curso_questionario_resposta WHERE idalunoqr = :id")->execute([':id' => $id]);

    // a_site_registraacessos.idusuariora
    $pdo->prepare("DELETE FROM a_site_registraacessos WHERE idusuariora = :id")->execute([':id' => $id]);

    // a_aluno_andamento_aula.idalunoaa
    $pdo->prepare("DELETE FROM a_aluno_andamento_aula WHERE idalunoaa = :id")->execute([':id' => $id]);

    // new_sistema_inscricao_PJA.codigousuario
    $pdo->prepare("DELETE FROM new_sistema_inscricao_PJA WHERE codigousuario = :id")->execute([':id' => $id]);

    // a_curso_forum.idusuarioCF
    $pdo->prepare("DELETE FROM a_curso_forum WHERE idusuarioCF = :id")->execute([':id' => $id]);

    // Por último: cadastro
    $pdo->prepare("DELETE FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1")->execute([':id' => $id]);

    $pdo->commit();
    echo json_encode(['status' => 'ok', 'msg' => 'Aluno e dados relacionados excluídos com sucesso.']);
} catch (Exception $e) {
    if (!empty($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'erro', 'msg' => 'Erro: ' . $e->getMessage()]);
}
