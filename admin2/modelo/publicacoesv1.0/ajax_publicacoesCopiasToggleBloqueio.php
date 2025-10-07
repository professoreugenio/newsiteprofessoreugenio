<?php

/**
 * Alterna bloqueio de publicação (aulaliberadapc) e atualiza data/hora de atualização.
 * Espera JSON no body:
 * {
 *   "idcurso": <int>,
 *   "idmodulo": <int>,
 *   "idlinha": <int>,           // PK: a_aluno_publicacoes_cursos.codigopublicacoescursos
 *   "idpublicacaopc": <int>     // FK: new_sistema_publicacoes_PJA.codigopublicacoes
 * }
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    // ---- Lê payload (JSON) com fallback para x-www-form-urlencoded ----
    $raw = file_get_contents('php://input');
    $data = [];
    if ($raw !== '' && $raw !== false) {
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            // fallback: tenta interpretar como querystring
            parse_str((string)$raw, $data);
        }
    } else {
        // fallback: usa $_POST
        $data = $_POST;
    }

    $idCurso  = (int)($data['idcurso'] ?? 0);
    $idModulo = (int)($data['idmodulo'] ?? 0);
    $idLinha  = (int)($data['idlinha'] ?? 0);
    $idPub    = (int)($data['idpublicacaopc'] ?? 0);

    if ($idCurso <= 0 || $idModulo <= 0 || $idLinha <= 0 || $idPub <= 0) {
        throw new Exception('Parâmetros inválidos.');
    }

    $pdo = config::connect();

    // ---- Verifica registro atual e estado de bloqueio ----
    $stSel = $pdo->prepare("
        SELECT aulaliberadapc
          FROM a_aluno_publicacoes_cursos
         WHERE codigopublicacoescursos = :idlinha
           AND idcursopc   = :idc
           AND idmodulopc  = :idm
           AND idpublicacaopc = :idpub
         LIMIT 1
    ");
    $stSel->execute([
        ':idlinha' => $idLinha,
        ':idc'     => $idCurso,
        ':idm'     => $idModulo,
        ':idpub'   => $idPub,
    ]);

    $row = $stSel->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception('Registro não encontrado.');
    }

    $bloqAtual = (int)$row['aulaliberadapc'] === 1 ? 1 : 0;
    $novo      = $bloqAtual ? 0 : 1;

    // ---- Atualiza bloqueio + data/hora de atualização ----
    $stUpd = $pdo->prepare("
        UPDATE a_aluno_publicacoes_cursos
           SET aulaliberadapc = :novo,
               dataatualizacaopc = CURDATE(),
               horaatualizacaopc = CURTIME()
         WHERE codigopublicacoescursos = :idlinha
           AND idcursopc   = :idc
           AND idmodulopc  = :idm
           AND idpublicacaopc = :idpub
         LIMIT 1
    ");
    $stUpd->execute([
        ':novo'    => $novo,
        ':idlinha' => $idLinha,
        ':idc'     => $idCurso,
        ':idm'     => $idModulo,
        ':idpub'   => $idPub,
    ]);

    echo json_encode(['ok' => true, 'bloqueado' => $novo], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(200); // mantém 200 para o fetch tratar a resposta JSON
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
