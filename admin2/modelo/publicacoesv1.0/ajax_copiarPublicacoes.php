<?php
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $ids = $_POST['ids'] ?? [];
    $idcurso_destino  = isset($_POST['idcurso']) ? (int)$_POST['idcurso'] : 0;
    $idmodulo_destino = isset($_POST['idmodulo']) ? (int)$_POST['idmodulo'] : 0;

    $idcurso_origem   = isset($_POST['idcurso_origem']) ? (int)$_POST['idcurso_origem'] : null;
    $idmodulo_origem  = isset($_POST['idorigem_modulo']) ? (int)$_POST['idorigem_modulo'] : null;

    if (!$ids || !$idcurso_destino || !$idmodulo_destino) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos para cópia.']);
        exit;
    }

    $pdo = config::connect();
    $pdo->beginTransaction();

    // Buscar dados básicos das publicações originais
    $inplace = implode(',', array_fill(0, count($ids), '?'));
    $sqlSrc = "SELECT codigopublicacoes, aula, ordem, visivel
               FROM new_sistema_publicacoes_PJA
               WHERE codigopublicacoes IN ($inplace)";
    $stmt = $pdo->prepare($sqlSrc);
    foreach ($ids as $k => $v) $stmt->bindValue($k + 1, (int)$v, PDO::PARAM_INT);
    $stmt->execute();
    $origs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $copiadas = 0;
    $ignoradas = 0;

    // (Opcional) Evitar duplicidade: checar existência no destino
    $check = $pdo->prepare("SELECT 1 FROM a_aluno_publicacoes_cursos
                             WHERE idpublicacaopc = :idpub
                               AND idcursopc      = :idcurso
                               AND idmodulopc     = :idmodulo
                             LIMIT 1");

    $ins = $pdo->prepare("
        INSERT INTO a_aluno_publicacoes_cursos
            (idpublicacaopc, atividadepc, idcursopc, idturmapc, idmoduloorigem, idmodulopc,
             publicopc, visivelpc,  aulapc, ordempc, datapc, horapc, destaquepc,
             dataatualizacaopc, horaatualizacaopc)
        VALUES
            (:idpublicacaopc, 0, :idcursodest, NULL, :idmodorig, :idmoddest,
             1, :visivelpc, :aulapc, :ordempc, CURDATE(), CURTIME(), 0,
             CURDATE(), CURTIME())
    ");

    foreach ($origs as $o) {
        $idpub = (int)$o['codigopublicacoes'];

        // Já existe?
        $check->execute([
            ':idpub'   => $idpub,
            ':idcurso' => $idcurso_destino,
            ':idmodulo' => $idmodulo_destino
        ]);
        if ($check->fetchColumn()) {
            $ignoradas++;
            continue;
        }

        $ins->execute([
            ':idpublicacaopc' => $idpub,
            ':idcursodest'    => $idcurso_destino,
            ':idmodorig'      => $idmodulo_origem,
            ':idmoddest'      => $idmodulo_destino,
            ':visivelpc'      => (int)$o['visivel'],
            ':aulapc'         => (int)$o['aula'],
            ':ordempc'        => (int)$o['ordem'],
        ]);

        $copiadas++;
    }

    $pdo->commit();
    echo json_encode(['sucesso' => true, 'copiadas' => $copiadas, 'ignoradas' => $ignoradas]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao copiar publicações.']);
}
