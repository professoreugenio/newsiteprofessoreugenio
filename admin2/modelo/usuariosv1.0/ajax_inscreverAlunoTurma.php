<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $idUsuarioEnc = $_POST['idUsuario'] ?? '';
    $chaveTurma   = trim($_POST['chaveTurma'] ?? '');

    if ($idUsuarioEnc === '' || $chaveTurma === '') {
        echo json_encode(['ok' => false, 'msg' => 'Parâmetros insuficientes.']);
        exit;
    }

    // Decrypt do usuário
    $idUsuarioDec = encrypt($idUsuarioEnc, $action = 'd');
    if (!is_numeric($idUsuarioDec) || (int)$idUsuarioDec <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'ID de usuário inválido.']);
        exit;
    }
    $idUsuario = (int)$idUsuarioDec;

    $pdo = config::connect();
    $pdo->beginTransaction();

    // Verifica turma e obtém codcurso (codcursost)
    $stT = $pdo->prepare("
        SELECT codcursost AS codcurso, chave
        FROM new_sistema_cursos_turmas
        WHERE chave = :ch LIMIT 1
    ");
    $stT->execute([':ch' => $chaveTurma]);
    $turma = $stT->fetch(PDO::FETCH_ASSOC);

    if (!$turma) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'msg' => 'Turma não encontrada.']);
        exit;
    }
    $codcurso_ip = (int)$turma['codcurso'];

    // Evita duplicidade
    $stC = $pdo->prepare("
        SELECT 1
        FROM new_sistema_inscricao_PJA
        WHERE codigousuario = :uid AND chaveturma = :ch
        LIMIT 1
    ");
    $stC->execute([':uid' => $idUsuario, ':ch' => $chaveTurma]);
    if ($stC->fetchColumn()) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'msg' => 'Aluno já está inscrito nesta turma.']);
        exit;
    }

    // Inserção com todos os campos solicitados
    // daya = DAY(NOW()), hora = HOUR(NOW()) ou hora completa? (Mantive hora completa em hora/hora_ci/hora_ins)
    // data_ci/hora_ci: timestamps de criação
    // ano_ip = YEAR(NOW())
    // dataprazosi = NOW() + 2 dias
    // data_ins/hora_ins: timestamps de inscrição
    $stI = $pdo->prepare("
        INSERT INTO new_sistema_inscricao_PJA
            (codigousuario, chaveturma,
             codcurso_ip, data, hora, data_ci, hora_ci, ano_ip, dataprazosi, data_ins, hora_ins)
        VALUES
            (:uid, :ch,
             :codcurso_ip,
             DAY(NOW()),
             DATE_FORMAT(NOW(), '%H:%i:%s'),
             CURDATE(),
             DATE_FORMAT(NOW(), '%H:%i:%s'),
             YEAR(NOW()),
             DATE_ADD(CURDATE(), INTERVAL 5 DAY),
             CURDATE(),
             DATE_FORMAT(NOW(), '%H:%i:%s'))
    ");
    $stI->execute([
        ':uid'         => $idUsuario,
        ':ch'          => $chaveTurma,
        ':codcurso_ip' => $codcurso_ip,
    ]);

    $pdo->commit();
    echo json_encode(['ok' => true, 'msg' => 'Inscrição realizada com sucesso.']);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['ok' => false, 'msg' => 'Erro no banco de dados.']);
    // opcional: logar $e->getMessage()
}
