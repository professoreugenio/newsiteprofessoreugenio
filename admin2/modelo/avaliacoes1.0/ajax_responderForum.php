<?php

/**
 * ajax_responderForum.php
 * Salva a resposta do professor para o aluno na tabela a_curso_forum_comentarios.
 * Entrada esperada (POST):
 *  - codigoForum (int)
 *  - respostaProfessor (string)
 *
 * Saída (JSON):
 *  - { ok: true } em sucesso
 *  - { ok: false, msg: '...' } em erro
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
require_once APP_ROOT . '/conexao/class.conexao.php';
require_once APP_ROOT . '/autenticacao.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * Obtém o ID do usuário logado pelos cookies já utilizados no seu sistema.
 * Retorna 0 se não identificado (permite gravar como "anônimo" se você quiser).
 */
function getLoggedUserId(): int
{
    try {
        if (!empty($_COOKIE['adminstart'])) {
            $dec = encrypt($_COOKIE['adminstart'], 'd');
        } elseif (!empty($_COOKIE['startusuario'])) {
            $dec = encrypt($_COOKIE['startusuario'], 'd');
        } else {
            return 0;
        }
        if (!$dec || strpos($dec, '&') === false) return 0;
        $exp = explode('&', $dec);
        $id  = (int)($exp[0] ?? 0);
        return $id > 0 ? $id : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

try {
    $codigoForum = isset($_POST['codigoForum']) ? (int)$_POST['codigoForum'] : 0;
    $resposta    = isset($_POST['respostaProfessor']) ? trim((string)$_POST['respostaProfessor']) : '';

    if ($codigoForum <= 0) {
        throw new Exception('ID do fórum inválido.');
    }
    if ($resposta === '') {
        throw new Exception('Informe a resposta.');
    }

    $con = config::connect();

    // 1) Busca o aluno (destinatário) a partir do fórum
    $st = $con->prepare("SELECT idusuarioCF FROM a_curso_forum WHERE codigoforum = :id LIMIT 1");
    $st->bindParam(':id', $codigoForum, PDO::PARAM_INT);
    $st->execute();
    $forumRow = $st->fetch(PDO::FETCH_ASSOC);
    if (!$forumRow) {
        throw new Exception('Fórum não encontrado.');
    }

    $idAluno = (int)($forumRow['idusuarioCF'] ?? 0); // idusuariopara
    if ($idAluno <= 0) {
        // Se quiser permitir enviar mesmo sem id do aluno, remova essa validação.
        throw new Exception('Aluno destinatário não identificado.');
    }

    // 2) Identifica o professor (remetente)
    $idProfessor = getLoggedUserId(); // idusuariode
    if ($idProfessor <= 0) {
        // Se preferir bloquear quando não logado, descomente a linha abaixo:
        // throw new Exception('Sessão expirada. Faça login novamente.');
        // Caso contrário, permite gravar com 0 (anônimo/sistema).
        $idProfessor = 0;
    }

    // 3) Grava comentário
    $data = date('Y-m-d');
    $hora = date('H:i:s');

    $ins = $con->prepare("
        INSERT INTO a_curso_forum_comentarios
            (idusuariode, idusuariopara, textofc, datafc, horafc)
        VALUES
            (:de, :para, :texto, :data, :hora)
    ");
    $ins->bindParam(':de',    $idProfessor, PDO::PARAM_INT);
    $ins->bindParam(':para',  $idAluno, PDO::PARAM_INT);
    $ins->bindParam(':texto', $resposta, PDO::PARAM_STR);
    $ins->bindParam(':data',  $data, PDO::PARAM_STR);
    $ins->bindParam(':hora',  $hora, PDO::PARAM_STR);
    $ins->execute();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
