<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

    $idCurso  = (int)($data['idcurso']  ?? 0);
    $idModulo = (int)($data['idmodulo'] ?? 0);
    $items    = $data['items'] ?? [];

    if ($idCurso <= 0 || $idModulo <= 0 || !is_array($items) || empty($items)) {
        throw new Exception('Parâmetros inválidos.');
    }

    $pdo = config::connect();
    $pdo->beginTransaction();

    $sql = "
        UPDATE a_aluno_publicacoes_cursos
           SET ordempc = :ordem,
               dataatualizacaopc = CURDATE(),
               horaatualizacaopc = CURTIME()
         WHERE codigopublicacoescursos = :idlinha
           AND idcursopc = :idc
           AND idmodulopc = :idm
    ";
    $st = $pdo->prepare($sql);

    foreach ($items as $it) {
        $idlinha = (int)($it['idlinha'] ?? 0);
        $ordem   = (int)($it['ordempc'] ?? 0);
        if ($idlinha <= 0 || $ordem <= 0) continue;

        $st->execute([
            ':ordem' => $ordem,
            ':idlinha' => $idlinha,
            ':idc' => $idCurso,
            ':idm' => $idModulo,
        ]);
    }

    $pdo->commit();

    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
