<?php

/**
 * atendimento1.0/ajax_AtendimentoInsere.php
 * Insere um registro de atendimento (etapa) para um aluno e retorna JSON.
 * Espera via POST: idaluno (int), idetapa (int)
 * Saída: {"ok":1,"idAtendimento":123} em caso de sucesso.
 */

header('Content-Type: application/json; charset=utf-8');

try {
    /* Cabeçalho padrão AJAX */
    define('BASEPATH', true);
    define('APP_ROOT', dirname(__DIR__, 3));
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    // Apenas POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => 0, 'erro' => 'Método não permitido.']);
        exit;
    }

    // Captura e saneamento
    $idaluno = isset($_POST['idaluno']) ? (int)$_POST['idaluno'] : 0;
    $idetapa = isset($_POST['idetapa']) ? (int)$_POST['idetapa'] : 0;

    if ($idaluno <= 0 || $idetapa <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => 0, 'erro' => 'Parâmetros inválidos.']);
        exit;
    }

    // Conexão
    $pdo = config::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Valida existência do aluno
    $stmt = $pdo->prepare("SELECT codigocadastro FROM new_sistema_cadastro WHERE codigocadastro = :id LIMIT 1");
    $stmt->bindValue(':id', $idaluno, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['ok' => 0, 'erro' => 'Aluno não encontrado.']);
        exit;
    }

    // Valida existência da etapa
    $stmt = $pdo->prepare("SELECT codigoetapas FROM a_aluno_atendimento_etapas WHERE codigoetapas = :e LIMIT 1");
    $stmt->bindValue(':e', $idetapa, PDO::PARAM_INT);
    $stmt->execute();
    if (!$stmt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['ok' => 0, 'erro' => 'Etapa não encontrada.']);
        exit;
    }

    // (Opcional) Anti-duplo-clique: evita duplicar o mesmo registro nos últimos 10s
    // Descomente se quiser ativar.
    /*
    $dup = $pdo->prepare("
        SELECT 1
        FROM a_aluno_atendimento
        WHERE idaluno = :a AND idetapaaa = :e
          AND dataat = CURDATE()
          AND TIMESTAMPDIFF(SECOND, CONCAT(dataat,' ',horaat), NOW()) <= 10
        LIMIT 1
    ");
    $dup->execute([':a' => $idaluno, ':e' => $idetapa]);
    if ($dup->fetchColumn()) {
        echo json_encode(['ok' => 1, 'duplicado' => 1]); // já houve inserção recente
        exit;
    }
    */

    // Inserção
    $pdo->beginTransaction();

    $ins = $pdo->prepare("
        INSERT INTO a_aluno_atendimento (idaluno, idetapaaa, dataat, horaat)
        VALUES (:idaluno, :idetapa, CURDATE(), CURTIME())
    ");
    $ins->bindValue(':idaluno', $idaluno, PDO::PARAM_INT);
    $ins->bindValue(':idetapa', $idetapa, PDO::PARAM_INT);
    $ins->execute();

    $idAt = (int)$pdo->lastInsertId();
    $pdo->commit();

    // Retorno
    $resp = ['ok' => 1, 'idAtendimento' => $idAt];

    // Devolve (opcional) ids encryptados para conveniência
    if (function_exists('encrypt')) {
        $resp['idUsuarioEnc'] = encrypt((string)$idaluno, 'e');
        $resp['idEtapaEnc']   = encrypt((string)$idetapa, 'e');
    }

    echo json_encode($resp);
    exit;
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'ok'   => 0,
        'erro' => 'Falha no servidor.',
        'msg'  => $e->getMessage() // se preferir, remova em produção
    ]);
    exit;
}
