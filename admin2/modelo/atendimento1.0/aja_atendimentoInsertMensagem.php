<?php

/**
 * atendimento1.0/aja_atendimentoInsertMensagem.php
 * Insere ou atualiza mensagens de atendimento (por etapa).
 *
 * Espera via POST:
 *  - iddetapaatm (int)        [obrigatório]
 *  - idtipomsgatm (int: 1/2)  [obrigatório] 1=WhatsApp, 2=E-mail
 *  - tituloatm (string <=50)  [obrigatório]
 *  - textoatm (html)          [obrigatório]
 *  - codigoatendimentomsg (int) [opcional: >0 => UPDATE, senão INSERT]
 *
 * Saída (JSON):
 *  - {"ok":1, "mode":"insert|update", "idMensagem":123, "idEtapa":10, "...Enc": "..."} em sucesso
 *  - {"ok":0, "erro":"..."} em erro
 */

header('Content-Type: application/json; charset=utf-8');

try {
    /* Cabeçalho padrão / dependências */
    define('BASEPATH', true);
    // Ajuste a raiz do projeto conforme sua estrutura (mantido igual ao outro AJAX)
    define('APP_ROOT', dirname(__DIR__, 3));
    require_once APP_ROOT . '/conexao/class.conexao.php';
    require_once APP_ROOT . '/autenticacao.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => 0, 'erro' => 'Método não permitido.']);
        exit;
    }

    // Captura parâmetros
    $iddetapaatm = isset($_POST['iddetapaatm']) ? (int)$_POST['iddetapaatm'] : 0;
    $idtipomsg   = isset($_POST['idtipomsgatm']) ? (int)$_POST['idtipomsgatm'] : 0;
    $titulo      = isset($_POST['tituloatm']) ? trim((string)$_POST['tituloatm']) : '';
    $texto       = isset($_POST['textoatm']) ? (string)$_POST['textoatm'] : '';
    $idMsg       = isset($_POST['codigoatendimentomsg']) ? (int)$_POST['codigoatendimentomsg'] : 0;

    // Normaliza título (máx 50 chars)
    if ($titulo !== '') {
        if (function_exists('mb_substr')) $titulo = mb_substr($titulo, 0, 50, 'UTF-8');
        else $titulo = substr($titulo, 0, 50);
    }

    // Validações básicas
    if ($iddetapaatm <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => 0, 'erro' => 'Etapa não informada.']);
        exit;
    }
    if (!in_array($idtipomsg, [1, 2], true)) {
        http_response_code(400);
        echo json_encode(['ok' => 0, 'erro' => 'Tipo de mensagem inválido.']);
        exit;
    }
    if ($titulo === '') {
        http_response_code(400);
        echo json_encode(['ok' => 0, 'erro' => 'Título é obrigatório.']);
        exit;
    }
    if (trim(strip_tags($texto)) === '' && trim($texto) === '') {
        http_response_code(400);
        echo json_encode(['ok' => 0, 'erro' => 'Texto da mensagem é obrigatório.']);
        exit;
    }

    $pdo = config::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Valida existência da etapa
    $ckEt = $pdo->prepare("SELECT codigoetapas FROM a_aluno_atendimento_etapas WHERE codigoetapas = :e LIMIT 1");
    $ckEt->bindValue(':e', $iddetapaatm, PDO::PARAM_INT);
    $ckEt->execute();
    if (!$ckEt->fetchColumn()) {
        http_response_code(404);
        echo json_encode(['ok' => 0, 'erro' => 'Etapa não encontrada.']);
        exit;
    }

    if ($idMsg > 0) {
        // UPDATE (não altera data/hora)
        $ck = $pdo->prepare("SELECT codigoatendimentomsg, iddetapaatm FROM a_aluno_atendimento_mensagem WHERE codigoatendimentomsg = :id LIMIT 1");
        $ck->bindValue(':id', $idMsg, PDO::PARAM_INT);
        $ck->execute();
        $row = $ck->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            http_response_code(404);
            echo json_encode(['ok' => 0, 'erro' => 'Mensagem não encontrada.']);
            exit;
        }

        $up = $pdo->prepare("
            UPDATE a_aluno_atendimento_mensagem
               SET idtipomsgatm = :tipo,
                   tituloatm    = :titulo,
                   textoatm     = :texto
             WHERE codigoatendimentomsg = :id
             LIMIT 1
        ");
        $up->bindValue(':tipo',   $idtipomsg, PDO::PARAM_INT);
        $up->bindValue(':titulo', $titulo,    PDO::PARAM_STR);
        $up->bindValue(':texto',  $texto,     PDO::PARAM_STR);
        $up->bindValue(':id',     $idMsg,     PDO::PARAM_INT);
        $up->execute();

        $resp = ['ok' => 1, 'mode' => 'update', 'idMensagem' => $idMsg, 'idEtapa' => $iddetapaatm];
    } else {
        // INSERT (preenche data/hora atuais)
        $pdo->beginTransaction();

        $ins = $pdo->prepare("
            INSERT INTO a_aluno_atendimento_mensagem
                (iddetapaatm, idtipomsgatm, tituloatm, textoatm, dataatm, horaatm)
            VALUES
                (:etapa, :tipo, :titulo, :texto, CURDATE(), CURTIME())
        ");
        $ins->bindValue(':etapa',  $iddetapaatm, PDO::PARAM_INT);
        $ins->bindValue(':tipo',   $idtipomsg,   PDO::PARAM_INT);
        $ins->bindValue(':titulo', $titulo,      PDO::PARAM_STR);
        $ins->bindValue(':texto',  $texto,       PDO::PARAM_STR);
        $ins->execute();

        $newId = (int)$pdo->lastInsertId();
        $pdo->commit();

        $resp = ['ok' => 1, 'mode' => 'insert', 'idMensagem' => $newId, 'idEtapa' => $iddetapaatm];
    }

    // Acrescenta encrypts (opcional, se existir)
    if (function_exists('encrypt')) {
        $resp['idEtapaEnc']     = encrypt((string)$iddetapaatm, 'e');
        if (!empty($resp['idMensagem'])) {
            $resp['idMensagemEnc'] = encrypt((string)$resp['idMensagem'], 'e');
        }
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
        'msg'  => $e->getMessage() // remova em produção se preferir
    ]);
    exit;
}
