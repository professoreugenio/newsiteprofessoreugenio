<?php define('BASEPATH', true);
include '../../../conexao/class.conexao.php';
include '../../../autenticacao.php';
header('Content-Type: application/json; charset=utf-8');


try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);


    if (!isset($data['chaveturma']) || empty($data['chaveturma'])) {
        echo json_encode(['status' => 'erro', 'msg' => 'Turma não informada.']);
        exit;
    }
    if (!isset($data['alunos']) || !is_array($data['alunos']) || count($data['alunos']) === 0) {
        echo json_encode(['status' => 'erro', 'msg' => 'Nenhum aluno selecionado.']);
        exit;
    }


    $chaveTurma = $data['chaveturma'];

    $query = $con->prepare("SELECT * FROM new_sistema_cursos_turmas WHERE chave = :chave ");
    $query->bindParam(":chave", $chaveTurma);
    $query->execute();
    $rw = $query->fetch(PDO::FETCH_ASSOC);
    $idcurso = $rw['codcursost'];

    $alunos = array_map('intval', $data['alunos']); // segurança

    $pdo = config::connect();
    $pdo->beginTransaction();

    // Prazo = hoje + 2 dias
    $hoje = new DateTime();
    $prazo = (clone $hoje)->modify('+2 days');

    // Prepara consultas
    $stmtCheck = $pdo->prepare("
        SELECT codigoinscricao 
        FROM new_sistema_inscricao_PJA
        WHERE codigousuario = :uid AND chaveturma = :chave
        LIMIT 1
    ");
    $stmtInsert = $pdo->prepare("
        INSERT INTO new_sistema_inscricao_PJA
          (codigousuario, codcurso_ip, chaveturma, ano_ip, data_ins, hora_ins, dataprazosi,datarenovacao,horarenovacao, visivel_ci)
        VALUES
          (:uid, :idcurso, :chave, :ano_ip,:data_ins,:hora_ins, :dataprazo, :datarenovacao,:horarenovacao, 1)
    ");
    $stmtUpdatePrazo = $pdo->prepare("
        UPDATE new_sistema_inscricao_PJA
        SET dataprazosi = :dataprazo
        WHERE codigoinscricao = :id
    ");

    $countInseridos = 0;
    $countAtualizados = 0;

    foreach ($alunos as $uid) {
        $stmtCheck->execute([':uid' => $uid, ':chave' => $chaveTurma]);
        $existe = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            // Atualiza prazo
            $stmtUpdatePrazo->execute([
                ':dataprazo' => $prazo->format('Y-m-d'),
                ':id'        => $existe['codigoinscricao']
            ]);
            $countAtualizados++;
        } else {
            // Insere inscrição
            $stmtInsert->execute([
                ':uid'       => $uid,
                ':idcurso'   => $idcurso,
                ':chave'     => $chaveTurma,
                ':ano_ip'     =>  $hoje->format('Y'),
                ':data_ins'  => $hoje->format('Y-m-d'),
                ':hora_ins'  => $hora,
                ':dataprazo' => $prazo->format('Y-m-d'),
                ':datarenovacao' => $hoje->format('Y-m-d'),
                ':horarenovacao' => $hora,
            ]);
            $countInseridos++;
        }
    }

    $pdo->commit();
    echo json_encode([
        'status' => 'ok',
        'msg'    => "Processo concluído: $countInseridos inserido(s), $countAtualizados atualizado(s) (prazo +2 dias)."
    ]);
} catch (Exception $e) {
    if (!empty($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'erro', 'msg' => 'Erro: ' . $e->getMessage()]);
}
